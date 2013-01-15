<?php
require('App.php');

use core\Config;

class AppUtils {

	private static $instance = null;

	private function __construct() {
		App::getInstance();
	}

	private function __clone() {
	}

	/**
	 * @return AppUtils
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new AppUtils();
		}
		return self::$instance;
	}

	public function import() {
		try {

			$import = new \core\Import();

			if (!isset($_GET['action'])) {
				$tables = $import->listTables();
					
				if ($tables) {
					echo '<ul>';
					foreach ($tables as $row) {
						echo '<li>';
						echo $row;
						echo '</li>';
					}
					echo '</ul>';
				}
					
			} else if ($_GET['action'] == 'personalize') {
				$form = $import->personalizeTable($_GET['tableName']);
				echo '<form '.$form[0][0].' >';
				echo 'Personalize all info!<br /><br />'.PHP_EOL;
				for ($i = 1; $i < count($form); $i++) {
					if ($i == 1) {
						echo 'Table: ';
					} else if ($i < count($form) -2) {
						echo 'Column: ';
					}
					echo $form[$i][0];
					if (isset($form[$i][1])) {
						echo ' - Label: '.$form[$i][1];
					}
					echo '<br />'.PHP_EOL;
				}
				echo '</form>';
					
			} else if ($_GET['action'] == 'generate') {
				if ($import->generateClass($_POST)) {
					header("location: ".$_SERVER['SCRIPT_NAME']);
				}
			}

		} catch (\Exception $e) {
			p($e);
		}
	}

	public function siteMap() {
		try {
			$presentationClasses = \helper\FolderHelper::listFilesByType(realpath(Config::CUSTOM_CODE_FOLDER).'/'.Config::CUSTOM_CODE_PRESENTATION, '.php', true);

			echo '<ul>'.PHP_EOL;

			foreach ($presentationClasses as $class) {
				echo '<li>'.$class.'</li>';

				$listAnnotation = \annotation\Annotation::extractAnnotations($class);
				if (isset($listAnnotation[\annotation\Annotation::CLAZZ])) {
					foreach ($listAnnotation[\annotation\Annotation::CLAZZ] as $annotation) {
						if ($annotation[\annotation\Annotation::BEHAVIOR] == \annotation\Annotation::T_ROUTE
								&& $annotation[\annotation\Annotation::VALUES][\annotation\Annotation::O_TYPE] == 'static') {
							if (isset($annotation[\annotation\Annotation::VALUES][\annotation\Annotation::O_ROLE]))
								continue;
							if (isset($annotation[\annotation\Annotation::VALUES][\annotation\Annotation::O_CONTENT_TYPE])
									&& $annotation[\annotation\Annotation::VALUES][\annotation\Annotation::O_CONTENT_TYPE] == 'json')
								continue;
							$link = $annotation[\annotation\Annotation::VALUES][\annotation\Annotation::O_MAPPER];
							$basePrefix = preg_replace('/\/sitemap.php$/', '', $_SERVER['SCRIPT_NAME']);
							echo '<li style="margin-left: 25px; list-style: circle"><a href="'.$basePrefix.$link.'">'.$link.'</a></li>';
						}
					}
				}
			}

			echo '</ul>'.PHP_EOL;

		} catch (\Exception $e) {
			p($e);
		}
	}
}
?>