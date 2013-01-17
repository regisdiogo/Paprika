<div id="top">
    <div id="top-content">
    <div id="logo"><a href="<?=\App::getInstance()->findRoute('WelcomePresentation', 'main')?>"></a></div>
    </div>
</div>
<div id="top-shadow">&nbsp;</div>
<div id="main">
    <div id="main-content">
        <h1><?= isset($c[presentation\BasePresentation::PAGE_TITLE]) ? $c[presentation\BasePresentation::PAGE_TITLE] : '' ?></h1>
    </div>
</div>
<div id="body">
    <div id="body-content">
        <div id="body-upper">&nbsp;</div>
        <?php require('/../core/form.base.php'); ?>
        <div id="body-lower">&nbsp;</div>
    </div>
</div>
<div id="bottom"></div>