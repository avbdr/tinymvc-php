<?php
class usersController extends crudController {
    public $modelName = 'user';
    public $displayFields = ["id", "role", "email", "lastlogindate", "lastloginip"];
    public $formFields = ["id", "role", "email", "firstName", "lastName", "password"];


    public function can ($operation, $id = null) {
        if (isset ($_GET['token']) && $_GET['token'] == '1234444')
            return;
        if (!$this->session->user)
            $this->redirect ('/users/login');
    }

    public function login () {
        if (!$this->reqIs ("POST"))
            return new View ("users/login");

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

    public function reset ($id) {
        if (!$this->session->user)
            return;

        $user = user::byId ($id);
        $user->password = user::genPassword (6, true);
        $user->save();
        // send mail notification
        $this->flash ("Password were reset");
        $this->redirect ('/users/edit/'. $id);
    }

    public function password () {
        if (!$this->reqIs("POST"))
            return new View ("users/password");

        return $this->session->user->changePassword ($_POST);
    }

    public function updated ($model) {
    }

    public function created ($model) {
    }

    public function edit ($id = null) {
        $v = parent::edit ($id);
        if (!$v->errors  && !$this->reqIs ("GET"))
            return $v;

        $v->id = $v->item['id'];
        return $v;
    }
}
?>
