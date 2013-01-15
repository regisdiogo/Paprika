<?php
namespace helper;

class JsonHelper {

	public static function convertResponseToJson(\presentation\HttpResponse $httpResponse) {
		$stdObject = self::convertObjectToStdClass($httpResponse);
		if (is_object($stdObject)) {
			$stdObject = get_object_vars($stdObject);
		}
		return json_encode($stdObject);
	}

	private static function convertObjectToStdClass($object, $key = null) {
		if (is_array($object)) {
			$array = array();
			foreach ($object as $key=>$value) {
				$array[] = self::convertObjectToStdClass($value, $key);
			}
			return $array;
		}
		$stdObject = new \stdClass();
		if (!is_object($object)) {
			$stdObject->$key = $object;
		} else {
			$rc = new \ReflectionClass($object);
			$methods = $rc->getMethods(\ReflectionMethod::IS_PUBLIC);
			if ($methods) {
				foreach ($methods as $method) {
					if (strpos($method->name, 'get') !== false) {
						$rm = new \ReflectionMethod($object, $method->name);
						$name = strtolower(substr($method->name, 3));
						$value = $rm->invoke($object);
						if (is_object($value) || is_array($value)) {
							$stdObject->$name = self::convertObjectToStdClass($value);
						} else {
							$stdObject->$name = $value;
						}
					}
				}
			}
		}
		return $stdObject;
	}
}
?>