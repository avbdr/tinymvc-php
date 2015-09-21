<?php $this->section ("_navbar");?>
<?php $this->pageTitle = 'Products'; ?>


<div class="container">
    <legend><?php e ($this->pageTitle) ?> :: <a href='/products/edit'>[new]</a></legend>
    <?php $this->section ("_flash");?>
    <link href="/assets/bundles/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <table data-toggle="table" id="grid" data-url="/products/json" class="table table-hover table-striped" data-pagination="true" data-side-pagination="server" data-search="true">
        <thead>
            <tr>
                <th data-field="id" data-sortable="true">ID</th>
                <th data-field="customerid" data-sortable="true">Client</th>
                <th data-field="email" data-sortable="true">User</th>
                <th data-field="productName" data-sortable="true">Product</th>
                <th data-field="link" data-formatter="link" data-sortable="false">Action</th>
            </tr>
        </thead>
    </table>
    <script src="/assets/bundles/bootstrap-table/bootstrap-table.min.js"></script>

    <script>
        function link (value, row) {
            return "<a class='editBtn' href='/products/edit/" + row.id +"'>Edit</a> :: <a class='editBtn' href='/products/rm/" + row.id +"'>Delete</a>";
        }
    </script>
</div>
