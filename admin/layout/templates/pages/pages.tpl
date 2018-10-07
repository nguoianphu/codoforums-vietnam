<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>

        <li><i class="fa fa-file-powerpoint-o"></i> Pages</li>
    </ol>

</section>
<div class="col-md-12">

    <div class="box box-info">
        <form  action="?page=ui/blocks" role="form" method="post" enctype="multipart/form-data">
            <input type="hidden" value="!AwCT43Vhl#$@kDbkF" name="test_post"/>
            <div class="box-header">
                <br>


            </div><!-- /.box-header -->
            <div class="box-body table-responsive">


                <div class='col-md-2' style='padding:0'> 
                    <a class='btn btn-primary btn-block' style='color:#fff' href="?page=pages/pages&action=addnewpage"><i class="fa fa-plus"></i> Add Page</a>
                </div>
                <br>
                <hr>
                <table id="blocktable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Page Title</th>
                            <th>Page URL</th>
                            <th>Actions</th>


                        </tr>
                    </thead>
                    <tbody>

                        {section name=blk loop=$pages}
                            <tr>
                                <td>{$pages[blk].title}</td>
                                <td>
                                    {$pages[blk].url}

                                </td>

                                <td>
                                    <span class="">      
                                        <a class='btn btn-success btn-flat btn-sm' target="_blank" href="../index.php?u=/page/{$pages[blk].id}/{$pages[blk].url}"><i style="color:#fff" class="fa fa-eye"></i> View</a>                                                            
                                        &nbsp;&nbsp; <a class='btn btn-info btn-flat btn-sm' href="index.php?page=pages/pages&action=editpage&id={$pages[blk].id}"><i style="color:#fff" class="fa fa-edit"></i> Edit</a>                                                            
                                        &nbsp;&nbsp; <a class='btn btn-danger btn-flat btn-sm' href="javascript:void(0)" onclick="delete_page({$pages[blk].id})"><i style="color:#fff" class="fa fa-trash-o"></i> Delete</a>
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


    function delete_page(id) {


        var flag = confirm("Are you sure you want to delete this Page?");

        if (flag == true) {

            console.log("block " + id + " delete req sent");
            window.location = "index.php?page=pages/pages&id=" + id + "&action=delete&CSRF_token={$token}";

        } else {
            console.log("req cancelled");
        }



    }

</script>
