<?php if (!empty ($flash)): ?>
<div class="alert alert-<?php echo $flash[0]?> alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <strong><?php echo $flash[0]?>!</strong> <?php echo $flash[1]?>
</div>
<?php endif ?>
