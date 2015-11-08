<?php $this->layout = null;?>

<div id='passwordErrorBox' class='alert' style='display:none'></div>

<?php require_once (BASEPATH . "/libs/PFBC/Form.php");
$form = Array (
    "ajax" => 'passwordReset',
    "id" => Array ("Hidden", ""),
    "currPassword" => Array ("Password", "CURRENT PASSWORD", "", Array("required" => 1, "minlength" => 3)),
    "password1" => Array ("Password", "NEW PASSWORD", "", Array("required" => 1, "minlength" => 3)),
    "password2" => Array ("Password", "REPEAT PASSWORD", "", Array("required" => 1, "minlength" => 3)),
);
Form::renderArray ("password", $form);
?>
