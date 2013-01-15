<?php
use business\BaseBusiness;

class NewsCategoryBusiness extends BaseBusiness {

	public function __construct() {
		$this->setRepository(new NewsCategoryRepository());
	}

}
?>