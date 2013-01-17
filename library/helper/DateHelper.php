<?php
namespace helper;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class DateHelper {

    const DT_FMT_COMPLETE = 'Y/m/d H:i:s';
    const DT_MYSQL = 'Y-m-d H:i:s';
    const DT_ONLY_HOURS = 'H:i:s';
    const DT_WITHOUT_HOURS = 'Y-m-d';
    const DT_PTBR = 'd/m/Y';
    const DT_TIMESTAMP = 'YmdHis';

    /**
     * Get current date with database style
     */
    public static function getSysdateDatabase() {
        return self::getSysdateCustomFormat(self::DT_FMT_COMPLETE);
    }

    public static function getTimeWithMicroSeconds($format, $showMicroSeconds = false) {
        return self::getSysdateCustomFormat(self::DT_TIMESTAMP, true, false);
    }

    /**
     * Get current date as string
     * @param $format
     * @param $showMicroSeconds
     */
    public static function getSysdateCustomFormat($format, $showMicroSeconds = false, $usePeriod = true) {
        $date = date($format);
        if ($showMicroSeconds) {
            if ($usePeriod)
                $date .= substr((string)microtime(), 1, 8);
            else
                $date .= substr((string)microtime(), 2, 8);
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

    public static function dateDiffFromToday($dateTarget) {
        $dateToday = time();
        $dateTarget = strtotime($dateTarget);
        return self::_date_diff($dateToday, $dateTarget);
        /*$dateToday = date("Y-m-d");

        $dateToday = new \DateTime($dateToday);
        $dateTarget = new \DateTime($dateTarget);

        $interval = $dateToday->diff($dateTarget);

        return $interval;*/
    }

    private static function _date_range_limit($start, $end, $adj, $a, $b, &$result) {
        if ($result[$a] < $start) {
            $result[$b] -= intval(($start - $result[$a] - 1) / $adj) + 1;
            $result[$a] += $adj * intval(($start - $result[$a] - 1) / $adj + 1);
        }

        if ($result[$a] >= $end) {
            $result[$b] += intval($result[$a] / $adj);
            $result[$a] -= $adj * intval($result[$a] / $adj);
        }

        return $result;
    }

    private static function _date_range_limit_days(&$base, &$result) {
        $days_in_month_leap = array(31, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $days_in_month = array(31, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

        self::_date_range_limit(1, 13, 12, "m", "y", $base);

        $year = $base["y"];
        $month = $base["m"];

        if (!$result["invert"]) {
            while ($result["d"] < 0) {
                $month--;
                if ($month < 1) {
                    $month += 12;
                    $year--;
                }

                $leapyear = $year % 400 == 0 || ($year % 100 != 0 && $year % 4 == 0);
                $days = $leapyear ? $days_in_month_leap[$month] : $days_in_month[$month];

                $result["d"] += $days;
                $result["m"]--;
            }
        } else {
            while ($result["d"] < 0) {
                $leapyear = $year % 400 == 0 || ($year % 100 != 0 && $year % 4 == 0);
                $days = $leapyear ? $days_in_month_leap[$month] : $days_in_month[$month];

                $result["d"] += $days;
                $result["m"]--;

                $month++;
                if ($month > 12) {
                    $month -= 12;
                    $year++;
                }
            }
        }

        return $result;
    }

    private static function _date_normalize(&$base, &$result) {
        $result = self::_date_range_limit(0, 60, 60, "s", "i", $result);
        $result = self::_date_range_limit(0, 60, 60, "i", "h", $result);
        $result = self::_date_range_limit(0, 24, 24, "h", "d", $result);
        $result = self::_date_range_limit(0, 12, 12, "m", "y", $result);

        $result = self::_date_range_limit_days($base, $result);

        $result = self::_date_range_limit(0, 12, 12, "m", "y", $result);

        return $result;
    }

    private static function _date_diff($one, $two) {
        $invert = false;
        if ($one > $two) {
            list($one, $two) = array($two, $one);
            $invert = true;
        }

        $key = array("y", "m", "d", "h", "i", "s");
        $a = array_combine($key, array_map("intval", explode(" ", date("Y m d 0 0 0", $one))));
        $b = array_combine($key, array_map("intval", explode(" ", date("Y m d 0 0 0", $two))));

        $result = array();
        $result["y"] = $b["y"] - $a["y"];
        $result["m"] = $b["m"] - $a["m"];
        $result["d"] = $b["d"] - $a["d"];
        $result["h"] = $b["h"] - $a["h"];
        $result["i"] = $b["i"] - $a["i"];
        $result["s"] = $b["s"] - $a["s"];
        $result["invert"] = $invert ? 1 : 0;
        $result["days"] = intval(abs(($one - $two)/86400));

        if ($invert) {
            self::_date_normalize($a, $result);
        } else {
            self::_date_normalize($b, $result);
        }

        $finalResult = new \stdClass();

        $finalResult->y = $result["y"];
        $finalResult->m = $result["m"];
        $finalResult->d = $result["d"];
        $finalResult->h = $result["h"];
        $finalResult->i = $result["i"];
        $finalResult->s = $result["s"];
        $finalResult->invert = $result["invert"];
        $finalResult->days = $result["days"];

        return $finalResult;
    }
}
?>