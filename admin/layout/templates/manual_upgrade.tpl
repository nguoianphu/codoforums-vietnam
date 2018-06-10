<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active"><i class="fa fa-wrench"></i> Manual upgrade</li>
    </ol>

</section>
<div class="col-md-12">
    <div  class="box box-info">


        <form class="box-body" action="?page=manual_upgrade" role="form" method="post" enctype="multipart/form-data">


            <h3>This will only upgrade your database and will not touch your files.</h3>


            <hr/><br/><br/>
            <label>Current version of codoforum </label>
            <input type="text" class="form-control" name="version" value="{"version"|get_opt}"/><br/>


            <input type="hidden" name="CSRF_token" value="{$token}" />
            <input type="submit" value="Upgrade" class="btn btn-primary"/>

        </form>
        <br/>
        <br/>
    </div>
</div>