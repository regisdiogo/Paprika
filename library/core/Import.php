<?php
namespace core;

use helper\FileHelper;
use helper\HtmlHelper;
use repository\SupportDatabase;

class Import extends SupportDatabase {

	const TAB = '	';
	const CLASS_HEADER = '<?php%1$sif (!defined(\'PAPRIKA_PATH\')) {%1$s%2$sdie(\'Direct access not allowed\');%1$s}%1$s%1$s';
	const CLASS_ENTITY_BODY = 'use entity\BaseEntity;%1$s%1$s/**%1$s * @Table(name="%3$s")%1$s */%1$sclass %2$s extends BaseEntity {%1$s%1$s%4$s%1$s}%1$s?>';
	const CLASS_REPOSITORY_BODY = 'use repository\BaseRepository;%1$s%1$sclass %3$sRepository extends BaseRepository {%1$s%1$s%2$spublic function __construct() {%1$s%2$s%2$s$this->setEntity(new %3$s());%1$s%2$s}%1$s%1$s}%1$s?>';
	const CLASS_BUSINESS_BODY = 'use business\BaseBusiness;%1$s%1$sclass %3$sBusiness extends BaseBusiness {%1$s%1$s%2$spublic function __construct() {%1$s%2$s%2$s$this->setRepository(new %3$sRepository());%1$s%2$s}%1$s%1$s%4$s}%1$s?>';
	const CLASS_PRESENTATION_BODY = 'use presentation\BasePresentation;%1$suse presentation\HttpRequest;%1$suse presentation\HttpResponse;%1$suse presentation\component\ListViewTextColumn;%1$s%1$s/**%1$s * @Route(type="static",mapper="/%4$s/create.html",method="create",page="core/form.base.php",template="default.template.php")%1$s * @Route(type="static",mapper="/%4$s/list.html",method="getList",page="core/list.base.php",template="default.template.php")%1$s * @Route(type="dynamic",mapper="/%4$s/update-{%5$s}.html",method="update",page="core/form.base.php",template="default.template.php")%1$s * @Route(type="dynamic",mapper="/%4$s/delete-{%5$s}.html",method="delete")%1$s */%1$sclass %3$sPresentation extends BasePresentation {%1$s%1$s%2$spublic function __construct() {%1$s%2$s%2$s$this->setEntity(new %3$s());%1$s%2$s%2$s$this->setBusiness(new %3$sBusiness());%1$s%2$s%2$s$this->setListViewColumns(array(%1$s%2$s%2$s%2$s%2$s%6$s%1$s%2$s%2$s));%1$s%2$s}%1$s%1$s}%1$s?>';
	const CLASS_PROPERTY = '%4$s%2$sprivate $%3$s;%1$s';
	const CLASS_GET_SET = '%2$spublic function get%3$s() {%1$s%2$s%2$sreturn $this->%4$s;%1$s%2$s}%1$s%1$s%2$spublic function set%3$s($%4$s) {%1$s%2$s%2$s$this->%4$s = $%4$s;%1$s%2$s}%1$s';
	const PROP_DOC = '%2$s/**%1$s%3$s%1$s%2$s */%1$s';
	const PROP_DOC_SINGLE = '%1$s * @%2$s(%3$s)';
	const FUNC_GET_BY = '%2$spublic function %3$s($%4$s) {%1$s%2$s%2$s$filters = array();%1$s%1$s%2$s%2$s$searchFilter = new \core\component\SearchFilter();%1$s%2$s%2$s$searchFilter->setComparisonTypeToLike();%1$s%2$s%2$s$searchFilter->setValue($%4$s);%1$s%2$s%2$s$searchFilter->setProperty(\'%4$s\');%1$s%1$s%2$s%2$s$filters[] = $searchFilter;%1$s%1$s%2$s%2$sreturn $this->getFilteredList($filters);%1$s%2$s}%1$s%1$s';

	private $businessFunctions = array();

	public function listTables() {
		try {
			$allTables = 'SHOW TABLES';
			$statement = $this->getConnection()->prepare($allTables);
			$statement->execute();
			$statement->setFetchMode(\PDO::FETCH_BOTH);
			$resultSet = $statement->fetchAll();

			if ($resultSet) {
				$tables = array();
				foreach ($resultSet as $dataRow) {
					$tables[] = HtmlHelper::createLink($dataRow[0], '?action=personalize&tableName='.$dataRow[0]);
				}

				return $tables;
			}

		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function personalizeTable($tableName) {
		try {
			$formElements = array();
			$formElements[] = array(HtmlHelper::createFormParameters('personalize', '?action=generate', 'post'));
			$formElements[] = array(HtmlHelper::createInput('text', 'name', 'name', $tableName));

			$columns = $this->getColumnsFromTable($tableName);

			foreach ($columns as $column) {
				$inputId = 'ppk-';
				if ($column->Key == 'PRI') {
					$inputId .= 'id-';
				} else {
					$inputId .= 'column-';
				}
				$inputId .= $column->Field;
				$inputLabel = 'ppk-label-'.$column->Field;

				$formElements[] = array(
						HtmlHelper::createInput('text', $inputId, $inputId, $column->Field),
						HtmlHelper::createInput('text', $inputLabel, $inputLabel, $column->Field)
				);
			}

			$formElements[] = array(HtmlHelper::createInput('hidden', 'tableName', 'tableName', $tableName));
			$formElements[] = array(HtmlHelper::createInput('submit', 'submit', 'submit', 'Generate'));

			return $formElements;

		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function generateClass($request) {
		try {
			if ($request) {

				$this->businessFunctions = array();

				$tableColumns = $this->getColumnsFromTable($request['tableName']);

				$columns = array();

				foreach ($request as $key=>$custom) {
					$isId = false;
					$sizeLabel = 0;
					if (strstr($key, 'ppk-id-')) {
						$isId = true;
						$sizeLabel = 7;

					} else if (strstr($key, 'ppk-column-')) {
						$sizeLabel = 11;

					}

					if ($sizeLabel) {
						$col = substr($key, $sizeLabel);
						$label = utf8_encode($request['ppk-label-'.$col]);
						foreach ($tableColumns as $tblCol) {
							if ($tblCol->Field == $col) {
								$columns[] = array(
										'IsId' => $isId,
										'Column' => $col,
										'Property' => $custom,
										'Label' => $label,
										'TableColumn' => $tblCol
								);
								break;
							}
						}
					}
				}

				$entityClass = $this->createEntityClass($request['name'], $request['tableName'], $columns);
				$repositoryClass = $this->createRepositoryClass($request['name']);
				$businessClass = $this->createBusinessClass($request['name']);
				$presentationClass = $this->createPresentationClass($request['name'], $columns);

				FileHelper::putContent($request['name'].'.php', Config::CUSTOM_CODE_FOLDER.'/'.Config::CUSTOM_CODE_ENTITY, $entityClass);
				FileHelper::putContent($request['name'].'Repository.php', Config::CUSTOM_CODE_FOLDER.'/'.Config::CUSTOM_CODE_REPOSITORY, $repositoryClass);
				FileHelper::putContent($request['name'].'Business.php', Config::CUSTOM_CODE_FOLDER.'/'.Config::CUSTOM_CODE_BUSINESS, $businessClass);
				FileHelper::putContent($request['name'].'Presentation.php', Config::CUSTOM_CODE_FOLDER.'/'.Config::CUSTOM_CODE_PRESENTATION, $presentationClass);

				return true;
			}

		} catch (\Exception $e) {
			throw $e;
		}
	}

	private function getColumnsFromTable($tableName) {
		try {
			$sql = 'SHOW COLUMNS FROM '.$tableName;
			$statement = $this->getConnection()->prepare($sql);
			$statement->execute();
			$statement->setFetchMode(\PDO::FETCH_OBJ);
			$resultSet = $statement->fetchAll();

			$columns = array();
			if ($resultSet) {
				foreach ($resultSet as $row) {
					$columns[] = $row;
				}
			}
			return $columns;

		} catch (\Exception $e) {
			throw $e;
		}
	}

	private function createEntityClass($customName, $tableName, $columns) {
		try {
			$entityClass = sprintf(self::CLASS_HEADER, PHP_EOL, self::TAB);

			$entityProperties = array();
			$entityGettersSetters = array();

			foreach ($columns as $col) {
				$docs = array();

				$docs[] = sprintf(self::PROP_DOC_SINGLE, self::TAB, $col['IsId'] ? 'Id' : 'Column', 'name="'.$col['Column'].'"');
				$docs[] = sprintf(self::PROP_DOC_SINGLE, self::TAB, 'Label', 'value="'.$col['Label'].'"');

				if (!$col['IsId']) {
					$type = 'text';
					$maxlength = 0;
					$useFormat = false;
					$formatType = '';
					$formatMask = '';
					$formatExtra = '';

					if (strstr($col['TableColumn']->Type, 'double') !== false) {
						$maxlength = substr($col['TableColumn']->Type, strpos($col['TableColumn']->Type, '(') + 1);
						$maxlength = substr($maxlength, 0, strpos($maxlength, ')'));
						$maxlength = preg_split('/[,]/', $maxlength);

						$useFormat = true;
						$formatType = 'decimal';
						$formatExtra = 'prefix="R$"';
						for ($i = 0; $i < $maxlength[0]; $i++)
							$formatMask .= '9';
						$formatMask .= '.';
						for ($i = 0; $i < $maxlength[1]; $i++)
							$formatMask .= '9';

					} else if (strstr($col['TableColumn']->Type, 'varchar') !== false) {
						$maxlength = substr($col['TableColumn']->Type, strpos($col['TableColumn']->Type, '(') + 1);
						$maxlength = substr($maxlength, 0, strpos($maxlength, ')'));

					} else if (strstr($col['TableColumn']->Type, 'tinyint') !== false) {
						$maxlength = substr($col['TableColumn']->Type, strpos($col['TableColumn']->Type, '(') + 1);
						$maxlength = substr($maxlength, 0, strpos($maxlength, ')'));

						if ($maxlength == 1) {
							$type = 'checkbox';
							$maxlength = 0;
						}
					} else if (strstr($col['TableColumn']->Type, 'int') !== false) {
						$maxlength = substr($col['TableColumn']->Type, strpos($col['TableColumn']->Type, '(') + 1);
						$maxlength = substr($maxlength, 0, strpos($maxlength, ')'));

						$useFormat = true;
						$formatType = 'number';
						for ($i = 0; $i < $maxlength; $i++)
							$formatMask .= '9';

					} else if (strstr($col['TableColumn']->Type, 'timestamp') !== false || strstr($col['TableColumn']->Type, 'datetime') !== false) {
						$useFormat = true;
						$formatType = 'date';
						$formatMask = '99/99/9999';
						$formatExtra = 'patternJS="dd/mm/yy",patternPHP="d/m/Y"';
					}

					$args = 'type="'.$type.'"';
					if ($maxlength)
						$args .= ',maxlength="'.$maxlength.'"';

					$docs[] = sprintf(self::PROP_DOC_SINGLE, self::TAB, 'Input', $args);

					if ($useFormat) {
						if (strlen($formatExtra) > 0)
							$formatExtra = ','.$formatExtra;
						$docs[] = sprintf(self::PROP_DOC_SINGLE, self::TAB, 'Format', 'type="'.$formatType.'",mask="'.$formatMask.'"'.$formatExtra);
					}

					if ($col['TableColumn']->Null == 'NO')
						$docs[] = sprintf(self::PROP_DOC_SINGLE, self::TAB, 'NotNull', 'message="'.$col['Label'].' é obrigatório"');

					if ($col['TableColumn']->Key == 'UNI') {
						$funcName = 'getBy'.ucfirst($col['Property']);
						$this->businessFunctions[$col['Property']] = $funcName;
						$args = sprintf('check="%1$sBusiness.%2$s",message="Esse %3$s já foi utilizado"', $customName, $funcName, $col['Label']);
						$docs[] = sprintf(self::PROP_DOC_SINGLE, self::TAB, 'Unique', $args);

					} else if ($col['TableColumn']->Key == 'UNI') {
						// TODO: Não sei o que fazer ainda
					}

				}

				$docs = sprintf(self::PROP_DOC, PHP_EOL, self::TAB, implode(PHP_EOL, $docs));

				$entityProperties[] = sprintf(self::CLASS_PROPERTY, PHP_EOL, self::TAB, $col['Property'], $docs);
				$entityGettersSetters[] = sprintf(self::CLASS_GET_SET, PHP_EOL, self::TAB, ucfirst($col['Property']), $col['Property']);
			}

			$entityProperties = implode(PHP_EOL, $entityProperties);
			$entityGettersSetters = implode(PHP_EOL, $entityGettersSetters);

			$entityClass .= sprintf(self::CLASS_ENTITY_BODY, PHP_EOL, $customName, $tableName, $entityProperties.PHP_EOL.$entityGettersSetters);

			return $entityClass;

		} catch (\Exception $e) {
			throw $e;
		}
	}

	private function createRepositoryClass($customName) {
		try {

			$repositoryClass = sprintf(self::CLASS_HEADER, PHP_EOL, self::TAB);

			$repositoryClass .= sprintf(self::CLASS_REPOSITORY_BODY, PHP_EOL, self::TAB, $customName);

			return $repositoryClass;

		} catch (\Exception $e) {
			throw $e;
		}
	}

	private function createBusinessClass($customName) {
		try {

			$businessClass = sprintf(self::CLASS_HEADER, PHP_EOL, self::TAB);

			$functions = array();

			foreach ($this->businessFunctions as $key=>$function) {
				if (strstr($function, 'getBy') !== false) {
					$functions[] = sprintf(self::FUNC_GET_BY, PHP_EOL, self::TAB, $function, $key);
				}
			}

			$businessClass .= sprintf(self::CLASS_BUSINESS_BODY, PHP_EOL, self::TAB, $customName, implode(PHP_EOL, $functions));

			return $businessClass;

		} catch (\Exception $e) {
			throw $e;
		}
	}

	private function createPresentationClass($customName, $columns) {
		try {

			$presentationClass = sprintf(self::CLASS_HEADER, PHP_EOL, self::TAB);

			$listViewColumns = array();
			$identityProperty = '';

			foreach ($columns as $col) {
				if ($col['IsId']) {
					$identityProperty = $col['Property'];
					$listViewColumns[] = 'new ListViewTextColumn(\''.$col['Property'].'\', true, \App::getInstance()->findRoute(__CLASS__, \'update\', false))';

				} else {
					$listViewColumns[] = 'new ListViewTextColumn(\''.$col['Property'].'\')';
				}
			}

			$listViewColumns = implode(','.PHP_EOL.self::TAB.self::TAB.self::TAB.self::TAB, $listViewColumns);

			$presentationClass .= sprintf(self::CLASS_PRESENTATION_BODY, PHP_EOL, self::TAB, $customName, strtolower($customName), $identityProperty, $listViewColumns);

			return $presentationClass;
		}
		catch (\Exception $e) {
			throw $e;
		}
	}
}
?>