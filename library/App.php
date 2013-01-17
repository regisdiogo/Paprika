<?php
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

use annotation\Annotation;
use core\Config;
use core\SessionManager;
use helper\JsonHelper;
use presentation\BasePresentation;

class App {

    private static $instance = null;

    private function __construct() {
        spl_autoload_register(array($this, 'loadPaprikaClass'));
        spl_autoload_register(array($this, 'loadCustomCodeClass'));
    }

    private function __clone() {
    }

    /**
     * @return App
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new App();
        }
        return self::$instance;
    }

    public function getURIPrevious() {
        return $this->getBasePrefix().SessionManager::getInstance()->getValue(SessionManager::URI_PREVIOUS);
    }

    public function setURICurrent($uri) {
        $uriCurrent = SessionManager::getInstance()->getValue(SessionManager::URI_CURRENT);
        if ($uri != $uriCurrent) {
            SessionManager::getInstance()->putValue(SessionManager::URI_PREVIOUS, $uriCurrent);
            SessionManager::getInstance()->putValue(SessionManager::URI_CURRENT, $uri);
        }
    }

    public function getOutputMessage() {
        $outputMessage = SessionManager::getInstance()->getValue(SessionManager::OUTPUT_MESSAGE);
        SessionManager::getInstance()->destroy(SessionManager::OUTPUT_MESSAGE);
        return $outputMessage;
    }

    public function setOutputMessage($outputMessage) {
        SessionManager::getInstance()->putValue(SessionManager::OUTPUT_MESSAGE, $outputMessage);
    }

    public function getBasePrefix() {
        return SessionManager::getInstance()->getValue(SessionManager::BASE_PREFIX);
    }

    public function getWebDir() {
        return $this->getBasePrefix().\core\Config::WEB_DIR;
    }

    private function setBasePrefix($basePrefix) {
        $storedBasePrefix = SessionManager::getInstance()->getValue(SessionManager::BASE_PREFIX);
        if ($basePrefix != $storedBasePrefix) {
            SessionManager::getInstance()->putValue(SessionManager::BASE_PREFIX, $basePrefix);
        }
    }

    public function init() {
        ob_start();
        try {
            SessionManager::getInstance()->init();

            $mappingFound = false;

            $listRequestURI = array();
            $listRequestURI[] = $_SERVER['REQUEST_URI'];
            if (strpos($_SERVER['REQUEST_URI'], '?')) {
                $urlArray = preg_split('/\?/', $_SERVER['REQUEST_URI']);
                $listRequestURI[] = $urlArray[0];
            }
            if (preg_match('/(.)*\/(\w)+$/', $_SERVER['REQUEST_URI'])) {
                $listRequestURI[] = $_SERVER['REQUEST_URI'].'/';
            }

            $presentationClasses = \helper\FolderHelper::listFilesByType(PAPRIKA_CUSTOM_PATH.'/'.Config::CUSTOM_CODE_PRESENTATION, '.php', true);

            foreach ($listRequestURI as $requestURI) {
                $basePrefix = preg_replace('/\/'.Config::APPLICATION_FILE_NAME.'$/', '', $_SERVER['SCRIPT_NAME']);
                if ($basePrefix) {
                    $this->setBasePrefix($basePrefix);
                    $requestURI = substr($requestURI, strlen($this->getBasePrefix()), strlen($requestURI));
                }

                $requestURI = strtolower($requestURI);

                $mappingFound = $this->findMapping($presentationClasses, $requestURI);

                if ($mappingFound) {
                    break;
                }
            }

            if (!$mappingFound) {
                if (\helper\StringHelper::isNotNull(Config::MAPPING_NOT_FOUNDED)) {

                    $aux = explode('.', Config::MAPPING_NOT_FOUNDED);

                    $annotation = array();
                    $annotation[Annotation::VALUES][Annotation::O_METHOD] = $aux[1];

                    $reflectionClass = new \ReflectionClass($aux[0]);
                    $classInstance = $reflectionClass->newInstance();

                    $this->invokeMappingClass($classInstance, $annotation, $this->getQueryString(), $requestURI);

                } else {
                    throw new \core\exception\MappingNotFoundException($requestURI);
                }
            }

        } catch (\Exception $e) {
            $this->handleException($e);
        }
        ob_flush();
    }

    public function redirect($url, $message = null) {
        if (isset($message)) {
            $this->setOutputMessage($message);
        }
        header('Location: '.$url);
        exit;
    }

    public function findRoute($className, $method, $usesBasePrefix = true, $args = null) {
        $presentationClasses = \helper\FolderHelper::listFilesByType(PAPRIKA_CUSTOM_PATH.'/'.Config::CUSTOM_CODE_PRESENTATION, '.php', true);
        foreach ($presentationClasses as $presentationClassName) {
            $listAnnotation = \annotation\Annotation::extractAnnotations($presentationClassName);
            if (isset($listAnnotation[\annotation\Annotation::CLAZZ])) {
                foreach ($listAnnotation[\annotation\Annotation::CLAZZ] as $annotation) {
                    if ($annotation[Annotation::BEHAVIOR] == Annotation::T_ROUTE) {
                        if ($presentationClassName == $className && $annotation[Annotation::VALUES][Annotation::O_METHOD] == $method) {
                            $url = null;
                            if ($usesBasePrefix) {
                                $url = $this->getBasePrefix().$annotation[Annotation::VALUES][Annotation::O_MAPPER];
                            } else {
                                $url = $annotation[Annotation::VALUES][Annotation::O_MAPPER];
                            }
                            if (isset($args)) {
                                foreach ($args as $key=>$arg) {
                                    $var = '({'.$key.'})';
                                    $url = preg_replace($var, $arg, $url);
                                }
                            }
                            return $url;
                        }
                    }
                }
            }
        }
        return null;
    }

    private function findMapping($presentationClasses, $requestURI) {
        $queryString = $this->getQueryString();

        foreach ($presentationClasses as $className) {
            $listAnnotation = \annotation\Annotation::extractAnnotations($className);
            if (isset($listAnnotation[\annotation\Annotation::CLAZZ])) {
                foreach ($listAnnotation[\annotation\Annotation::CLAZZ] as $annotation) {
                    if ($annotation[Annotation::BEHAVIOR] == Annotation::T_ROUTE) {
                        if ($annotation[Annotation::VALUES][Annotation::O_TYPE] == 'static' && $annotation[Annotation::VALUES][Annotation::O_MAPPER] == $requestURI)  {
                            $reflectionClass = new \ReflectionClass($className);
                            $classInstance = $reflectionClass->newInstance();
                            if (!method_exists($classInstance, $annotation[Annotation::VALUES][Annotation::O_METHOD])) {
                                $annotation[Annotation::VALUES][Annotation::O_METHOD] = 'main';
                            }
                            $this->invokeMappingClass($classInstance, $annotation, $queryString, $requestURI);
                            return true;

                        } else if ($annotation[Annotation::VALUES][Annotation::O_TYPE] == 'dynamic') {
                            $var = addcslashes($annotation[Annotation::VALUES][Annotation::O_MAPPER], '/.');
                            $var = '/^'.$var.'$/';
                            $var = str_replace('?', '\?', $var);
                            $var = str_replace('{', '(?\'p', $var);
                            $var = str_replace('}', '\'([^\/])+?)', $var);
                            if (preg_match($var, $requestURI, $matches, PREG_OFFSET_CAPTURE)) {
                                $reflectionClass = new \ReflectionClass($className);
                                $classInstance = $reflectionClass->newInstance();
                                if (method_exists($classInstance, $annotation[Annotation::VALUES][Annotation::O_METHOD])) {
                                    $urlParams = array();
                                    foreach ($matches as $key=>$value) {
                                        if (strstr($key, 'p')) {
                                            $urlParams[substr($key, 1, strlen($key))] = $value[0];
                                        }
                                    }
                                    $this->invokeMappingClass($classInstance, $annotation, $queryString, $requestURI, $urlParams);
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    private function invokeMappingClass($classInstance, $annotation, $queryString, $requestURI, $urlParams = null) {
        $this->setURICurrent($requestURI);
        if (isset($annotation[Annotation::VALUES][Annotation::O_ROLE])) {
            $this->controlAccess($annotation[Annotation::VALUES][Annotation::O_ROLE]);
        }

        $httpRequest = new \presentation\HttpRequest();
        $httpRequest->setRequest($_POST);
        $httpRequest->setQueryString($queryString);
        $httpRequest->setRequestURI($requestURI);
        $httpRequest->setUrlParams($urlParams);

        if (!isset($annotation[Annotation::VALUES][Annotation::O_CONTENT_TYPE])) {
            $httpRequest->setContentType(Config::CONTENT_TYPE_TEXT);

        } else if ($annotation[Annotation::VALUES][Annotation::O_CONTENT_TYPE] == 'json') {
            $httpRequest->setContentType(Config::CONTENT_TYPE_JSON);
            $httpRequest->setPostAjax(true);

        } else if ($annotation[Annotation::VALUES][Annotation::O_CONTENT_TYPE] == 'json-post') {
            $httpRequest->setPostAjax(true);
            if (count($httpRequest->getRequest()) > 0) {
                $httpRequest->setContentType(Config::CONTENT_TYPE_JSON);
            }
        }

        $reflectionMethod = new \ReflectionMethod($classInstance, $annotation[Annotation::VALUES][Annotation::O_METHOD]);

        try {
            $httpResponse = $reflectionMethod->invoke($classInstance, $httpRequest);
        } catch (\core\exception\BaseException $e) {
            $httpResponse = new \presentation\HttpResponse();
            if ($e->getFatal()) {
                $httpResponse->setMessage(\core\Messages::SYSTEM_ERROR);
            } else {
                $httpResponse->setMessage($e->getMessage());
            }
        }

        if (isset($httpResponse)) {
            if ($httpRequest->getContentType() == Config::CONTENT_TYPE_JSON) {
                header('Content-Type: '.$httpRequest->getContentType());
                echo JsonHelper::convertResponseToJson($httpResponse);
            } else {
                if ($httpResponse->getSuccess() && !helper\StringHelper::isNull($httpResponse->getMessage())) {
                    $this->setOutputMessage($httpResponse->getMessage());
                } else if (!$httpResponse->getSuccess()) {
                    global $c;
                    $c[BasePresentation::EXCEPTION_VALIDATION] = $httpResponse->getMessage();
                }

                if (!helper\StringHelper::isNull($httpResponse->getRedirectURL())) {
                    $this->redirect($httpResponse->getRedirectURL(), $httpResponse->getMessage());
                }
                $outputPage = null;
                $template = null;
                if (isset($annotation[Annotation::VALUES][Annotation::O_TEMPLATE])) {
                    $template = $annotation[Annotation::VALUES][Annotation::O_TEMPLATE];
                }
                if (!$httpResponse->getPage()) {
                    if (isset($annotation[Annotation::VALUES][Annotation::O_PAGE])) {
                        $outputPage = $annotation[Annotation::VALUES][Annotation::O_PAGE];
                    }
                } else {
                    $outputPage = $httpResponse->getPage();
                }
                $this->renderPage($outputPage, $template, $httpResponse->getContent());
            }
        }
    }

    private function handleException(\Exception $e) {
        $content = array(BasePresentation::EXCEPTION_VALIDATION => $e->getMessage());
        if ($e instanceof \core\exception\MappingNotFoundException) {
            \core\Logger::warning($e);
            $this->renderPage(Config::ERROR_PAGE_404, null, $content);
        } else {
            \core\Logger::error($e);
            $this->renderPage(Config::ERROR_PAGE_500, null, $content);
        }
    }

    private function renderPage($page, $template = null, $var = null) {
        global $c;
        if (isset($var)) {
            if (is_array($c) && is_array($var)) {
                $c = array_merge($c, $var);
            } else {
                $c = $var;
            }
        }
        if ($c) {
            foreach ($c as $key=>$value) {
                $$key = $value;
            }
        }

        $message = $this->getOutputMessage();
        if (isset($message) && strlen($message) > 0) {
            if (!isset($c[\presentation\BasePresentation::OUTPUT_MESSAGE])) {
                $c[\presentation\BasePresentation::OUTPUT_MESSAGE] = '';
            }
            $c[\presentation\BasePresentation::OUTPUT_MESSAGE] .= $message;
        }

        $pages = array();

        if (isset($page)) {
            if (!file_exists(Config::getWebIncludePagePath().$page)) {
                throw new \Exception('File does not exists - '.$page);
            } else {
                $pages[] = Config::getWebIncludePagePath().$page;
            }
        }

        if ($template) {
            $template = Config::getWebIncludeTemplatePath().$template;
            $regex = '/<\?\/\/{PPK-USE-INSIDE-TEMPLATE-FILE:"(?<value>.+?)"}\?>/';
        } else {
            $template = $pages[0];
            $regex = '/<\?\/\/{PPK-USE-TEMPLATE-FILE:"(?<value>.+?)"}\?>/';
        }

        $fileContent = file_get_contents($template);
        if (preg_match($regex, $fileContent, $matches, PREG_OFFSET_CAPTURE)) {
            if (file_exists(Config::getWebIncludeTemplatePath().$matches['value'][0])) {
                $pages = array_merge(array($template), $pages);
                $template = Config::getWebIncludeTemplatePath().$matches['value'][0];
            }
        }

        $c[\presentation\BasePresentation::TEMPLATE_INNER_FILE] = array_unique($pages);
        if (!file_exists($template))
            throw new \Exception('File does not exists - '.$template);
        require_once($template);
    }

    private function controlAccess($routeRole) {
        $sessionRoles = SessionManager::getInstance()->getValue(SessionManager::USER_ROLE);
        $routeRoles = explode('|', $routeRole);
        $allowed = false;
        if (isset($sessionRoles) && isset($routeRoles)) {
            $sessionRoles = explode('|', $sessionRoles);
            foreach ($routeRoles as $role) {
                if (in_array($role, $sessionRoles)) {
                    $allowed = true;
                    break;
                }
            }
        }
        if (!$allowed && !\helper\StringHelper::isNull(Config::LOGIN)) {
            $aux = explode('.', Config::LOGIN);
            $this->redirect($this->findRoute($aux[0], $aux[1]), \core\Messages::RESTRICTED_AREA);
            exit;
        }
    }

    private function loadPaprikaClass($className) {
        $namespaces = array('core', 'annotation', 'presentation', 'business', 'repository', 'helper');
        foreach ($namespaces as $namespace) {
            if (!$namespace && strpos($className, $namespace.'\\') !== 0) {
                continue;
            }
            $classFile = PAPRIKA_LIBRARY_PATH.'/'.$className.'.php';
            if (file_exists($classFile)) {
                require $classFile;
                return;
            }
        }
    }

    private function loadCustomCodeClass($className) {
        $srcFolder = PAPRIKA_CUSTOM_PATH.'/';
        foreach (Config::getCustomCodeFolders() as $folder) {
            $classFile = $srcFolder.$folder.'/'.$className.'.php';
            if (file_exists($classFile)) {
                require_once($classFile);
                return;
            }
        }
    }

    private function getQueryString() {
        $queryString = null;
        if ($_SERVER['QUERY_STRING']) {
            $arrayQueryString = preg_split('/\&/', $_SERVER['QUERY_STRING']);
            foreach ($arrayQueryString as $partQueryString) {
                $aux = preg_split('/\=/', $partQueryString);
                if (count($aux) > 1) {
                    $queryString[$aux[0]] = $aux[1];
                }
            }
        }
        return $queryString;
    }
}

/**
 * Print preformated strings or objects
 * @param $object
 */
function p($object, $title = '') {
    echo "<div align='left' style='font-size:11px;border:1px solid gray;background-color:#FFF;'><pre>";
    echo "<div style='font-size:12px;'>$title</div>";
    if (!isset($object))
        print_r('null');
    else
        print_r($object);
    echo "</pre></div>";
}
?>