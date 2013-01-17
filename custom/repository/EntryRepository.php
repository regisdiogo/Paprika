<?php
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

use repository\BaseRepository;

class EntryRepository extends BaseRepository {

    public function __construct() {
        $this->setEntity(new Entry());
    }

}
?>
