<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item active"><i class="fa fa-level-up"></i> Promotions</li>
    </ol>

</section>

<style type='text/css'>

    .well {

        background: #fff;
    }

    table .btn{
        padding: 1px 5px 1px;
        font-size: 12px;
        margin-right: -3px;
    }

</style>

<div class="col-md-12">
    <div class='card card-body bg-white'>
        The user will be promoted or demoted according to the rules mentioned here.
            <br/>
            If a new rule is added, it will only take affect when a user's reputation or post count changes

    </div>
</div>
<div class="col-md-6">
    <div  class="box box-info">


        <form class="box-body" action="?page=reputation/promotions&action=add" role="form" method="post" enctype="multipart/form-data">

            <label>If a user has</label>
            <br/>
            <div class="input-group">
                <input name="reputation" placeholder='Enter required reputation points here' type="number" class="form-control" required>
                <div class="input-group-append">
                    <span class="input-group-text" id="basic-addon2">reputation</span>
                </div>
            </div>
            <br/>
            <select name="type" class='form-control' >
                <option value="0">AND</option>
                <option value="1">OR</option>                
            </select>
            <br/>
            <div class="input-group">
                <input name="posts" placeholder='Enter required no. of posts here' type="text" class="form-control" required>
                <div class="input-group-append">
                    <span class="input-group-text" id="basic-addon2">posts</span>
                </div>
            </div>
            <br/>
            <label>promote/demote to</label>
            <br/>
            <select name="role" class='form-control'>
                {foreach from=$groups item=group}
                    <option value='{$group.rid}'>{$group.rname}</option>
                {/foreach}
            </select>
            <br/>

            <input type="hidden" name="CSRF_token" value="{$token}" />
            <input type="submit" value="Add rule" class="btn btn-primary"/>

        </form>
    </div>
</div>
<div class="col-md-12">
    <div  class="box box-info">

        <table class="table">

            <tr>
                <th>

                </th>
                <th>
                    reputation
                </th>

                <th>
                    type
                </th>

                <th>
                    posts
                </th>

                <th>
                    promote/demote to group
                </th>
                <th>
                    action
                </th>
            </tr>         

            {foreach from=$rules item=rule}
                <tr>

                    <td>
                        If user has
                    </td>

                    <td id="reputation_{$rule.id}">
                        {$rule.reputation}
                    </td>

                    <td>

                        <span id="type_{$rule.id}" style="display:none">{$rule.type}</span>
                        {if $rule.type eq 0}
                            AND
                        {else}
                            OR
                        {/if} 
                    </td>

                    <td id="posts_{$rule.id}">
                        {$rule.posts}
                    </td>

                    <td>
                        <span id="group_{$rule.id}" style="display:none">{$rule.rid}</span>                            
                        {$rule.rname}
                    </td>

                    <td>
                        <div style="display: inline-block" id="edit_{$rule.id}" class="btn btn-success edit">edit</div> &nbsp;&nbsp; 
                        <div style="display: inline-block">
                            <form action="?page=reputation/promotions&action=delete" method="POST">
                                <input type="hidden" name="CSRF_token" value="{$token}" />
                                <input type="hidden" value="{$rule.id}" name="ruleid" />
                                <button type="submit" id="delete_{$rule.id}" class="btn btn-danger delete"> delete</button>
                            </form>
                        </div>
                    </td>
                </tr>

            {/foreach}

        </table>
    </div>

    <div id="edit_rule" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Editing rule</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <form action="?page=reputation/promotions&action=edit" method="POST">
                    <div class="modal-body">

                        <label>If a user has</label>
                        <br/>
                        <div class="input-group">
                            <input id="modal_rep" name="reputation" placeholder='Enter required reputation points here' type="number" class="form-control" required>
                            <span class="input-group-addon" id="basic-addon2">reputation</span>
                        </div>
                        <br/>
                        <select id="modal_type" name="type" class='form-control' >
                            <option value="0">AND</option>
                            <option value="1">OR</option>                
                        </select>
                        <br/>
                        <div class="input-group">
                            <input id="modal_posts" name="posts" placeholder='Enter required no. of posts here' type="text" class="form-control" required>
                            <span class="input-group-addon" id="basic-addon2">posts</span>
                        </div>
                        <br/>
                        <label>promote/demote to</label>
                        <br/>
                        <select id="modal_group" name="role" class='form-control'>
                            {foreach from=$groups item=group}
                                <option value='{$group.rid}'>{$group.rname}</option>
                            {/foreach}
                        </select>
                        <br/>

                        <input type="hidden" name="CSRF_token" value="{$token}" />
                        <input type="hidden" id="modal_ruleid" name="ruleid" value="" />
                        <div class="modal-footer">
                            <button type="reset" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                </form>
            </div>
        </div>    </div>

</div>

<script type="text/javascript">


    jQuery(document).ready(function ($) {

        $('.edit').click(function () {


            var id = $(this).attr('id').replace("edit_", "");

            var rep = parseInt($('#reputation_' + id).html());
            var type = $('#type_' + id).html();
            var posts = parseInt($('#posts_' + id).html());
            var rid = $('#group_' + id).html();

            $('#modal_rep').val(rep);
            $('#modal_type option[value=' + type + ']').prop('selected', true);
            $('#modal_posts').val(posts);
            $('#modal_group option[value=' + rid + ']').prop('selected', true);
            $('#modal_ruleid').val(id);

            $('#edit_rule').modal();

        });


        $('.delete').click(function () {


            $.get('{$home}Ajax/cron/run/' + name, {
                token: '{$token}'
            }, function (data) {

                if (data != '') {

                    alert(data);
                }
            });

        })



    });
</script>