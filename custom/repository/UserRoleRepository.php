<?php
use repository\BaseRepository;

class UserRoleRepository extends BaseRepository {

	public function __construct() {
		$this->setEntity(new UserRole());
	}

}
?>