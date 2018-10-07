<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item active"><i class="fa fa-wrench"></i> Global Settings</li>
    </ol>

</section>
<div class="col-md-6">
    <div  class="box box-info">
        <form class="box-body" action="?page=config" role="form" method="post" enctype="multipart/form-data">
            <!--
                Site URL: 
            <input type="text" class="form-control" name="site_url" value="{"site_url"|get_opt}" /><br/>
            -->

            <label>Site Title</label>
            <input type="text" class="form-control" name="site_title" value="{"site_title"|get_opt}" /><br/>

            <label>Site Description</label>
            <input type="text" class="form-control" name="site_description" value="{"site_description"|get_opt}" /><br/>

            <label>Admin Email</label>
            <input type="text" class="form-control" name="admin_email" value="{"admin_email"|get_opt}" /><br/>
            <!--
            Captcha Public Key:
            <input type="text" class="form-control" name="captcha_public_key" value="{"captcha_public_key"|get_opt}" /><br/>
            
            Captcha Private Key:
            <input type="text" class="form-control" name="captcha_private_key" value="{"captcha_private_key"|get_opt}" /><br/>
            -->
            <label>Password Min Length</label>
            <input type="text" class="form-control" name="register_pass_min" value="{"register_pass_min"|get_opt}" /><br/>

            <label>Num of posts(All topics Page)</label>
            <input type="text" class="form-control" name="num_posts_all_topics" value="{"num_posts_all_topics"|get_opt}" /><br/>

            <label>Num of posts(while viewing a category)</label>
            <input type="text" class="form-control" name="num_posts_cat_topics" value="{"num_posts_cat_topics"|get_opt}" /><br/>

            <label>Num of posts(While viewing a topic)</label>
            <input type="text" class="form-control" name="num_posts_per_topic" value="{"num_posts_per_topic"|get_opt}" /><br/>

            <label>Forum attachment path</label>
            <input type="text" class="form-control" name="forum_attachments_path" value="{"forum_attachments_path"|get_opt}" /><br/>

            <label>Allowed Upload types(comma separated)</label>
            <input type="text" class="form-control" name="forum_attachments_exts" value="{"forum_attachments_exts"|get_opt}" /><br/>

            <label>Max Upload size(MB)</label>
            <input type="text" class="form-control" name="forum_attachments_size" value="{"forum_attachments_size"|get_opt}" /><br/>

            <label>Allowed Mimetypes</label>
            <input type="text" class="form-control" name="forum_attachments_mimetypes" value="{"forum_attachments_mimetypes"|get_opt}" /><br/>

            
            <label>Max no. of tags allowed</label>
            <input type="text" class="form-control" name="forum_tags_num" value="{"forum_tags_num"|get_opt}" /><br/>


            <label>Max characters per tag </label>
            <input type="text" class="form-control" name="forum_tags_len" value="{"forum_tags_len"|get_opt}" /><br/>
            
            <!--
            <input type="text" class="form-control" name="forum_attachments_multiple" value="{"forum_attachments_mimetypes"|get_opt}" /><br/>
            
            <input type="text" class="form-control" name="forum_attachments_parallel" value="{"forum_attachments_mimetypes"|get_opt}" /><br/>
            <input type="text" class="form-control" name="forum_attachments_max" value="{"forum_attachments_mimetypes"|get_opt}" /><br/>
            -->
            <label>Min characters for a post</label>
            <input type="text" class="form-control" name="reply_min_chars" value="{"reply_min_chars"|get_opt}" /><br/>

            <label>
                Account registrations require admin approval ?
            </label>
            <br/>
            <input id='reg_req'
                   class="simple form-control" name="reg_req_admin" 
                   {if {"reg_req_admin"|get_opt} eq "yes"} checked="checked" {/if}
                   type="checkbox"  data-toggle="toggle"
                   data-size="small"
                   data-on="yes" data-off="no"
                   data-onstyle="success" data-offstyle="danger">
            <br/><hr/>
            <label>Upload Logo</label>
            <br>
            {"forum_logo"|get_opt}
            <input type="file" class="form-control" name="forum_logo" /><br/>
            <!--
            Captcha:
            <input type="text" class="form-control" name="captcha" value="{"captcha"|get_opt}" /><br/>
            -->
            <input type="hidden" name="CSRF_token" value="{$token}" />
            <input type="submit" value="Save" class="btn btn-primary"/>
        </form>
        <br/>
        <br/>
    </div>
</div>