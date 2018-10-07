<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><i class="fa fa-desktop"></i> System</li>            
        <li class="breadcrumb-item active"><i class="fa fa-envelope-square"></i> Mass mail</li>
    </ol>

</section>
<style>

    .well {

        background: #fff;
    }

    textarea {

        resize: vertical;

    }
</style>
<div class="row col-md-6">

    {if $sent}
        <div class="alert alert-success">
          Mass mail triggered sucessfully!
        </div>
    {/if}

    <p>Send mass emails to users of your forum</p>
    <div  class="box box-info">
        <form class="box-body form" action="?page=system/massmail" role="form" method="post" enctype="multipart/form-data">


            <div class="form-group">
                <label for="subject">Email subject</label>
                <input type="text" name="subject" class="form-control" required=""/>
            </div>

            <div class="form-group">
                <label for="body">Email body</label>
                <textarea name="body" class="form-control" rows="6" required=""></textarea>
                <span style="color:grey;">Following placeholders will be replaced: [username], [userid]</span>
            </div>

            <legend>Filters</legend>

            <div class="form-group">
                <label for="roles">Send email to specified roles</label>

                {foreach from=$roles item=role}
                    <div class="form-group">
                        <input type="checkbox" name="roles[]" value="{$role.rid}" class="form-control" /> {$role.rname}
                    </div>
                {/foreach}

                <div class="callout callout-info">
                    If you do not select any role, the email will be sent to all roles.
                </div>                
                <div class="callout callout-info">
                    <b>Note</b>: Emails are not sent instantly but are queued which are sent at 10 emails every 30 minutes
                </div>                
                
            </div>
            <input type="hidden" name="CSRF_token" value="{$token}">
            <input type="submit" value="Send" class="btn btn-success">

        </form>        
    </div>

</div>