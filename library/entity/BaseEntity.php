<?php
namespace entity;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

abstract class BaseEntity {

    public function __toString() {
        $rc = new \ReflectionClass($this);
        $properties = $rc->getProperties(\ReflectionProperty::IS_PRIVATE);

        $string = array();

        foreach ($properties as $property) {
            $method = 'get'.ucfirst($property->getName());
            if (method_exists($this, $method)) {
                $result = \helper\ReflectionHelper::getMethodResultFromInstance($this, $method);
                $string[] = sprintf('%s = %s', $property->getName(), $result == null ? 'null' : '\''.$result.'\'');
            }
        }

        return sprintf('class %s { %s }', $rc->getName(), implode(', ', $string));
    }

}
?>