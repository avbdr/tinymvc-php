<?php $this->pageTitle = 'User properties'; ?>
<div class="container">
    <legend>User Properties</legend>
    <form class="col-md-8 form-horizontal" id='paymentForm' role='form' method='POST'>
        <fieldset>
            <?php foreach ($item as $label => $value) :?>
             <div class="form-group">
                 <label for="billingName" class="col-sm-3"><?php echo $label?></label>
                 <div class="col-sm-9">
                     <input placeholder="John Doe" type="text" value="<?php echo $value?>" id="<?php echo $label?>" name='<?php echo $label?>' class='form-control'>
                 </div>
             </div>
            <?php endforeach;?>
            <button class='btn btn-success' type=submit>Save</button>
        </fieldset>
    </form>
</div>

