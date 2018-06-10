<div class="col-md-6">
<form  action="index.php?page=ploader&plugin=sso" role="form" method="post" enctype="multipart/form-data">


SSO Name:
<input type="text" class="form-control" name="sso_name" value="{"sso_name"|get_opt}" /><br/>
 
SSO Client ID:
<input type="text" class="form-control" name="sso_client_id" value="{"sso_client_id"|get_opt}" /><br/>

SSO Secret:
<input type="text" class="form-control" name="sso_secret" value="{"sso_secret"|get_opt}" /><br/>

SSO Get User Path:
<input type="text" class="form-control" name="sso_get_user_path" value="{"sso_get_user_path"|get_opt}" /><br/>

SSO Login User Path:
<input type="text" class="form-control" name="sso_login_user_path" value="{"sso_login_user_path"|get_opt}" /><br/>

SSO Logout User Path:
<input type="text" class="form-control" name="sso_logout_user_path" value="{"sso_logout_user_path"|get_opt}" /><br/>

SSO Register User Path:
<input type="text" class="form-control" name="sso_register_user_path" value="{"sso_register_user_path"|get_opt}" /><br/>

<input type="submit" value="Save" class="btn btn-primary"/>
</form>
<br/>
<br/>
</div>