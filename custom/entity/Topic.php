<?php
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

use entity\BaseEntity;

/**
 * @Table(name="topic")
 */
class Topic extends BaseEntity {

    /**
     * @Id(name="id")
     * @Label(value="Identification")
     */
    private $id;

    /**
     * @Column(name="name")
     * @Label(value="Name")
     * @Input(type="text")
     * @NotNull(message="Name is required")
     */
    private $name;

    /**
     * @Column(name="description")
     * @Label(value="Description")
     * @Input(type="text")
     * @NotNull(message="Description is required")
     */
    private $description;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

}
?>
