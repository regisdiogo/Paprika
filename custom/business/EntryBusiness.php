<?php
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

use business\BaseBusiness;

class EntryBusiness extends BaseBusiness {

    public function __construct() {
        $this->setRepository(new EntryRepository());
    }

}
?>
