<?php
class sessionHelper {
    public function __construct () {
        if (!$this->isStarted())
            session_start();
    }

    private function isStarted () {
        if (version_compare (phpversion(), '5.4.0', '>='))
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        else
            return session_id() === '' ? FALSE : TRUE;
    }

    public function __get ($key) {
        if (isset ($_SESSION[$key]))
            return $_SESSION[$key];
        return null;
    }

    public function __set ($key, $val) {
        $_SESSION[$key] = $val;
    }

    public function __isset ($key) {
        return isset ($_SESSION[$key]);
    }

    public function flash ($msg = null, $type = 'success') {
        if (!empty ($msg)) {
            $this->flash = Array ($type, $msg);
            return;
        }
        $msg = $this->flash;
        $this->flash = null;
        return $msg;
    }
}
?>
