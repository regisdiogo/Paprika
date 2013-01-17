<?php
namespace helper;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class ReflectionHelper {

    public static function getMethodResultFromInstance($classInstance, $methodName, $args = null) {
        $reflectionMethod = null;
        try {
            if (method_exists($classInstance, $methodName)) {
                $reflectionMethod = new \ReflectionMethod($classInstance, $methodName);
                $value = $reflectionMethod->invoke($classInstance, $args);
                return $value;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getMethodResultFromName($className, $methodName, $args = null) {
        $reflectionClass = null;
        try {
            $reflectionClass = new \ReflectionClass($className);
            $classInstance = $reflectionClass->newInstance();
            return self::getMethodResultFromInstance($classInstance, $methodName, $args);

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
?>