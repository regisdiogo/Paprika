<?php
use presentation\BasePresentation;

class UserRolePresentation extends BasePresentation {

	public function __construct() {
		$this->setBusiness(new UserRoleBusiness());
		$this->setListViewColumns(array(
		new \presentation\component\ListViewTextColumn('id')
		));
	}

}
?>