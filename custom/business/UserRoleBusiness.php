<?php
use business\BaseBusiness;

class UserRoleBusiness extends BaseBusiness {

	public function __construct() {
		$this->setRepository(new UserRoleRepository());
	}

}
?>