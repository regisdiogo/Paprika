<?php
namespace presentation;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class HttpRequest {

    private $contentType;
    private $queryString;
    private $urlParams;
    private $requestURI;
    private $request;
    private $postAjax = false;

    public function getContentType() {
        return $this->contentType;
    }

    public function setContentType($contentType) {
        $this->contentType = $contentType;
    }

    public function getQueryString($key = null) {
        if (isset($key)) {
            if (isset($this->queryString[$key])) {
                return $this->queryString[$key];
            } else {
                return null;
            }
        } else {
            return $this->queryString;
        }
    }

    public function setQueryString($queryString) {
        $this->queryString = $queryString;
    }

    public function getUrlParams($key = null) {
        if (isset($key)) {
            if (isset($this->urlParams[$key])) {
                return $this->urlParams[$key];
            } else {
                return null;
            }
        } else {
            return $this->urlParams;
        }
    }

    public function setUrlParams($urlParams) {
        $this->urlParams = $urlParams;
    }

    public function getRequestURI() {
        return $this->requestURI;
    }

    public function setRequestURI($requestURI) {
        $this->requestURI = $requestURI;
    }

    public function getRequest($key = null) {
        if (isset($key) && isset($this->request[$key])) {
            return $this->request[$key];
        } else {
            return $this->request;
        }
    }

    public function setRequest($request) {
        $this->request = $request;
    }

    public function setRequestByKey($key, $value) {
        $this->request[$key] = $value;
    }

    public function getPostAjax() {
        return $this->postAjax;
    }

    public function setPostAjax($postAjax) {
        $this->postAjax = $postAjax;
    }
}
?>