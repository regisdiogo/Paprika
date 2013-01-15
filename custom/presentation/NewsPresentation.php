<?php
use presentation\BasePresentation;

class NewsPresentation extends BasePresentation {

	public function __construct() {
		$this->setBusiness(new NewsBusiness());
		$this->setListViewColumns(array(
		new \presentation\component\ListViewTextColumn('id')
		));
	}
	
}
?>