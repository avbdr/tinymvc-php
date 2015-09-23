<?php $this->pageTitle = 'Admin login'; ?>
<style>
body {
  padding-top: 40px;
  padding-bottom: 40px;
}

.shadow {
    max-width: 330px;
    box-shadow: 10px 10px 5px #888888;
    margin: 0 auto;
}
.form-signin {
  padding: 15px;
  background-color: #eee;
  border-radius: 5px 5px 0px 0px
}
.form-signin .form-signin-heading,
.form-signin .checkbox {
  margin-bottom: 10px;
}
.form-signin .checkbox {
  font-weight: normal;
}
.form-signin .form-control {
  position: relative;
  height: auto;
  font-size: 16px;
}
.form-signin .form-control:focus {
  z-index: 2;
}
.form-signin input[type="email"] {
  margin-bottom: -1px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}
.form-signin input[type="password"] {
  margin-bottom: 10px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}
</style>
    <div class="container">
      <div class='shadow'>
      <form class="form-signin" method=POST>
        <h2 class="form-signin-heading">Please sign in</h2>
      	<?php $this->section ("_flash");?>
        <label for="inputEmail" class="sr-only">Email address</label>
        <input type="email" id="inputEmail" name=email class="form-control" placeholder="Email address" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" name=password class="form-control" placeholder="Password" required>
        <div class="checkbox">
          <label>
            <input type="checkbox" value="remember-me"> Remember me
          </label>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      </form>
      </div>
    </div> <!-- /container -->
