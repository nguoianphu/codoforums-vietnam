<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
         <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
         <li class=""><i class="fa fa-envelope"></i> Mail Settings</li>
         <li class="active"><i class="fa fa-gear"></i> Configuration</li>
    </ol>
    
</section>
<div class="col-md-6">
<div  class="box box-info">
<form class="box-body" action="?page=mail/configuration" role="form" method="post" enctype="multipart/form-data">



Mail Type:


<select name='mail_type' class="form-control">
    <option value='smtp' {if "mail_type"|get_opt == 'smtp' } selected {/if}>SMTP</option>
    <option value='mail'  {if "mail_type"|get_opt == 'mail' } selected {/if}>mail()</option>
    
</select><br>

<hr>
<span style="font-size:smaller">The below settings are required only if you have selected SMTP above.</span>

<br><br>
SMTP Protocol:
<input type="text" class="form-control" name="smtp_protocol" value="{"smtp_protocol"|get_opt}" />

<br/>

SMTP Server:
<input type="text" class="form-control" name="smtp_server" value="{"smtp_server"|get_opt}" /><br/>

SMTP Port:
<input type="text" class="form-control" name="smtp_port" value="{"smtp_port"|get_opt}" /><br/>

SMTP username:
<input type="text" class="form-control" name="smtp_username" value="{"smtp_username"|get_opt}" /><br/>

SMTP Password:
<input type="text" class="form-control" name="smtp_password" value="{"smtp_password"|get_opt}" /><br/>


<input type="hidden" name="CSRF_token" value="{$token}" />

<input type="submit" value="Save" class="btn btn-primary"/>
</form>
 
