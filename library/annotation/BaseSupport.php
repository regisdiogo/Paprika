<?php
namespace annotation;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

abstract class BaseSupport {

    protected function getIdentityProperty($entity) {
        $className = get_class($entity);
        $listAnnotations = Annotation::extractAnnotations($className);

        foreach ($listAnnotations[Annotation::PROPERTIES] as $property=>$propertiesAnnotations) {
            foreach ($propertiesAnnotations as $annotation) {
                if ($annotation[Annotation::BEHAVIOR] == Annotation::T_ID) {
                    return $property;
                }
            }
        }
        throw new \core\exception\AnnotationException('Identity annotation @Id not found');
    }
}
?>