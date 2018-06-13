{*
/*
* @CODOLICENSE
*/
*}
{* Smarty *}
{extends file='layout.tpl'}

{block name=body}

    <!--{"block_breadcrumbs_before"|load_block}
    <div id="breadcrumb" class="col-md-12">
    {"block_breadcrumbs_before"|load_block}

    <div class="codo_breadcrumb_list btn-breadcrumb hidden-xs">
        <a href="{$smarty.const.RURI}{$site_url}"><div><i class="glyphicon glyphicon-home"></i></div></a>

    {foreach from=$parents item=crumb}
        <a title="{$crumb.name}" data-toggle="tooltip" href="{$smarty.const.RURI}category/{$crumb.alias}"><div>{$crumb.name}</div></a>                    
    {/foreach}
</div>

<select id="codo_breadcrumb_select" class="form-control hidden-sm hidden-md hidden-lg">
<option selected="selected" value="">{_t("Where am I ?")}</option>
    {assign space "&nbsp;&nbsp;&nbsp;"}
    {assign indent "{$space}"}

    <option value="{$smarty.const.RURI}{$site_url}">{$indent}{$home_title}</option>

    {foreach from=$parents item=crumb}
        {assign indent "{$indent}{$space}"}
        <option value="{$smarty.const.RURI}category/{$crumb.alias}">{$indent}{$crumb.name}</option>                   
    {/foreach}

</select>    
    {"block_breadcrumbs_after"|load_block}                
</div>

    {"block_breadcrumbs_after"|load_block}-->



    <div class="container-fluid top-custom-container-profile">
        <div class="container" style="padding-top:50px;">
            <div class="row">
                <div class="col-md-9">
                    {"block_breadcrumbs_before"|load_block}
                    <div id="breadcrumb" class="col-md-12">
                        {"block_breadcrumbs_before"|load_block}

                        <div class="codo_breadcrumb_list btn-breadcrumb hidden-xs">
                            <a href="{$smarty.const.RURI}{$site_url}"><div>{_t("Home")}</div></a>

                            {foreach from=$parents item=crumb}
                                <a title="{$crumb.name}" data-toggle="tooltip" href="{$smarty.const.RURI}category/{$crumb.alias}"><div>{$crumb.name}</div></a>                    
                                    {/foreach}
                        </div>

                        <select id="codo_breadcrumb_select" class="form-control hidden-sm hidden-md hidden-lg">
                            <option selected="selected" value="">{_t("Where am I ?")}</option>
                            {assign space "&nbsp;&nbsp;&nbsp;"}
                            {assign indent "{$space}"}

                            <option value="{$smarty.const.RURI}{$site_url}">{$indent}{$home_title}</option>

                            {foreach from=$parents item=crumb}
                                {assign indent "{$indent}{$space}"}
                                <option value="{$smarty.const.RURI}category/{$crumb.alias}">{$indent}{$crumb.name}</option>                   
                            {/foreach}

                        </select>    
                        {"block_breadcrumbs_after"|load_block}                
                    </div>

                    {"block_breadcrumbs_after"|load_block}

                    <div class="row codo_cat_top_title_area">
                        <div class="col-md-2">
                            <img src='{$smarty.const.DURI}{$smarty.const.CAT_IMGS}{$cat_info.cat_img}' style="width:75px;border-radius:50%;"/>
                        </div>
                        <div class="col-md-8 codo_cat_top_title_box">
                            <div class="codo_cat_top_title">{$cat_info.cat_name}</div>
                            <p>{$cat_info.cat_description}</p>
                            {if isset($new_topics) && count($new_topics)}
                                <div id='mark_all_read' class="mark_unread">
                                    <div>
                                        {_t("Mark all as read")}
                                    </div>
                                    <div class="codo_mark_unread_checkbox">
                                        <input type="checkbox" name="group0" id="codo_sidebar_title_switch" class="chk-box codo_switch codo_switch_off">
                                        <label for="codo_sidebar_title_switch"></label>
                                    </div>
                                </div>                                   
                            {/if}


                        </div>

                    </div>

                </div>
            </div>		
        </div>
    </div>

    <div class="container" id="codo_category_topics" style="padding:0px;">
        <div class="row">


            {*
            {"block_category_create_topic_before"|load_block}
            {if $can_create_topic}
            <div class="codo_topics col-md-9 col-xs-12 clearfix">
            <div id="codo_topics_create" class="codo_topics_create">

            <div class="codo_widget">
            <div class="codo_widget-header codo_topics_on_focus_show" id="codo_create_new_topic">
            {_t("Create Topic")}
            </div>

            <div class="codo_widget-content">
            <form id="codo_new_topic_form" method="POST" class="" role="form">

            <div class="form-group codo_topics_on_focus_show">
            <div>
            <input id="codo_topic_title" type="text" class="codo_input" placeholder="{_t("Give a title for your topic")}" required>
            </div>
            </div>

            {if $can_add_tags}   

            <div class="form-group codo_tags codo_topics_on_focus_show">

            <div>
            <input id="codo_tags" data-role="tagsinput" type="text" placeholder="tags" />                               
            </div>

            </div> 
            {/if}
            <div class="form-group no-margin-bottom">
            <div id="codo_topic_desc_div" class="form-control">{_t("Create new topic")}</div>


            <div id="codo_new_reply" class="codo_new_reply">

            <!--<div class="codo_reply_resize_handle"></div>-->

            <div class="codo_reply_box" id="codo_reply_box">
            <textarea placeholder="{_t('Describe your topic . You can use BBcode or Markdown')}" id="codo_new_reply_textarea" name="input_text"></textarea>
            <div class="codo_new_reply_preview" id="codo_new_reply_preview_container">
            <div class="codo_editor_preview_placeholder">{_t("live preview")}</div>
            <div id="codo_new_reply_preview"></div>
            </div>
            <div class="codo_reply_min_chars">{_t("enter atleast ")}<span id="codo_reply_min_chars_left">{$reply_min_chars}</span>{_t(" characters")}</div>
            </div>

            <div id="codo_non_mentionable" class="codo_non_mentionable"><b>{_t("WARNING:")} </b>{_t("You mentioned %MENTIONS%, but they cannot see this message and will not be notified")} 
            </div>

            <div class="codo_new_reply_action">
            <button class="codo_btn codo_new_reply_action_post" id="codo_new_topic_btn"><i class="icon-check"></i><span class="codo_action_button_txt">{_t("Post")}</span></button>
            <button class="codo_btn codo_btn_def codo_create_topic_cancel" id="codo_cancel_topic_btn"><i class="icon-times"></i><span class="codo_action_button_txt">{_t("Cancel")}</span></button>

            <img id="codo_new_reply_loading" src="{$smarty.const.CURR_THEME}img/ajax-loader.gif" />
            <button class="codo_btn codo_btn_def codo_post_preview_bg" id="codo_post_preview_btn">&nbsp;</button>
            </div>
            <input type="text" class="end-of-line" name="end_of_line" />

            </div>


            {if $can_make_sticky || $can_close_topics}

            <hr class="perm_sticky_auto_close"/>
            {/if}
            {if $can_make_sticky}
            <div class="form-group perm_sticky_auto_close" id="alter_topic_status" style="display: none">
            <label for="title">{_t('Topic should be')}</label>
            <div>
            <input name="topic_status" value="stickyfc" {if $radio_topic_status eq 'stickyfc'}checked{/if} type="radio" /> {_t('sticky and show in front page and category page')}
            </div>
            <div>
            <input name="topic_status" value="stickyc" {if $radio_topic_status eq 'stickyc'}checked{/if} type="radio" /> {_t('sticky and show only in category page')}
            </div>
            <div>
            <input name="topic_status" value="notsticky" {if $radio_topic_status eq 'notsticky'}checked{/if} type="radio" /> {_t('not sticky')}      
            </div>

            <div class="muted">{_t('sticky topics are always shown before the normal topics')}</div>
            </div>
            {/if}
            {if $can_close_topics}
            <br class="perm_sticky_auto_close"/>

            <div class="form-group perm_auto_close" style="display: none">
            <label for="title">{_t('Set the topic status as')}</label>

            <div>
            <div id="is_topic_open" class="codo_switch codo_switch_on">
            <div class="codo_switch_toggle"></div>
            <span class="codo_switch_on">{_t('Open')}</span>
            <span class="codo_switch_off">{_t('Close')}</span>
            </div>
            </div>
            <div class="muted">{_t('people cannot reply to closed topics')}.</div>
            </div>

            <div class="form-group perm_auto_close" style="display: none">
            <label for="title">{_t('Should the topic be auto-closed ?')}</label>

            <div>
            <div id="topic_auto_close" class="codo_switch codo_switch_off">
            <div class="codo_switch_toggle"></div>
            <span class="codo_switch_on">{_t('Yes')}</span>
            <span class="codo_switch_off">{_t('No')}</span>
            </div>
            </div>

            <div id="content_toggle_topic_auto_close">     
            <b>{_t('On')}</b>
            <div class="form-group">
            <div class="col-sm-6" style="padding-left: 0">
            <input placeholder="{_("Select a date")}" class="form-control col-md-3" type="text" id="datetimepicker"/>
            </div>
            </div>
            <div class="muted">{_t('topic will automatically closed on mentioned date')}.</div>
            </div>
            </div>

            {/if}

            {if $can_close_topics || $can_make_sticky} 

            <hr class="perm_sticky_auto_close"/>

            <div class="codo_new_reply_action perm_sticky_auto_close">

            <button class="codo_btn codo_new_reply_action_post" id="trigger_codo_new_reply_action_post"><i class="icon-check"></i><span class="codo_action_button_txt">{_t("Post")}</span></button>
            <button class="codo_btn codo_btn_def codo_create_topic_cancel" id="trigger_codo_new_reply_action_cancel"><i class="icon-times"></i><span class="codo_action_button_txt">{_t("Cancel")}</span></button>
            </div>
            {/if}
            </div>

            <div class="codo_topics_on_focus_show">

            <input type="text" class="end-of-line" name="end_of_line" />
            <input id="codo_topic_cat" name="codo_topic_cat" type="hidden" />
            <input id="codo_topic_cat_alias" name="codo_topic_cat_alias" type="hidden" />
            </div>
            </form>
            </div>

            </div>
            </div>
            </div>
            {/if}
            {"block_category_create_topic_after"|load_block}
            *}
            <div class="codo_categories col-md-3" id="codo_categories">

                {"block_category_desc_before"|load_block}
                <div class="codo_categories_container">
                    <!--<a href="{$smarty.const.RURI}category/{$cat_alias}"><div class="codo_cat_title">{$cat_info.cat_name}</div></a>-->

                    <div class="row codo_multi_column">
                        <!--<div class="col-sm-6 col-md-12">
                            <div class="codo_cat_imgs">
                                <div class="codo_cat_img" style="background-image:url('{$smarty.const.DURI}{$smarty.const.CAT_IMGS}{$cat_info.cat_img}')">
                                </div>
                            </div>
                        </div>-->
                        <div class="col-sm-6 col-md-12">
                            <!--{if $can_search}
                                <div class="codo_sidebar_search">
                                    <input type="text" placeholder="{_t('Search')}" class="form-control codo_topics_search_input" />
                                    <i class="glyphicon glyphicon-search codo_topics_search_icon" title="Advanced search" ></i>
                                </div>
                            {/if}-->

                            <!--<div class="codo_cat_desc">{$cat_info.cat_description}</div>-->

                                <div class="codo_cat_info row clearfix">

                                    <div class="codo_cat_num col-xs-4" title="{_t("No. of topics")}">
                                        <i class="icon icon-eye2" style="color:#00b147;font-size:20px;padding-top: 2px;padding-left: 6px;"></i>
                                        <div class="codo-topic-right">
                                            {$no_topics}
                                        </div>

                                    </div>

                                    <div class="codo_cat_num col-xs-4" title="{_t("No. of posts")}">
                                        <i class="icon icon-message" style="color:#0097f6;font-size:20px;padding-top: 2px;"></i>
                                        <div class="codo-topic-right">
                                            {$no_posts}
                                        </div>

                                    </div>

                                    <div class="codo_cat_num col-xs-4" title="{_t("No. of followers")}">
                                        <i class="glyphicon glyphicon-stats" style="font-size:20px;color:#5a7fee;"></i>
                                        <div class="codo-topic-right">
                                            {$no_followers|abbrev_no}
                                        </div>

                                    </div>

                                    <!--<div class="codo_cat_num col-xs-4">
                                        <div>{$no_topics}</div>
                                    {_t("Topics")}
                                </div>

                                <div class="codo_cat_num col-xs-4">
                                    <div>{$no_posts}</div>
                                    {_t("Posts")}
                                </div>
                                <div class="codo_cat_num col-xs-4">
                                    <div>{$no_followers|abbrev_no}</div>
                                    {_t("Followers")}
                                </div>-->

                                </div>
                                {if $logged_in}
                                    {include file='forum/notification_level.tpl'}
                                {/if}
                            </div>
                        </div>

                    </div>

                    {if !empty($sub_cats)}
                        <div class="codo_sub_categories" style="margin-top:15px;">


                            <div class="codo_sub_categories_txt">{_t("Sub Categories")}</div>
                            <ul id="codo_categories_ul">

                                {assign var=total_topics value=0}
                                {foreach from=$sub_cats item=cat}

                                    <li>
                                        <div class="row">

                                            <div class="codo_categories_category">
                                                <a href="{$smarty.const.RURI}category/{$cat->cat_alias}">
                                                    <div class="codo_category_title">{$cat->cat_name}</div>
                                                </a>
                                                <span data-toggle="tooltip" data-placement="bottom" title="{_t('No. of topics')}" class="codo_category_num_topics codo_bs_tooltip">
                                                    {if $cat->granted eq 1}
                                                        {$cat->no_topics|abbrev_no}
                                                    {else} --
                                                    {/if}
                                                </span>
                                                {*

                                                {if isset($new_topics) && isset($new_topics[$cat->cat_id])}                                     
                                                <a title="{_t('new topics')}"><span class="codo_new_topics_count">{$new_topics[$cat->cat_id]|abbrev_no}</span></a>
                                                {/if}
                                                *}
                                            </div>
                                        </div>
                                        {get_children cat=$cat new_topics=$new_topics}
                                    </li>
                                {/foreach}
                            </ul>

                        </div>
                    {/if}

                    {"block_category_desc_after"|load_block}
                    <div class="codo_categories_container">
                        <div class="codo_sidebar_fixed">

                            {if $can_search}
                                <div id="codo_sidebar_fixed_search" class="codo_sidebar_search codo_sidebar_fixed_els">
                                    <input type="text" placeholder="{_t('Search')}" class="form-control codo_topics_search_input" />
                                    <i class="glyphicon glyphicon-search codo_topics_search_icon" title="Advanced search" ></i>
                                </div>
                            {/if}

                            <div class="dropdown codo_sidebar_navigation codo_sidebar_fixed_els" id="codo_category_select">
                                <button value="" class="dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
                                    <span>{_t("All topics")}</span>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">

                                    {foreach from=$cats item=cat}

                                        <li role="presentation"><a data-alias="{$cat->cat_alias}">{$cat->cat_name}</a></li>

                                        {print_children cat=$cat}
                                    {/foreach}
                                </ul>
                            </div>


                            <div class="codo_sidebar_settings">
                                <div class="codo_sub_categories_txt" style="margin:0px;">{_t("Actions")}</div>

                                <!--<div style="border-bottom:1px solid #cccccc;margin:0;height:50px;">
                                        
                                        
                                        
                                        <div class="codo_categories_category col-md-12">
                                                
                                                <div class="codo_category_title" style="margin-top:15px;">{_t("Actions")}</div>
                                                
                                                
                                        </div>
                                </div>-->

                                <div>
                                    <div class="codo_switch">
                                        <!--<div class="codo_switch_toggle"></div>-->
                                        <input type="checkbox" name="group1" id="codo_sidebar_hide_msg_switch" class="chk-box codo_switch codo_switch_off">
                                        <label for="codo_sidebar_hide_msg_switch"></label>
    <!--<span class="codo_switch_on">{_t('Yes')}</span>
    <span class="codo_switch_off">{_t('No')}</span>-->
                                    </div>
                                    <span>{_t("Hide topic messages")}</span>                            
                                </div>

                                <div>                           
                                    <div class="codo_switch">
                                        <!--<div class="codo_switch_toggle"></div>
                                        <span class="codo_switch_on">{_t('Yes')}</span>
                                        <span class="codo_switch_off">{_t('No')}</span>-->
                                        <input type="checkbox" name="group1" id="codo_sidebar_inf_scroll_switch" class="chk-box codo_switch codo_switch_off">
                                        <label for="codo_sidebar_inf_scroll_switch"></label>
                                    </div>
                                    <span>{_t("Enable infinite scrolling")}           
                                </div>

                            </div>    

                        </div>
                    </div>

                    {*if $can_create_topic}

                    <div class="codo_categories_container codo_new_topic">
                    <button id="codo_create_topic_btn" type="submit" class="codo_btn codo_btn_primary" style="width:100%">{_t("Create new topic")}</button>
                    </div>
                    {/if*}

                </div>
                <div style="display:none" id="codo_no_topics_display" class="codo_no_topics">{_t("No posts to display")}</div>

                <div class="codo_topics col-md-9 col-xs-12">

                    <div id="codo_topics_list">
                        {if $cat_info.no_topics > 0}
                            {$topics}
                        {else}
                            <div class="codo_zero_topics">
                                {_t("No topics created yet!")}<br/><br/>
                                {if $logged_in}                            
                                    {_t("Be the first to")} <a href="#" id="codo_zero_topics">{_t("create")}</a> {_t("a topic")}                            
                                {/if}
                            </div>
                        {/if}
                    </div>

                    <span style="display: none">
                        {*Skeleton DIV to clone in jQuery*}
                        <div id="codo_topic_page_info">
                            <span id="codo_page_info_time_spent" data-toggle="tooltip" title="{_t("time spent reading previous page")}"></span>
                            <span id="codo_page_info_page_no" data-toggle="tooltip" title="{_t("page no.")}"></span>
                            <span id="codo_page_info_pages_to_go" data-toggle="tooltip" title="{_t("pages to go")}"></span>
                        </div>
                    </span>
                </div>

            </div>


        </div>

        <div class="codo_topics_loadmore_div row" id="codo_topics_load_more">
            {if true or !$load_more_hidden}

                <div onclick="CODOF.changePage(this, {$curr_page}, 'prev')" class="pagination_previous_page col-md-offset-1 col-md-2 col-sm-12{if $curr_page neq 1} active_page_controls{/if}">

                    <i class="icon icon-arrow-left"></i>
                    <div>{_t("Previous")}</div>
                </div>

                <div class="col-md-4 pagination_pages col-sm-12">
                    {$pagination}
                </div>

                <div  onclick="CODOF.changePage(this, {$curr_page}, 'next')" class="pagination_next_page col-md-2 col-sm-12{if $curr_page neq $total_pages} active_page_controls{/if}">

                    <div>{_t("Next")}</div>
                    <i class="icon icon-arrow-right"></i>
                </div>

                {if $can_create_topic}

                    <div class="pagination_new_topic col-md-2 col-sm-12">
                        <button id="codo_create_topic_btn" type="submit" class="codo_btn codo_btn_primary">{_t("Create new topic")}</button>
                    </div>
                {/if}
                <div class="col-md-offset-1"></div>

            {/if}
        </div>                         
        <div id='codo_delete_topic_confirm_html'>
            <div class='codo_posts_topic_delete'>
                <div class='codo_content'>
                    {_t("All posts under this topic will be ")}<b>{_t("deleted")}</b> ?
                    <br/>

                    <div class="codo_consider_as_spam codo_spam_checkbox">
                        <input id="codo_spam_checkbox" name="spam" type="checkbox" checked="">
                        <label class="codo_spam_checkbox" for="spam">{_t('Mark as spam')}</label>
                    </div>

                </div>
                <div class="codo_modal_footer">
                    <div class="codo_btn codo_btn_def codo_modal_delete_topic_cancel">{_t("Cancel")}</div>
                    <div class="codo_btn codo_btn_primary codo_modal_delete_topic_submit">{_t("Delete")}</div>
                </div>
                <div class="codo_spinner"></div>
            </div>
        </div>


        {include file='forum/editor.tpl'}

        <script type="text/javascript">

            CODOFVAR = {
                cid: '{$cat_info.cat_id}',
                cat_alias: '{$cat_alias}',
                curr_page: parseInt('{$curr_page}'),
                total: {$cat_info.no_topics},
                num_posts_per_page: {$num_posts_per_page},
                smileys: JSON.parse('{$forum_smileys}'),
                reply_min_chars: parseInt({$reply_min_chars}),
                dropzone: {
                    dictDefaultMessage: '{_t("Drop files to upload &nbsp;&nbsp;(or click)")}',
                    max_file_size: parseInt('{$max_file_size}'),
                    allowed_file_mimetypes: '{$allowed_file_mimetypes}',
                    forum_attachments_multiple: {$forum_attachments_multiple},
                    forum_attachments_parallel: parseInt('{$forum_attachments_parallel}'),
                    forum_attachments_max: parseInt('{$forum_attachments_max}')

                },
                trans: {
                    continue_mesg: '{_t("Continue")}'
                },
                login_url: '{$login_url}',
                search_data: '{$search_data}',
                last_page: '{_t("last page")}',
                no_more_posts: '{_t("No more topics to display!")}',
                no_posts: '{_t("No topics found matching your criteria!")}'

            };

        </script>


        <link rel="stylesheet" type="text/css" href="{$smarty.const.DURI}assets/dropzone/css/basic.css" />
        {/block}
