<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
         <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
         <li><i class="fa fa-laptop"></i> UI Elements</li>
         <li><i class="fa fa-image"></i> Themes</li>
    </ol>
    
</section>

{section name=thm loop=$themes}
<div class="col-md-4">
    <div class="box box-solid box-primary">
        <div class="box-header">
            <h3 class="box-title">{$themes[thm].name}</h3>

        </div>
        <div class="box-body">
            
            <img src="{$themes[thm].thumb}" style="width:250px" />
            <hr>
            <p>
                {$themes[thm].description}
            </p>
        </div><!-- /.box-body -->
        <div class="box-footer">
        {if $themes[thm].active} 
            Currently Active
         {else}   
        <form class="box-body" action="?page=ui/themes" role="form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="theme" value="{$themes[thm].name}" />
            <input type="hidden" name="CSRF_token" value="{$token}" />
            <button class="btn btn-success">Activate</button>
        </form>
           {/if} 
        </div>
    </div>
</div>
{/section}