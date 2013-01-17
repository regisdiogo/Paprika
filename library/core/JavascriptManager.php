<?php
namespace core;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class JavascriptManager {

    private $commands = array();
    private $libs = array();
    private static $instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    /**
     * @return JavascriptManager
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new JavascriptManager();
        }
        return self::$instance;
    }

    public function putCommand($command) {
        $this->commands[] = $command;
    }

    public function windowLoad() {
        $jsPattern = '$(window).load(function(){'.PHP_EOL.'%1$s'.PHP_EOL.'});'.PHP_EOL;
        $output = implode(PHP_EOL, $this->commands);
        return sprintf($jsPattern, $output);
    }

    public function useAdditionalLib($libURL) {
        $this->libs[] = $libURL;
    }

    public function loadAdditionalLibs() {
        $jsLibs = '';
        foreach ($this->libs as $libURL) {
            $jsLibs .= '<script type="text/javascript" src="'.$libURL.'"></script>'.PHP_EOL;
        }
        return $jsLibs;
    }
}
?>