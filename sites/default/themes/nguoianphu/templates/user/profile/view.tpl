{*
/*
* @CODOLICENSE
*/
*}
{* Smarty *}
{extends file='layout.tpl'}

{block name=body}
	
	<div class="container-fluid top-custom-container-profile" style="padding-bottom: 10px">
		<div class="container">
			<div class="row">
				<div class="col-md-1">
					
                    <img draggable="false" class="img-rounded profile-avatar-img" src="{$user->avatar}" alt="{$user->username}"/> <!-- nguoianphu -->
				</div>	
                <div class="codo_username col-md-6 codo-username-profile">
					<h4>{$user->username}</h4>
					<p>
                        {$user->signature}
					</p>
				</div> 
			</div>
			<hr align="left" style="width:65%;"/>
			<div class="row nav-main-profile">
				<div id="overview" class="col-md-1 nav-box-profile nav-box-profile-active">
					<span class="nav-text-profile">{_t("Overview")}</span>
				</div>
				<div class="col-md-2 nav-box-profile" id="codo_messenger" style="display:none;width:150px">
					<span class="nav-text-profile" id="codo_pms">{_t("Private messenger")}</span>
				</div>
				<div class="col-md-2 nav-box-profile">
					<span class="nav-text-profile" id="codo_edit_profile">{_t("Edit Account")}</span>
				</div>
                                
			</div>
		</div>
	</div>
	
    <div class="container" style="padding-top: 20px">
        {"block_profile_view_before"|load_block}
        <div style="display:none" class="codo_notification codo_notification_error" id="codo_resend_mail_failed"></div>

        <div style="display:none" id="codo_mail_resent" class="codo_notification codo_notification_success">
            {_t("A confirmation email has been sent to your email address!")}
        </div>


        {if $user_not_confirmed}

            <div class="codo_notification codo_notification_warning">
                {_t("You have not yet confirmed your email address.")}
                <a id="codo_resend_mail" href="#">{_t("Resend email")}</a>
                <img id="codo_email_sending_img" src="{$smarty.const.CURR_THEME}img/ajax-loader-orange.gif" />
            </div>
        {/if}
        {if $user_not_approved}

            <div class="codo_notification codo_notification_warning">
                {_t("Your account is awaiting approval.")}
            </div>
        {/if}
		
        <div class="row" style="padding-top: 20px">
			
			

            <div class="col-md-8">

                <div class="codo_tabs">

                    <!--<ul class="nav nav-tabs">
                        <li role="presentation" class="active"><a data-toggle="tab" href="#recent_posts">Recent posts</a></li>
                    </ul>-->

                    <div class="codo_tabs_content tab-content">

                        <div id="recent_posts" class="tab-pane fade in active codo_topics_body">

                            {literal}
                                <div class='codo_load_more_gif'></div>

                                <script style="display: none" id="codo_template" type="text/html">


                                    {{#each topics}}
                                    <article class="clearfix">

                                        <!--<div class="codo_topics_topic_img">
                                            <a href="{{../RURI}}category/{{cat_alias}}">
                                                <img draggable="false" src="{{../DURI}}{{../CAT_IMGS}}{{cat_img}}" />
                                            </a>
                                        </div>-->
										
										<div class="row" style="position:absolute;top: 10px;right: 0;width:120px;">
			
											<div class="col-md-5" style="padding-left:0px;padding-right:0px;float:right;">
                                                                                                <div style="float:left;padding-top:2px;" style="float:left;">
                                                                                                    <i class="icon icon-message" style="font-size:16px;color:#0097f6;"></i>
                                                                                                </div>
												<div style="float:left;font-weight:bold;padding-left:3px;">
												<span>{{no_replies}}</span>
												</div>
											</div>
											<div class="col-md-5" style="padding-left:0px;padding-right:0px;float:right;" id="codo_topics_no_views">
                                                                                                <div style="float:left;padding-top:2px;" style="float:left;">
                                                                                                    <i class="icon icon-eye2" style="font-size:16px;color:#00b147;"></i>
                                                                                                </div>
												<div style="float:left;font-weight:bold;padding-left:3px;">
												{{no_views}}
												</div>
											</div>
											
											
										</div>

                                        <div class="codo_topics_topic_content">
                                            <div class="codo_topics_topic_avatar">
                                                <a href="{{../RURI}}user/profile/{{id}}">

                                                    {{#if avatar}}
                                                    <img draggable="false" src="{{avatar}}" />
                                                    {{else}}
                                                    <img draggable="false" src="{{../../DURI}}{{../../DEF_AVATAR}}" />
                                                    {{/if}}

                                                </a>
                                            </div>
											
											<div class="codo_topics_topic_title"><a href="{{../RURI}}topic/{{topic_id}}/{{{safe_title}}}"style="font-size:16px;color:#000;">{{{title}}}</a></div>
											

                                            

                                        </div>

                                        {{#each contents}}
                                        <div class='codo_topics_topic_contents'>
                                            <div class="codo_topics_topic_message">{{{message}}}
                                            </div>
                                            <div class='codo_virtual_space'></div>    
                                            <div class="codo_topics_last_post">
                                                <a href="{{../../RURI}}topic/{{../topic_id}}/{{../safe_title}}/post-{{post_id}}#post-{{post_id}}">{{post_created}}</a>
                                            </div>
                                        </div>
                                        {{/each}}
										
										<!--<div class="codo_topics_topic_name">
                                            <a href="{{../RURI}}user/profile/{{id}}"><span class="role_{{role}}">{{name}}</span></a>
                                            <span>{{../created}} {{topic_created}}</span>
                                        </div>-->

                                        <!--<div class="codo_topics_topic_foot clearfix">

                                            <div class="codo_topics_no_replies"><span>{{no_replies}}</span>{{../reply_txt}}</div>
                                            <div class="codo_topics_no_replies"><span>{{no_views}}</span>{{../views_txt}}</div>

                                        </div>-->

									<br/>
									<br/>
                                    </article>
                                    {{else}}

                                    <div class="codo_no_posts">
                                        {{no_topics}}
                                        {{#if can_create}}
                                        <br/><br/>
                                        <button class="codo_btn codo_btn_primary" onclick="codo_create_topic()" href="#" >{{new_topic}}</button> 
                                        {{/if}}
                                    </div>
                                    {{/each}}
                                    </script>

                                {/literal}
                            </div>

                            {"block_profile_view_tabs_after"|load_block}

                        </div>
                    </div>
                </div>
				
				
				<!--admin profile-->
            <div class="col-md-4 profile-user-statistics-right">

                <div class="codo_profile profile-user-statistics-right-inner" id="">

                    <!--<div class="codo_user">

                        <div class="codo_user_header">

                            <span>{_t("Profile")}</span>
                            {if $can_edit}

                                <i id="codo_edit_profile" class="icon-edit"></i>
                            {/if}

                        </div>

                        <div class="codo_user_body">
                            <div>
                                <img draggable="false" src="{$user->avatar}" />
                                <div class="codo_username">{$user->username}</div>

                            </div>
                        </div>
                    </div>-->

                    <div class="codo_user_statistics">


                        <div class="row codo_info_block">
                            <!--<div class="codo_blue_dot">

                            </div>
                            <div class="codo_user_info_label">
							
                                {_t("views")}
                            </div>-->
							<div class="col-md-6 codo_profile_left">
							<i class="material-icons" style="color:#00b147;">visibility</i>
							</div>
                            <div class="col-md-6 codo_user_info_num codo_profile_right">
                                {$user->profile_views|abbrev_no}
                            </div>
                        </div>
                        <div class="row codo_info_block">
                            <!--<div class="codo_red_dot">

                            </div>
                            <div class="codo_user_info_label">
							
                                {_t("posts")}
                            </div>-->
							<div class="col-md-6 codo_profile_left">
							<i class="material-icons" style="color:#0097f6;">chat_bubble</i>
							</div>
                            <div class="col-md-6 codo_user_info_num codo_profile_right">
                                {$user->no_posts|abbrev_no}
                            </div>
                        </div>
                        <div class="row codo_info_block">
                            <!--<div class="codo_green_dot">

                            </div>
                            <div class="codo_user_info_label">
							
                                {_t("reputation")}
                            </div>-->
							<div class="col-md-6 codo_profile_left">
							<i class="material-icons" style="color:#5a7fee;">stars</i>
							</div>
                            <div class="codo_user_info_num col-md-6 codo_profile_right">
                                {$user->reputation}
                            </div>
                        </div>

                    </div>
                    <div class="codo_user_details">

                        <div style="color:#9f9f9f;"> {_t("Joined :")} <span style="float:right;color:#3e3e3e;font-weight:bold">{$user->created|get_pretty_time}</span>
                        </div>
                        <div style="color:#9f9f9f;">
                            {_t("Last login :")} <span style="float:right;color:#3e3e3e;font-weight:bold">{if $user->last_access eq 0}{_t('never')}{else}{$user->last_access|get_pretty_time}{/if}</span>
                        </div>
                        {foreach from=$custom_fields item=field}
                        
                            {$field.output}
                        {/foreach}
                    </div>

                </div>
            </div>
			
			<!--end admin profile-->
				
				
            </div>


            <script type="text/javascript">

                CODOFVAR = {
                userid: {$user->id},
                tab: '{$tab}'
                }
            </script>
        {/block}
