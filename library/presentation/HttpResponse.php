<?php
namespace presentation;
if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

class HttpResponse {

	private $success = false;
	private $content;
	private $redirectURL;
	private $message;
	private $page;

	public function getSuccess() {
		return $this->success;
	}

	public function setSuccess($success) {
		$this->success = $success;
	}

	public function getContent() {
		return $this->content;
	}

	public function setContent($content) {
		$this->content = $content;
	}

	public function getRedirectURL() {
		return $this->redirectURL;
	}

	public function setRedirectURL($redirectURL) {
		$this->redirectURL = $redirectURL;
	}

	public function getMessage() {
		return $this->message;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function getPage() {
	    return $this->page;
	}

	public function setPage($page) {
	    $this->page = $page;
	}
}
?>