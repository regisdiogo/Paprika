<?php
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

use business\BaseBusiness;

class TopicBusiness extends BaseBusiness {

    public function __construct() {
        $this->setRepository(new TopicRepository());
    }

}
?>
