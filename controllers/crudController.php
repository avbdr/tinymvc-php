<?php
class crudController extends Controller {
    /*
     * Url to redirect user in case of successfull /edit/ or /rm/
     * var $successUrl string
     */
    protected $successUrl;
    /*
     * Default form values used in /edit/
     * var $formData Array
     */
    public $formData = Array ();
    public $list = null;

    public function __construct () {
        if ($this->successUrl == null)
            $this->successUrl = "/" . TinyMvc::App ()->controller. "/";
    }

    public function can ($operation, $id = null) {}

    public function index () {
        $this->can (TinyMvc::App ()->controller . '/index');
        return new View (TinyMvc::App ()->controller . "/index");
    }

    public function json () {
        $this->can (TinyMvc::App ()->controller . '/json');
        if (!$this->list)
            $this->list = new $this->modelName;
        $list = $this->list->ArrayBuilder()->withTotalCount();
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
        $isNew = true;
        $this->can (TinyMvc::App ()->controller . '/edit', $id);

        $v = new View (TinyMvc::App ()->controller . "/edit");
        if ($this->reqIs ("POST")) {
			if (!empty ($_POST['id'])) {
                $model = new $this->modelName;
	            $model  = $model::byId ($_POST['id']);
                $isNew = false;
			} else
				$model = new $this->modelName ($_POST);

    	    $model->save ($_POST);
            if (count ($model->errors) == 0) {
                $this->flash ("Changes were saved","success");
                if ($isNew && is_callable (Array ($this, "created")))
                    call_user_func_array (array ($this, "created"), [$model]);
                else if (!$isNew && is_callable (Array ($this, "updated")))
                    call_user_func_array (array ($this, "updated"), [$model]);
                $this->redirect ($this->successUrl);
                return;
            }
            $v->errors = $model->errors;
        }
		if ($id) {
            $model = new $this->modelName;
            $v->item = $model::ArrayBuilder()->byId($id, $this->formFields);
            if (!$v->item) {
                $err = new View ("404");
                $err->render();
                return;
            }
		} else {
            foreach ($this->formFields as $f)
                $item[$f] = isset ($this->formData[$f]) ? $this->formData[$f] : "";
            $v->item = $item;
        }
        return $v;
    }

    public function rm ($id) {
        if (!$id)
            return;
        $this->can (TinyMvc::App ()->controller . '/rm', $id);
        $model = new $this->modelName;
        $model  = $model::byId ($id);
        $model->delete();
        $this->flash ("Changes were saved","success");
        $this->redirect ($this->successUrl);
    }
}
?>
