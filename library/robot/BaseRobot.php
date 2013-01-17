<?php
namespace robot;

if (!defined('PAPRIKA_LIBRARY_PATH')) {
    die('Direct access not allowed');
}

abstract class BaseRobot {

    private $callback;

    public function setCallback($callback) {
        $this->callback = $callback;
    }

    public function main() {
        if (\helper\StringHelper::isNull($this->callback)) {
            throw new \Exception('Callback is required');
        }

        if (!isset($_POST['exec'])) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array('exec' => 'true'));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $result = trim(curl_exec($curl));

            echo $result;

            curl_close($curl);

        } else {
            \core\Logger::info('start');

            \helper\ReflectionHelper::getMethodResultFromInstance($this, $this->callback);

            \core\Logger::info('stop');
        }
    }
}
?>