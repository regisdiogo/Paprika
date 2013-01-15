<?php
use business\BaseBusiness;

class UserBusiness extends BaseBusiness {

	public function __construct() {
		$this->setRepository(new UserRepository());
	}

}
?>