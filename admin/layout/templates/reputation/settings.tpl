<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active"><i class="fa fa-wrench"></i> Settings</li>
    </ol>

</section>
<div class="col-md-6">
    <div  class="box box-info">
        <form class="box-body" action="?page=reputation/settings" role="form" method="post" enctype="multipart/form-data">

            <div class="form-group">
                <label>Enable reputation system ?</label>
                <br/>
                <input 
                    class="simple form-control" name="enable_reputation" 
                    data-permission='yes'
                    {if {"enable_reputation"|get_opt} eq 'yes'} checked="checked" {/if}
                    type="checkbox"  data-toggle="toggle"
                    data-on="yes" data-off="no" data-size="small"
                    data-onstyle="success" data-offstyle="danger">
            </div>

            <div class="form-group">
                <label>Maximum times a user can give/take reputation in one day</label>
                <br/>
                <input type="text" class="form-control" name="max_rep_per_day" value="{"max_rep_per_day"|get_opt}" />
            </div>

            <div class="form-group">
                <label>Maximum times reputation can be incremented/decremented of the same user</label>
                <br/>
                <input type="text" class="form-control" name="rep_times_same_user" value="{"rep_times_same_user"|get_opt}" />
            </div>

            <div class="form-group">
                <label>Time in hours after which the reputation counts will be reset for above rule </label>
                <br/>
                <input type="text" class="form-control" name="rep_hours_same_user" value="{"rep_hours_same_user"|get_opt}" />
            </div>

                    
            <div class="form-group">
                <label>Reputation required to increment reputation points of a post </label>
                <br/>
                <input type="text" class="form-control" name="rep_req_to_inc" value="{"rep_req_to_inc"|get_opt}" />
            </div>

            <div class="form-group">
                <label>Number of posts required to increment reputation points of a post </label>
                <br/>
                <input type="text" class="form-control" name="posts_req_to_inc" value="{"posts_req_to_inc"|get_opt}" />
            </div>

            <div class="form-group">
                <label>Reputation required to decrement reputation points of a post </label>
                <br/>
                <input type="text" class="form-control" name="rep_req_to_dec" value="{"rep_req_to_dec"|get_opt}" />
            </div>

            <div class="form-group">
                <label>Number of posts required to decrement reputation points of a post </label>
                <br/>
                <input type="text" class="form-control" name="posts_req_to_dec" value="{"posts_req_to_dec"|get_opt}" />
            </div>
                    
            <input type="hidden" name="CSRF_token" value="{$token}" />
            <input type="submit" value="Save" class="btn btn-primary"/>

        </form>
    </div>
</div>