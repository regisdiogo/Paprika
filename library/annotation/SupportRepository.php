<?php
namespace annotation;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

use core\component\SearchFilter;
use core\exception\AnnotationException;
use helper\SQLHelper;
use helper\ReflectionHelper;

abstract class SupportRepository {

    const OPERATION_INSERT = 1;
    const OPERATION_UPDATE = 2;
    const OPERATION_SELECTBYID = 3;
    const OPERATION_DELETE = 3;

    protected function mountSelectCount($listAnnotations) {
        if (!$listAnnotations[Annotation::CLAZZ]) {
            throw new AnnotationException(\core\Messages::CLASS_HAS_NO_ANNOTATIONS);
        }

        $tableName = null;

        foreach ($listAnnotations[Annotation::CLAZZ] as $annotation) {
            if ($annotation[Annotation::BEHAVIOR] == Annotation::T_TABLE) {
                $tableName = $annotation[Annotation::VALUES][Annotation::O_NAME];
                break;
            }
        }

        return SQLHelper::createSelectCountFull($tableName);
    }

    protected function mountSelect($listAnnotations, $offset, $rowsPerSelect, $orderBy, $orderDirection, $filters = null, $whereCustomized = false) {
        if (!$listAnnotations[Annotation::PROPERTIES] || !$listAnnotations[Annotation::CLAZZ]) {
            throw new AnnotationException(\core\Messages::CLASS_HAS_NO_ANNOTATIONS);
        }

        $columns = array();
        $tableName = null;

        $behaviorList = array(Annotation::T_ID, Annotation::T_COLUMN);

        foreach ($listAnnotations[Annotation::PROPERTIES] as $property=>$annotations) {
            foreach ($annotations as $annotation) {
                if (in_array($annotation[Annotation::BEHAVIOR], $behaviorList)) {
                    $columns[] = $annotation[Annotation::VALUES][Annotation::O_NAME];
                }
            }
        }

        foreach ($listAnnotations[Annotation::CLAZZ] as $annotation) {
            if ($annotation[Annotation::BEHAVIOR] == Annotation::T_TABLE) {
                $tableName = $annotation[Annotation::VALUES][Annotation::O_NAME];
            }
        }

        $where = null;
        if ($filters) {
            $where = array();
            $i = 0;
            foreach ($filters as $filter) {
                if (array_key_exists($filter->getProperty(), $listAnnotations[Annotation::PROPERTIES])) {
                    $annotation = $listAnnotations[Annotation::PROPERTIES][$filter->getProperty()];
                    if (isset($annotation)) {
                        foreach ($annotation as $part) {
                            if (in_array($part[Annotation::BEHAVIOR], $behaviorList)) {
                                $values = array();
                                $column = $part[Annotation::VALUES][Annotation::O_NAME];
                                $values[] = $column;
                                if ($filter->getComparisonType() == SearchFilter::COMPARISON_BETWEEN) {
                                    $values[] = $filter->getComparisonType();
                                    $values[] = SQLHelper::MYSQL_PDO_PARAM_DELIMITER.$column.'_filter_'.$i++;
                                    $values[] = 'AND';
                                    $values[] = SQLHelper::MYSQL_PDO_PARAM_DELIMITER.$column.'_filter_'.$i++;

                                } else {
                                    $values[] = $filter->getComparisonType();
                                    $values[] = SQLHelper::MYSQL_PDO_PARAM_DELIMITER.$column.'_filter_'.$i++;
                                }
                                $where[] = implode(' ', $values);
                            }
                        }
                    }
                }
            }
        } else if ($whereCustomized) {
            $where[] = '%1$s';
        }

        if ($orderBy) {
            foreach ($listAnnotations[Annotation::PROPERTIES] as $property=>$annotations) {
                if ($property == $orderBy) {
                    foreach ($annotations as $annotation) {
                        if (in_array($annotation[Annotation::BEHAVIOR], $behaviorList)) {
                            $orderBy = $annotation[Annotation::VALUES][Annotation::O_NAME];
                            break;
                        }
                    }
                }
            }
        }

        return SQLHelper::createSelectWithPagination($columns, $tableName, $offset, $rowsPerSelect, $orderBy, $orderDirection, $where);
    }

    protected function mountSelectById($listAnnotations) {
        if (!$listAnnotations[Annotation::PROPERTIES] || !$listAnnotations[Annotation::CLAZZ]) {
            throw new AnnotationException(\core\Messages::CLASS_HAS_NO_ANNOTATIONS);
        }

        $identity = null;
        $columns = array();
        $tableName = null;

        foreach ($listAnnotations[Annotation::PROPERTIES] as $property=>$annotations) {
            foreach ($annotations as $annotation) {
                if ($annotation[Annotation::BEHAVIOR] == Annotation::T_ID) {
                    $identity = $annotation[Annotation::VALUES][Annotation::O_NAME];

                } else if ($annotation[Annotation::BEHAVIOR] == Annotation::T_COLUMN) {
                    $columns[] = $annotation[Annotation::VALUES][Annotation::O_NAME];
                }
            }
        }

        foreach ($listAnnotations[Annotation::CLAZZ] as $annotation) {
            if ($annotation[Annotation::BEHAVIOR] == Annotation::T_TABLE) {
                $tableName = $annotation[Annotation::VALUES][Annotation::O_NAME];
            }
        }

        return SQLHelper::createSelectById($identity, $columns, $tableName);
    }

    protected function mountDeleteById($listAnnotations) {
        if (!$listAnnotations[Annotation::PROPERTIES] || !$listAnnotations[Annotation::CLAZZ]) {
            throw new AnnotationException(\core\Messages::CLASS_HAS_NO_ANNOTATIONS);
        }

        $identity = null;
        $tableName = null;

        foreach ($listAnnotations[Annotation::PROPERTIES] as $property=>$annotations) {
            foreach ($annotations as $annotation) {
                if ($annotation[Annotation::BEHAVIOR] == Annotation::T_ID) {
                    $identity = $annotation[Annotation::VALUES][Annotation::O_NAME];
                }
            }
        }

        foreach ($listAnnotations[Annotation::CLAZZ] as $annotation) {
            if ($annotation[Annotation::BEHAVIOR] == Annotation::T_TABLE) {
                $tableName = $annotation[Annotation::VALUES][Annotation::O_NAME];
            }
        }

        return SQLHelper::createDeleteById($identity, $tableName);
    }

    protected function mountInsert($listAnnotations) {
        if (!$listAnnotations[Annotation::PROPERTIES] || !$listAnnotations[Annotation::CLAZZ]) {
            throw new AnnotationException(\core\Messages::CLASS_HAS_NO_ANNOTATIONS);
        }

        $columns = array();
        $tableName = null;

        foreach ($listAnnotations[Annotation::PROPERTIES] as $property=>$annotations) {
            foreach ($annotations as $annotation) {
                if ($annotation[Annotation::BEHAVIOR] == Annotation::T_COLUMN) {
                    $columns[] = $annotation[Annotation::VALUES][Annotation::O_NAME];
                }
            }
        }

        foreach ($listAnnotations[Annotation::CLAZZ] as $annotation) {
            if ($annotation[Annotation::BEHAVIOR] == Annotation::T_TABLE) {
                $tableName = $annotation[Annotation::VALUES][Annotation::O_NAME];
            }
        }

        return SQLHelper::createInsert($columns, $tableName);
    }

    protected function mountUpdate($listAnnotations) {
        if (!$listAnnotations[Annotation::PROPERTIES] || !$listAnnotations[Annotation::CLAZZ]) {
            throw new AnnotationException(\core\Messages::CLASS_HAS_NO_ANNOTATIONS);
        }

        $identity = null;
        $columns = array();
        $tableName = null;

        foreach ($listAnnotations[Annotation::PROPERTIES] as $property=>$annotations) {
            foreach ($annotations as $annotation) {
                if ($annotation[Annotation::BEHAVIOR] == Annotation::T_ID) {
                    $identity = $annotation[Annotation::VALUES][Annotation::O_NAME];

                } else if ($annotation[Annotation::BEHAVIOR] == Annotation::T_COLUMN) {
                    $columns[] = $annotation[Annotation::VALUES][Annotation::O_NAME];
                }
            }
        }

        foreach ($listAnnotations[Annotation::CLAZZ] as $annotation) {
            if ($annotation[Annotation::BEHAVIOR] == Annotation::T_TABLE) {
                $tableName = $annotation[Annotation::VALUES][Annotation::O_NAME];
            }
        }

        return SQLHelper::createUpdateById($identity, $columns, $tableName);
    }

    protected function prepareParameters($operationType, $listAnnotation, $classInstance) {
        $parameters = array();
        $propertyBehavior = array();

        switch ($operationType) {
            case self::OPERATION_INSERT:
                $propertyBehavior[] = Annotation::T_COLUMN;
                break;

            case self::OPERATION_UPDATE:
                $propertyBehavior[] = Annotation::T_COLUMN;
                $propertyBehavior[] = Annotation::T_ID;
                break;

            case self::OPERATION_SELECTBYID:
                $propertyBehavior[] = Annotation::T_ID;
                break;
        }

        if ($listAnnotation[Annotation::PROPERTIES]) {
            foreach ($listAnnotation[Annotation::PROPERTIES] as $property=>$annotations) {
                foreach ($annotations as $annotation) {
                    if (in_array($annotation[Annotation::BEHAVIOR], $propertyBehavior)) {
                        $value = ReflectionHelper::getMethodResultFromInstance($classInstance, 'get'.ucfirst($property));
                        $parameters[SQLHelper::MYSQL_PDO_PARAM_DELIMITER.$annotation[Annotation::VALUES][Annotation::O_NAME]] = $value;
                        if ($annotation[Annotation::BEHAVIOR] == Annotation::T_ID) {
                            $identity = SQLHelper::MYSQL_PDO_PARAM_DELIMITER.$annotation[Annotation::VALUES][Annotation::O_NAME];
                        }
                    }
                }
            }
        }

        return $parameters;
    }

    protected function preparaSelectParametersWithSearchFilter($listAnnotations, $searchFilters) {
        $where = array();
        if ($searchFilters) {
            $behaviorList = array(Annotation::T_COLUMN, Annotation::T_ID);
            $i = 0;
            foreach ($searchFilters as $filter) {
                if (array_key_exists($filter->getProperty(), $listAnnotations[Annotation::PROPERTIES])) {
                    $annotation = $listAnnotations[Annotation::PROPERTIES][$filter->getProperty()];
                    if (isset($annotation)) {
                        foreach ($annotation as $part) {
                            if (in_array($part[Annotation::BEHAVIOR], $behaviorList)) {
                                $column = $part[Annotation::VALUES][Annotation::O_NAME];
                                if ($filter->getComparisonType() == SearchFilter::COMPARISON_BETWEEN) {
                                    $tmpValue = $filter->getValue();
                                    $where[SQLHelper::MYSQL_PDO_PARAM_DELIMITER.$column.'_filter_'.$i++] = $tmpValue[0];
                                    $where[SQLHelper::MYSQL_PDO_PARAM_DELIMITER.$column.'_filter_'.$i++] = $tmpValue[1];
                                } else {
                                    $where[SQLHelper::MYSQL_PDO_PARAM_DELIMITER.$column.'_filter_'.$i++] = $filter->getValue();
                                }
                            }
                        }
                    }
                }
            }
        }
        return $where;
    }

    protected function mapperFromDatabase($result, $databaseRow, $entity) {
        if (!$result[Annotation::PROPERTIES]) {
            throw new AnnotationException(\core\Messages::CLASS_HAS_NO_ANNOTATIONS);
        }
        $cloneEntity = clone $entity;

        $behaviorList = array(Annotation::T_COLUMN, Annotation::T_ID);

        foreach ($result[Annotation::PROPERTIES] as $key=>$annotations) {
            foreach ($annotations as $annotation) {
                if (in_array($annotation[Annotation::BEHAVIOR], $behaviorList)) {
                    $reflectionMethod = new \ReflectionMethod($cloneEntity, 'set'.ucfirst($key));
                    $reflectionMethod->invoke($cloneEntity, $databaseRow->$annotation[Annotation::VALUES][Annotation::O_NAME]);
                }
            }
        }
        return $cloneEntity;
    }
}
?>