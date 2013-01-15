<!DOCTYPE html>
<html lang="<?=\core\Config::HTML_CONTENT_LANGUAGE?>" id="<?=\core\Config::HTML_ID?>">
<head>
<title><?= isset($c[presentation\BasePresentation::PAGE_TITLE]) ? $c[presentation\BasePresentation::PAGE_TITLE] : '' ?></title>
<meta charset="<?=\core\Config::CONTENT_CHARSET?>" />
<meta http-equiv="content-type" content="text/html; charset=<?=\core\Config::CONTENT_CHARSET?>" />
<meta http-equiv="content-language" content="<?=\core\Config::HTML_CONTENT_LANGUAGE?>" />
<meta name="keywords" content="<?= isset($c[presentation\BasePresentation::PAGE_KEYWORDS]) ? $c[presentation\BasePresentation::PAGE_KEYWORDS] : '' ?>" />
<meta name="robots" content="<?= isset($c[presentation\BasePresentation::PAGE_ALLOW_ROBOTS]) ? $c[presentation\BasePresentation::PAGE_ALLOW_ROBOTS] : '' ?>" /> 
<link type="text/css" href="<?=\App::getInstance()->getWebDir()?>/css/custom/site.css" rel="stylesheet" />
<!--[if IE]>
<link rel="stylesheet" type="text/css" href="<?=\App::getInstance()->getWebDir()?>/css/custom/site-ie.css" />
<![endif]-->
</head>
<body>

<?
if (isset($c[presentation\BasePresentation::TEMPLATE_INNER_FILE])) {
	if (is_array($c[presentation\BasePresentation::TEMPLATE_INNER_FILE])) {
		foreach ($c[presentation\BasePresentation::TEMPLATE_INNER_FILE] as $file) {
			require($file);
		}
	} else {
		require($c[presentation\BasePresentation::TEMPLATE_INNER_FILE]);
	}
}
?>

<script type="text/javascript" src="<?=\App::getInstance()->getWebDir()?>/javascript/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="<?=\App::getInstance()->getWebDir()?>/javascript/jquery-ui-1.8.20.custom.min.js"></script>
<script type="text/javascript" src="<?=\App::getInstance()->getWebDir()?>/javascript/jquery.maskedinput-1.2.2.min.js"></script>
<script type="text/javascript" src="<?=\App::getInstance()->getWebDir()?>/javascript/jquery.meio.mask.min.js"></script>
<script type="text/javascript" src="<?=\App::getInstance()->getWebDir()?>/javascript/jquery.blockUI.js"></script>
<script type="text/javascript" src="<?=\App::getInstance()->getWebDir()?>/javascript/default.js"></script>
<script type="text/javascript">
<?=\core\JavascriptManager::getInstance()->windowLoad();?>
</script>
</body>
</html>