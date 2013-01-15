<?php
namespace repository;
if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

use core\exception\RepositoryException;
use helper\SQLHelper;
use annotation\Annotation;

abstract class BaseRepository extends SupportDatabase {

	const PAGINATION_ASC = 'ASC';
	const PAGINATION_DESC = 'DESC';

	private $entity;

	protected function setEntity(\entity\BaseEntity $entity) {
		$this->entity = $entity;
	}

	public function getEntity() {
		return $this->entity;
	}

	public function getListCount() {
		try {
			$sql = $this->mountSelectCount(Annotation::extractAnnotations($this->entity));
			$statement = $this->getConnection()->prepare($sql);
			$statement->execute();
			$statement->setFetchMode(\PDO::FETCH_OBJ);
			$result = $statement->fetch();
			if ($result) {
				return $result->COUNT;
			}
			return 0;

		} catch (\core\exception\RepositoryException $e) {
			throw $e;

		} catch (\Exception $e) {
			throw new RepositoryException($e);
		}
	}

	public function getList($offset = null, $rowsPerSelect = null, $orderBy = null, $orderDirection = null) {
		try {
			if ($orderDirection) {
				$orderDirection = strtoupper($orderDirection);
				if ($orderDirection != self::PAGINATION_ASC && $orderDirection != self::PAGINATION_DESC) {
					$orderDirection = null;
				}
			}
			$listAnnotations = Annotation::extractAnnotations($this->entity);
			$sql = $this->mountSelect($listAnnotations, $offset, $rowsPerSelect, $orderBy, $orderDirection);
			$statement = $this->getConnection()->prepare($sql);
			$statement->execute();
			$statement->setFetchMode(\PDO::FETCH_OBJ);
			$resultSet = $statement->fetchAll();
			if ($resultSet) {
				$list = array();
				foreach ($resultSet as $obj) {
					$list[] = $this->mapperFromDatabase($listAnnotations, $obj, $this->entity);
				}
				return $list;
			}
			return null;

		} catch (\core\exception\RepositoryException $e) {
			throw $e;

		} catch(\Exception $e) {
			throw new RepositoryException($e);
		}
	}

	public function getFilteredList($searchFilters = array(), $offset = null, $rowsPerSelect = null, $orderBy = null, $orderDirection = null) {
		try {
			if ($orderDirection) {
				$orderDirection = strtoupper($orderDirection);
				if ($orderDirection != self::PAGINATION_ASC && $orderDirection != self::PAGINATION_DESC) {
					$orderDirection = null;
				}
			}
			$listAnnotations = Annotation::extractAnnotations($this->entity);
			$sql = $this->mountSelect($listAnnotations, $offset, $rowsPerSelect, $orderBy, $orderDirection, $searchFilters);
			$params = $this->preparaSelectParametersWithSearchFilter($listAnnotations, $searchFilters);
			$statement = $this->getConnection()->prepare($sql);
			foreach ($params as $key=>$value) {
				$statement->bindValue($key, $value);
			}
			$statement->execute();
			$statement->setFetchMode(\PDO::FETCH_OBJ);
			$resultSet = $statement->fetchAll();
			if ($resultSet) {
				$list = array();
				foreach ($resultSet as $obj) {
					$list[] = $this->mapperFromDatabase($listAnnotations, $obj, $this->entity);
				}
				return $list;
			}
			return null;

		} catch (\core\exception\RepositoryException $e) {
			throw $e;

		} catch (\Exception $e) {
			throw new RepositoryException($e);
		}
	}

	public function getItem($identity) {
		try {
			$this->entity->setId($identity);
			$listAnnotations = Annotation::extractAnnotations($this->entity);
			$sql = $this->mountSelectById($listAnnotations);
			$params = $this->prepareParameters(self::OPERATION_SELECTBYID, $listAnnotations, $this->entity);
			$statement = $this->getConnection()->prepare($sql);
			foreach ($params as $key=>$value) {
				$statement->bindValue($key, $value);
			}
			$statement->execute();
			$statement->setFetchMode(\PDO::FETCH_OBJ);
			$obj = $statement->fetch();
			if ($obj) {
				return $this->mapperFromDatabase($listAnnotations, $obj, $this->entity);
			}
			return null;

		} catch (\core\exception\RepositoryException $e) {
			throw $e;

		} catch(\Exception $e) {
			throw new RepositoryException($e);
		}
	}

	public function insert($entity, $usesTransaction) {
		try {
			$listAnnotations = Annotation::extractAnnotations($this->entity);
			$sql = $this->mountInsert($listAnnotations);
			$params = $this->prepareParameters($this::OPERATION_INSERT, $listAnnotations, $entity);
			$statement = $this->getConnection($usesTransaction)->prepare($sql);
			foreach ($params as $key=>$value) {
				$statement->bindValue($key, $value);
			}
			$statement->execute();
			return $this->getConnection()->lastInsertId();

		} catch (\core\exception\RepositoryException $e) {
			throw $e;

		} catch(\PDOException $e) {
			if ($e->errorInfo[0] == '23000') {
				throw new \core\exception\RepositoryException(new \Exception($e->errorInfo[2], $e->errorInfo[1], $e));
			} else {
				throw new \core\exception\RepositoryException($e);
			}

		} catch(\Exception $e) {
			throw new \core\exception\RepositoryException($e);
		}
	}

	public function update($entity, $usesTransaction) {
		try {
			$listAnnotations = Annotation::extractAnnotations($this->entity);
			$sql = $this->mountUpdate($listAnnotations);
			$params = $this->prepareParameters(self::OPERATION_UPDATE, $listAnnotations, $entity);
			$statement = $this->getConnection($usesTransaction)->prepare($sql);
			foreach ($params as $key=>$value) {
				$statement->bindValue($key, $value);
			}
			$statement->execute();

		} catch (\core\exception\RepositoryException $e) {
			throw $e;

		} catch(\Exception $e) {
			throw new RepositoryException($e);
		}
	}

	public function delete($entity, $usesTransaction) {
		try {
			$listAnnotations = Annotation::extractAnnotations($this->entity);
			$sql = $this->mountDeleteById($listAnnotations);
			$params = $this->prepareParameters($this::OPERATION_DELETE, $listAnnotations, $this->entity);
			$statement = $this->getConnection()->prepare($sql);
			foreach ($params as $key=>$value) {
				$statement->bindValue($key, $value);
			}
			$statement->execute();

		} catch (\core\exception\RepositoryException $e) {
			throw $e;

		} catch (\Exception $e) {
			throw new RepositoryException($e);
		}
	}
}
?>