<?php
class testController extends Controller {
    public $var = 'qqqq';
    public function a () {
        if ($this->reqIs ("CLI"))
            echo "CLI";
        else echo "WEB";

        logger ("warn", "hello %s%s", "world", "!");
        echo "\n";
    }
    public function ccc () {
        $this->session->cnt++;
        $v = new View ("test");
        $v->pageTitle = "hello world";
        $v->cnt = $this->session->cnt;
        $v->hello = $this->helloHelper->say();
        $v->render ();
    }
    public function qqq () {
        echo BASEURL;
        echo "<br>";
        echo u ("/users");
        echo "<br>";
        echo u ("/users/1");
        echo "<br>";
        echo u ("users");
        echo "<br>";
        echo u ();
        echo "<br>";
        echo u ("users/data");
        echo "<br>";
        echo a ("/users/edit/1", "Edit");
    }
}

?>
