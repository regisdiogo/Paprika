<?php
namespace core;
if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

class Config {

	const APPLICATION_FILE_NAME = 'ppk.php';
	
	const HTML_ID = 'paprika';
	const HTML_CONTENT_LANGUAGE = 'en';

	const CONTENT_TYPE_TEXT = 'text/html;charset=utf-8';
	const CONTENT_TYPE_JSON = 'application/json;charset=utf-8';
	const CONTENT_CHARSET = 'utf-8';

	const CUSTOM_CODE_FOLDER = 'custom';
	const CUSTOM_CODE_PRESENTATION = 'presentation';
	const CUSTOM_CODE_ENTITY = 'entity';
	const CUSTOM_CODE_REPOSITORY = 'repository';
	const CUSTOM_CODE_BUSINESS = 'business';

	const PAGINANTION_MAX_ROWS = 15;

	const DATABASE_ADDRESS = 'mysql:host=localhost;dbname=paprika;port=3306';
	const DATABASE_USERNAME = 'root';
	const DATABASE_PASSWORD = '';

	const ERROR_PAGE_404 = 'core/error.404.php';
	const ERROR_PAGE_500 = 'core/error.500.php';

	const WEB_DIR = '/web';

	const LOGIN = 'UserPresentation.login';

	public static function getCustomCodeFolders() {
		return array(
				self::CUSTOM_CODE_PRESENTATION,
				self::CUSTOM_CODE_ENTITY,
				self::CUSTOM_CODE_REPOSITORY,
				self::CUSTOM_CODE_BUSINESS
		);
	}

	public static function getWebIncludePagePath() {
		return realpath('web/page').'/';
	}

	public static function getWebIncludeTemplatePath() {
		return realpath('web/template').'/';
	}
}
?>
