<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=users/profile_fields"><i class="fa fa-puzzle-piece"></i> Custom profile fields</a></li>
        <li class="breadcrumb-item active"><i class="fa fa-pencil"></i> Edit field</li>
        
    </ol>


</section>

<div class="row" id="msg_cntnr">
    <div class="col-lg-6">
        {if $msg eq ""}

        {else}    
            <div class="alert alert-info alert-dismissable">
                <i class="fa fa-info"></i>
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {$msg}
            </div>
        {/if}

    </div>
</div>

<style>

    fieldset {

        padding: 10px !important;
    }
</style>
<form class="box-body" action="?page=users/profile_fields&action=newfield{if isset($id)}&id={$id}{/if}" role="form" method="post" enctype="multipart/form-data">

    <div class="col-md-6">
        <div  class="box box-info">

            <fieldset>
                <div class="form-group">
                    <label>Field name [for your reference]</label>
                    <input type="text" class="form-control" name="name" value="{if isset($field.name)}{$field.name}{/if}" required/>
                </div>
                <div class="form-group">
                    <label>Field title/label [shown to the user]</label>
                    <input type="text" class="form-control" name="title" value="{if isset($field.title)}{$field.name}{/if}" placeholder="if your field does not require a title, you may leave it empty"/>
                </div>

                <div class="form-group">
                    <label>Field type</label>
                    <select id="field_type" name="field_type" class="form-control">
                        <option {if $field.type eq "input"}selected{/if}>input</option>
                        <option {if $field.type eq "radio"}selected{/if}>radio</option>
                        <option {if $field.type eq "dropdown"}selected{/if}>dropdown</option>                
                        <option {if $field.type eq "checkbox"}selected{/if}>checkbox</option>
                        <option {if $field.type eq "textarea"}selected{/if}>textarea</option>                
                    </select>
                </div> 

                <div id="input_type" class="form-group for_type_input">
                    <label>input type</label>
                    <select name="input_type" class="form-control">
                        <option {if $field.input_type eq "text"}selected{/if}>text</option>
                        <option {if $field.input_type eq "password"}selected{/if}>password</option>
                        <option {if $field.input_type eq "email"}selected{/if}>email</option>                
                        <option {if $field.input_type eq "url"}selected{/if}>url</option>
                        <option {if $field.input_type eq "tel"}selected{/if}>tel</option>                
                        <option {if $field.input_type eq "number"}selected{/if}>number</option>                
                        <option {if $field.input_type eq "date"}selected{/if}>date</option>                
                        <option {if $field.input_type eq "time"}selected{/if}>time</option>                
                        <option {if $field.input_type eq "datetime"}selected{/if}>datetime</option>                
                        <option {if $field.input_type eq "color"}selected{/if}>color</option>                                    
                    </select>
                </div>

                <div class="form-group for_type_input">
                    <label>Max length of input</label>
                    <input type="number" class="form-control" name="input_length" value="{$field.input_length}" placeholder="0 means no limit"/>
                </div>                
                <div class="form-group for_type_others" style="display: none">
                    <label>Options</label>
                    <textarea name="options_data" class="form-control" >{$field.data}</textarea>
                    <span class="text-muted">options on each line.<br/>
                        for eg.<br/> 
                        Yes<br/>
                        No<br/>

                        text may include html                        
                    </span>
                </div>

                <div class="form-group">
                    <label>If the field is not yet set for any user</label>
                    <br/>
                    <input 
                        class="simple form-control" name="hide_not_set" 
                        data-permission='yes'
                        {if $field.hide_not_set eq 1}checked="checked"{/if} 
                        type="checkbox"  data-toggle="toggle"
                        data-on="just hide it" data-off="still show it" data-size="mini"
                        data-onstyle="success" data-offstyle="danger">

                </div>


                <div id="def_value" class="form-group">
                    <label>With the default value</label>
                    <input type="text" class="form-control" name="def_value" value="{$field.def_value}"/>
                </div>                

            </fieldset>

        </div>

    </div>
    <div class="col-md-6">
        <div  class="box box-info">
            <fieldset>
                <div class="form-group">
                    <label>Show during registration ?</label>
                    <br/>
                    <input 
                        class="simple form-control" name="show_reg" 
                        data-permission='yes'
                        {if $field.show_reg eq 1}checked="checked"{/if} 
                        type="checkbox"  data-toggle="toggle"
                        data-on="yes" data-off="no" data-size="mini"
                        data-onstyle="success" data-offstyle="danger">
                </div>
                <div class="form-group">
                    <label>Show in profile and profile edit page ?</label>
                    <br/>
                    <input 
                        class="simple form-control" name="show_profile" 
                        data-permission='yes'
                        {if $field.show_profile eq 1}checked="checked"{/if} 
                        type="checkbox"  data-toggle="toggle"
                        data-on="yes" data-off="no" data-size="mini"
                        data-onstyle="success" data-offstyle="danger">
                </div>

                <div class="form-group">
                    <label>Is this field mandatory during registration ?</label>
                    <br/>
                    <input 
                        class="simple form-control" name="mandatory" 
                        data-permission='yes'
                        {if $field.is_mandatory eq 1}checked="checked"{/if} 
                        type="checkbox"  data-toggle="toggle"
                        data-on="yes" data-off="no" data-size="mini"
                        data-onstyle="success" data-offstyle="danger">
                </div>

                <div class="form-group">

                    <label>Hidden to following user groups</label>
                    <select multiple class="form-control" name="roles[]">

                        {foreach from=$roles item=role}
                            <option {if isset($role.fid) }selected="selected"{/if} value="{$role.rid}">{$role.rname}</option>

                        {/foreach}

                    </select>

                    <span class="text-muted">Hold ctrl to multi select or delselect roles</span>
                </div>        

                <div class="form-group">

                    <label>Field output format</label>
                    <textarea class="form-control" name="format">{if isset($field.output_format)}{$field.output_format}{else}<b>field.title</b> field.value{/if}</textarea>
                </div>        
            </fieldset>
        </div>

    </div>


    <div class="col-md-12">
        <div  class="box box-info">

            <fieldset>
                <input type="hidden" name="CSRF_token" value="{$token}" />
                <input type="submit" value="Save" class="btn btn-primary"/>
            </fieldset>
        </div>

    </div>
</form>


<script type="text/javascript">


    jQuery(document).ready(function ($) {

        if ($('#field_type').val() === 'input') {


            $('.for_type_input').show();
            $('.for_type_others').hide();

        } else if ($('#field_type').val() === 'textarea') {

            $('.for_type_input').show();
            $('.for_type_others').hide();
            $('#input_type').hide();
        } else {

            $('.for_type_input').hide();
            $('.for_type_others').show();
        }
        
        $('input[name=hide_not_set]').on('change', function() {
        
    
            if(!this.checked) {
            
                $('#def_value').show();
            }else {
            
                $('#def_value').hide();
            }
        });
        
        $('#field_type').on('change', function () {


            if ($(this).val() === 'input') {


                $('.for_type_input').show();
                $('.for_type_others').hide();

            } else if ($(this).val() === 'textarea') {

                $('.for_type_input').show();
                $('.for_type_others').hide();
                $('#input_type').hide();
            } else {

                $('.for_type_input').hide();
                $('.for_type_others').show();
            }
        });
    })
</script>
