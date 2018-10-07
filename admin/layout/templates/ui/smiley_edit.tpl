<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><i class="fa fa-laptop"></i> UI Elements</li>

        <li class="breadcrumb-item"><a href="index.php?page=ui/smileys"><i class="fa fa-smile-o"></i>  Smileys</a></li>
        <li class="breadcrumb-item"><i class="fa fa-edit"></i> Edit Smiley</li>
    </ol>

</section>
{if $msg eq ""}
{else}
    <div class='row'>
        <div class="col-md-6">
            <div class="alert alert-info alert-dismissable">
                <i class="fa fa-info"></i>
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {$msg}
            </div>
        </div>
    </div>
{/if}
<div class="col-md-6">

    <div class="box box-info">

        <div class="box-body">

            <form  action="?page=ui/smileys&action=edit" role="form" method="post" enctype="multipart/form-data">


                <input type='hidden' name='id' value='{$smiley.id}' />
                <div class="form-group">
                    <label>Smiley Image:</label> <br>
                    <img src='{$smiley.image_name}' />
                </div>
                <hr>
                <div class="form-group">
                    <label>Smiley Code:</label>
                    <textarea name="smiley_code" required placeholder="" rows="4" class="form-control" >{$smiley.symbol}</textarea>
                    <div class="callout callout-info">
                        Specify Smiley by using their smiley code. <br>Enter one Smiley variant per line.<br>  
                        Example for "Happy Face":<br> <code>:)<br>:-)<br>:smile:</code>
                    </div>
                </div>


                <div class="form-group">
                    <label>Smiley Weight/Order:</label>
                    <input type="number" name="weight"  value="{$smiley.weight}" class="form-control" required />

                </div>                


                <div class="form-group">
                    <label>New Smiley Image: </label>
                    <input type="file" name="smiley_image"  value="" class="form-control"  />
                    <div class="callout callout-info">
                        Upload a new one only if you want to change the existing smiley imageF.
                    </div>
                </div>
                <input type="hidden" name="CSRF_token" value="{$token}" />
                <input type="submit" value="Save Smiley" class="btn btn-success" />

            </form>
        </div>
    </div>
</div>

