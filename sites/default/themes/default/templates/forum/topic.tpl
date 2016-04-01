{*
/*
* @CODOLICENSE
*/
*}
{* Smarty *}
{extends file='layout.tpl'}

{block name=body}

    {assign "safe_title" $title|URL_safe}
    {assign "tid" $topic_info.topic_id}
    {assign "cid" $topic_info.cat_id}

    <!-- nguoianphu -->
	<div id="breadcrumb" class="col-md-12">
	
        <div class="codo_breadcrumb_list btn-breadcrumb">
            <a href="{$smarty.const.RURI}{$site_url}"><div><i class="glyphicon glyphicon-home"></i>&nbsp;&nbsp;{_t('All topics')}</div></a>

            {foreach from=$parents item=crumb}
                <a title="{$crumb.name}" data-placement="bottom" data-toggle="tooltip" href="{$smarty.const.RURI}category/{$crumb.alias}"><div>{$crumb.name}</div></a>                    
                    {/foreach}
            &nbsp;
        </div>

		<!-- nguoianphu mobile -->
		<!--
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
		-->         
    </div>
    <!-- nguoianphu -->
    {if $topic_is_spam}
        <div class="codo_spam_alert alert alert-warning"><b>{_t('NOTE: ')}</b>{_t('This topic is marked as spam and is hidden from public view.')}</div>
            {/if}
    <div class="container">

        <div class="row">




            <div class="codo_posts col-md-9">

                {"block_posts_before"|load_block}
                <div class="codo_widget">
                    <div class="codo_widget-header" id="codo_head_title">
                        <div class="row">
                            <div class="codo_topic_title">
								<!-- nguoianphu Don't need a link here -->
                                <!-- <a href="{$smarty.const.RURI}topic/{$tid}/{$safe_title}"> -->
                                    <h1><div class="codo_widget_header_title">{$title|unescape}</div></h1>
                                <!-- </a> -->
                            </div>
                            <div id="codo_topic_title_pagination" class="codo_head_navigation">
                                {$pagination}
                            </div>
                        </div>
                    </div>


                    <div style="display: none" id="codo_no_topics_display" class="codo_no_topics">{_t("No posts to display")}</div>

                    <div id="codo_posts_container" class="codo_widget-content">

                        {$posts}
                        {if $num_pages > 1}
                            <div class="codo_topics_pagination">

                                {$pagination}
                            </div>
                        {/if}

                    </div>
                </div>
            </div>

            <div class="codo_topic col-md-3" id="codo_topic_sidebar">
                {"block_topic_info_before"|load_block}

                <div class="codo_topic_statistics codo_sidebar_fixed_els row">

                    <div class="codo_cat_num col-xs-4">
                        <div class="codo_topic_views" data-number="{$topic_info.no_views}">
                            {$topic_info.no_views|abbrev_no}
                        </div>
                        {_t('views')}
                    </div>
                    <div class="codo_cat_num col-xs-4">
                        <div>
                            {$topic_info.no_replies|abbrev_no}
                        </div>
                        {_t('replies')}
                    </div>
                    <div class="codo_cat_num col-xs-4">
                        <div>
                            {$no_followers|abbrev_no}
                        </div>
                        {_t('followers')}
                    </div>

                </div>

                {if $can_search}    
                    <div class="codo_sidebar_search">
                        <input type="text" placeholder="{_t('Search')}" class="form-control codo_topics_search_input" />
                        <i class="glyphicon glyphicon-search codo_topics_search_icon" title="Advanced search" ></i>
                    </div>
                {/if}


                {if $tags}
                    <div class="codo_statistic_block">
                        <ul class="codo_tags">

                            {foreach from=$tags item=tag}
                                <li ><a href="{$smarty.const.RURI}tags/{$tag}">{$tag}</a></li>
                                {/foreach}
                        </ul>
                    </div>
                {/if}
                {if $logged_in}
                    {include file='forum/notification_level.tpl'}
                {/if}

                <div class="codo_sidebar_fixed">

                    {if $can_search}
                        <div id="codo_sidebar_fixed_search" class="codo_sidebar_search codo_sidebar_fixed_els">
                            <input type="text" placeholder="{_t('Search')}" class="form-control codo_topics_search_input" />
                            <i class="glyphicon glyphicon-search codo_topics_search_icon" title="Advanced search" ></i>
                        </div>
                    {/if}

                </div>

                {if $is_closed}
                    <div class="codo_topic_side_div codo_topic_closed">

                        {_t('This topic is closed')}
                    </div>
                {/if}

				<!-- nguoianphu -->
             <!--   "block_topic_info_after"|load_block -->

            </div>

        </div>
        <div id="codo_new_reply" class="codo_new_reply">

            <div class="codo_reply_resize_handle"></div>
            <form id="codo_new_reply_post" action="/" method="POST">

                <div class="codo_reply_box" id="codo_reply_box">
                    <textarea placeholder="{_t('Start typing here . You can use BBcode or Markdown')}" id="codo_new_reply_textarea" name="input_text"></textarea>
                    <div class="codo_new_reply_preview" id="codo_new_reply_preview_container">
                        <div class="codo_editor_preview_placeholder">{_t("live preview")}</div>
                        <div id="codo_new_reply_preview"></div>
                    </div>
                    <div class="codo_reply_min_chars">{_t("enter atleast ")}<span id="codo_reply_min_chars_left">{$reply_min_chars}</span>{_t(" characters")}</div>

                </div>
                <div id="codo_non_mentionable" class="codo_non_mentionable"><b>{_t("WARNING:")} </b>{_t("You mentioned %MENTIONS%, but they cannot see this message and will not be notified")} 
                </div>

                <div class="codo_new_reply_action">
                    <button class="codo_btn" id="codo_post_new_reply"><i class="icon-check"></i><span class="codo_action_button_txt">{_t("Post")}</span></button>
                    <button class="codo_btn codo_btn_def" id="codo_post_cancel"><i class="icon-times"></i><span class="codo_action_button_txt">{_t("Cancel")}</span></button>

                    <img id="codo_new_reply_loading" src="{$smarty.const.DEF_THEME_PATH}img/ajax-loader.gif" />
                    <button class="codo_btn codo_btn_def codo_post_preview_bg" id="codo_post_preview_btn">&nbsp;</button>
                    <button class="codo_btn codo_btn_def codo_post_preview_bg" id="codo_post_preview_btn_resp">&nbsp;</button>
                    <div class="codo_draft_status_saving">{_t("Saving...")}</div>
                    <div class="codo_draft_status_saved">{_t("Saved")}</div>

                </div>
                <input type="text" class="end-of-line" name="end_of_line" id="end_of_line" />
            </form>

        </div>

        {include file='forum/editor.tpl'}

		<!-- nguoianphu -->
		
        {"block_topic_info_after"|load_block}
		
		<div id="breadcrumb_footer" class="col-md-12">

			<div class="codo_breadcrumb_list btn-breadcrumb">
				<a href="{$smarty.const.RURI}{$site_url}"><div><i class="glyphicon glyphicon-home"></i>&nbsp;&nbsp;{_t('All topics')}</div></a>
				{foreach from=$parents item=crumb}
					<a title="{$crumb.name}" data-placement="bottom" data-toggle="tooltip" href="{$smarty.const.RURI}category/{$crumb.alias}">
					<div>{$crumb.name}</div></a>                    
						{/foreach}
						&nbsp;
			</div>
			
		</div>
		
		
	</div>
	
	{* History modal *}
    <div class="modal fade" id='codo_history_modal'>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">{_t("Edit history")}</h4>
                </div>
                <div class="modal-body">

                    <div id="codo_history_table"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{_t("Close")}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

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

    <script>

        CODOFVAR = {
            tid: {$tid},
            cid: {$cid},
            post_id: {$topic_info.post_id},
            cat_alias: '{$topic_info.cat_alias}',
            title: '{$safe_title}',
            full_title: '{$title}',
            curr_page: {$curr_page},
            num_pages: {$num_pages},
            url: '{$url}',
            new_page: '{$new_page}',
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
            deleted_msg: '{_t("The post has been ")}',
            deleted: '{_t("deleted")}',
            undo_msg: '{_t("undo")}',
            search_data: '{$search_data}'
        }

    </script>

    <link rel="stylesheet" type="text/css" href="{$smarty.const.DURI}assets/markitup/highlight/styles/github.css" />
    <link rel="stylesheet" type="text/css" href="{$smarty.const.DURI}assets/dropzone/css/basic.css" />
    <link rel="stylesheet" type="text/css" href="{$smarty.const.DURI}assets/oembedget/oembed-get.css" />

{/block}