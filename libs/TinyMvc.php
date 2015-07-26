<?php
/**
 * TinyMvc Framework
 *
 * @category  Frameworks
 * @package   TinyMvc
 * @author    Alexander V. Butenko <a.butenka@gmail.com>
 * @copyright Copyright (c) 2015
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @version   1.0
 *
**/
require_once ("MysqliDb.php");
require_once ("dbObject.php");
if (!defined ('BASEPATH'))
    define('BASEPATH', dirname (dirname (__FILE__)));
if (!defined ('BASEURL'))
    define('BASEURL', !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'?"https":"http" . "://" . $_SERVER['SERVER_NAME'] . "/");

class TinyMvc {
    /**
     * Config file
     *
     * @var Array
     */
    public $config = Array ('defaultController' => 'root', 'defaultAction' => 'index');
    /**
     * Static instance of self
     *
     * @var TinyMvc
     */
    public static $self;
    /**
     * Instance of MysqliDb if created
     *
     * @var MysqliDb
     */
    public $db;

    /**
     * @param Array $config Config file to use
     */
    public function __construct ($config = Array()) {
        TinyMvc::$self = $this;
        $this->readConfig ($config);
        if (isset ($this->config['db'])) {
            $this->db = new MysqliDb ($this->config['db']);
            dbObject::autoload (BASEPATH . "/models/");
        }
        spl_autoload_register ("TinyMvc::autoload");
    }

    /**
     * Main static method to start request processing.
     *
     * @uses TinyMvc::run ($config);
     *
     * @param Array $config
     */
    public static function run ($config = Array ()) {
        $obj = new TinyMvc ($config);
        $obj->processRequest ();
    }

    /**
     * Method returns static instance of a TinyMvc object to access its data
     *
     * @uses $app = TinyMvc::app();
     *
     * @return object Returns the current instance.
     */
    public static function app () {
        return TinyMvc::$self;
    }

    /**
     * Private config reader method. Used to merge default config with a main and
     * /config.$hostname.php override config files
     *
     * @param Array $config
     */
    private function readConfig ($config = Array()) {
        $this->config = array_merge ($this->config, $config);
        $hostCfg = BASEPATH . "/config." . $_SERVER['SERVER_NAME'] . ".php";
        if (!file_exists ($hostCfg))
            return;
        $params = require ($hostCfg);
        if (is_array ($params))
            $this->config = array_merge ($this->config, $params);
    }

    /*
     * Class autoloader to load controllers, helpers and library files
     *
     * @param string $classname
     */
    public static function autoload ($classname) {
        $lclassname = strtolower ($classname);
        if (endsWith ($lclassname, "controller"))
            $path = BASEPATH. "/controllers/";
        else if (endsWith ($lclassname, "helper"))
            $path = BASEPATH . "/helpers/";
        else
            $path = BASEPATH . "/libs/";
        $filename = $path . $classname .".php";
        if (!file_exists ($filename))
            return false;
        include ($filename);
        return true;
    }

    /*
     * URL Parser and routing logic
     * By default /test1/test2 will be mapped to test2() method of a testController if defined
     * If it wont be found TinyMvc will search action_test1_test2 () function.
     * If it also wont be found, error page will be displayed
     *
     */
    private function processRequest () {
        // parse url. Strip out script name from the url and params after last ? sign
        if (PHP_SAPI == "cli")
            $request = ($_SERVER['argv'][1]);
        else
            $request = preg_replace ("~^({$_SERVER['SCRIPT_NAME']})?(.*)\?.*~", '\2', $_SERVER['REQUEST_URI']);
        if (isset ($this->config['routes'])) {
            foreach ($this->config['routes'] as $from => $to)
                $request = preg_replace("@".$from."@", $to, $request);
        }
        $splits = explode ('/', trim ($request, '/'));

        // assign values
        $this->controller = isset ($splits[0]) && !empty ($splits[0]) ? strtolower (array_shift ($splits)) : $this->config['defaultController'];
        $this->action = isset ($splits[0]) && !empty ($splits[0]) ? strtolower (array_shift ($splits)) : $this->config['defaultAction'];
        $this->params = array_map ("urldecode", array_values ($splits));

        // execute controller handler via class if exists or a function
        $controllerClassName = $this->controller . "Controller";
        if (class_exists ($controllerClassName, 1)) {
            $controller = new $controllerClassName;
            if ($controller->__action ($this->action, $this->params))
                return;
        }

        $functionName = "action_" . $this->controller ."_". $this->action;
        if (function_exists ($functionName)) {
            call_user_func_array ($functionName, $this->params);
            return;
        }

        $v = new View ("404");
        $v->error = "{$this->controller}/{$this->action}";
        $v->render ();
    }
}

/*
 * @var $db MysqliDb instance shortcut
 * @var $app TinyMvc instance shortcut
 * @var $session Session helper shortcut
*/
class Controller {
    /**
     * Initialized helpers cache
     *
     * @var Array
     */
    private $helpers = Array();

    /**
     * Internal function used by routing method to invoke needed controller action with correct params
     * In case __error method is defined it will be called if no required $action will be found
     *
     * @param $action Array Method name to call
     * @param $args Array Array of arguments to pass to the method
     *
     * @return boolean Execution status
     */
    public function __action ($action, $args) {
        if (is_callable (Array ($this, $action))) {
            call_user_func_array (array ($this, $action), $args);
            return true;
        }
        if (is_callable (Array ($this, '__error'))) {
            call_user_func_array(array($this, '__error'), array('error' => 'NOACTION', 'action' => $action));
            return true;
        }
        return false;
    }

    /**
     * Helper method to load helper libraries
     *
     * @param $name string helperName
     *
     * @return object Helper instance
     */
    private function loadHelper ($name) {
        if (!isset ($this->helpers[$name]))
            $this->helpers[$name] = new $name;
        return $this->helpers[$name];
    }

    public function __get ($key) {
        $lkey = strtolower ($key);
        if ($lkey == 'db')
            return TinyMvc::app()->db;
        else if ($lkey == 'app')
            return TinyMvc::app();
        else if (endsWith ($lkey, 'helper'))
            return $this->loadHelper ($key);
        else if ($lkey == 'session')
            return $this->loadHelper ('sessionHelper');
        return null;
    }

    /**
     * Helper method to set flash
     *
     * @var $msg string Notification message text
     * @var $type string Notification type: success, warning, danger
     *
     * @return string Flash message
     */
    public function flash ($msg = null, $type = 'success') {
        return $this->session->flash ($msg, $type);
    }

    /**
     * Method to create redirect reply
     *
     * @param $url string URL to redirect user to
     */
    public function redirect ($url) {
        ob_clean ();
        header ("Location: $url");
        exit;
    }

    /**
     * Request type checker
     *
     * Supported types: GET/POST/PUT/HEAD/DELETE/OPTIONS/HTTPS/SSL/CLI/AJAX
     *
     * @param $method Requested method
     *
     * @return boolean
     */
    public function reqIs ($method) {
        $method = strtoupper ($method);
        if ($method == 'AJAX')
            return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? TRUE : FALSE;

        if (in_array ($method, Array ('HTTPS', 'SSL')))
            return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        if ($method == 'CLI')
            return PHP_SAPI == "cli";

        if (isset ($_POST['_method']))
            return $_POST['_method'] == $method;

        return $_SERVER['REQUEST_METHOD'] == $method;
    }
}
/*
 * View class
 *
 * @uses $v = new View($tpl); $v->render();
 *
 * @param $tpl string Template name from view/ directory
 *
 * @return object
 */
class View {
    /**
     * View variables storage space
     *
     * @var Array
     */
    private $_data = Array ('pageTitle' => '');
    /**
     * View layout name to use
     *
     * By default set to 'default' which resolves to views/layouts/default.php
     *
     * @var string
     */
    public $layout = 'default';
    /**
     * Template name to render.
     *
     * @var string
     */
    private $tpl;

    public function __construct ($tpl) {
        $this->tpl = BASEPATH . '/views/' . $tpl . '.php';
    }

    public function __set ($key, $val) {
        $this->_data[$key] = $val;
    }

    public function __get ($key) {
        return $this->_data[$key];
    }

    /**
     * Internal method to render file to a variable.
     *
     * @param $filename string
     */
    public function renderToString ($filename) {
        extract ($this->_data);
        ob_start();
        require ($filename);
        return ob_get_clean ();
    }

    /**
     * Include subtemplate into view without a layout.
     *
     * @param $tpl string Template name
     */
    public function section ($tpl) {
        if ($tpl == 'content')
            echo $this->content;
        else
            echo $this->renderToString (BASEPATH . '/views/' . $tpl . '.php');
    }

    /**
     * Main method to render a template with layout if needed
     *
     * @param $isParticle bool If layout should be included
     */
    public function render ($isParticle = false) {
        $session = new sessionHelper ();
        $this->flash = $session->flash ();
        $this->content = $this->renderToString ($this->tpl);
        if ($isParticle || empty ($this->layout)) {
            echo $this->content;
            return;
        }
        echo $this->renderToString (BASEPATH . '/views/layouts/' . $this->layout . '.php');
    }
}

/*
 * Model base class
 *
 * @var $app TinyMvc instance shortcut
*/
class Model extends dbObject {
    public function __construct ($data = null) {
        $this->app = TinyMvc::app();
        parent::__construct ($data);
    }
}

/*
 * Convinience function to check if string ends with a $needle
 *
 * @param $haystack string
 * @param $needle work to check
 * @return boolean
*/
function endsWith ($haystack, $needle) {
    return substr ($haystack, -strlen($needle)) === $needle;
}

/*
 * Quick Url Builder
 *
 * @param $url string
*/
function u ($url = '/') {
    $urlP = explode ("/", $url);
    if (!empty ($urlP[0]))
        $urlP = array_merge (Array ("/" . TinyMvc::app ()->controller), $urlP);

    return implode ("/", $urlP);
}

/*
 * Quick Link builder
 *
 * @param $href string Link URL
 * @param $title string Link title
*/
function a ($href, $title = null) {
    if (!$title)
        $title = $href;
    return '<a href="' . u ($href). "\">{$title}</a>";
}

/*
 * Quick echo +  htmlspecialchars helper
 *
 * @param $str Echo string
 * @param $shouldEscape boolean if string should be echoed or escaped before
*/
function e ($str, $shouldEscape = true) {
    if ($shouldEscape)
        echo htmlspecialchars ($str);
    else
        echo $str;
}

function logger () {
    $args = func_get_args ();
    $verb = strtoupper (array_shift ($args));
    $args[0] = date ('Y-m-d H:i:s') . " " . $verb . " " . $args[0] . "\n";
    foreach ($args as $k => $v)
        if (is_array ($v) || is_object ($v))
            $args[$k] = print_r ($args[$k], true);

    $log = BASEPATH . "/logs/debug.log";
    file_put_contents ($log, call_user_func_array ('sprintf', $args), FILE_APPEND);
}
?>
