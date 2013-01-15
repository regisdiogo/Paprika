<?php
use repository\BaseRepository;

class UserRepository extends BaseRepository {

	public function __construct() {
		$this->setEntity(new User());
	}

}
?>