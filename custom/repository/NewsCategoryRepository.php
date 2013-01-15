<?php
use repository\BaseRepository;

class NewsCategoryRepository extends BaseRepository {

	public function __construct() {
		$this->setEntity(new NewsCategory());
	}

}
?>