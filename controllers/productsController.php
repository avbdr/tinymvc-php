<?php
class productsController extends crudController {
    public $modelName = 'product';
    public $editFields = Array ("id", "customerId", "userId", "productName");
    public $displayFields = Array ("t_products.id", "t_products.customerid", "t_users.email", "t_products.productName");

    public function can ($operation, $id = null) {
        if (!$this->session->user)
            $this->redirect ('/users/login');
    }

    public function json ($list = null) {
        $list = product::join('user');
        parent::json ($list);
    }
}
?>
