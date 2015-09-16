<?php
class usersController extends crudController {
    public $modelName = 'user';
    public $editFields = Array ("id", "email", "password");
    public $displayFields = Array ("id", "email", "lastlogindate", "lastloginip");

    public function can ($operation, $id = null) {
        if (!$this->session->user)
            $this->redirect ('/users/login');
    }

    public function login () {
        if (!$this->reqIs ("POST")) {
            $v = new View ("users/login");
            $v->render();
            return;
        }
        if (isset ($_POST['email']) && isset ($_POST['password'])) {
            $user = user::where ('email', $_POST['email'])
                        ->where ('password', md5($_POST['password']))
                        ->getOne ();
        }
        if (!$user) {
            $this->flash ('Wrong username or password', 'danger');
            $this->redirect ('/users/login/');
        }
        $user->lastlogindate = date("Y-m-d H:i:s");
        $user->lastloginip = $_SERVER['REMOTE_ADDR'];
        $user->save();
        $this->session->user = $user;
        $this->redirect ('/users/');
    }

    public function logout () {
        $this->session->destroy();
        $this->redirect ('/users/login');
    }

    public function edit ($id = null) {
        if ($this->reqIs ("POST") && empty($_POST['id']))
            $_POST['password'] = md5 ($_POST['password']);
        return parent::edit ($id);
    }
}
?>
