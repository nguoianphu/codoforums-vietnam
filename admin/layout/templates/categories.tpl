<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item active"><i class="fa fa-table"></i> Categories</li>
    </ol>

</section>


<div class="row" id="msg_cntnr">
    <div class="col-lg-6">
        {if $msg eq ""}

        {elseif $err==1}
            <div class="alert alert-danger alert-dismissable">
                <i class="fa fa-ban"></i>
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {$msg}
            </div>
        {else}   
            <div class="alert alert-info alert-dismissable">
                <i class="fa fa-info"></i>
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {$msg}
            </div>
        {/if}

    </div>
</div>



<div class="row" id="add_cat">
    <div class="col-lg-6">
        <div class="box box-info">
            <form class="box-body" action="?page=categories" role="form" method="post" enctype="multipart/form-data">
                <input type="hidden" value="new" name="mode"/>
                <input type="text" name="cat_name"  value="" class="form-control" placeholder="Category name" required />
                <br/>
                Category Icon[Only visible for labels]:<br/>
                <input type="text" name="cat_img" class="form-control" placeholder="icon that must be available in fonts"  required />
                <br/>
                Is a Label ?:<br/>
                <select class="form-control" name="is_label">
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
                <br/>
                <textarea name="cat_description" placeholder="Category Description" class="form-control" required ></textarea>
                <br/>
                <input type="hidden" name="CSRF_token" value="{$token}" />

                <input type="submit" value="Add Category" class="btn btn-success" />
            </form>
        </div>
    </div>

</div>

<hr/>
<div class="row">

    <div class="col-lg-12">
        <div class="box box-success" style="padding: 15px;">

            <p style="padding:4px;background:#eee"><i class="fa fa-question-circle"></i> To create sub-categories drag the category to the right </p>   
            {$cats}

            <br/><br/>
            <button value="Save" class="btn btn-primary" id="save_order_btn" onclick="save_order()">Save Order</button>

        </div>

    </div>
</div><!-- /.row -->
<br/>
<br/>
<div class="row">
    <div class="col-lg-4">
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="delete_cat_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Delete category <b id="del_cat_name"></b> ?</h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>
            <div class="modal-body">

                <br/>
                <div class="form-group">
                    <input id="del_cat_children" type="checkbox" checked />&nbsp;
                    <label for="children"> Delete all sub categories</label>
                </div>
                
                <b> Note: </b> All topics and posts will be permanently deleted and cannot be recovered.<br/>
                <b> Note: </b> If you untick 'Delete all sub categories', the topics and posts in the sub-categories will not be deleted.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="cat_del_btn">Delete</button>
            </div>
        </div>
    </div>
</div>
<style type="text/css">

    .dd { position: relative; display: block; margin: 0; padding: 0; max-width: 100%; list-style: none; font-size: 13px; line-height: 20px; }

    .dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
    .dd-list .dd-list { padding-left: 30px; }
    .dd-collapsed .dd-list { display: none; }

    .dd-item,
    .dd-empty,
    .dd-placeholder { display: block; position: relative; margin: 0; padding: 0; min-height: 20px; font-size: 13px; line-height: 20px;
                      
    }



    .dd-options {

        display: inline-block;
        height: 30px;
        border: 1px solid #ccc;
        border-left: 0;
        background: #fff;
        margin: 5px 0;
        vertical-align: top;
    }

    .dd-options .btn {
        padding: 3px 10px;
        height: 100%;
        display: inline-block;
        border-radius: 0;
        border-bottom: 0;
        border-top: 0;
    }

    .dd-options i {

    }

    .dd-handle { display: inline-block; height: 30px; width: 75%; margin: 5px 0; padding: 5px 10px; color: #333; text-decoration: none; font-weight: bold; 
                 background: #fff;
                 cursor: all-scroll;
                 border: 1px solid #ccc;
                 /*background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
                 background:    -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
                 background:         linear-gradient(top, #fafafa 0%, #eee 100%);
                 -webkit-border-radius: 3px;
                 border-radius: 3px;*/
                 border-right: 0;
                 
                 box-sizing: border-box; -moz-box-sizing: border-box;
    }
    .dd-handle:hover { color: #000; background: #fff; }

    .dd-item > button {



        display: block; position: relative; cursor: pointer; float: left; width: 25px; height: 20px; margin: 5px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; border: 0; background: transparent; font-size: 12px; line-height: 1; text-align: center; font-weight: bold;


        background: #fff;
        margin: 5px 0; 
        height: 30px;
        border: 1px solid #ccc;
        border-right: 0;


    }
    .dd-item > button:before { content: '+'; display: block; position: absolute; width: 100%; text-align: center; text-indent: 0; }
    .dd-item > button[data-action="collapse"]:before { content: '-'; }

    .dd-placeholder,
    .dd-empty { margin: 5px 0; padding: 0; min-height: 30px; background: #f2fbff; border: 1px dashed #b6bcbf; box-sizing: border-box; -moz-box-sizing: border-box; }
    .dd-empty { border: 1px dashed #bbb; min-height: 100px; background-color: #e5e5e5;
                background-image: -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff), 
                    -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
                background-image:    -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff), 
                    -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
                background-image:         linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff), 
                    linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
                background-size: 60px 60px;
                background-position: 0 0, 30px 30px;
    }

    .dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
    .dd-dragel > .dd-item .dd-handle { margin-top: 0; }
    .dd-dragel .dd-handle {
        -webkit-box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
        box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
    }

    /**
     * Nestable Draggable Handles
     */

    .dd3-content { display: block; height: 30px; margin: 5px 0; padding: 5px 10px 5px 40px; color: #333; text-decoration: none; font-weight: bold; border: 1px solid #ccc;
                   background: #fff;
                   /*background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
                   background:    -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
                   background:         linear-gradient(top, #fafafa 0%, #eee 100%);
                   */ -webkit-border-radius: 3px;
                   border-radius: 3px;
                   box-sizing: border-box; -moz-box-sizing: border-box;
    }
    .dd3-content:hover { color: #000; background: #fff; }

    .dd-dragel > .dd3-item > .dd3-content { margin: 0; }

    .dd3-item > button { margin-left: 0px; }

    .dd3-handle { position: absolute; margin: 0; left: 0; top: 0; cursor: pointer; width: 30px; text-indent: 100%; white-space: nowrap; overflow: hidden;
                  border: 1px solid #aaa;
                  background: #ddd;
                  background: -webkit-linear-gradient(top, #ddd 0%, #bbb 100%);
                  background:    -moz-linear-gradient(top, #ddd 0%, #bbb 100%);
                  background:         linear-gradient(top, #ddd 0%, #bbb 100%);
                  border-top-right-radius: 0;
                  border-bottom-right-radius: 0;
    }
    .dd3-handle:hover { background: #ddd; }
</style>


<form id="delete_cat_form" method="post" action="index.php?page=categories&action=delete">
    <input type="hidden" id="del_cat_id" name="del_cat_id" value=""/>
    <input type="hidden" id="del_cat_children_input" name="del_cat_children" value="" />
    <input type="hidden" name="CSRF_token" value="{$token}" />

</form>
<script>
    window.onload = function () {
        console.log('s')
        jQuery('.dd').nestable({ /* config options */});
    };


    function save_order() {
        var d = jQuery('.dd').nestable('serialize');
        d = JSON.stringify(d);
        $('#save_order_btn').html('Saving.......');
        $.post('?page=categories&action=reorder', {
            data: d,
            CSRF_token: "{$token}"
        }, function (data) {

            //console.log(data);
            $('#save_order_btn').html('Save Order');
            alert('order saved!');
        });

    }


    function delete_cat(id, name) {

        $('#del_cat_name').html(name);
        $('#delete_cat_modal').modal();

        $('#cat_del_btn').on('click', function () {

            $('#del_cat_id').val(id + '');
            $('#del_cat_children_input').val($('#del_cat_children').prop('checked') ? 'yes' : 'no');
            $('#delete_cat_form').submit();

        });
    }


</script>