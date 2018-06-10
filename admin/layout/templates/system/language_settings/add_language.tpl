<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="index.php?page=system/language_settings"><i class="fa fa-language"></i> Language Settings</a></li>
        <li class="active"><i class="fa fa-plus"></i>  Add Language  </li>
    </ol>

</section>
<div class="col-md-6">
    <div  class="box box-info">
        <form class="box-body" action="?page=system/add_language" role="form" method="post" enctype="multipart/form-data">
            <label>Enter Language Name</label>
            <input type="text" class="form-control" name="language" value="" /><br/>
             <input type="hidden" name="CSRF_token" value="{$token}" />
            <input type="submit" value="Save" class="btn btn-primary"/>
        </form>
        <br/>
        <br/>
    </div>
</div>