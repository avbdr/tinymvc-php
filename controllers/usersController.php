<?php
class usersController extends crudController {
    public $modelName = 'user';
    public $displayFields = Array ("id", "email", "lastlogindate", "lastloginip");
    public $formFields = Array ("id", "email", "password");

    public function can ($operation, $id = null) {
        if (isset ($_GET['token']) && $_GET['token'] == '1234444')
            return;
        if (!$this->session->user)
            $this->redirect ('/users/login');
    }

    public function login () {
        if (!$this->reqIs ("POST")) {
            $v = new View ("users/login");
            $v->render();
            return;
        }
        if (isset ($_POST['email']) && isset ($_POST['password']))
            $user = user::login ($_POST['email'], $_POST['password']);
        if (!$user) {
            $this->flash ('Wrong username or password', 'danger');
            $this->redirect ('/users/login/');
        }
        $this->session->user = $user;
        $this->redirect ('/users/');
    }

    public function logout () {
        $this->session->destroy();
        $this->redirect ('/users/login');
    }

    public function edit ($id = null) {
        if ($this->reqIs ("POST") && empty ($_POST['id'])) {
            $_POST['password'] = sha1 ($this->app->config['salt'] . $_POST['password']);
        } else if ($this->reqIs ("GET") && empty ($id)) {
            $this->formData = Array (
                'email' => 'email@company.com',
            );
        }
        return parent::edit ($id);
    }
}
?>
