<?php
namespace business;

if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

use annotation\Annotation;
use annotation\SupportBusiness;
use core\exception\ValidationException;
use core\exception\BusinessException;
use helper\ReflectionHelper;
use repository\BaseRepository;

abstract class BaseBusiness extends SupportBusiness {

	private $validations = array();

	/**
	 * @var BaseRepository
	 */
	private $repository;

	protected function setRepository(\repository\BaseRepository $repository) {
		$this->repository = $repository;
	}

	/**
	 * @return repository\BaseRepository
	 */
	public function getRepository() {
		return $this->repository;
	}

	public function setValidation($functionName) {
		$this->validations[] = $functionName;
	}

	/**
	 * Commit changes in database
	 */
	public function commit() {
		try {
			$this->repository->commit();
		} catch (\Exception $e) {
			throw new BusinessException($e);
		}
	}

	/**
	 * Rollback changes in database
	 */
	public function rollback() {
		try {
			$this->repository->rollback();
		} catch (\Exception $e) {
			throw new BusinessException($e);
		}
	}

	public function save($entity, $usesTransaction = false) {
		try {
			$entityValueId = null;
			$identityProperty = null;

			try {
				$identityProperty = $this->getIdentityProperty($entity);
				$entityValueId = ReflectionHelper::getMethodResultFromInstance($entity, 'get'. ucfirst($identityProperty));
			} catch (\core\exception\AnnotationException $e) {
			}

			if ($entityValueId) {
				$this->validate($entity, $identityProperty);
				$this->repository->update($entity, $usesTransaction);
			} else {
				$this->validate($entity);
				$value = $this->repository->insert($entity, $usesTransaction);
				if ($identityProperty) {
					ReflectionHelper::getMethodResultFromInstance($entity, 'set'. ucfirst($identityProperty), $value);
				}
			}

		} catch (\core\exception\ValidationException $e) {
			throw $e;

		} catch (\Exception $e) {
			throw new BusinessException($e);
		}
	}

	public function delete($entity, $usesTransaction = false) {
		try {
			if ($entity) {
				$this->repository->delete($entity, $usesTransaction);
			} else {
				//TODO o que fazer aqui
				p("E aqui??");
			}
		} catch (\Exception $e) {
			throw new BusinessException($e);
		}
	}

	public function getItem($identity) {
		try {
			if (!$identity) {
				return null;
			}
			return $this->repository->getItem($identity);
		} catch (\Exception $e) {
			throw new BusinessException($e);
		}
	}

	public function getList($offset = null, $rowsPerSelect = null, $orderBy = null, $orderDirection = null) {
		try {
			return $this->repository->getList($offset, $rowsPerSelect, $orderBy, $orderDirection);
		} catch (\Exception $e) {
			throw new BusinessException($e);
		}
	}

	public function getListCount() {
		try {
			return $this->repository->getListCount();
		} catch (\Exception $e) {
			throw new BusinessException($e);
		}
	}

	public function getFilteredList($searchFilters = array(), $offset = null, $rowsPerSelect = null, $orderBy = null, $orderDirection = null) {
		try {
			return $this->repository->getFilteredList($searchFilters, $offset, $rowsPerSelect, $orderBy, $orderDirection);
		} catch (\Exception $e) {
			throw new BusinessException($e);
		}
	}

	private function validate($entity, $identityProperty = null) {
		$messages = $this->generateNullableMessages($entity);
		$messages = $this->generateNeedsConfirmationMessages($entity, $messages);
		$messages = $this->generateUniqueMessages($entity, $messages, $identityProperty);
		foreach ($this->validations as $validation) {
			$messages = \helper\ReflectionHelper::getMethodResultFromName(get_class($this), $validation, array($entity, $messages));
		}
		if ($messages) {
			throw new ValidationException($messages);
		}
	}
}
?>