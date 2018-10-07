<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=pages/pages"><i class="fa fa-file-powerpoint-o"></i> Pages</a></li>
        <li class="breadcrumb-item active"><i class="fa fa-edit"></i> Edit Page</li>

    </ol>

</section>


<div class="row" id="msg_cntnr">
    <div class="col-lg-12">
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
<form class="box-body" action="?page=pages/pages&action=add" role="form" method="post" enctype="multipart/form-data">


    <div class="row" id="add_cat" style="">
        <div class="col-lg-12">
            <div class="box box-info">
                <div class="box-body">
                    <input type="hidden" value="{$mode|default:'add'}" name="mode"/>
                    <input type="hidden" value="{$pid|default:'0'}" name="pid"/>

                    <div class="form-group">
                        <label>Page Title:</label>

                        <input id="page_title" type="text" name="page_title"  value="{$current_page.title|default:''}" class="form-control" placeholder="Page Title" required />
                    </div>


                    <label>Page URL:(only alphabets, numbers and dashes are allowed)</label>



                    <div class="input-group form-group col-md-12" >

                        <span class="input-group-addon no-radius" style="width:110px;text-align: left">{$smarty.const.RURI}page/{$pid}/<span id="page_alias">{$current_page.url|default:''}</span></span>
                        <input id="page_url" type="hidden" name="page_url"  value="{$current_page.url|default:''}" class="form-control no-radius" placeholder="page-url" required="required">

                    </div>



                    <div class="form-group"  id="block_html" > 
                        <label>Page HTML:</label><br>
                        <textarea rows="5" id="block_html_tarea" name="page_html" placeholder="<!-- HTML CODE -->" class="form-control" >{$current_page.content|default:''|escape:'html'}</textarea>


                        <div id="editor" style=" position: relative;height: 300px;">{$current_page.content|default:''|escape:'html'}</div>

                    </div>




                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="box box-info">
                <div class="box-body">
                    Visiblility & Permissions:
                    <br><br>
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs" role="tablist" id="myTab">
                            <li  class="active"><a class="nav-link" href="#profile" role="tab" data-toggle="tab">Roles</a></li>

                        </ul>

                        <div class="tab-content">

                            <div class="tab-pane active" id="profile">
                                <div class="form-group">
                                    <label>Show page for specific roles</label>
                                </div>
                                {foreach from=$roles item=role}
                                    <div class="form-group">
                                        <input type="checkbox" name="roles[]" value="{$role.rid}" class="form-control" {$role.checked|default:''} /> {$role.rname}
                                    </div>
                                {/foreach}

                                <div class="callout callout-info">
                                    Show this page only for the selected role(s).<br>
                                    If you do not select any role, the page will be visible to all users.
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>


        <script src="//cdn.jsdelivr.net/ace/1.1.7/min/ace.js" type="text/javascript" charset="utf-8"></script>
        <script>

            try {
                var editor = ace.edit("editor");
                editor.setTheme("ace/theme/chrome");
                editor.getSession().setMode("ace/mode/html");

                $('#block_html_tarea').hide();
                editor.getSession().on('change', function () {
                    $('#block_html_tarea').val(editor.getSession().getValue());
                });

            }
            catch (e) {

                $('#editor').hide();
                $('#block_html_tarea').show();

            }

            function convertToSlug(Text)
            {
                return Text
                        .toLowerCase()
                        .replace(/[^\w ]+/g, '')
                        .replace(/ +/g, '-')
                        ;
            }
            
            jQuery(document).ready(function($) {
            
    
    
                $('#page_title').on('keyup', function(){
                
    
                    var title = this.value;
                    
                    var slug = convertToSlug(title);
                    
                    $('#page_alias').html(slug);
                    $('#page_url').val(slug);
                    
                })
            })
        </script>

    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="box">
                <div class="box-body">
                    <input type="hidden" name="CSRF_token" value="{$token}" />

                    <input type="submit" value="Save" class="btn btn-success" />
                    <a href="index.php?page=pages/pages" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
</form>


<br/> 
