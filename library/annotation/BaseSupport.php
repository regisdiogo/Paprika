<?php
namespace annotation;

if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

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