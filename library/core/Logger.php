<?php
namespace core;
if (!defined('PAPRIKA_LIBRARY_PATH')) {
    die('Direct access not allowed');
}

class Logger {

    const DEBUG = 1;

    const INFO = 2;

    const WARNING = 3;

    const ERROR = 4;

    const FATAL = 5;

    private static $instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    /**
     * @return Logger
     */
    private static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    public static function debug($log) {
        self::getInstance()->log(self::DEBUG, $log);
    }

    public static function info($log) {
        self::getInstance()->log(self::INFO, $log);
    }

    public static function warning($log) {
        self::getInstance()->log(self::WARNING, $log);
    }

    public static function error($log) {
        self::getInstance()->log(self::ERROR, $log);
    }

    public static function fatal($log) {
        self::getInstance()->log(self::FATAL, $log);
    }

    private function getTypeText($type) {
        switch($type) {
            case self::DEBUG: return 'DEBUG';

            case self::INFO: return 'INFO';

            case self::WARNING: return 'WARNING';

            case self::ERROR: return 'ERROR';

            case self::FATAL: return 'FATAL';
        }
    }

    private function log($type, $log) {
        if ($type < Config::LOGGING_LEVEL)
            return;

        $detailTime = \helper\DateHelper::getTimeWithMicroSeconds();
        $logTime = \helper\DateHelper::getSysdateCustomFormat(\helper\DateHelper::DT_ONLY_HOURS, true);
        $logDay = \helper\DateHelper::getSysdateCustomFormat(\helper\DateHelper::DT_WITHOUT_HOURS);

        $trace = array();
        foreach (debug_backtrace() as $line) {
            if (isset($line['file'])) {
                $trace[] = $line['class'].$line['type'].$line['function'].'('.$line['file'].':'.$line['line'].')';
            } else {
                $trace[] = $line['class'].$line['type'].$line['function'];
            }
        }

        if (is_a($log, 'Exception')) {
            $message = $log->getMessage();
        } else {
            $message = $log;
        }

        $content = $logTime.' '.self::getTypeText($type).' '.$line['class'].$line['type'].$line['function'].' - '.$message;
        $content .= ' <a href="'.$logDay.'/'.$detailTime.'.html">[ detail ]</a>'.'<br />';

        \helper\FileHelper::createFolder(Config::LOGGING_FOLDER, ROOT_PATH);

        \helper\FileHelper::createFolder($logDay, ROOT_PATH.Config::LOGGING_FOLDER);

        \helper\FileHelper::putContent($logDay.'.html', ROOT_PATH.Config::LOGGING_FOLDER, $content);

        \helper\FileHelper::putContent($detailTime.'.html', ROOT_PATH.Config::LOGGING_FOLDER.'/'.$logDay, implode('<br />', $trace));
    }
}
?>