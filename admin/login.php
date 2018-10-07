<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Application Name - Admin Login</title>
<meta name="viewport" content="width=device-width,initial-scale=1" />
 
<!-- StyleSheet -->
<link rel="stylesheet" href="css/bootstrap.css" />
<link rel="stylesheet" href="css/bootstrap-responsive.css" />
<link rel="stylesheet" href="css/custom.css" />
</head>
 
<body>
<!-- Navigation Bar -->
<div class="navbar navbar-inverse navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
 
            </a>
 
    <a href="#" class="brand">Application Name</a>
 
    <div class="nav-collapse collapse pull-right">
        <ul class="nav">
            <li><a href="#forgot" data-toggle="modal"><i class="icon-user icon-white"></i> Forgot Password</a></li>
            <li class="divider-vertical"></li>
            <li><a href="#contact" data-toggle="modal"><i class="icon-envelope icon-white"></i> Contact Us</a></li>
            <li class="divider-vertical"></li>
 
        </ul>
    </div>
 
    </div>
  </div>
</div>
<!-- Navigation Ends -->
 
<!-- Main Container -->
<section>
<div class="container login">
    <div class="row ">
        <div class="center span4 well">
            <legend>Please Sign In</legend>
            <div class="alert alert-error">
                <a class="close" data-dismiss="alert" href="#">×</a>Incorrect Username or Password!
            </div>
            <form method="post" action="" accept-charset="UTF-8">
            <input type="text" id="username" class="span4" name="username" placeholder="Username" />
            <input type="password" id="password" class="span4" name="password" placeholder="Password" />
            <label class="checkbox">
                <input type="checkbox" name="remember" value="1" /> Remember Me
            </label>
            <button type="submit" name="submit" class="btn btn-primary btn-block">Sign in</button>
            </form>
        </div>
    </div>
</div>
<p class="text-center muted ">&copy; Copyright 2013 - Application Name</p>
</section>
<!-- Main Container Ends -->
 
<!-- Forgot Password Model Box -->
<div id="forgot" class="modal hide fade show" style="display: none; ">
<div class="modal-header">
<a class="close" data-dismiss="modal">×</a>
<h3>Forgot Password</h3>
</div>
<div class="modal-body">
<p>Enter your username to reset the password</p>
<form>
      <div class="controls controls-row">
          <input id="name" name="name" type="text" class="span3" placeholder="Name" />
      </div>
 
</form>
</div>
<div class="modal-footer">
<a href="#" class="btn btn-primary">Submit</a>
<a href="#" class="btn" data-dismiss="modal">Close</a>
</div>
</div>
 
<!-- Contact Us Model Box -->
<div id="contact" class="modal hide fade show" style="display: none; ">
<div class="modal-header">
<a class="close" data-dismiss="modal">×</a>
<h3>Contact Us</h3>
</div>
<div class="modal-body">
<form>
      <div class="controls controls-row">
          <input id="name" name="name" type="text" class="span3" placeholder="Name" />
      </div>
 
       <div class="controls controls-row">
       <input id="email" name="email" type="email" class="span3" placeholder="Email address" />
       </div>
 
      <div class="controls">
          <textarea id="message" name="message" class="span5" placeholder="Your Message" rows="5"></textarea>
      </div>
 
  </form>
</div>
 
<div class="modal-footer">
<a href="#" class="btn btn-primary">Submit</a>
<a href="#" class="btn" data-dismiss="modal">Close</a>
</div>
</div>
 
<!-- JavaScript -->
<script src="http://code.jquery.com/jquery-latest.js"></script>
<script src="js/bootstrap.js"></script>
</body>
</html>
