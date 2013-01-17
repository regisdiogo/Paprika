<?php
namespace annotation;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

use helper\ReflectionHelper;

abstract class SupportBusiness extends BaseSupport {

    protected function generateNullableMessages($entity, $messages = null) {
        $listAnnotations = Annotation::extractAnnotations($entity);
        if ($messages == null) {
            $messages = array();
        }
        if ($listAnnotations[Annotation::PROPERTIES]) {
            foreach ($listAnnotations[Annotation::PROPERTIES] as $property=>$annotations) {
                foreach ($annotations as $annotation) {
                    if ($annotation[Annotation::BEHAVIOR] == Annotation::T_NOTNULL) {
                        $value = ReflectionHelper::getMethodResultFromInstance($entity, 'get'.ucfirst($property));
                        if (!$value) {
                            $messages[$property] = $annotation[Annotation::VALUES][Annotation::O_MESSAGE];
                        }
                    }
                }
            }
        }
        if (!count($messages)) {
            return null;
        }
        return $messages;
    }

    protected function generateNeedsConfirmationMessages($entity, $messages = null) {
        $listAnnotations = Annotation::extractAnnotations($entity);
        if ($messages == null) {
            $messages = array();
        }
        if ($listAnnotations[Annotation::PROPERTIES]) {
            $listBusinessRules = array(Annotation::T_NEEDS_CONFIRMATION);
            foreach ($listAnnotations[Annotation::PROPERTIES] as $property=>$annotations) {
                foreach ($annotations as $annotation) {
                    if (in_array($annotation[Annotation::BEHAVIOR], $listBusinessRules)) {
                        $confirmKey = 'confirm'.$property;
                        $value = ReflectionHelper::getMethodResultFromInstance($entity, 'get'.ucfirst($property));
                        if (!isset($entity->$confirmKey) || $entity->$confirmKey != $value) {
                            $messages['confirm-'.$property] = $annotation[Annotation::VALUES][Annotation::O_MESSAGE];
                        }
                    }
                }
            }
        }
        if (!count($messages)) {
            return null;
        }
        return $messages;
    }

    protected function generateUniqueMessages($entity, $messages = null, $identityProperty = null) {
        $listAnnotations = Annotation::extractAnnotations($entity);
        if ($messages == null) {
            $messages = array();
        }
        if ($listAnnotations[Annotation::PROPERTIES]) {
            $listBusinessRules = array(Annotation::T_UNIQUE);
            foreach ($listAnnotations[Annotation::PROPERTIES] as $property=>$annotations) {
                foreach ($annotations as $annotation) {
                    if (in_array($annotation[Annotation::BEHAVIOR], $listBusinessRules)) {
                        $value = ReflectionHelper::getMethodResultFromInstance($entity, 'get'.ucfirst($property));
                        $check = explode('.', $annotation[Annotation::VALUES][Annotation::O_CHECK]);
                        $result = \helper\ReflectionHelper::getMethodResultFromName($check[0], $check[1], $value);
                        if (isset($result) && count($result) > 0) {
                            $showMessage = true;
                            if (\helper\StringHelper::isNotNull($identityProperty)) {
                                $tmpProp = 'get'.ucfirst($identityProperty);
                                $simpleResult = null;
                                if (is_array($result)) {
                                    $simpleResult = $result[0];
                                } else {
                                    $simpleResult = $result;
                                }
                                if (\helper\ReflectionHelper::getMethodResultFromInstance($entity, $tmpProp) == \helper\ReflectionHelper::getMethodResultFromInstance($simpleResult, $tmpProp)) {
                                    $showMessage = false;
                                }
                            }
                            if ($showMessage) {
                                $messages[$property] = $annotation[Annotation::VALUES][Annotation::O_MESSAGE];
                            }
                        }
                    }
                }
            }
        }
        if (!count($messages)) {
            return null;
        }
        return $messages;
    }
}
?>