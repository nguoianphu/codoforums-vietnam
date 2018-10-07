<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active breadcrumb-item"><i class="fa fa-puzzle-piece"></i> ReCaptcha</li>
    </ol>

</section>

<div class="col-md-6">
    <div  class="box box-info">

        <form class="box-body" action="?page=spam/recaptcha" role="form" method="post" enctype="multipart/form-data">
            <p>Codoforum uses Google's No CAPTCHA reCAPTCHA for protecting your forms.</p>


            <hr/>
            
            <div class="form-group">
                <label>Enable recaptcha ?</label>
                <br/>
                <input 
                    class="simple form-control" name="captcha" 
                    data-permission='yes'
                    {if {"captcha"|get_opt} eq 'enabled'} checked="checked" {/if}
                    type="checkbox"  data-toggle="toggle"
                    data-on="yes" data-off="no" data-size="small"
                    data-onstyle="success" data-offstyle="danger">
            </div>
            
            <hr/>
            <div class=''>
                <label>Site key</label>
                <input type='text' name="captcha_public_key" class='form-control' value="{"captcha_public_key"|get_opt}">

            </div>

            <br/>
            <div class=''>
                <label>Secret key</label>
                <input type='text' name="captcha_private_key" class='form-control' value="{"captcha_private_key"|get_opt}">

            </div>

            <br/>
            
            <p>
                If you do not have the <b>site key</b> and <b>secret key</b>, get it from here:
                <a href="https://www.google.com/recaptcha/admin#list">https://www.google.com/recaptcha/admin#list</a>
                    
            </p>
            
            <input type="hidden" name="CSRF_token" value="{$token}" />
<br/>
            <input type="submit" value="Save" class="btn btn-primary"/>

            
        </form>

    </div>


</div>
