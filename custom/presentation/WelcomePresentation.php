<?php
use presentation\BasePresentation;

/**
 * @Route(type="static",mapper="/",method="main",page="custom/home.php",template="core/page.template.php")
 */
class WelcomePresentation extends BasePresentation {
    
    public function __construct() {
        $this->setPageTitle('Paprika - Making PHP easier');
    }

}
?>