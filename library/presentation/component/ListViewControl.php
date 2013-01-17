<?php
namespace presentation\component;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class ListViewControl {

    const PAGE = 'page';
    const ORDER_BY = 'orderby';
    const ORDER_DIRECTION = 'direction';
    const ORDER_DIRECTION_ASC = 'asc';
    const ORDER_DIRECTION_DESC = 'desc';

    private $currentPage;
    private $orderBy;
    private $orderDirection;
    private $totalRecords;
    private $rowsPerPage;

    public function __construct($queryString, $totalRecords, $rowsPerPage) {
        $this->currentPage = isset($queryString[self::PAGE]) ? $queryString[self::PAGE] : 1;
        $this->orderBy = isset($queryString[self::ORDER_BY]) ? $queryString[self::ORDER_BY] : null;
        $this->orderDirection = isset($queryString[self::ORDER_DIRECTION]) ? $queryString[self::ORDER_DIRECTION] : null;
        $this->totalRecords = $totalRecords;
        $this->rowsPerPage = $rowsPerPage;
    }

    public function makePagination() {
        $links = array();
        if (!$this->rowsPerPage) {
            return $links;
        }
        $totalPages = ceil($this->totalRecords / $this->rowsPerPage);

        $previousLink = array();
        if ($this->orderBy) {
            $previousLink[] = self::ORDER_BY.'='.$this->orderBy;
        }
        if ($this->orderDirection) {
            $previousLink[] = self::ORDER_DIRECTION.'='.$this->orderDirection;
        }
        $previousLink = implode('&', $previousLink);
        if ($previousLink) {
            $previousLink = '&'.$previousLink;
        }

        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i != $this->currentPage) {
                $links[$i] = '?page='.$i.$previousLink;
            } else {
                $links[$i] = '';
            }
        }

        return $links;
    }

    public function mountOrderByLink($name) {
        $orderDirection = null;
        if ($this->orderBy != $name) {
            $orderDirection = self::ORDER_DIRECTION_ASC;
        } else {
            if (!$this->orderDirection) {
                $orderDirection = self::ORDER_DIRECTION_ASC;
            } else if (strtolower($this->orderDirection) == self::ORDER_DIRECTION_ASC) {
                $orderDirection = self::ORDER_DIRECTION_DESC;
            } else {
                $orderDirection = self::ORDER_DIRECTION_ASC;
            }
        }
        $link = '?'.self::ORDER_BY.'='.$name.'&'.self::ORDER_DIRECTION.'='.$orderDirection;
        return $link;
    }

    public function getOffset() {
        if ($this->rowsPerPage && $this->currentPage) {
            $offset = ($this->currentPage - 1) * $this->rowsPerPage;
        } else {
            $offset = 0;
        }
        return $offset;
    }

    public function getCurrentPage() {
        return $this->currentPage;
    }

    public function getOrderBy() {
        return $this->orderBy;
    }

    public function getOrderDirection() {
        return $this->orderDirection;
    }

    public function getTotalRecords() {
        return $this->totalRecords;
    }

    public function getRowsPerPage() {
        return $this->rowsPerPage;
    }
}
?>