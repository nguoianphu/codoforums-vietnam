<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active"><i class="fa fa-users"></i> Roles</li>
    </ol>

</section>

{if $msg eq ""}
{else}
    <div class="alert alert-{$msgType} alert-dismissable">
        <i class="fa fa-info"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {$msg}
    </div>
{/if}


<div class="row" id="add_cat">
    <div class="col-lg-4"> 
        <div class="box box-info ">

            <form class="box-body" action="index.php?page=permission/roles" role="form" id="add_user_form" method="post">

                <input type="hidden" name="CSRF_token" value="{$token}" />

                <input type="text" name="role_name"  value="" class="form-control" placeholder="Role Name"  required="required"/>
                <br/>

                <p class="help-block">Copy permissions from role: </p>                
                <select name="copy_from_role_id" class="form-control">
                    {foreach from=$roles item=role}
                        <option value="{$role.rid}">{$role.rname}</option>
                    {/foreach}
                </select>
                <br/>

                <input type="submit" value="Add Role" class="btn btn-success" />

            </form>


        </div>

    </div>


</div>


<div class="row">
    <div class="col-lg-6">

        <div class="box box-success">
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Role Name</th>
                                <th style="text-align: center">Actions</th>


                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$roles item=role}
                                <tr>
                                    <td>{$role.rid}</td>
                                    <td>{$role.rname}</td>
                                    <td style="text-align: center">

                                        <a class="btn btn-sm btn-primary" href="index.php?page=permission/role_edit&role_id={$role.rid}" title="Edit Role"><i style="color:#fff" class="fa fa-edit"></i> Edit permissions</a>                                                            

                                        &nbsp;&nbsp; <a class="btn btn-sm btn-danger" href="javascript:void(0)" onclick="delete_role({$role.rid});" title="Delete Role"><i style="color:#fff" class="fa fa-trash-o"></i></a>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="delete_role_form" method="post" action="index.php?page=permission/roles&action=delete">
    <input type="hidden" id="del_role_id" name="del_role_id" value=""/>
    <input type="hidden" name="CSRF_token" value="{$token}" />

</form> 
<script type="text/javascript">

    function delete_role(id) {

        var r = confirm("Are you sure, you want to delete?");
        if (r === true)
        {

            $('#del_role_id').val(id + '');
            $('#delete_role_form').submit();

        }
        else
        {
            return;
        }
    }

</script>