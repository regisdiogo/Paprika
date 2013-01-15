<?php
use presentation\BasePresentation;

/**
 * @Route(type="static",mapper="/news-category/list.ppk",method="getList",page="base.list.php",template="default.template.php")
 */
class NewsCategoryPresentation extends BasePresentation {

	public function __construct() {
		$this->setBusiness(new NewsCategoryBusiness());
		$this->setListViewColumns(array(
		new \presentation\component\ListViewTextColumn('id')
		));
	}

}
?>