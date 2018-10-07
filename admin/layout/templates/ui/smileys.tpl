<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><i class="fa fa-laptop"></i> UI Elements</li>
        <li class="breadcrumb-item"><i class="fa fa-smile-o"></i> Smileys</li>
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

            <form  action="?page=ui/smileys&action=add" role="form" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Smiley Code:</label>
                    <textarea name="smiley_code" required placeholder="" rows="4" class="form-control" ></textarea>
                    <div class="callout callout-info">
                        Specify Smiley by using their smiley code. <br>Enter one Smiley variant per line.<br>  
                        Example for "Happy Face":<br> <code>:)<br>:-)<br>:smile:</code>
                    </div>
                </div>


                <div class="form-group">
                    <label>Smiley Weight/Order:</label>
                    <input type="number" name="weight"  value="0" class="form-control" required />

                </div>                


                <div class="form-group">
                    <label>Smiley Image:</label>
                    <input type="file" name="smiley_image"  value="" class="form-control" required />

                </div>
                <input type="hidden" name="CSRF_token" value="{$token}" />
                <input type="submit" value="Add Smiley" class="btn btn-success" />

            </form>
        </div>
    </div>
</div>

<div class="col-md-12">

    <div class="box box-info">
        <form  action="?page=ui/blocks" role="form" method="post" enctype="multipart/form-data">
            <input type="hidden" value="!AwCT43Vhl#$@kDbkF" name="test_post"/>
            <div class="box-header">
                <br>


            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <table id="blocktable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Smiley</th>
                            <th>Code</th>
                            <th>Weight</th>

                            <th>Configure</th>

                        </tr>
                    </thead>
                    <tbody>

                        {section name=smile loop=$smilies}
                            <tr>
                                <td><img src='{$smilies[smile].image_name}' /></td>
                                <td>{$smilies[smile].symbol|replace:"\n":"<br>"}</td>
                                <td>{$smilies[smile].weight}</td>

                                <td>
                                    <span class="">                                                             
                                        <a class='btn btn-info btn-flat btn-sm' href="index.php?page=ui/smileys&action=edit&id={$smilies[smile].id}"><i style="color:#fff" class="fa fa-edit"></i> Edit</a>                                                           
                                        &nbsp;&nbsp; <a class='btn btn-danger btn-flat btn-sm' href="javascript:void(0)" onclick="delete_smiley({$smilies[smile].id})"><i style="color:#fff" class="fa fa-trash-o"></i> Delete</a>
                                    </span>
                                </td>
                            </tr>
                        {/section}


                    </tbody>

                </table>
            </div><!-- /.box-body -->


        </form>
    </div>    


</div> 

<script>


    function delete_smiley(id) {


        var flag = confirm("Are you sure you want to delete this smiley?");

        if (flag == true) {

            console.log("block " + id + " delete req sent");
            window.location = "index.php?page=ui/smileys&id=" + id + "&action=delete&CSRF_token={$token}";

        } else {
            console.log("req cancelled");
        }



    }

</script>
