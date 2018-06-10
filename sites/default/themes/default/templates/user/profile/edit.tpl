{*
/*
* @CODOLICENSE
*/
*}
{* Smarty *}
{extends file='layout.tpl'}


{*

Edit

0. Back to Profile [header]
1. Settings
2. Preferences
3. My subscriptions
4. Notifications

*}
{block name=body}
    <div class="container codo_profile_container" style="padding-top: 60px">

        <div id="profile_edit_status" class="codo_notification" style="display: none"></div>

        <div class="row">

            <!-- Nav tabs -->
            <ul class="nav nav-tabs col-md-12" role="tablist">
                <li class="active">
                    <a href="#edit" role="tab" data-toggle="tab">
                        <i class="glyphicon glyphicon-edit"></i> <span class="hidden-label"> {_t("Edit")}</span>
                    </a>
                </li>
                <li>
                    <a href="#preferences" role="tab" data-toggle="tab">
                        <i class="glyphicon glyphicon-wrench"></i> <span class="hidden-label"> {_t("Preferences")}</span>
                    </a>
                </li>
                <li>
                    <a href="#subscriptions" role="tab" data-toggle="tab">
                        <i class="glyphicon glyphicon-certificate"></i> <span class="hidden-label"> {_t("My subscriptions")}</span>
                    </a>
                </li>
                <li>
                    <a href="#notifications" role="tab" data-toggle="tab">
                        <i class="glyphicon glyphicon-bullhorn"></i> <span class="hidden-label"> {_t("All notifications")}</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="row tab-content">
            <!-- Tab panes -->
            <!--<div class="tab-content col-md-12 col-xs-12">-->
            <div class="tab-pane fade in active" id="edit">

                {"block_profile_edit_before"|load_block}

                <div class="col-md-8 col-sm-12">

                    {"block_profile_edit_details_before"|load_block}

                    <div class="codo_edit_profile">

                        {"block_profile_edit_details_start"|load_block}

                        {if isset($file_upload_error)}

                            <div class="codo_notification codo_notification_error">{$file_upload_error}</div>
                        {/if}

                        {if isset($user_profile_edit) AND $user_profile_edit}
                            <div class="codo_notification codo_notification_success">{_t("user profile edits saved successfully")}</div>
                        {/if}
                        <form action="{$smarty.const.RURI}user/profile/{$user->id}/edit" method="POST" enctype="multipart/form-data" class="form-horizontal" role="form">
                            <div class="form-group">
                                <label for="username" class="col-sm-2 control-label">{_t("username")}</label>
                                <div class="col-sm-8">
                                    <input type="text" name="username" class="codo_input codo_input_disabled" id="username"  value="{$user->username}" disabled="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="display_name" class="col-sm-2 control-label">{_t("display name")}</label>
                                <div class="col-sm-8">
                                    <input type="text" name="name" class="codo_input" id="codo_display_name" placeholder="" value="{$user->name}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="display_name" class="col-sm-2 control-label">{_t("avatar")}</label>
                                <div class="col-sm-8 codo_avatar">

                                    <img class="codo_avatar_img" draggable="false" src="{$user->avatar}" />
                                    <input class="codo_change_avatar" id="codo_avatar_file" type="file" name="avatar" />
                                    <div style="display: none" id="codo_new_avatar_selected_name"></div>
                                    <img class="codo_right_arrow" id="codo_right_arrow" src="{$smarty.const.DEF_THEME_PATH}img/arrow-right.jpg" />
                                    <img class="codo_avatar_preview" src="" id="codo_avatar_preview"/>
                                    <div class="codo_btn codo_btn_def">{_t("Change")}</div>
                                    <div style="text-align: center"><span class="small text-muted">100x100</span></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="display_name" class="col-sm-2 control-label">{_t("signature")}</label>
                                <div class="col-sm-8">
                                    <textarea name="signature" maxlength="{$signature_char_lim}" id="codo_signature_textarea" class="codo_input">{$user->signature}</textarea>
                                </div>
                                <span id="codo_countdown_signature_characters">{$signature_char_lim}</span>
                            </div>

                            {foreach from=$custom_fields item=field}


                                <div class="form-group" id="custom_field_{$field.id}">

                                    {if $field.title}
                                        <label class="col-sm-2 control-label" for="{$field.title}" >{$field.title}</label>
                                    {/if}

                                    <div class="col-sm-8">

                                        {if $field.type eq 'input'}
                                            <input value="{$field.def_val}" class="codo_input" type="{$field.input_type}" name="input_{$field.id}" placeholder="{_t({$field.title})}"
                                                   {if !($field.input_length eq 0)} maxlength="{$field.input_length}"{/if}
                                                   {if $field.is_mandatory}required=""{/if}/>

                                        {else}

                                            {if $field.type eq 'radio'}
                                                {foreach from=$field.data.options item=text}

                                                    <div class="radio">
                                                        <label>
                                                            <input
                                                                {if $field.def_val eq $text}checked="checked"{/if}
                                                                {if $field.is_mandatory}required=""{/if} 
                                                                type="radio" name="input_{$field.id}"/>{_t({$text})}
                                                        </label>
                                                    </div>
                                                {/foreach}

                                            {else if $field.type eq 'dropdown'}
                                                <select class="form-control" {if $field.is_mandatory}required=""{/if} name="input_{$field.id}">

                                                    {foreach from=$field.data.options item=text}

                                                        <option {if $field.def_val eq $text}selected="selected"{/if}>{_t({$text})}</option>
                                                    {/foreach}
                                                </select>
                                            {else if $field.type eq 'checkbox'}
                                                {foreach from=$field.data.options item=text}

                                                    <div class="checkbox">
                                                        <label>
                                                            <input {if $field.def_val eq $text}checked="checked"{/if} 
                                                                                               {if $field.is_mandatory}required=""{/if} type="checkbox" name="input_{$field.id}[]" />{_t({$text})}
                                                        </label>
                                                    </div>
                                                {/foreach}

                                            {else if $field.type eq 'textarea'}

                                                <textarea {if $field.is_mandatory}required=""{/if} name="input_{$field.id}">{$field.def_val}</textarea>
                                            {/if}
                                        {/if}                                    
                                    </div>

                                </div>
                            {/foreach}

                            <div id="codo_before_save_user_profile">
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="codo_btn codo_btn_primary">{_t("Save edits")}</button>
                                </div>
                            </div>

                            <input type="hidden" name="token" value="{$CSRF_token}" />
                        </form>

                        {"block_profile_edit_details_end"|load_block}

                    </div>
                    {"block_profile_edit_details_after"|load_block}

                </div>


                <div class="col-md-4 col-sm-12">
                    <div class="codo_edit_profile">
                        {"block_profile_change_pass_start"|load_block}
                        <form class="form-horizontal" role="form">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <input type="password" name="curr_pass" class="codo_input" id="curr_pass"  placeholder="{_t("Current password")}" required="">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <input type="password" name="new_pass" class="codo_input" id="new_pass"  placeholder="{_t("New password")}" required="">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <input type="password" name="confirm_new_pass" class="codo_input" id="confirm_pass"  placeholder="{_t("Confirm password")}" required="">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button id="change_pass" type="submit" class="codo_btn codo_btn_primary">{_t("Change password")}</button>
                                    <span id="codo_pass_no_match_txt" class="codo_pass_no_match_txt">{_t("passwords do not match!")}</span>
                                </div>
                            </div>
                        </form>
                        {"block_profile_change_pass_end"|load_block}
                    </div>
                </div>

            </div>
            <div class="tab-pane fade" id="preferences">


                <div class="codo_edit_profile">

                    <form class="form-horizontal" id="codo_form_user_preferences">
                        <fieldset> 
                            <legend>{_t("General")}</legend>
                            <div class="form-group">
                                <label for="frequency" class="col-sm-3 control-label">{_t("Notification frequency")}</label>
                                <div class="col-sm-7">
                                    <select id="codo_notification_frequency" class="form-control">
                                        <option value="immediate" {match_option key='notification_frequency' value='immediate'}>{_t("Immediate")}</option>
                                        <option value="daily" {match_option key='notification_frequency' value='daily'}>{_t("Daily digest")}</option>
                                        <option value="weekly" {match_option key='notification_frequency' value='weekly'}>{_t("Weekly digest")}</option>                                    
                                    </select>
                                </div> 
                            </div>
                            {*
                            <div class="form-group">
                            <label class="control-label col-sm-3">{_t("Send emails when i am online")}</label>
                            <div class="col-sm-7">
                            <div id="codo_send_emails_when_online" class="codo_switch {match_switch key='send_emails_when_online' value='yes'}" style="margin-top: 6px">
                            <div class="codo_switch_toggle"></div>
                            <span class="codo_switch_on">{_t('Yes')}</span>
                            <span class="codo_switch_off">{_t('No')}</span>
                            </div>
                            </div>
                            </div>*}
                            <div class="form-group">
                                <label class="control-label col-sm-3">{_t("Show real-time notifications")}</label>
                                <div class="col-sm-7">
                                    <div id="real_time_notifications" class="codo_switch {match_switch key='real_time_notifications' value='yes'}" style="margin-top: 6px">
                                        <div class="codo_switch_toggle"></div>
                                        <span class="codo_switch_on">{_t('Yes')}</span>
                                        <span class="codo_switch_off">{_t('No')}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-3">{_t("Show desktop notifications")}</label>
                                <div class="col-sm-7">
                                    <div id="desktop_notifications" class="codo_switch {match_switch key='desktop_notifications' value='yes'}" style="margin-top: 6px">
                                        <div class="codo_switch_toggle"></div>
                                        <span class="codo_switch_on">{_t('Yes')}</span>
                                        <span class="codo_switch_off">{_t('No')}</span>
                                    </div>
                                </div>
                            </div>

                            <legend>{_t("Notification level")}</legend>
                            <div class="form-group">
                                <label class="control-label col-sm-3">{_t("When I create a topic")}</label>
                                <div class="col-sm-7">
                                    {assign id '1'}
                                    {assign my_subscription_type 'notification_type_on_create_topic'|get_preference}
                                    {include file='forum/notification_level.tpl'}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-3">{_t("When I reply a topic")}</label>
                                <div class="col-sm-7">

                                    {assign id '2'}                                    
                                    {assign my_subscription_type 'notification_type_on_reply_topic'|get_preference}                                    
                                    {include file='forum/notification_level.tpl'}
                                </div>
                            </div>

                            <br/><br/><hr>    
                            <div class="form-group">
                                <div class="col-sm-7">
                                    <button id="codo_update_preferences" type="submit" class="codo_btn codo_btn_primary">{_t("Update preferences")}</button>
                                    <span style="display: none" class="codo_load_more_bar_blue_gif"></span>
                                </div>
                            </div>                                
                        </fieldset>
                    </form>
                </div>
            </div>

            <div class="tab-pane fade" id="subscriptions">

                <div class="codo_edit_profile">
                    <fieldset>
                        <legend>{_t("Categories")}</legend>
                        {assign is_category 'yes'}
                        {foreach from=$categories item=cat}

                            <div class="codo_subscription col-sm-12">
                                <div class="col-sm-4">
                                    <div class="codo_subscription_img">
                                        <img draggable="false" src="{$smarty.const.DURI}{$smarty.const.CAT_IMGS}{$cat.cat_img}" />
                                    </div>

                                    <a href="{$smarty.const.RURI}topics/{$cat.cat_alias}">
                                        {$cat.cat_name}
                                    </a>
                                </div>
                                <div class="col-sm-7">
                                    {assign my_subscription_type $cat.type}
                                    {include file='forum/notification_level.tpl'}
                                </div>
                            </div>
                        {/foreach}
                        <div class='col-md-12' style='height: 3em'></div>
                        <legend>{_t("Topics")}</legend>
                        {assign is_category 'no'}

                        {foreach from=$topics item=topic}

                            {assign var="avatar" value="{$smarty.const.DURI}{$smarty.const.PROFILE_IMG_PATH}{$topic.avatar}"}

                            {if $avatar == null}

                                {assign var="avatar" value="{$smarty.const.DURI}{$smarty.const.DEF_AVATAR}"}
                            {/if}

                            <div class="codo_subscription col-sm-12">
                                <div class="col-sm-4">
                                    <div class="codo_subscription_img">
                                        <a href="{$smarty.const.RURI}user/profile/{$topic.id}">
                                            <img draggable="false" src="{$avatar}" />
                                        </a>
                                    </div>

                                    <a href="{$smarty.const.RURI}topic/{$topic.tid}/">{$topic.title}</a>   
                                </div>
                                <div class="col-sm-7">
                                    {assign my_subscription_type $topic.type}
                                    {include file='forum/notification_level.tpl'}
                                </div>
                            </div>

                        {/foreach}

                    </fieldset>
                </div>
            </div>
            <div class="tab-pane fade" id="notifications">

                <div class='codo_edit_profile'>
                    <div id='codo_all_notifications'>

                    </div>
                </div>
            </div>

        </div>
    </div>           


    <script type="text/javascript">

        CODOFVAR = {
            signature_char_limit: '{$signature_char_lim}',
            lim_notifications: 20,
            trans: {
                preferences: {
                    title: "{_t('Preferences')}", text: "{_t('Your preferences have been successfully saved')}"
                },
                subscriptions: {
                    title: "{_t('Subscriptions')}", text: "{_t('Subscription updated successfully')}"
                }
            }
        };

    </script>
{/block}
