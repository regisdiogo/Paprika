<?php
use business\BaseBusiness;

class NewsBusiness extends BaseBusiness {

	public function __construct() {
		$this->setRepository(new NewsRepository());
	}

}
?>