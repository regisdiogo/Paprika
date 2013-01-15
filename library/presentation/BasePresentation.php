<?php
namespace presentation;

if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

use annotation\Annotation;
use annotation\SupportPresentation;
use annotation\BaseEntity;
use business\BaseBusiness;
use core\JavascriptManager;
use core\exception\PresentationException;
use core\Messages;
use core\Config;
use helper\JavascriptHelper;
use helper\HtmlHelper;
use helper\ReflectionHelper;
use helper\DateHelper;
use presentation\component\ListViewCommandColumn;
use presentation\component\ListViewTextColumn;
use presentation\component\ListViewControl;
use presentation\HttpResponse;

abstract class BasePresentation extends SupportPresentation {

	const LIST_VIEW = '_ppk_listView';
	const FORM_ELEMENTS = '_ppk_elements';
	const FORM_INPUT = '_ppk_formInput';
	const FORM_PARAMS = '_ppk_formParams';
	const FORM_SUBMIT = '_ppk_submit';
	const FORM_CANCEL = '_ppk_reset';
	const FORM_LABEL = '_ppk_label';
	const TEMPLATE_INNER_FILE = '_ppk_templateInnerFile';
	const EXCEPTION_VALIDATION = '_ppk_exceptionValidation';
	const OUTPUT_MESSAGE = '_ppk_outputMessage';
	const PAGE_TITLE = '_ppk_pageTitle';
	const PAGE_KEYWORDS = '_ppk_pageKeywords';
	const PAGE_ALLOW_ROBOTS = '_ppk_pageAllowRobots';

	/**
	 * @var \business\BaseBusiness
	 */
	private $business;
	/**
	 * @var \entity\BaseEntity
	 */
	private $entity;
	private $listViewColumns;

	public function __construct() {
		$this->setPageAllowRobots(true);
	}

	/**
	 * Base presentation just to load a page
	 * @param HttpRequest $httpRequest
	 * @return HttpResponse $httpResponse
	 * @throws PresentationException
	 */
	public function main(HttpRequest $httpRequest) {
		$httpResponse = new HttpResponse();
		try {
			return $httpResponse;
		} catch (\Exception $e) {
			throw new PresentationException($e);
		}
	}

	/**
	 * Base presentation to create new entity
	 * @param HttpRequest $httpRequest
	 * @return HttpResponse $httpResponse
	 * @throws PresentationException
	 */
	public function create(HttpRequest $httpRequest, $customKeys = null, $showNeedsConfirmation = true, $submitCustomText = null) {
		$httpResponse = new HttpResponse();
		try {
			if ($httpRequest->getRequest()) {
				try {
					$this->castRequestToEntity($this->entity, $httpRequest->getRequest());
					$this->business->save($this->entity);

					$httpResponse->setRedirectURL(\App::getInstance()->getURIPrevious());
					$httpResponse->setMessage(Messages::OPERATION_SUCCESSFUL);
					$httpResponse->setSuccess(true);

				} catch (\core\exception\ValidationException $e) {
					$this->handleValidationException($e, $httpRequest, $httpResponse);
				}

				if ($httpRequest->getPostAjax()) {
					return $httpResponse;
				}
			}

			$formAnnotations = $this->extractFormAnnotationsFromEntity($this->entity, $customKeys, $showNeedsConfirmation);

			$formAction = $this->getFormAction($httpRequest);

			if (!isset($submitCustomText))
				$submitCustomText = __FUNCTION__;

			$htmlForm = $this->generateHtmlForm(get_class($this->entity), $formAnnotations, $submitCustomText, $formAction, false, null, $httpRequest->getPostAjax());

			$httpResponse->setContent($htmlForm);

			return $httpResponse;

		} catch (\Exception $e) {
			throw new PresentationException($e);
		}
	}

	/**
	 * Base presentation to update entity
	 * @param HttpRequest $httpRequest
	 * @return HttpResponse $httpResponse
	 * @throws PresentationException
	 */
	public function update(HttpRequest $httpRequest, $customKeys = null, $showNeedsConfirmation = true, $submitCustomText = null) {
		$httpResponse = new HttpResponse();
		try {
			$urlParams = $httpRequest->getUrlParams();
			$identityProperty = $this->getIdentityProperty($this->entity);

			if ($urlParams[$identityProperty]) {
				$this->entity = $this->business->getItem($urlParams[$identityProperty]);
			}

			if (!$urlParams[$identityProperty] || !$this->entity) {
				throw new \Exception(Messages::OBJECT_NOT_FOUND);
			}

			if ($httpRequest->getRequest()) {
				try {
					$this->castRequestToEntity($this->entity, $httpRequest->getRequest());
					$this->business->save($this->entity);
					$this->business->commit();

					$httpResponse->setRedirectURL(\App::getInstance()->getURIPrevious());
					$httpResponse->setMessage(Messages::OPERATION_SUCCESSFUL);
					$httpResponse->setSuccess(true);

					return $httpResponse;

				} catch (\core\exception\ValidationException $e) {
					$this->handleValidationException($e, $httpRequest, $httpResponse);
				}

				if ($httpRequest->getPostAjax()) {
					return $httpResponse;
				}
			}

			$formAnnotations = $this->extractFormAnnotationsFromEntity($this->entity, $customKeys, $showNeedsConfirmation);

			$formAction = $this->getFormAction($httpRequest);

			if (!isset($submitCustomText))
				$submitCustomText = __FUNCTION__;

			$htmlForm = $this->generateHtmlForm(get_class($this->entity), $formAnnotations, $submitCustomText, $formAction, false, null, $httpRequest->getPostAjax());

			$httpResponse->setContent($htmlForm);

			return $httpResponse;

		} catch (\Exception $e) {
			throw new PresentationException($e);
		}
	}

	public function delete(HttpRequest $httpRequest) {
		$httpResponse = new HttpResponse();
		try {
			$urlParams = $httpRequest->getUrlParams();
			$identityProperty = $this->getIdentityProperty($this->entity);

			if ($urlParams[$identityProperty]) {
				$this->entity = $this->business->getItem($urlParams[$identityProperty]);
			}

			if (!$urlParams[$identityProperty] || !$this->entity) {
				throw new \Exception(Messages::OBJECT_NOT_FOUND);
			}

			if ($httpRequest->getRequest()) {
				try {
					$this->business->delete($this->entity);
					\App::getInstance()->redirect(\App::getInstance()->getURIPrevious(), Messages::OPERATION_SUCCESSFUL);

				} catch (\core\exception\ValidationException $e) {
					$this->handleValidationException($e, $httpRequest, $httpResponse);
				}
			}

			$formAnnotations = $this->extractFormAnnotationsFromEntity($this->entity);

			$formAction = $this->getFormAction($httpRequest);

			$submitCallback = JavascriptHelper::createAlertConfirm(ucfirst(__FUNCTION__).'?');

			$htmlForm = $this->generateHtmlFormDisabled(get_class($this->entity), $formAnnotations, __FUNCTION__, $formAction, $submitCallback, $httpRequest->getPostAjax());

			\App::getInstance()->setOutputMessage(Messages::CONFIRM_DELETE);

			$httpResponse->setContent($htmlForm);

			return $httpResponse;

		} catch (\Exception $e) {
			throw new PresentationException($e);
		}
	}

	public function getList(HttpRequest $httpRequest, $paginationMaxRows = null) {
		$httpResponse = new HttpResponse();
		try {
			if (!isset($paginationMaxRows)) {
				$paginationMaxRows = \core\Config::PAGINANTION_MAX_ROWS;
			}

			$listViewControl = new ListViewControl($httpRequest->getQueryString(), $this->business->getListCount(), $paginationMaxRows);

			$list = $this->business->getList(
					$listViewControl->getOffset(),
					$paginationMaxRows,
					$listViewControl->getOrderBy(),
					$listViewControl->getOrderDirection()
			);

			$content = null;

			if ($httpRequest->getContentType() == Config::CONTENT_TYPE_TEXT) {
				if ($list) {
					$listView = $this->extractListViewFromEntityList($this->entity, $this->listViewColumns, $list, $listViewControl, true);
					$content[self::LIST_VIEW] = HtmlHelper::createTable($listView[self::LIST_VIEW_HEADER], $listView[self::LIST_VIEW_CONTENT], $listView[self::LIST_VIEW_PAGINATOR]);
				} else {
					\App::getInstance()->setOutputMessage(Messages::NO_RECORDS_FOUND);
					$content[self::LIST_VIEW] = null;
				}

			} else if ($httpRequest->getContentType() == Config::CONTENT_TYPE_JSON) {
				if ($list) {
					$content = $list;
				} else {
					$httpResponse->setSuccess(false);
					$content = Messages::NO_RECORDS_FOUND;
				}
			}

			$httpResponse->setContent($content);

			return $httpResponse;

		} catch (\Exception $e) {
			throw new PresentationException($e);
		}
	}

	public function getItem(HttpRequest $httpRequest) {
		$httpResponse = new HttpResponse();
		try {
			$urlParams = $httpRequest->getUrlParams();
			$identityProperty = $this->getIdentityProperty($this->entity);

			if ($urlParams[$identityProperty]) {
				$this->entity = $this->business->getItem($urlParams[$identityProperty]);
			}

			$content = null;

			if ($httpRequest->getContentType() == Config::CONTENT_TYPE_TEXT) {
				if (!$urlParams[$identityProperty] || !$this->entity) {
					throw new \Exception(Messages::OBJECT_NOT_FOUND);
				}

				$formAnnotations = $this->extractFormAnnotationsFromEntity($this->entity);

				$content = $this->generateHtmlFormDisabled(get_class($this->entity), $formAnnotations, '', '');

				$content[self::FORM_SUBMIT] = null;
				$content[self::FORM_PARAMS] = null;
					
			} else if ($httpRequest->getContentType() == Config::CONTENT_TYPE_JSON) {
				if (!$urlParams[$identityProperty] || !$this->entity) {
					$httpResponse->setSuccess(false);
					$content = Messages::OBJECT_NOT_FOUND;
				} else {
					$content = $this->entity;
				}
			}

			$httpResponse->setContent($content);

			return $httpResponse;

		} catch (\Exception $e) {
			throw new PresentationException($e);
		}
	}

	/**
	 * Handle validation exception
	 * @param $e
	 */
	protected function handleValidationException(\core\exception\ValidationException $e, HttpRequest $httpRequest, HttpResponse $httpResponse) {
		$httpResponse->setSuccess(false);
		if ($httpRequest->getContentType() == Config::CONTENT_TYPE_JSON) {
			$params = array();
			foreach ($e->getParameters() as $key=>$param) {
				$params[strtolower(get_class($this->getEntity())).'-'.$key] = $param;
			}
			$httpResponse->setContent($params);

		} else {
			$httpResponse->setMessage($e->getParameters());
		}
	}

	/**
	 * Cast Request info Entity, using SET defined properties;
	 * @param AnnotatedEntity $entity
	 * @param $request
	 */
	protected function castRequestToEntity($entity, $request) {
		if ($request) {
			$listAnnotations = Annotation::extractAnnotations($entity);
			foreach ($request as $key=>$value) {
				$entityKey = preg_split('/[-]/', $key);
				$method = 'set'.ucfirst($entityKey[1]);
				if (method_exists($entity, $method)) {
					foreach ($listAnnotations[Annotation::PROPERTIES][$entityKey[1]] as $annotation) {
						if ($annotation[Annotation::BEHAVIOR] == Annotation::T_INPUT) {
							if (isset($annotation[Annotation::VALUES][Annotation::T_FORMAT]) && \helper\StringHelper::isNotNull($value)) {
								if ($annotation[Annotation::VALUES][Annotation::T_FORMAT] == \helper\HtmlHelper::INPUT_FORMAT_DATE) {
									$value = \helper\DateHelper::invertDate($value);
									$value = \helper\DateHelper::getAsString($value, \helper\DateHelper::DT_MYSQL);
								} else if ($annotation[Annotation::VALUES][Annotation::T_FORMAT] == \helper\HtmlHelper::INPUT_FORMAT_NUMBER) {
									// TODO Fazer algo
								} else if ($annotation[Annotation::VALUES][Annotation::T_FORMAT] == \helper\HtmlHelper::INPUT_FORMAT_DECIMAL) {
									$value = \helper\StringHelper::convertFromDecimal($value);
								}
							}
							break;
						}
					}
					$reflectionMethod = new \ReflectionMethod($entity, $method);
					$reflectionMethod->invoke($entity, $value);
					continue;
				}
				if ($entityKey[1] == 'confirm') {
					$tmpKey = 'confirm'.$entityKey[2];
					$entity->$tmpKey = $value;
				}
			}
		}
	}

	/**
	 * Generate HTML form with options disabled
	 * @param $className
	 * @param $formAnnotations
	 * @param $operationName
	 * @param $formAction
	 * @param $submitCallback
	 */
	protected function generateHtmlFormDisabled($className, $formAnnotations, $operationName, $formAction, $submitCallback = null) {
		return $this->generateHtmlForm($className, $formAnnotations, $operationName, $formAction, true, $submitCallback);
	}

	/**
	 * Generate HTML form
	 * @param $className
	 * @param $formAnnotations
	 * @param $operationName
	 * @param $formAction
	 * @param $disabled
	 * @param $submitCallback
	 */
	protected function generateHtmlForm($className, $formAnnotations, $operationName, $formAction, $disabled = false, $submitCallback = null, $isJson = false) {
		$htmlForm = array();
		$className = strtolower($className);

		// FORM_ELEMENTS
		if ($formAnnotations) {
			$formElements = array();
			foreach ($formAnnotations as $formAnnotation) {
				$inputId = $className.'-'.$formAnnotation[Annotation::O_REFERS_TO];
				$label = '';
				$input = '';
				if ($formAnnotation[Annotation::VALUES][Annotation::O_TYPE] == HtmlHelper::SELECT) {
					$fetchList = explode('.', $formAnnotation[Annotation::VALUES][Annotation::O_FETCH_LIST]);
					$list = ReflectionHelper::getMethodResultFromName($fetchList[0], $fetchList[1]);
					$optionList = array();
					if (count($list) > 1) {
						$optionList = array(0=>'Selecione');
					}
					foreach ($list as $item) {
						$key = ReflectionHelper::getMethodResultFromInstance($item, 'get'.ucfirst($formAnnotation[Annotation::VALUES][Annotation::O_ID]));
						$value = ReflectionHelper::getMethodResultFromInstance($item, 'get'.ucfirst($formAnnotation[Annotation::VALUES][Annotation::O_VALUE]));
						$optionList[$key] = $value;
					}
					if (!$disabled) {
						$input = HtmlHelper::createSelect($inputId, $inputId, $optionList, $formAnnotation[Annotation::O_VALUE]);
					} else {
						$input = HtmlHelper::createSelectDisabled($inputId, $inputId, $optionList, $formAnnotation[Annotation::O_VALUE]);
					}

				} else if ($formAnnotation[Annotation::VALUES][Annotation::O_TYPE] == HtmlHelper::RADIO) {
					$fetchList = explode('.', $formAnnotation[Annotation::VALUES][Annotation::O_FETCH_LIST]);
					$list = ReflectionHelper::getMethodResultFromName($fetchList[0], $fetchList[1]);
					$optionList = array();
					foreach ($list as $item) {
						$key = ReflectionHelper::getMethodResultFromInstance($item, 'get'.ucfirst($formAnnotation[Annotation::VALUES][Annotation::O_ID]));
						$value = ReflectionHelper::getMethodResultFromInstance($item, 'get'.ucfirst($formAnnotation[Annotation::VALUES][Annotation::O_VALUE]));
						$optionList[$key] = $value;
					}
					if (!$disabled) {
						$input = HtmlHelper::createRadioList($inputId, $inputId, $optionList, $formAnnotation[Annotation::O_VALUE]);
					} else {
						p($optionList);
					}

				} else if ($formAnnotation[Annotation::VALUES][Annotation::O_TYPE] == HtmlHelper::TEXTAREA) {
					$value = $formAnnotation[Annotation::O_VALUE];
					$input = HtmlHelper::createTextArea(
							$inputId,
							$inputId,
							$formAnnotation[Annotation::VALUES][Annotation::O_COLS],
							$formAnnotation[Annotation::VALUES][Annotation::O_ROWS],
							$value
					);

				} else {
					$value = $formAnnotation[Annotation::O_VALUE];
					$style = '';
					if (isset($formAnnotation[Annotation::T_FORMAT]) && isset($formAnnotation[Annotation::T_FORMAT][Annotation::O_TYPE])) {
						if ($formAnnotation[Annotation::T_FORMAT][Annotation::O_TYPE] == HtmlHelper::INPUT_FORMAT_DATE) {
							$style = 'formDate';
							$pattern = '';
							if (isset($formAnnotation[Annotation::T_FORMAT][Annotation::O_PATTERN_JS])) {
								$pattern = $formAnnotation[Annotation::T_FORMAT][Annotation::O_PATTERN_JS];
							}
							if (isset($formAnnotation[Annotation::T_FORMAT][Annotation::O_PATTERN_PHP]) && \helper\StringHelper::isNotNull($value)) {
								$value = DateHelper::getAsString($value, $formAnnotation[Annotation::T_FORMAT][Annotation::O_PATTERN_PHP]);
							}
							JavascriptManager::getInstance()->putCommand(JavascriptHelper::createDatePicker($inputId, $pattern));
						} else if ($formAnnotation[Annotation::T_FORMAT][Annotation::O_TYPE] == HtmlHelper::INPUT_FORMAT_NUMBER) {
							$style = 'formNumber';
						}
						if (isset($formAnnotation[Annotation::T_FORMAT][Annotation::O_MASK])) {
							if ($formAnnotation[Annotation::T_FORMAT][Annotation::O_TYPE] == HtmlHelper::INPUT_FORMAT_DECIMAL) {
								$style = 'formDecimal';
								$mask = strrev($formAnnotation[Annotation::T_FORMAT][Annotation::O_MASK]);
								$mask = preg_replace('/[.]/', ',', $mask, 1);
								JavascriptManager::getInstance()->putCommand(JavascriptHelper::createMask($inputId, $mask, true));
							} else {
								JavascriptManager::getInstance()->putCommand(JavascriptHelper::createMask($inputId, $formAnnotation[Annotation::T_FORMAT][Annotation::O_MASK]));
							}
						}
					}
					if (!$disabled) {
						$input = HtmlHelper::createInput(
								$formAnnotation[Annotation::VALUES][Annotation::O_TYPE],
								$inputId,
								$inputId,
								$value,
								isset($formAnnotation[Annotation::VALUES][Annotation::O_MAX_LENGTH]) ? $formAnnotation[Annotation::VALUES][Annotation::O_MAX_LENGTH] : null,
								null,
								$style
						);
					} else {
						$input = HtmlHelper::createInputDisabled(
								$formAnnotation[Annotation::VALUES][Annotation::O_TYPE],
								$value
						);
					}
				}
				if ($formAnnotation[Annotation::T_LABEL]) {
					$label = HtmlHelper::createLabel(
							$formAnnotation[Annotation::T_LABEL],
							$inputId
					);
				}
				if ($formAnnotation[Annotation::VALUES][Annotation::O_TYPE] == HtmlHelper::INPUT_TYPE_CHECKBOX) {
					$formElements[$inputId] = array(self::FORM_INPUT => $label, self::FORM_LABEL => $input);

				} else {
					$formElements[$inputId] = array(self::FORM_INPUT => $input, self::FORM_LABEL => $label);
				}
			}
			$htmlForm[self::FORM_ELEMENTS] = $formElements;
		}

		// FORM_PARAMS
		$formId = 'form-'.$className.'-'.strtolower($operationName);
		$htmlForm[self::FORM_PARAMS] = HtmlHelper::createFormParameters($formId, $formAction, 'POST', $submitCallback);

		// FORM SUBMIT
		$submitId = $formId.'-'.HtmlHelper::INPUT_TYPE_SUBMIT;
		if (!$isJson) {
			$htmlForm[self::FORM_SUBMIT] = HtmlHelper::createInput(HtmlHelper::INPUT_TYPE_SUBMIT, $submitId, $submitId, ucfirst($operationName));
		} else {
			$htmlForm[self::FORM_SUBMIT] = HtmlHelper::createInput(HtmlHelper::INPUT_TYPE_BUTTON, $submitId, $submitId, ucfirst($operationName));
			JavascriptManager::getInstance()->putCommand(JavascriptHelper::bindAjaxPostInClick($submitId, $formId));
		}

		// FORM CANCEL
		$cancelOperation = 'cancelar';
		$cancelId = $formId.'-'.$cancelOperation;
		$cancelCallback = JavascriptHelper::createLocationHrefChange(\App::getInstance()->getURIPrevious());
		$htmlForm[self::FORM_CANCEL] = HtmlHelper::createInput(HtmlHelper::INPUT_TYPE_BUTTON, $cancelId, $cancelId, ucfirst($cancelOperation), null, $cancelCallback);

		return $htmlForm;
	}

	protected function getFormAction(HttpRequest $httpRequest) {
		return \App::getInstance()->getBasePrefix().$httpRequest->getRequestURI();
	}

	public function getBusiness() {
		return $this->business;
	}

	protected function setBusiness(\business\BaseBusiness $business) {
		$this->business = $business;
		if ($this->business->getRepository() && $this->business->getRepository()->getEntity()) {
			$this->setEntity($this->business->getRepository()->getEntity());
		}
	}

	public function getEntity() {
		return $this->entity;
	}

	protected function setEntity(\entity\BaseEntity $entity) {
		$this->entity = $entity;
	}

	public function getListViewColumns() {
		return $this->listViewColumns;
	}

	protected function setListViewColumns($listViewColumns) {
		$this->listViewColumns = $listViewColumns;
	}

	protected function setPageTitle($pageTitle) {
		global $c;
		$c[self::PAGE_TITLE] = $pageTitle;
	}

	protected function setPageAllowRobots($allowRobots) {
		global $c;
		$c[self::PAGE_ALLOW_ROBOTS] = $allowRobots ? 'all' : 'none';
	}

	protected function setPageKeywords($keywords) {
		global $c;
		$c[self::PAGE_KEYWORDS] = $keywords;
	}
}
?>