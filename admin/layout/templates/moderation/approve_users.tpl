<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><i class="fa"></i> Moderation</li>            
        <li class="active"><i class="fa fa-check"></i> Approve users</li>    </ol>
</section>


<div class="row" id="add_cat">
    <div class="col-lg-8"> 
        <div class="box box-info ">

            <form class="box-body" action="index.php?page=moderation/approve_users" role="form" id="add_user_form" method="post">


                <label>
                    Pending registrations that require your approval
                </label>

                {{if empty($users)}}

                <br/><br/>
                <p>
                    No users awaiting approval.
                </p>
                {{else}}
                <br/><br/>
                <table class='table table-bordered'>

                    <tr>
                        <th>{*<input type='checkbox' id="check_all"/>*}</th>
                        <th>username</th>
                        <th>registration time </th>
                        <th>email id</th>
                        <th>email confirmed ?</th>                        
                    </tr>
                    {foreach from=$users item=user}
                        <tr>
                            <td><input name='ids[]' value="{$user.id}" type='checkbox'/></td>
                            <td>{$user.username}</td>
                            <td>{$user.created}</td>
                            <td>{$user.mail}</td>
                            <td>{$user.confirmed}</td>
                        </tr>
                    {/foreach}
                </table>

                <br/>
                <i id='select_to_act'>Select user(s) to approve/reject</i>

                <div id='when_checked'>

                    With <b id='num_checked'></b> selected
                    <select name="action" class='form-control col-ld-3'>
                        <option value="approve">Approve</option>
                        <option value="delete">Delete</option>
                    </select><br/>
                    <input type="hidden" name="CSRF_token" value="{$token}" />

                    <input class="btn btn-success" type='submit' />
                </div>

                {{/if}}
            </form>
        </div>

    </div>
</div>

<script type='text/javascript'>

    jQuery('document').ready(function ($) {

        $('#when_checked').hide();
        $('#select_to_act').show();

        $('#check_all').on('ifChecked', function () {
            $('#add_cat input').iCheck('check');
        }).on('ifUnchecked', function () {
            $('#add_cat input').iCheck('uncheck');
        });

        $('#add_cat input').on('ifToggled', function () {

            var checked = $('#add_cat input[type=checkbox]:checked').length; //recompute and waste resources :P

            if ($('#check_all').is(':checked'))
                checked--;

            if (checked) {

                $('#num_checked').html(checked + "");
                $('#when_checked').show();
                $('#select_to_act').hide();
            } else {

                $('#when_checked').hide();
                $('#select_to_act').show();
            }
        });
    });
</script>