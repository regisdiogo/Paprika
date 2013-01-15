<?php
use repository\BaseRepository;

class NewsRepository extends BaseRepository {

	public function __construct() {
		$this->setEntity(new News());
	}

}
?>