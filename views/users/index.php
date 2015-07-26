<?php $this->section ("_navbar");?>
<?php $this->pageTitle = 'Users'; ?>

<div class="container">
    <legend><?php e ($this->pageTitle) ?></legend>
    <?php $this->section ("_flash");?>

    <table class="table table-striped table-hover table-responsive">
        <thead>
            <tr>
            <?php
                foreach (array_keys($items[0]) as $item)
                         echo "<td><b>{$item}</b></td>";
            ?>
                <td></td>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($items as $item) {
                    echo "<tr>";
                    foreach ($item as $col)
                         echo "<td>{$col}</td>";
                    echo "<td>" . a ("edit/" . $item['id'], "Edit") . "</td>";
                    echo "<td>" . a ("rm/" . $item['id'], "RM") . "</td>";
                    echo "</tr>";
                }
            ?>
        </tbody>
    </table>
</div>
