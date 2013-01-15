<?php
namespace annotation;

if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

use core\exception\PresentationException;
use core\exception\AnnotationException;
use core\JavascriptManager;
use presentation\component\ListViewCommandColumn;
use presentation\component\ListViewTextColumn;
use presentation\component\ListViewControl;
use helper\HtmlHelper;
use helper\ReflectionHelper;
use helper\JavascriptHelper;

abstract class SupportPresentation extends BaseSupport {

	const LIST_VIEW_HEADER = 'tableHeader';
	const LIST_VIEW_CONTENT = 'tableContent';
	const LIST_VIEW_PAGINATOR = 'tablePaginator';

	/**
	 * Generate html form based on Entity @Input annotated params
	 * @param \entity\BaseEntity $entity
	 * @param $customKeys
	 * @throws AnnotationException
	 * @return
	 */
	protected function extractFormAnnotationsFromEntity(\entity\BaseEntity $entity, $customKeys = null, $showNeedsConfirmation = true) {
		$className = get_class($entity);
		$listAnnotations = Annotation::extractAnnotations($className);

		if (!$listAnnotations) {
			throw new AnnotationException(\core\Messages::CLASS_HAS_NO_ANNOTATIONS);
		}

		$elements = array();

		$listFormBehavior = array(Annotation::T_INPUT);

		foreach ($listAnnotations[Annotation::PROPERTIES] as $key=>$propertiesAnnotations) {
			if ($customKeys && is_array($customKeys) && !in_array($key, $customKeys)) {
				continue;
			}
			foreach ($propertiesAnnotations as $annotation) {
				if (in_array($annotation[Annotation::BEHAVIOR], $listFormBehavior)) {
					$label = '';
					$format = '';
					foreach ($propertiesAnnotations as $subAnnotation) {
						if ($subAnnotation[Annotation::BEHAVIOR] == Annotation::T_LABEL) {
							$label = $subAnnotation[Annotation::VALUES][Annotation::O_VALUE];
						} else if ($subAnnotation[Annotation::BEHAVIOR] == Annotation::T_FORMAT) {
							$format = $subAnnotation[Annotation::VALUES];
						}
					}
					$value = \helper\ReflectionHelper::getMethodResultFromInstance($entity, 'get'.ucfirst($key));
					$elements[] = array(
							Annotation::O_REFERS_TO => $key,
							Annotation::O_VALUE => $value,
							Annotation::T_LABEL => $label,
							Annotation::VALUES => $annotation[Annotation::VALUES],
							Annotation::T_FORMAT => $format
					);
					if ($showNeedsConfirmation) {
						foreach ($propertiesAnnotations as $subAnnotation) {
							if ($subAnnotation[Annotation::BEHAVIOR] == Annotation::T_NEEDS_CONFIRMATION) {
								$tmpKey = 'confirm'.$key;
								$values = $annotation[Annotation::VALUES];
								$values[Annotation::T_NEEDS_CONFIRMATION] = $tmpKey;
								$elements[] = array(
										Annotation::O_REFERS_TO => 'confirm-'.$key,
										Annotation::O_VALUE => isset($entity->$tmpKey) ? $entity->$tmpKey : null,
										Annotation::T_LABEL => $subAnnotation[Annotation::VALUES][Annotation::O_VALUE],
										Annotation::VALUES => $values
								);
								JavascriptManager::getInstance()->putCommand(JavascriptHelper::createPreventCutCopyPaste(strtolower(get_class($entity)).'-'.$key));
								JavascriptManager::getInstance()->putCommand(JavascriptHelper::createPreventCutCopyPaste(strtolower(get_class($entity)).'-confirm-'.$key));
								break;
							}
						}
					}
				}
			}
		}

		return $elements;
	}

	protected function extractListViewFromEntityList($entity, $listViewColumns, $list, ListViewControl $listViewControl, $showPaginator = false) {
		$listAnnotations = Annotation::extractAnnotations($entity);

		if (!$listAnnotations) {
			throw new AnnotationException(\core\Messages::CLASS_HAS_NO_ANNOTATIONS);

		} else if (!$listViewColumns) {
			throw new PresentationException('List of ListViewColumn is required');
		}

		// MAKE HEADER
		$header = array();
		foreach ($listViewColumns as $listViewColumn) {
			$headerLabel = '';
			foreach ($listAnnotations[Annotation::PROPERTIES] as $input=>$annotations) {
				if ($input == $listViewColumn->getName()) {
					foreach ($annotations as $annotation) {
						if ($annotation[Annotation::BEHAVIOR] == Annotation::T_LABEL) {
							$headerLabel = $annotation[Annotation::VALUES][Annotation::O_VALUE];
						} else if ($listViewColumn instanceof ListViewTextColumn) {
							if ($annotation[Annotation::BEHAVIOR] == Annotation::T_INPUT) {
								$listViewColumn->setType($annotation[Annotation::VALUES]);
							}
							if ($annotation[Annotation::BEHAVIOR] == Annotation::T_FORMAT) {
								$listViewColumn->setFormat($annotation[Annotation::VALUES]);
							}
						}
					}
				}
			}
			if ($headerLabel) {
				$linkOrderBy = null;
				if ($listViewColumn instanceof ListViewTextColumn) {
					if ($listViewColumn->getOrderEnabled()) {
						$linkOrderBy = $listViewControl->mountOrderByLink($listViewColumn->getName());
					}
				}
				$header[] = array(HtmlHelper::ELEMENT_VALUE => $headerLabel, HtmlHelper::ELEMENT_LINK => $linkOrderBy);
			} else {
				$header[] = '';
			}
		}

		// MAKE CONTENT
		$content = array();
		foreach ($list as $item) {
			$row = array();
			foreach ($listViewColumns as $listViewColumn) {
				$type = null;
				$value = null;
				$link = null;
				$align = null;
				if ($listViewColumn instanceof ListViewTextColumn) {
					$type = $listViewColumn->getType();
					$format = $listViewColumn->getFormat();
					$methodName = 'get'.ucfirst($listViewColumn->getName());
					$value = ReflectionHelper::getMethodResultFromInstance($item, $methodName);
					if (isset($format[Annotation::O_TYPE])) {
						if ($format[Annotation::O_TYPE] == \helper\HtmlHelper::INPUT_FORMAT_DECIMAL) {
							$value = \helper\StringHelper::convertToDecimal($value);
							$align = 'right';
						} else if ($format[Annotation::O_TYPE] == \helper\HtmlHelper::INPUT_FORMAT_DATE && isset($format[Annotation::O_PATTERN_PHP])) {
							$value = \helper\DateHelper::getAsString($value, $format[Annotation::O_PATTERN_PHP]);
							$align = 'center';
						}
						if (isset($format[Annotation::O_PREFIX])) {
							$value = $format[Annotation::O_PREFIX].' '.$value;
						}
					}
					$fetchEntity = null;
					$fetchTypes = array(HtmlHelper::SELECT, HtmlHelper::RADIO);
					if (in_array($type[Annotation::O_TYPE], $fetchTypes)) {
						$fetch = preg_split('/\./', $type[Annotation::O_FETCH_ITEM]);
						$fetchEntity = ReflectionHelper::getMethodResultFromName($fetch[0], $fetch[1], $value);
						if ($fetchEntity) {
							$value = ReflectionHelper::getMethodResultFromInstance($fetchEntity, 'get'.$type[Annotation::O_VALUE]);
						} else {
							$value = '-';
						}
					}
					$callback = $listViewColumn->getCallback();
					if ($callback) {
						while (preg_match('/{(?P<element>.+?)}/', $callback, $matches)) {
							$tempEntity = isset($fetchEntity) ? $fetchEntity : $item;
							$param = ReflectionHelper::getMethodResultFromInstance($tempEntity, 'get'.ucfirst($matches['element']));
							$callback = str_replace('{'.$matches['element'].'}', $param, $callback);
						}
						$link = $callback;
					}
				} else if ($listViewColumn instanceof ListViewCommandColumn) {
					$value = $listViewColumn->getName();
					$callback = $listViewColumn->getCallback();
					if ($callback) {
						while (preg_match('/{(?P<element>.+?)}/', $callback, $matches)) {
							$get = 'get'.ucfirst($matches['element']);
							$param = ReflectionHelper::getMethodResultFromInstance($item, $get);
							$callback = str_replace('{'.$matches['element'].'}', $param, $callback);
						}
						$link = $callback;
					}
				}
				if ($link && strpos($link, 'http://') !== 0) {
					$link = \App::getInstance()->getBasePrefix().$link;
				}
				$row[] = array(
						HtmlHelper::ELEMENT_VALUE => $value,
						HtmlHelper::ELEMENT_LINK => $link,
						HtmlHelper::ELEMENT_TYPE => $type[Annotation::O_TYPE],
						HtmlHelper::ELEMENT_ALIGN => $align
				);
			}
			$content[] = $row;
		}

		// MAKE PAGINATOR
		$paginator = null;
		if ($showPaginator) {
			$paginator = $listViewControl->makePagination();
		}

		return array(self::LIST_VIEW_HEADER => $header, self::LIST_VIEW_CONTENT => $content, self::LIST_VIEW_PAGINATOR => $paginator);
	}
}
?>