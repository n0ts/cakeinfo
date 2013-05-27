<?php
App::uses('Sanitize', 'Utility');

  /**
   * CakeInfo.
   *
   * This class is based on cakeinfo 0.1.2 for CakePHP 1.2-beta
   * http://www.1x1.jp/blog/2008/05/cakephp_cakeinfo_012.html (Japanese)
   */
class CakeInfo {
    /**
     * Configure
     *
     * @var object Configure
     */
    var $conf = null;
    /**
     * version
     *
     * @var string
     */
    var $version = null;
    /**
     * config values
     *
     * @var array
     */
    var $values = array();

    /**
     * construct for php5
     */
    function __construct() {
        $this->version = $this->getVersion();
    }

    /**
     * make CakeInfo values
     */
    function execute() {
        $this->values = array();
        $this->values['PHP'] = array($this->makePhpValues());
        $this->values['Conifugre'] = array($this->getConfigureValues());
        $this->values['Database'] = $this->makeDatabaseValues();
        $this->values['Controller'] = $this->makeControllerValues();
        $this->values['Model'] = $this->makeModelValues();
    }

    /**
     * get version
     *
     * @return string
     */
    function getVersion() {
        if (method_exists('Configure', 'version')) {
            return Configure::version();
        } else {
            $handle = fopen(CAKE . DS . 'VERSION.txt', "r");
            if ($handle) {
                while (($line = fgets($handle, 4096)) !== false) {
                    if (preg_match('/\/\//', $line)) {
                        continue;
                    }

                    if (preg_match('/([0-9]+\.[0-9]+\.[0-9]+)/', $line, $m)) {
                        return $m[1];
                    }
                }

                fclose($handle);
            }
        }

        return null;
    }

    /**
     * get configure values
     *
     * @return array
     */
    function getConfigureValues() {
        $array = array();
        if (method_exists('Configure', 'read')) {
            $array = Configure::read();
        }

        return $array;
    }

    /**
     * make php values
     *
     * @return array
     */
    function makePhpValues() {
        $array = array();
        $array['VERSION'] = phpversion();
        $array['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
        $array['SERVER_PORT'] = $_SERVER['SERVER_PORT'];
        $array['SCRIPT_FILENAME'] = $_SERVER['SCRIPT_FILENAME'];
        $array['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'];

        return $array;
    }

    /**
     * make db values
     *
     * @return array
     */
    function makeDatabaseValues() {
        $array = array();

        config('database');
        $db = new DATABASE_CONFIG;
        foreach ($db as $k1 => $v1) {
            foreach ($v1 as $k2 => $v2) {
                $array[$k1][$k2] = $v2;
            }
        }

        return $array;
    }

    /**
     * make controller values
     *
     * @return array
     */
    function makeControllerValues() {
        $array = array();

        $paths = $this->_getFileListDirs(App::path('Controller'));
        foreach ($paths as $path) {
            if (is_file($path) && preg_match("/^(.+)Controller\.php$/", basename($path), $m)) {
                $ctrlName = Inflector::camelize($m[1]);
                $this->loadController($ctrlName);
                $class = $ctrlName . 'Controller';
                $obj = new $class();

                $v = $this->_getClassDiffValues(get_class_vars('Controller'), get_object_vars($obj));

                $v['action_method'] = $this->_getActionList($class);
                $v['view'] = $this->_getViewList($ctrlName);

                $array[$ctrlName] = $v;
            }
        }

        return $array;
    }

    /**
     * get action list
     *
     * @param string $className
     * @return array
     */
    function _getActionList($className) {
        $array = get_class_methods($className);
        $actions = array();
        $protected = array_map('strtolower', get_class_methods('controller'));
        foreach ($array as $method) {
            if (in_array(strtolower($method), $protected) || strpos($method, '_', 0) === 0) {
                continue;
            }
            $actions[] = $method;
        }

        return $actions;
    }

    /**
     * get view list
     *
     * @param string $ctrlName
     * @return array
     */
    function _getViewList($ctrlName) {
        $array = array();

        foreach (App::path('View') as $dirPath) {
            $dirPath .= Inflector::underscore($ctrlName);

            $array[] = sprintf('[%s]', $dirPath);
            $offset = strlen($dirPath) + 1; // add DS character


            $paths = $this->_getFileList($dirPath);
            foreach ($paths as $path) {
                if (is_file($path)) {
                    $view = substr($path, $offset);
                    if (preg_match("/^\./", $view)) {
                        continue;
                    }
                    $array[] = $view;
                }
            }
        }

        return $array;
    }

    /**
     * make model values
     *
     * @return array
     */
    function makeModelValues() {
        $array = array();

        $paths = $this->_getFileListDirs(App::path('Model'));
        foreach ($paths as $path) {
            if (is_file($path) && preg_match("/^(.+)\.php$/", basename($path), $m)) {
                $modelName = Inflector::camelize($m[1]);
                $this->loadModel($modelName);

                $v = $this->_getClassDiffValues(get_class_vars('Model'), get_class_vars($modelName));

                $array[$modelName] = $v;
            }
        }

        return $array;
    }


    /**
     * get constants
     *
     * @param string $path
     * @return array
     */
    function _getConstants($path) {
        $contents = file($path);

        $array = array();
        foreach ($contents as $line) {
            if (preg_match("/define\('([^']+)'/", $line , $m)) {
                $name = $m[1];

                if (defined($name)) {
                    $array[$name] = constant($name);
                }
            }
        }

        return $array;
    }

    /**
     * make file list
     *
     * @param array $dirPaths
     * @param boolean $isRecursive
     * @return array
     */
    function _getFileListDirs($dirPaths, $isRecursive = true) {
        $array = array();

        foreach ($dirPaths as $dirPath) {
            $array += $this->_getFileList($dirPath, $isRecursive);
        }

        return $array;
    }

    /**
     * make file list
     *
     * @param string $dirPath
     * @param boolean $isRecursive
     * @return array
     */
    function _getFileList($dirPath, $isRecursive = true) {
        $array = array();

        if (!file_exists($dirPath) || !is_dir($dirPath)) {
            return $array;
        }
        $d = dir($dirPath);

        while ($file = $d->read()) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            if (substr($dirPath, -1) != DS) {
                $dirPath .= DS;
            }
            $path = $dirPath . $file;
            if (is_dir($path) && $isRecursive) {
                $array += $this->_getFileList($path);
            }

            $array[] = $path;
        }

        return $array;
    }

    /**
     * get class difference values
     *
     * @param array& $baseVars
     * @param array& $classVars
     * @return array
     */
    function _getClassDiffValues($baseVars, $classVars) {
        $array = array();

        foreach ($classVars as $name => $var) {
            if (@$baseVars[$name] !== $classVars[$name]) {
                $array[$name] = $this->_arrayToString($var);
            }
        }

        return $array;
    }

    /**
     * get value
     *
     * @param mixed &$value
     * @return string
     */
    function _arrayToString(&$value) {
        $str = '';

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if (!empty($str)) {
                    $str .= "<br />";
                }
                if (is_array($v)) {
                    $str .= $k;
                } else {
                    $str .= (is_numeric($k) ? $v : $k);
                }
            }
        } else {
            $str =  $value;
        }

        return $str;
    }

    /**
     * load Controller
     *
     * @param string  $name
     */
    function loadController($name) {
        if (class_exists('App')) {
            App::import('Controller', $name);
        } else {
            loadController($name);
        }
    }

    /**
     * load Model
     *
     * @param string  $name
     */
    function loadModel($name) {
        if (class_exists('App')) {
            App::import('Model', $name);
        } else {
            loadModel($name);
        }
    }

    /**
     * get display value
     *
     * @param mixed $value
     * @return string
     */
    function toString($value) {
        if (is_array($value)) {
            return implode('<br />', $value);
        } else if (is_null($value)) {
            return 'NULL';
        } else if (is_bool($value)) {
            return ($value ? 'TRUE' : 'FALSE');
        } else {
            return $value;
        }
    }
  }


//$_GET['url'] = 'favicon.ico';
//require_once('index.php');
//require_once('cake/libs/sanitize.php');

//$info = new CakeInfo();
//$info->execute();
//if (!defined('DATABASE_CONFIG_FLAG')) {
//    unset($info->values['Database']);
//}
?>