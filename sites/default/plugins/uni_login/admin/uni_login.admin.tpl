
{if $flash['flash']==true}
    <div class="col-md-8">
            <div class="alert alert-success">
                {$flash['message']}
            </div>
    </div>
{/if}

<style type="text/css">

    legend {

        padding-top: 10px;
    }
</style>
<div class="col-md-6">
    <div>
        <form action="index.php?page=ploader&plugin=uni_login" role="form" method="post" enctype="multipart/form-data">

            <div class="box box-info">
                <fieldset class="box-body">
                    <legend>Google Login</legend>
                    <label>Client ID</label>
                    <input type="text" class="form-control" name="GOOGLE_ID" value="{"GOOGLE_ID"|get_opt}" /><br/>

                    <label>Client secret</label>
                    <input type="text" class="form-control" name="GOOGLE_SECRET" value="{"GOOGLE_SECRET"|get_opt}" /><br/>
                </fieldset>
            </div>

            <div class="box box-info">
                <fieldset class="box-body">

                    <legend>Facebook Login</legend>
                    <label>App ID</label>
                    <input type="text" class="form-control" name="FB_ID" value="{"FB_ID"|get_opt}" /><br/>

                    <label>App Secret</label>
                    <input type="text" class="form-control" name="FB_SECRET" value="{"FB_SECRET"|get_opt}" /><br/>
                </fieldset>
            </div>


            <div class="box box-info">
                <fieldset class="box-body">
                    <legend>Twitter Login</legend>
                    <label>Consumer Key (API Key)</label>
                    <input type="text" class="form-control" name="TW_ID" value="{"TW_ID"|get_opt}" /><br/>

                    <label>Consumer Secret (API Secret)</label>
                    <input type="text" class="form-control" name="TW_SECRET" value="{"TW_SECRET"|get_opt}" /><br/>
                </fieldset>
            </div>

            <div class="box box-info">
                <fieldset class="box-body">
                    <legend>Github Login</legend>
                    <label>Client ID</label>
                    <input type="text" class="form-control" name="GITHUB_ID" value="{"GITHUB_ID"|get_opt}" /><br/>

                    <label>Client secret</label>
                    <input type="text" class="form-control" name="GITHUB_SECRET" value="{"GITHUB_SECRET"|get_opt}" /><br/>
                </fieldset>
            </div>


            <input type="hidden" name="CSRF_token" value="{$token}" />
            <input type="submit" value="Save" class="btn btn-primary"/>
        </form>
        <br/>
        <br/>
    </div>
</div>