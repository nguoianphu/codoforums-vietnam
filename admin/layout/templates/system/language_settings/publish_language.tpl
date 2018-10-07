<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=language_settings"><i class="fa fa-language"></i> Language Settings</a></li>
        <li class="breadcrumb-item active"><i class="fa fa-plus"></i>  Publish Language  </li>
    </ol>

</section>
<div class="col-md-6">
    <div  class="box box-info">
        <form class="box-body" action="http://central.localhost/published_language/name{$name}" role="form" method="post" enctype="multipart/form-data">
            <label>Enter your email</label>
            <input type="text" class="form-control" name="publish_language" value="" /><br/>
            <input type="hidden" class="form-control" name="name" value={$name} /><br/>
            <input type="submit" value="Share" class="btn btn-primary"/>
        </form>
        <br/>
        <br/>
    </div>
</div>