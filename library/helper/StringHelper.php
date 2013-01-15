<?php
namespace helper;
if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

class StringHelper {

	/**
	 * Check if a string ends with another string
	 * @param $haystack
	 * @param $needle
	 */
	public static function endsWith($haystack, $needle) {
		$StrLen = strlen($needle);
		$tempHaystack = substr($haystack, strlen($haystack) - $StrLen);
		return $tempHaystack == $needle;
	}

	/**
	 * Check if String is null
	 * @param $value
	 */
	public static function isNull($value) {
		if (!isset($value)) {
			return true;
		}
		if (strlen($value) == 0) {
			return true;
		}
		return false;
	}

	/**
	 * Check if String is not null
	 * @param $value
	 */
	public static function isNotNull($value) {
		return !self::isNull($value);
	}

	public static function convertFromDecimal($value) {
		if (strstr($value, ',')) {
			$value = str_replace('.', '', $value);
		} else {
			//FIXME e outro formato decimal
			p($value); exit;
		}
		return $value;
	}

	public static function convertToDecimal($value) {
		if (strstr($value, '.')) {
			$aux = explode('.', $value);
			$result = array();
			$i = 0;
			foreach (array_reverse(str_split($aux[0])) as $part) {
				$result[] = $part;
				if (fmod(++$i, 3) == 0 && $i < strlen($aux[0])) {
					$result[] = '.';
				}
			}
			$value = implode('', array_reverse($result)).','.$aux[1];
		}
		return $value;
	}
}
?>