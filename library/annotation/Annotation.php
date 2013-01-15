<?php
namespace annotation;
if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

use core\exception\AnnotationException;

class Annotation {

	/* CONTROLS */
	const CLAZZ = 'class';
	const PROPERTIES = 'properties';
	const BEHAVIOR = 'behavior';
	const VALUES = 'values';

	/* TYPES */
	const T_ROUTE = 'route';
	const T_TABLE = 'table';
	const T_ID = 'id';
	const T_INPUT = 'input';
	const T_LABEL = 'label';
	const T_COLUMN = 'column';
	const T_NOTNULL = 'notnull';
	const T_NEEDS_CONFIRMATION = 'needsconfirmation';
	const T_UNIQUE = 'unique';
	const T_FORMAT = 'format';

	/* OPTIONS */
	const O_NAME = 'name';
	const O_TYPE = 'type';
	const O_MAX_LENGTH = 'maxlength';
	const O_VALUE = 'value';
	const O_ALLOW = 'allow';
	const O_MESSAGE = 'message';
	const O_FETCH_LIST = 'fetchlist';
	const O_FETCH_ITEM = 'fetchitem';
	const O_ID = 'id';
	const O_MAPPER = 'mapper';
	const O_METHOD = 'method';
	const O_PAGE = 'page';
	const O_TEMPLATE = 'template';
	const O_REFERS_TO = 'refersto';
	const O_CONTENT_TYPE = 'contenttype';
	const O_ROLE = 'role';
	const O_CHECK = 'check';
	const O_PATTERN_JS = 'patternJS';
	const O_PATTERN_PHP = 'patternPHP';
	const O_MASK = 'mask';
	const O_PREFIX = 'prefix';
	const O_COLS = 'cols';
	const O_ROWS= 'rows';

	public static function extractAnnotations($className) {
		try {
			if (is_object($className)) {
				$className = get_class($className);
			}
			$className = strtolower($className);

			$listAnnotations = CachedAnnotation::getInstance()->getAnnotations($className);
			if ($listAnnotations) {
				return $listAnnotations;
			}

			$listAnnotations = array();
			$reflectionClass = new \ReflectionClass($className);

			// Class doc annotations
			if ($reflectionClass->getDocComment()) {
				$annotations = self::extractAnnotationsFromDocComment($reflectionClass->getDocComment());
				$listAnnotations[self::CLAZZ] = $annotations;
			}

			// Properties doc annotations
			$properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PRIVATE);
			if ($properties) {
				$propetiesAnnotations = array();
				foreach ($properties as $property) {
					if ($property) {
						$reflectionProperty = new \ReflectionProperty($property->class, $property->name);
						$annotations = self::extractAnnotationsFromDocComment($reflectionProperty->getDocComment());
						$propetiesAnnotations[$property->name] = $annotations;
					}
				}
				$listAnnotations[self::PROPERTIES] = $propetiesAnnotations;
			}

			// Cache list of annotations
			CachedAnnotation::getInstance()->setAnnotations($className, $listAnnotations);

			return $listAnnotations;

		} catch (\Exception $e) {
			throw new AnnotationException($e);
		}
	}

	private static function extractAnnotationsFromDocComment($string) {
		$tempString = $string;
		while (true) {
			if (preg_match('/([\s]+|[*])[@](?P<type>[^\s(]*)/', $tempString, $matches, PREG_OFFSET_CAPTURE)) {
				$offset = $matches['type'][1] + 1;
				$tempString = substr($tempString, $offset);
				$listDocComments[] = $matches['type'][0];
			} else {
				break;
			}
		}
		$listAnnotation = array();
		if ($listDocComments) {
			$tempString = $string;
			foreach ($listDocComments as $docComment) {
				$annotation = array();
				if (preg_match("/@".$docComment."\s*[(]\s*(?P<params>.+)[)]/", $tempString, $matches, PREG_OFFSET_CAPTURE)) {
					$params = preg_split("/[,]/", $matches['params'][0]);
					$offset = $matches['params'][1] + 1;
					$tempString = substr($tempString, $offset);
					$values = array();
					foreach ($params as $param) {
						if (preg_match('/(?<key>.*?)\s*=\s*"(?P<value>.+)"\s*/', $param, $paramsMatches)) {
							$values[$paramsMatches['key']] = $paramsMatches['value'];
						}
					}
					if (count($values)) {
						$annotation = $values;
					}
				}
				$listAnnotation[] = array(self::BEHAVIOR => strtolower($docComment), self::VALUES => $annotation);
			}
		}
		return $listAnnotation;
	}
}

class CachedAnnotation {

	private static $instance = null;
	private $annotations = array();

	private function __construct() {
	}

	private function __clone() {
	}

	/**
	 * @return CachedAnnotation
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new CachedAnnotation();
		}
		return self::$instance;
	}

	public function getAnnotations($className) {
		if (key_exists($className, $this->annotations)) {
			return $this->annotations[$className];
		} else {
			return null;
		}
	}

	public function setAnnotations($className, $annotations) {
		$this->annotations[$className] = $annotations;
	}
}
?>