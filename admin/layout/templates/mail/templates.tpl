<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
         <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
         <li class=""><i class="fa fa-envelope"></i> Mail Settings</li>
         <li class="active"><i class="fa fa-file"></i> Templates</li>
    </ol>
    
</section>
<div class="col-md-6">
<div  class="box box-info">
<form class="box-body" action="?page=mail/templates" role="form" method="post" enctype="multipart/form-data">

Await Approval Subject:
<input type="text" class="form-control" name="await_approval_subject" value="{"await_approval_subject"|get_opt}"/><br/>
Await Approval Message:
<textarea class="form-control" style="height:200px" name="await_approval_message">{"await_approval_message"|get_opt}</textarea><br/>

Post Notify Subject:
<input type="text" class="form-control" name="post_notify_subject" value="{"post_notify_subject"|get_opt}"/><br/>
Post Notify Message:
<textarea class="form-control" style="height:200px" name="post_notify_message">{"post_notify_message"|get_opt}</textarea><br/>

Password Reset Subject:
<input type="text" class="form-control" name="password_reset_subject" value="{"password_reset_subject"|get_opt}"/><br/>
Password Reset Message:
<textarea class="form-control" style="height:200px" name="password_reset_message">{"password_reset_message"|get_opt}</textarea><br/>
<input type="hidden" name="CSRF_token" value="{$token}" />
<input type="submit" value="Save" class="btn btn-primary"/>
</form>
 
