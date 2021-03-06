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
spl_autoload_register ("TinyMvc::autoload");
if (!isset ($_SERVER['SERVER_NAME']))
    $_SERVER['SERVER_NAME'] = 'localhost';
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
        $this->configure ($config);
        if (isset ($this->config['db'])) {
            $this->db = new MysqliDb ($this->config['db']);
            if (class_exists ("dbObject"))
                dbObject::autoload (BASEPATH . "/models/");
        }
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
    private function configure ($config = Array()) {
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
        $filename = $path . str_replace ('\\', DIRECTORY_SEPARATOR, $classname) .".php";
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
    private function parseRequest () {
        $request = "";
        // convert json to array in request body if found
        $input = file_get_contents ("php://input");
        if (isset ($input[0]) && ($input[0] == '{' || $input[0] == '['))
            $_POST = json_decode ($input, true);

        // parse url
        if (PHP_SAPI != "cli") {
            $request = preg_replace ("~/*\?(.+)$~", "", $_SERVER['REQUEST_URI']);
        } else if (isset ($_SERVER['argv'][1])) {
            $request = $_SERVER['argv'][1];
            if (strchr ($request, '?')) {
                list ($request, $opts) = explode ("?", $request);
                parse_str ($opts, $_GET);
            }
        }

        if (isset ($this->config['routes'])) {
            foreach ($this->config['routes'] as $from => $to)
                $request = preg_replace("@".$from."@", $to, $request);
        }
        $splits = explode ('/', trim ($request, '/'));

        // assign values
        $this->controller = isset ($splits[0]) && !empty ($splits[0]) ? strtolower (array_shift ($splits)) : $this->config['defaultController'];
        $this->action = isset ($splits[0]) && !empty ($splits[0]) ? strtolower (array_shift ($splits)) : $this->config['defaultAction'];
        $this->params = array_map ("urldecode", array_values ($splits));
    }

    private function displayReply ($reply, $actionFound) {
        if (!$actionFound) {
            $v = new View ("404");
            $v->error = "{$this->controller}/{$this->action}";
            $v->render ();
        } else if ($reply && $reply instanceof View)
            $reply->render();
        else if ($reply && is_array ($reply)) {
            header ("Content-type: application/json");
            echo json_encode ($reply);
        } else if ($reply)
            echo $reply;
    }

    private function processRequest () {
        $actionFound = false;
        $reply = '';

        $this->parseRequest ();

        if (isset ($this->config['beforeLoad']))
            call_user_func ($this->config['beforeLoad']);

        // execute controller handler via class if exists or a function
        $controllerClassName = $this->controller . "Controller";
        if (class_exists ($controllerClassName, 1)) {
            $controller = new $controllerClassName;
            if ($actionFound = is_callable (Array ($controller, $this->action)))
                $reply = call_user_func_array (array ($controller, $this->action), $this->params);
        }

        if (!$actionFound) {
            $functionName = "action_" . $this->controller ."_". $this->action;
            if ($actionFound = function_exists ($functionName))
                $reply = call_user_func_array ($functionName, $this->params);
        }

        $this->displayReply ($reply, $actionFound);
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
        if ($lkey == 'app')
            return TinyMvc::app();
        if (endsWith ($lkey, 'helper'))
            return $this->loadHelper ($key);
        if ($lkey == 'session')
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

    /**
     * Separate storage for error messages
     * @var array
     */
    public $errors;

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
    public function section ($tpl, $doReturn = false) {
        if ($tpl == 'content') {
            echo $this->content;
            return;
        }
        if ($doReturn)
            return $this->renderToString (BASEPATH . '/views/' . $tpl . '.php');
        echo $this->renderToString (BASEPATH . '/views/' . $tpl . '.php');
    }

    /**
     * Main method to render a template with layout if needed
     *
     * @param $isParticle bool If layout should be included
     */
    public function render ($isParticle = false) {
        if (class_exists ('sessionHelper', 1)) {
            $session = new sessionHelper ();
            $this->flash = $session->flash ();
        }
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
if (class_exists ("dbObject")) {
    class Model extends dbObject {
        public function __construct ($data = null) {
            $this->app = TinyMvc::app();
            parent::__construct ($data);
        }
    }
} else {
    class Model {
        public function __construct ($data = null) {
            $this->app = TinyMvc::app();
        }
    }
}

/*
 * Convenience function to check if string ends with a $needle
 *
 * @param $haystack string
 * @param $needle word to check
 * @return boolean
*/
function endsWith ($haystack, $needle) {
    return substr ($haystack, -strlen($needle)) === $needle;
}

/*
 * Search backwards starting from haystack length characters from the end
 *
 * @param $haystack string
 * @param $needle work to check
 * @return boolean
*/
function startsWith ($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

/*
 * Quick echo +  htmlspecialchars helper
 *
 * @param $str Echo string
 * @param $shouldEscape boolean if string should be echoed or escaped before
*/
function e ($str, $shouldEscape = true) {
    if (is_array ($str)) {
        $k = key ($str);
        $str = $k . " " . $str[$k];
    }

    if ($shouldEscape)
        echo htmlspecialchars ($str);
    else
        echo $str;
}

function logger () {
    $log = BASEPATH . "/logs/debug.log";
    $args = func_get_args ();

    foreach ($args as $k => $v)
        if (is_array ($v) || is_object ($v))
            $args[$k] = print_r ($v, true);
    $verb = strtoupper (array_shift ($args));
    $args[0] = date ('Y-m-d H:i:s') . " " . $verb . " " . $args[0] . "\n";
    file_put_contents ($log, call_user_func_array ('sprintf', $args), FILE_APPEND);
}
?>
