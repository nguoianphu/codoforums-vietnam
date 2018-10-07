<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=users/manage"><i class="fa fa-users"></i> Users</a></li>
        <li class="breadcrumb-item active"><i class="fa fa-edit"></i> Edit User</li>
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


<div class="row" id="add_cat" style="">
    <div class="col-lg-6">
        <div class="box box-info">
            <form class='box-body' action="?page=users/manage&action=edit&user_id={$user.id}" role="form" method="post" enctype="multipart/form-data">
                <input type="hidden" value="edit" name="mode"/>

                <input type="hidden" value="{$user.id}" name="id"/>
                Username:<br>
                <input type="text" name="user_name"  value="{$user.username}" class="form-control" placeholder="" required />
                <br/>

                Display name:<br>
                <input type="text" name="display_name"  value="{$user.name}" class="form-control" placeholder="" />
                <br/>

                Email:<br>
                <input type="text" name="email"  value="{$user.mail}" class="form-control" placeholder="" required />
                <br/>                

             
                <style>
                    .role_selector label{
                        font-weight: normal;

                    }

                    .role_selector .icheckbox_minimal {
                        margin-right: 4px !important;
                    }

                </style>
                
                
                       Primary Role:
                <br>
                <select name="primary_role" class="form-control" id="primary_role">
                    {html_options options=$role_options selected=$prole_selected}


                </select>
                    <br>
                       Roles: <br>
                <div class='role_selector'>

                    {html_checkboxes name="roles" name='roles' options=$role_options selected=$role_selected separator='<br />'}

                </div>
                <br>

         
                <br>


                Password (type a pass only if you want to change it):<br>
                <input type="password" name="p1"  value="" class="form-control" placeholder=""  />
                <br/>
                Password Again: (type the same as above)<br>
                <input type="password" name="p2"  value="" class="form-control" placeholder=""  />
                <br/>                




                Profile Image(Upload a new one to change it):<br/>
                <img width="100" draggable="false" src="{$user.avatar}" />
                <br>
                <input type="file" name="user_img" class="form-control"   />
                <br/>
                Signature:<br>
                <textarea name="signature" placeholder="Forum signature" class="form-control" >{$user.signature}</textarea>
                <br/>

                {assign "checked" "checked"}

                {if $user.user_status eq 0}
                    {assign "checked" ""}                    
                {/if}


                <input name="user_status" type="checkbox" {$checked} />
                Confirmed<br/><br/>
                <input type="submit" value="Save" class="btn btn-success" />
                <a href="index.php?page=users/manage" class="btn btn-default">Back</a>

                <input type="hidden" value="{$token}" name="CSRF_token" />
            </form>
        </div>
    </div>

</div>

<script>

/*

function sync_primary_roles(){
        var chkboxes = $('input[name="roles[]"]:not(:checked)');


        $("#primary_role option").each(function () {

            this.disabled = false;
            for (i = 0; i < chkboxes.length; i++) {


                if ($(chkboxes[i]).val() === this.value) {
                    this.disabled = true;
                }

            }


            
        });
    }

    $('input:checkbox').on('ifChecked', function (event) {


        sync_primary_roles();

    });
    
    $(document).ready(function(){
        
         sync_primary_roles();
        
    });

*/


</script>
