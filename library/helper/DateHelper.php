<?php
namespace helper;
if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

class DateHelper {

	const DT_FMT_COMPLETE = "Y/m/d H:i:s";	
	const DT_MYSQL = 'Y-m-d H:i:s';
	const DT_PTBR = "d/m/Y";

	/**
	 * Get current date with database style
	 */
	public static function getSysdateDatabase() {
		return self::getSysdateCustomFormat(self::DT_FMT_COMPLETE);
	}

	/**
	 * Get current date as string
	 * @param $format
	 * @param $showMicroSeconds
	 */
	public static function getSysdateCustomFormat($format, $showMicroSeconds = false) {
		$date = date($format);
		if ($showMicroSeconds) {
			$date .= substr((string)microtime(), 1, 8);
		}
		return $date;
	}

	public static function getAsString($date, $format) {
		$datetime = strtotime($date);
		$dateFormated = date($format, $datetime);
		//$dateFormated = self::replaceMonthsToFull($dateFormated);
		return $dateFormated;
	}

	public static function isValid($date) {
		$date = preg_split('/[-\/]/', $date);
		if (!checkdate($date[1], $date[0], $date[2]) && !checkdate($date[1], $date[2], $date[0])) {
			return false;
		}
		return true;
	}

	public static function invertDate($date) {
		$result = array();
		$tmp = '';
		foreach (str_split($date) as $part) {
			if (preg_match('/[0-9]/', $part)) {
				$tmp .= $part;
			} else {
				$result[] = $tmp;
				$result[] = $part;
				$tmp = '';
			}
		}
		if ($tmp) {
			$result[] = $tmp;
		}
		return implode('',array_reverse($result));
	}

	public static function getFirstAndLastDayFromMonth($date) {
		$dateTime = strtotime($date);
		$firstDay = date(self::DT_MYSQL, strtotime(date('Y', $dateTime) . '-' . date('m', $dateTime) . '-01'));
		$lastDay = strtotime(date('Y', $dateTime).'-'.date('m', $dateTime).'-01');
		$lastDay = strtotime('+1 month', $lastDay);
		$lastDay = date(self::DT_MYSQL, strtotime('-1 second' ,$lastDay));
		return array($firstDay, $lastDay);
	}
}
?>