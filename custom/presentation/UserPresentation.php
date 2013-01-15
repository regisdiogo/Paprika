<?php
use presentation\BasePresentation;

class UserPresentation extends BasePresentation {

	public function __construct() {
		$this->setBusiness(new UserBusiness());
		$this->setListViewColumns(array(
		new \presentation\component\ListViewTextColumn('id')
		));
	}

}
?>