<?php
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

use entity\BaseEntity;

/**
 * @Table(name="entry")
 */
class Entry extends BaseEntity {

    /**
     * @Id(name="id")
     * @Label(value="Identification")
     */
    private $id;

    /**
     * @Column(name="topic")
     * @Label(value="Topic")
	 * @Input(type="select",fetchlist="TopicBusiness.getList",fetchitem="TopicBusiness.getItem",id="id",value="name")
     * @NotNull(message="Topic is required")
     */
    private $topic;

    /**
     * @Column(name="title")
     * @Label(value="Title")
     * @Input(type="text")
     * @NotNull(message="Title is required")
     */
    private $title;

    /**
     * @Column(name="content")
     * @Label(value="Content")
     * @Input(type="textarea",cols="31",rows="10")
     * @NotNull(message="Content is required")
     */
    private $content;

    /**
     * @Column(name="active")
     * @Label(value="Active")
     * @Input(type="checkbox")
     * @NotNull(message="Active is required")
     */
    private $active;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getTopic() {
        return $this->topic;
    }

    public function setTopic($topic) {
        $this->topic = $topic;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function getActive() {
        return $this->active;
    }

    public function setActive($active) {
        $this->active = $active;
    }

}
?>
