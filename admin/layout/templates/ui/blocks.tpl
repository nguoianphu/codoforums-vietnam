<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><i class="fa fa-laptop"></i> UI Elements</li>
        <li class="breadcrumb-item"><i class="fa fa-cubes"></i> Blocks</li>
    </ol>

</section>
<div class="col-md-12">

    <div class="box box-info">
        <form  action="?page=ui/blocks" role="form" method="post" enctype="multipart/form-data">
            <input type="hidden" value="!AwCT43Vhl#$@kDbkF" name="test_post"/>
            <div class="box-header">
                <br>
                <h3 class="box-title">Blocks for theme: <em>{$theme}</em></h3>

            </div><!-- /.box-header -->
            <div class="box-body table-responsive">


                <div class='col-md-2' style='padding:0'> 
                    <a class='btn btn-primary btn-block' style='color:#fff' href="?page=ui/blocks&action=addnewblock"><i class="fa fa-plus"></i> Add Block</a>
                </div>
                <br><br>
                <hr>
                <table id="blocktable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Block Name</th>
                            <th>Region</th>
                            <th>Weight</th>
                            <th>Type</th>
                            <th>Configure</th>

                        </tr>
                    </thead>
                    <tbody>

                        {section name=blk loop=$blocks}
                            <tr>
                                <td>{$blocks[blk].title}</td>
                                <td>
                                    <select name="bid_{$blocks[blk].id}" size="1" class='form-control'>
                                        <option value="<none>">&lt;none&gt;</option>
                                        {html_options values=$av_blocks output=$av_blocks selected=$blocks[blk].region}
                                    </select>

                                </td>
                                <td>
                                    <input type="number" name="bweight_{$blocks[blk].id}" value="{$blocks[blk].weight}" class="form-control" />
                                </td>
                                <td>{$blocks[blk].module}</td>
                                <td>
                                    <span class="">                                                             
                                        <a class='btn btn-info btn-flat btn-sm' href="index.php?page=ui/blocks&action=editblock&id={$blocks[blk].id}"><i style="color:#fff" class="fa fa-edit"></i> Edit</a>                                                            
                                        &nbsp;&nbsp; <a class='btn btn-danger btn-flat btn-sm' href="javascript:void(0)" onclick="delete_block({$blocks[blk].id})"><i style="color:#fff" class="fa fa-trash-o"></i> Delete</a>
                                    </span>
                                </td>
                            </tr>
                        {/section}


                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Block Name</th>
                            <th>Region</th>
                            <th>Weight</th>
                            <th>Type</th>
                            <th>Configure</th>

                        </tr>
                    </tfoot>
                </table>
            </div><!-- /.box-body -->

            <div class="box-footer">
                <input type="hidden" name="CSRF_token" value="{$token}" />
                <input type="submit" value="Save" class="btn btn-primary"/>
            </div>
        </form>
    </div>


</div>

<script type="text/javascript">
    function delete_block(id) {
        var flag = confirm("Are you sure you want to delete this block?");
        if (flag === true) {
            console.log("block " + id + " delete req sent");
            window.location = "index.php?page=ui/blocks&id="+id+"&action=delete&CSRF_token={$token}";
        } else {
            console.log("req cancelled");
        }
    }

</script>
