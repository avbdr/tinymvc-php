<?php
class crudController extends Controller {
    /*
     * Default form values used in /edit/
     * var $formData Array
     */
    public $formData = Array ();

    public function can ($operation, $id = null) {
    }

    public function index () {
        $this->can ('index');
        return new View (TinyMvc::App ()->controller . "/index");
    }

    public function json ($list = null) {
        $this->can ('json');
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

        return Array (
            'rows' => $list->get(Array ($offset, $rowCount), $this->displayFields),
            'total' =>$list->totalCount
        );
    }

    public function edit ($id = null) {
        $this->can ('edit', $id);

        $v = new View (TinyMvc::App ()->controller . "/edit");
        if ($this->reqIs ("POST")) {
			if (!empty ($_POST['id'])) {
                $model = new $this->modelName;
	            $model  = $model::byId ($_POST['id']);
			} else {
				$model = new $this->modelName ($_POST);
            }
    	    $model->save ($_POST);
            if (count ($model->errors) == 0) {
                $this->flash ("Changes were saved","success");
                $this->redirect ("/" .TinyMvc::App ()->controller. "/");
                return;
            }
            $v->errors = $model->errors;
        }
		if ($id) {
            $model = new $this->modelName;
            $v->item = $model::ArrayBuilder()->byId($id, $this->formFields);
		} else {
            foreach ($this->formFields as $f)
                $item[$f] = isset ($this->formData[$f]) ? $this->formData[$f] : "";
            $v->item = $item;
        }
        return $v;
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
