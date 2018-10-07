<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item active"><i class="fa fa-puzzle-piece"></i> Custom profile fields</li>
    </ol>


</section>
<style type="text/css">


    #selections {

        cursor: move;
    }
    #selections .sortable-ghost, .sortable-ghost td {
        opacity: 1;
        color: #fff;
        background-color: #555 !important;
    }


</style>
<div class="col-md-12">

    <div class="row" id="msg_cntnr">
    <div class="col-lg-6">
        {if $msg eq ""}

        {else}    
            <div class="alert alert-info alert-dismissable">
                <i class="fa fa-info"></i>
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {$msg}
            </div>
        {/if}

    </div>
</div>

    <div class="box box-info">
        <br/>            
        <div class="box-body table-responsive">


            <div class='col-md-2' style='padding:0'> 
                <a class='btn btn-primary btn-block' style='color:#fff' href="?page=users/profile_fields&action=addnewfield"><i class="fa fa-plus"></i> Add new field</a>
            </div>
            <br><br>
            <hr>

            <h4>Drag fields to reorder them.</h4>
            <table id="blocktable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Field Name</th>
                        <th>Field type</th>
                        <th>Show during registration ?</th>
                        <th>Mandatory field ?</th>
                        <th>Enabled ?</th>                            
                        <th>Configure</th>

                    </tr>
                </thead>
                <tbody id="selections">

                    {section name=fld loop=$fields}
                        <tr id="field_{$fields[fld].id}" data-id="{$fields[fld].weight}">
                            <td>{$fields[fld].name}</td>
                            <td>{$fields[fld].type}</td>
                            <td>{if $fields[fld].show_reg eq 1}yes{else}no{/if}</td>
                            <td>{if $fields[fld].is_mandatory eq 1}yes{else}no{/if}</td>
                            <td>                                
                                <input 
                                    class="simple form-control enabled_field"
                                    data-permission='yes'
                                    {if $fields[fld].enabled eq 1}checked="checked" {/if}
                                    type="checkbox"  data-toggle="toggle"
                                    data-on="yes" data-off="no" data-size="mini"
                                    data-onstyle="success" data-offstyle="danger">

                            </td>

                            <td>
                                <span class="">                                                             
                                    <a class='btn btn-info btn-flat btn-sm' href="index.php?page=users/profile_fields&action=editfield&id={$fields[fld].id}"><i style="color:#fff" class="fa fa-edit"></i> Edit</a>                                                            
                                    &nbsp;&nbsp; <a class='btn btn-danger btn-flat btn-sm' href="javascript:void(0)" onclick="javascript:delete_field({$fields[fld].id})"><i style="color:#fff" class="fa fa-trash-o"></i> Delete</a>
                                </span>
                            </td>
                        </tr>
                    {/section}
                </tbody>
            </table>
        </div><!-- /.box-body -->

        <div class="box-footer">
            <div id="order_saved" style="display: none" class="text-muted">changes have been saved</div>
        </div>

        <div id="delete_field" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Delete Field</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>
                    <form action="index.php?page=users/profile_fields&action=delete_field" method="POST">
                        <div class="modal-body">
                            Are you sure, you want to delete this field ?
                        </div>
                        <div class="modal-footer">
                            <button type="reset" class="btn btn-default" data-dismiss="modal">No</button>
                            <button type="submit" class="btn btn-danger">Yes</button>
                        </div>
                        <input id="delete_id" type="hidden" name="fid"/>
                        <input type="hidden" name="CSRF_token" value="{$token}" />
                    </form>
                </div>
            </div>  
        </div>                     

    </div>

</div>
<script type="text/javascript" src="js/Sortable.min.js"></script>    

<script type="text/javascript">


    function delete_field(fid) {

        $('#delete_id').val(fid);
        $('#delete_field').modal();

    }
    
    token = "{$token}";

    jQuery(document).ready(function ($) {


        sortable_fields = Sortable.create(selections, {
            animation: 150,
            dataIdAttr: 'data-id',
            onUpdate: function (evt) {

                var ids = [];
                $('#selections tr').each(function () {

                    ids.push(this.id.replace("field_", ""));

                });

                $.post('?page=users/profile_fields&action=update_order', {
                    ids: ids,
                    CSRF_token: token
                }, function (data) {

                    $('#order_saved').show().delay(2000).fadeOut('slow');
                });
            }
        });

        setTimeout(function () {
            $('.enabled_field').on('change', function () {

                var enabled = this.checked;
                var id = $(this).parent().parent().parent().attr('id');

                $.post('?page=users/profile_fields&action=updatevisibility', {
                    enabled: enabled ? 1 : 0,
                    id: id.replace('field_', ''),
                    CSRF_token: token                    
                }, function (data) {

                    $('#order_saved').show().delay(2000).fadeOut('slow');
                });

            });

        }, 100);
    });
</script>