<?php
class crudController extends Controller {
    public function can ($operation, $id = null) {
    }
    public function index () {
        $this->can ('index');
        $v = new View (TinyMvc::App ()->controller . "/index");
        $v->render();
    }

    public function json ($list = null) {
        $this->can ('index');
        if (!$list)
            $list = new $this->modelName;
        $list = $list->ArrayBuilder()->withTotalCount();
        $rowCount = isset ($_GET['limit']) ? $_GET['limit'] : 10;
        $offset = isset ($_GET['offset']) ? $_GET['offset'] : 0;

        if (!empty ($_GET['search'])) {
            $i = 0;
            foreach ($this->displayFields as $f) {
                if ($i == 0)
                    $list->where ($f, '%' . $_GET['search'] . '%', 'like');
                else
                    $list->orWhere ($f, '%' . $_GET['search'] . '%', 'like');
                $i++;
            }
        }
        if (isset ($_GET['sort']))
            $list->orderBy ($_GET['sort'], $_GET['order']);

        echo json_encode (Array (
            'rows' => $list->get(Array ($offset, $rowCount), $this->displayFields),
            'total' =>$list->totalCount 
        ));
    }

    public function edit ($id = null) {
        $this->can ('index', $id);

        $v = new View (TinyMvc::App ()->controller . "/edit");
        if ($this->reqIs ("POST")) {
			if (!empty ($_POST['id'])) {
                $model = new $this->modelName;
	            $model  = $model::byId ($_POST['id']);
			} else {
				$model = new $this->modelName ($_POST);
            }
    	    $model->save ($_POST);
            if (!isset ($model->errors)) {
                $this->flash ("Changes were saved","success");
                $this->redirect ("/" .TinyMvc::App ()->controller. "/");
                return;
            }
            $v->errors = $model->errors;
        }
		if ($id) {
            $model = new $this->modelName;
        	$v->item = $model::ArrayBuilder()->byId($id, $this->editFields);
		} else {
            foreach ($this->editFields as $f)
                $item[$f] = "";
            $v->item = $item;
		}
        $v->render();
    }

    public function rm ($id) {
        $this->can ('rm', $id);
        $u = user::byId ($id);
        $u->delete();
        $this->flash ("Changes were saved","success");
        $this->redirect ("/" . TinyMvc::App ()->controller . "/");
    }
}
?>
