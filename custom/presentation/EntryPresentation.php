<?php
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

use presentation\BasePresentation;
use presentation\HttpRequest;
use presentation\HttpResponse;
use presentation\component\ListViewTextColumn;

/**
 * @Route(type="static",mapper="/entry/create.html",method="create",page="custom/form.base.php",template="core/page.template.php")
 * @Route(type="static",mapper="/entry/list.html",method="getList",page="custom/list.base.php",template="core/page.template.php")
 * @Route(type="dynamic",mapper="/entry/update-{id}.html",method="update",page="custom/form.base.php",template="core/page.template.php")
 * @Route(type="dynamic",mapper="/entry/delete-{id}.html",method="delete")
 */
class EntryPresentation extends BasePresentation {

    public function __construct() {
        $this->setEntity(new Entry());
        $this->setBusiness(new EntryBusiness());
        $this->setListViewColumns(array(
                new ListViewTextColumn('id', true, \App::getInstance()->findRoute(__CLASS__, 'update', false)),
                new ListViewTextColumn('topic'),
                new ListViewTextColumn('title'),
                new ListViewTextColumn('content'),
                new ListViewTextColumn('active')
        ));
    }

}
?>
