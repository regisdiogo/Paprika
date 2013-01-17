<?php
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

use presentation\BasePresentation;
use presentation\HttpRequest;
use presentation\HttpResponse;
use presentation\component\ListViewTextColumn;

/**
 * @Route(type="static",mapper="/topic/create.html",method="create",page="custom/form.base.php",template="core/page.template.php")
 * @Route(type="static",mapper="/topic/list.html",method="getList",page="custom/list.base.php",template="core/page.template.php")
 * @Route(type="dynamic",mapper="/topic/update-{id}.html",method="update",page="custom/form.base.php",template="core/page.template.php")
 * @Route(type="dynamic",mapper="/topic/delete-{id}.html",method="delete")
 */
class TopicPresentation extends BasePresentation {

    public function __construct() {
        $this->setEntity(new Topic());
        $this->setBusiness(new TopicBusiness());
        $this->setListViewColumns(array(
                new ListViewTextColumn('id', true, \App::getInstance()->findRoute(__CLASS__, 'update', false)),
                new ListViewTextColumn('name'),
                new ListViewTextColumn('description')
        ));
    }

}
?>
