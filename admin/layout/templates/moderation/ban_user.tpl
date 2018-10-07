<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><i class="fa"></i> Moderation</li>            
        <li class="breadcrumb-item active"><i class="fa fa-ban"></i> Ban user</li>    </ol>
</section>

<style type="text/css">
    table .btn{
        padding: 1px 5px 1px;
        font-size: 11px;
        margin-right: -3px;
    }


    .one_line {

        margin-bottom: 30px;
    }


    .one_line > input, .one_line > select {

        display: inline-block;
        width: 40%;
        margin: 0px 4px;
    }
</style>

{if $msg eq ""}
{else}
    <div class="alert alert-info alert-dismissable">
        <i class="fa fa-info"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {$msg}
    </div>
{/if}


<div class="row col-md-12">


    <div id="ban" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Ban user</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <form action="{$self}?page=moderation/ban_user" method="POST">
                    <div class="modal-body">

                        <label>Ban type</label>
                        <select id="ban_type" name="ban_type" class="form-control">
                            <option value="name">Name</option>
                            <option value="mail">Mail</option>
                            <option value="ip">IP address</option>
                        </select>
                        <br/>    

                        <label>Ban UID (Name/Mail/IP)</label>
                        <input type="text" name="ban_uid" id="ban_uid" class="form-control" placeholder="enter username or email or ip address" required/>
                        <br/>

                        <label>Ban Reason</label>
                        <input type="text" name="ban_reason" id="ban_reason" class="form-control"/>                        
                        <br/>

                        <label>Ban length</label>

                        <div class="one_line">
                            <input type="number" name="ban_expires" id="ban_expires" class="col-md-6 form-control" value="24"/>
                            <select name="ban_expires_type" id="ban_expires_type" class="col-md-6 form-control">
                                <option value="hour">hour(s)</option>
                                <option value="day">day(s)</option>
                                <option value="month">month(s)</option>
                                <option value="forever">forever</option>                            
                            </select>
                        </div>

                        <input type="hidden" disabled="disabled" id="ban_id" name="id"/>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                    <input type="hidden" name="CSRF_token" value="{$token}" />
                </form>
            </div>
        </div>  
    </div>

    <form id="remove_ban" style="display: none" action="{$self}?page=moderation/ban_user" method="POST">

        <input type="text" name="remove_ban" />
        <input type="number" name="id" id="ban_remove_id" />
        <input type="hidden" name="CSRF_token" value="{$token}" />

    </form>                

    <p>
        <button class="btn btn-primary" id="add_ban"><i class="fa fa-plus"></i> Add ban record</button>       
    </p>

    <table class="table" style="background: #fff;box-shadow: 1px 1px 1px #ccc">
        <tr>
            <th>UID</th>
            <th>Type</th>
            <th>Banned by</th>
            <th>Banned on</th>
            <th>Ban reason</th>
            <th>Lift Ban on</th>
            <th></th>
        </tr>

        <tbody>

            {foreach from=$bans item=ban}
                <tr>
                    <td id="uid_{$ban.id}">{$ban.uid}</td>
                    <td id="type_{$ban.id}">{$ban.ban_type}</td>
                    <td id="by_{$ban.id}">{$ban.ban_by}</td>
                    <td id="on_{$ban.id}">{$ban.ban_on}</td>
                    <td id="reason_{$ban.id}">{$ban.ban_reason}</td>
                    <td id="_expires_{$ban.id}">{$ban.ban_expires}</td>


                    <td>
                        <div id="edit_{$ban.id}" class="btn btn-default edit">edit</div> &nbsp;|&nbsp; 
                        <div id="remove_{$ban.id}" class="btn btn-danger remove"> remove</div>
                        <span style="display:none" id="expires_{$ban.id}">{$ban.ban_real_expires}</span>
                    </td>

                {/foreach}            
        </tbody>
    </table>
</div>
<script type="text/javascript">

    jQuery(document).ready(function ($) {

        $('#add_ban').click(function () {

            //reset form
            $('#ban_type').val('name');
            $('#ban_uid').val('');
            $('#ban_reason').val('');
            $('#ban_expires').val('24');
            $('#ban_expires_type').val('hour');
            $('#ban').modal();

            setTimeout(function () {
                $('#ban_uid').focus();
            }, 200);
        });

        $('.edit').click(function () {

            var id = $(this).attr('id').replace("edit_", "");
            $('#ban_id').val(id).prop('disabled', false);
            //set form
            $('#ban_type').val($('#type_' + id).html().toLowerCase());
            $('#ban_uid').val($('#uid_' + id).html());
            $('#ban_reason').val($('#reason_' + id).html());
            var expires = $('#expires_' + id).html();
            var _exp = expires.split('#');
            $('#ban_expires').val(_exp[0]);
            $('#ban_expires_type').val(_exp[1]);
            $('#ban').modal();

            setTimeout(function () {
                $('#ban_uid').focus();
            }, 200);
        });

        $('.remove').click(function () {

            $('#ban_remove_id').val($(this).attr('id').replace("remove_", ""));
            $('#remove_ban').submit();
        });

    });
</script>