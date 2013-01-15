<?php
namespace core\component;
if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

class SearchFilter {

	const COMPARISON_EQUALS = '=';
	const COMPARISON_NOT_EQUALS = '!=';
	const COMPARISON_LIKE = 'LIKE';
	const COMPARISON_BETWEEN = 'BETWEEN';

	private $comparisonType;
	private $useLikeWithPercent;
	private $property;
	private $value;

	public function __construct() {
		$this->setComparisonTypeToEquals();
		$this->useLikeWithPercent = false;
		$this->value = '';
	}

	public function getComparisonType() {
		return $this->comparisonType;
	}

	public function setComparisonTypeToLike() {
		$this->comparisonType = self::COMPARISON_LIKE;
	}

	public function setComparisonTypeToEquals() {
		$this->comparisonType = self::COMPARISON_EQUALS;
	}

	public function setComparisonTypeToNotEquals() {
		$this->comparisonType = self::COMPARISON_NOT_EQUALS;
	}

	public function setComparisonTypeToBetween() {
		$this->comparisonType = self::COMPARISON_BETWEEN;
	}

	public function getUseLikeWithPercent() {
		return $this->useLikeWithPercent;
	}

	public function setUseLikeWithPercent($useLikeWithPercent) {
		$this->useLikeWithPercent = $useLikeWithPercent;
	}

	public function getProperty() {
		return $this->property;
	}

	public function setProperty($property) {
		$this->property = $property;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		$this->value = $value;
	}
}
?>