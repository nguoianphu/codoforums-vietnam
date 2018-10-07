<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=ui/blocks"><i class="fa fa-cubes"></i> Blocks</a></li>
        <li class="breadcrumb-item active"><i class="fa fa-edit"></i> Edit Block</li>

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
<form class="box-body" action="?page=ui/blocks&action=add" role="form" method="post" enctype="multipart/form-data">


    <div class="row" id="add_cat" style="">
        <div class="col-lg-6">
            <div class="box box-info">
                <div class="box-body">
                    <input type="hidden" value="{$mode|default:'add'}" name="mode"/>
                    <input type="hidden" value="{$bid|default:'0'}" name="bid"/>

                    <div class="form-group">
                        <label>Block Name:</label>

                        <input type="text" name="blk_name"  value="{$current_block.title|default:''}" class="form-control" placeholder="Block name" required />
                    </div>

                    <div class="form-group">
                        <label>Block Region:</label>
                        <select name="region" size="1" class='form-control'>
                            <option value="<none>">&lt;none&gt;</option>
                            {html_options values=$av_blocks output=$av_blocks selected=$selected_region}
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Block Output:</label>
                        <select class="form-control" onchange="codo_change_disp()" name="block_type" id="block_type">
                            <option value="html" {$h_selected|default:''}>HTML</option>
                            <option value="plugin" {$p_selected|default:''}>Plugin</option>
                        </select>
                    </div>


                    <div class="form-group" id="plugin_name">
                        <select class="form-control"   name="plugin_name">

                            {html_options options=$plugins selected=$selected_plugin}

                        </select>
                    </div>

                    <div class="form-group"  id="block_html" >  
                        <textarea rows="5" id="block_html_tarea" name="block_html" placeholder="<!-- HTML CODE -->" class="form-control" >{$current_block.content|default:''|escape:'html'}</textarea>


                        <div id="editor" style=" position: relative;height: 300px;">{$current_block.content|default:''|escape:'html'}</div>

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
                            <li class="active"><a class="nav-link" href="#home" role="tab" data-toggle="tab">Pages</a></li>
                            <li><a class="nav-link" href="#profile" role="tab" data-toggle="tab">Roles</a></li>

                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="home">

                                <div class="form-group">
                                    <select class="form-control" onchange="codo_change_disp()" name="block_page_visi_type" id="block_type">
                                        <option value="0" {$a_selected|default:''}>All pages except those listed</option>
                                        <option value="1" {$o_selected|default:''}>Only the listed pages</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <textarea name="pages" id="pages" placeholder="" class="form-control" >{$current_block.pages|default:''}</textarea>
                                </div>
                                <div class="callout callout-info">
                                    Specify pages by using their paths. Enter one path per line.<br> 
                                    The '*' character is a wildcard.<br> 
                                    Example paths are:<br><code>user</code> for the user page and <code>user/*</code> for all user pages.
                                </div>


                            </div>
                            <div class="tab-pane fade" id="profile">
                                <div class="form-group">
                                    <label>Show block for specific roles</label>
                                </div>
                                {foreach from=$roles item=role}
                                    <div class="form-group">
                                        <input type="checkbox" name="roles[]" value="{$role.rid}" class="form-control" {$role.checked|default:''} /> {$role.rname}
                                    </div>
                                {/foreach}

                                <div class="callout callout-info">
                                    Show this block only for the selected role(s).<br>
                                    If you do not select any role, the block will be visible to all users.
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
        </script>
        <script type="text/javascript">

            jQuery(document).ready(function ($) {

                codo_change_disp();

            });

            $('#inlineRadio1').change(function () {

                alert('adi');
            });

            function codo_change_disp() {

                var val = $('#block_type').val();
                if (val == 'html') {
                    $('#block_html').show();
                    $('#plugin_name').hide();

                } else {
                    $('#block_html').hide();
                    $('#plugin_name').show();
                }

            }
        </script>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="box">
                <div class="box-body">
                    <input type="hidden" name="CSRF_token" value="{$token}" />

                    <input type="submit" value="Save" class="btn btn-success" />
                    <a href="index.php?page=ui/blocks" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
</form>


<br/> 
