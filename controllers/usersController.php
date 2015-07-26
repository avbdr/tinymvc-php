<?php
class usersController extends Controller {
    public function index () {
        $v = new View ("users/index");
        $v->items = user::ArrayBuilder()->get();
        $v->render();
    }

    public function edit ($id) {
        if ($this->reqIs ("POST")) {
            $u = user::byId ($id);
            $u->save ($_POST);
            $this->flash ("User changes were saved","success");
            $this->redirect ("/users/");
            return;
        }

        $fields = Array ("id", "login", "active", "customerId", "firstName", "lastName", "password");
        $v = new View ("users/edit");
        $v->item = user::ArrayBuilder()->byId($id, $fields);
        $v->render();
    }

    public function rm ($id) {
        $u = user::byId ($id);
        $u->delete();
        $this->flash ("User changes were saved","success");
        $this->redirect ("/users/");
    }
}
?>
