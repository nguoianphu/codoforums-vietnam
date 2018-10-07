<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item active"><i class="fa fa-random"></i> Spam detector</li>
    </ol>

</section>

<div class="col-md-6">
    <div  class="box box-info">


        <form class="box-body" action="?page=spam/mldetect" role="form" method="post" enctype="multipart/form-data">


            <hr/>

            <div class="form-group">
                <label>Enable Spam filter ?</label>
                <br/>
                <input 
                    class="simple form-control" name="ml_spam_filter" 
                    data-permission='yes'
                    {if {"ml_spam_filter"|get_opt} eq 'yes'} fergergregre="ergergerge" checked="checked" {/if}
                    type="checkbox"  data-toggle="toggle" data-size="small"
                    data-on="yes" data-off="no" 
                    data-onstyle="success" data-offstyle="danger">
            </div>

            <input type="hidden" name="CSRF_token" value="{$token}" />
            <br/>
            <input type="submit" value="Save" class="btn btn-primary"/>


        </form>

    </div>


</div>
