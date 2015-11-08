<?php $this->section ("_navbar");?>
<?php $this->pageTitle = 'Users'; ?>


<div class="container">
    <legend><?php e ($this->pageTitle) ?> :: <a href='/users/edit'>[new]</a></legend>
    <?php $this->section ("_flash");?>
    <link href="/assets/bundles/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <table data-toggle="table" id="grid" data-url="/users/json" class="table table-hover table-striped" data-pagination="true" data-side-pagination="server" data-search="true"  data-detail-view="true"  data-detail-formatter="detailFormatter">
        <thead>
            <tr>
                <th data-field="id" data-type="numeric" data-sortable="true">ID</th>
                <th data-field="role" data-sortable="true">Role</th>
                <th data-field="email" data-sortable="true">Login</th>
                <th data-field="lastlogindate" class='hidden-xs' data-sortable="true">Last Login</th>
                <th data-field="lastloginip" class='hidden-xs' data-sortable="true">IP</th>
                <th data-field="link" data-formatter="linkFormatter" data-sortable="false">Action</th>
            </tr>
        </thead>
    </table>
    <script src="/assets/bundles/bootstrap-table/bootstrap-table.min.js"></script>
    <script>
        function detailFormatter(index, row) {
            var html = [];
            $.each(row, function (key, value) {
                html.push('<p><b>' + key + ':</b> ' + value + '</p>');
            });
            return html.join('');
        }
        function linkFormatter (value, row) {
            return "<a class='editBtn' href='/users/edit/" + row.id +"'>Edit</a> :: <a class='editBtn' href='/users/rm/" + row.id +"'>Delete</a>";
        }
    </script>
</div>
