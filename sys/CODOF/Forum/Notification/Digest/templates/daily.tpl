{*
/*
* @CODOLICENSE
*/
*}
{* Smarty *}
{extends file='layout.tpl'}

{block name=body}

    <td style="border-collapse:collapse;background:#fff;height:100%;padding: 20px;" align="left">

        <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" width="100%">
            <tr style="border-bottom: 1px solid #eee;">
                <td>
                    <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}">
                        <tr>
                            <td width="20" height="77">
                                <img alt="{$site_title} - Logo" src="{$brand_img}" title="{$site_title}" width="30" height="30"
                                     style="{$img_style}">
                            </td>
                            <td width="10" height="77">&nbsp;</td>
                            <td width="500" height="77">
                                <h1 style="{$h1_style}">{$site_title}</h1>
                            </td>
                            <td width="80" height="77">

                                <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}">
                                    <tr><td style="padding-bottom: 4px"> {$dayInfo.month}</td></tr>
                                    <tr><td> {$dayInfo.year}</td></tr>
                                </table>
                            </td>
                            <td width="10" height="77">&nbsp;</td>
                            <td width="70"  height="77" style="font-size: 40px;font-weight: bold;">
                                {$dayInfo.day}<span style="font-size: 14px;line-height: 0; vertical-align: 16px"> {$dayInfo.ordinal}</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr><td height="20"></td></tr>

            {if $nothing_new}

                <h1 style="{$h1_style}">{_t('Nothing new happened in your absence.')}</h1>
                <br/>
                <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" width="100%">
                    <tr>
                        <td width="50"></td>
                        <td width="100">  
                            <img style="{$img_style}" src="{$create_new_img}" alt="Create content" /> 
                        </td>
                        <td width="400">
                            <a target="_blank" href="{$smarty.const.RURI}new_topic" style="{$link_style}">{_t("Why not create something new for others ?")}</a>
                        </td>
                    </tr>
                </table>
            {else}
                <tr>
                    <td><b>{$username}</b>, {_t("here's what's new for you since yesterday.")}</td>
                </tr>

                <tr><td height="30"></td></tr>

                <tr>
                    <td>
                        <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" width="100%">
                            <tr>
                                <td width="50"></td>
                                <td width="150">
                                    <img alt="{_t('Summary')}" src="{$statistics_img}" title="{_t('Summary')}" width="50" height="50"
                                         style="{$img_style}" />

                                </td>

                                <td width="100">
                                    <table cellpadding="0" border="0" cellspacing="0" style="text-align: center;{$table_style}">
                                        <tr><td style="font-size: 32px;font-weight: bold;padding-bottom: 8px;padding-top:10px;">{$new_posts}</td></tr>
                                        <tr><td style="{$sec_text_color}font-size: 11px">{_t("New posts")}</td></tr>
                                    </table>
                                </td>
                                <td width="100">
                                    <table cellpadding="0" border="0" cellspacing="0" style="text-align: center;{$table_style}">
                                        <tr><td style="font-size: 32px;font-weight: bold;padding-bottom: 8px;padding-top:10px;">{$new_topics}</td></tr>
                                        <tr><td style="{$sec_text_color}font-size: 11px">{_t("New topics")}</td></tr>
                                    </table>
                                </td>
                                <td width="50"></td>                                                    
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr><td height="30"></td></tr>


                {if !empty($events.rawMentions)}
                    <tr>
                        <td style="padding-bottom: 10px;">

                            <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" width="100%">

                                <tr style="background: #1471af;">
                                    <td style="color: #fff;font-size: 20px;height: 15px;padding: 10px;font-weight: bold;">
                                        {_t("Mentions")}
                                    </td>
                                </tr>
                            </table>
                        </td>                                        
                    </tr>

                    <tr>
                        <td style="padding-bottom: 20px;">

                            <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" width="100%">

                                {foreach from=$events.rawMentions item=mention}
                                    <tr>                                
                                        <td style="padding-bottom: 6px">

                                            <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" >

                                                <tr>
                                                    <td rowspan="2" width="36">
                                                        <img style="{$img_style}" src="{$mention.actor.avatar}" alt="avatar" width="30" height="30"/>
                                                    </td>
                                                    <td style="font-weight: bold">
                                                        <a target="_blank" style="color:#222;text-decoration: underline;" href="{$smarty.const.RURI}user/profile/{$mention.actor.id}">{$mention.actor.username}</a>
                                                    </td> 
                                                    <td rowspan="2" width="400" style="padding:0 10px;">
                                                        <a target="_blank" style="{$link_style}" href="{print_post_url tid=$mention.tid title=$mention.title pid=$mention.pid}">{$mention.title}</a>
                                                    </td>                                                
                                                    <td style="{$sec_text_color}font-size: 11px;">{$mention.time.month} {$mention.time.day}</td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#777;font-size: 10px;">{$mention.actor.role}</td>
                                                    <td style="{$sec_text_color}font-size: 11px;width: 50px;">{$mention.time.hour}:{$mention.time.minute} {$mention.time.meridiem}</td>
                                                </tr>
                                            </table>

                                        </td>
                                    </tr>
                                {/foreach}
                            </table>
                        </td>                                        
                    </tr>
                {/if}


                {if !empty($events.myTopics)}
                    <tr>
                        <td style="padding-bottom: 5px;">

                            <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" width="100%">

                                <tr style="background: #1471af;">
                                    <td style="color: #fff;font-size: 20px;height: 15px;padding: 10px;font-weight: bold;">
                                        {_t("What's new in my topics ?")}
                                    </td>
                                </tr>
                            </table>
                        </td>                                        
                    </tr>

                    <tr>
                        <td style="padding-bottom: 20px;">

                            <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" width="100%">

                                {foreach from=$events.myTopics item=topic}
                                    <tr>                                
                                        <td style="padding-bottom: 6px">

                                            <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" >

                                                <tr>
                                                    <td style="padding-bottom: 8px;padding-top: 10px">
                                                        <table  cellpadding="0" border="0" cellspacing="0" style="{$table_style}" width="100%">
                                                            <tr>
                                                                <td>
                                                                    <a style="{$link_style}font-size: 20px;line-height: 20px;" href="{print_post_url tid=$topic.meta.tid title=$topic.meta.title}">{$topic.meta.title}</a>
                                                                </td>
                                                                <td style="text-align: right;">
                                                                    <a href="{print_post_url tid=$topic.meta.tid title=$topic.meta.title}" target="_blank" style="text-decoration: none;background: #1471af;color: #fff;padding: 5px;display: inline-block;box-shadow: 1px 1px 1px #ccc;cursor: pointer;border-radius: 4px;text-align: right;">reply</a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                {foreach from=$topic.replies item=reply}
                                                    <tr>
                                                        <td style="padding-left: 6px;padding-bottom: 6px;">

                                                            <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" >

                                                                <tr>
                                                                    <td rowspan="2" width="36">
                                                                        <img style="{$img_style}" src="{$reply.actor.avatar}" alt="avatar" width="30" height="30"/>
                                                                    </td>
                                                                    <td style="font-weight: bold;padding-bottom: 4px;">
                                                                        <a target="_blank" style="color:#222;text-decoration: underline;" href="{$smarty.const.RURI}user/profile/{$reply.actor.id}">{$reply.actor.username}</a>
                                                                    </td> 
                                                                    <td width="15"></td>
                                                                    <td width="400" style="color:#777">
                                                                        {if $reply.mention}
                                                                            {_t("mentioned you in the reply")}
                                                                        {else}
                                                                            {_t("replied you")}
                                                                        {/if}
                                                                    </td>
                                                                    <td width="100" style="padding:0 10px;text-align: right;">
                                                                        <a target="_blank" style="{$link_style}" href="{print_post_url tid=$topic.meta.tid title=$topic.meta.title pid=$reply.pid}">#</a>
                                                                    </td>                                                
                                                                    {*<td style="{$sec_text_color}font-size: 11px;">{$mention.time.month} {$mention.time.day}</td>*}
                                                                </tr>
                                                                <tr>
                                                                    {*<td style="color:#777;font-size: 10px;">{$mention.actor.role}</td>*}
                                                                    <td style="{$sec_text_color}font-size: 11px;width: 50px;">{$reply.time.hour}:{$reply.time.minute} {$mention.time.meridiem}</td>
                                                                </tr>
                                                            </table>
                                                        </td>       
                                                    </tr>
                                                    <tr>                                                    
                                                        <td style="padding-left: 10px;padding-bottom: 10px;color:#333;">
                                                            <table cellpadding="0" border="0" cellspacing="0" style="border-collapse:collapse;width:90%;" >
                                                                <tr>
                                                                    <td style="padding-bottom: 8px;border-bottom: 1px dotted #eee;padding-top: 4px;">
                                                                        {$reply.message|nl2br}
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>                                                    
                                                    </tr>
                                                {/foreach}
                                            </table>
                                        </td>
                                    </tr>
                                {/foreach}
                            </table>
                        </td>
                    </tr>
                {/if}

                {if !empty($events.following)}
                    <tr>
                        <td style="padding-bottom: 5px;">

                            <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" width="100%">

                                <tr style="background: #1471af;">
                                    <td style="color: #fff;font-size: 20px;height: 15px;padding: 10px;font-weight: bold;">
                                        {_t("New stuff in content that I follow")}
                                    </td>
                                </tr>
                            </table>
                        </td>                                        
                    </tr>

                    <tr>
                        <td style="padding-bottom: 20px;">

                            <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" width="100%">

                                {foreach from=$events.following item=topic}
                                    <tr>                                
                                        <td style="padding-bottom: 6px">

                                            <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" >

                                                <tr>
                                                    <td style="padding-bottom: 8px;padding-top: 10px">
                                                        <table  cellpadding="0" border="0" cellspacing="0" style="{$table_style}" width="100%">
                                                            <tr>
                                                                <td>
                                                                    <a style="{$link_style}font-size: 20px;line-height: 20px;" href="{print_post_url tid=$topic.meta.tid title=$topic.meta.title}">{$topic.meta.title}</a>
                                                                </td>
                                                                <td style="text-align: right;">
                                                                    <a href="{print_post_url tid=$topic.meta.tid title=$topic.meta.title}" target="_blank" style="text-decoration: none;background: #1471af;color: #fff;padding: 5px;display: inline-block;box-shadow: 1px 1px 1px #ccc;cursor: pointer;border-radius: 4px;text-align: right;">reply</a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                {foreach from=$topic.replies item=reply}
                                                    <tr>
                                                        <td style="padding-left: 6px;padding-bottom: 6px;">

                                                            <table cellpadding="0" border="0" cellspacing="0" style="{$table_style}" >

                                                                <tr>
                                                                    <td rowspan="2" width="36">
                                                                        <img style="{$img_style}" src="{$reply.actor.avatar}" alt="avatar" width="30" height="30"/>
                                                                    </td>
                                                                    <td style="font-weight: bold;padding-bottom: 4px;">
                                                                        <a target="_blank" style="color:#222;text-decoration: underline;" href="{$smarty.const.RURI}user/profile/{$reply.actor.id}">{$reply.actor.username}</a>
                                                                    </td> 
                                                                    <td width="15"></td>
                                                                    <td width="400" style="color:#777">
                                                                        {if $topic.meta.new_topic_pid eq $reply.pid}

                                                                            {if $reply.mention}
                                                                                {_t("mentioned you in the topic")}
                                                                            {else}
                                                                                {_t("created a new topic")}
                                                                            {/if}

                                                                        {else}
                                                                            {if $reply.mention}
                                                                                {_t("mentioned you in the reply")}
                                                                            {else}
                                                                                {_t("replied you")}
                                                                            {/if}

                                                                        {/if}
                                                                    </td>
                                                                    <td width="100" style="padding:0 10px;text-align: right;">
                                                                        <a target="_blank" style="{$link_style}" href="{print_post_url tid=$topic.meta.tid title=$topic.meta.title pid=$reply.pid}">#</a>
                                                                    </td>                                                
                                                                    {*<td style="{$sec_text_color}font-size: 11px;">{$mention.time.month} {$mention.time.day}</td>*}
                                                                </tr>
                                                                <tr>
                                                                    {*<td style="color:#777;font-size: 10px;">{$mention.actor.role}</td>*}
                                                                    <td style="{$sec_text_color}font-size: 11px;width: 50px;">{$reply.time.hour}:{$reply.time.minute} {$mention.time.meridiem}</td>
                                                                </tr>
                                                            </table>
                                                        </td>       
                                                    </tr>
                                                    <tr>                                                    
                                                        <td style="padding-left: 10px;padding-bottom: 10px;color:#333;">
                                                            <table cellpadding="0" border="0" cellspacing="0" style="border-collapse:collapse;width:90%;" >
                                                                <tr>
                                                                    <td style="padding-bottom: 8px;border-bottom: 1px dotted #eee;padding-top: 4px;">
                                                                        {$reply.message|nl2br}
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>                                                    
                                                    </tr>
                                                {/foreach}
                                            </table>
                                        </td>
                                    </tr>
                                {/foreach}
                            </table>
                        </td>
                    </tr>
                {/if}
            {/if}
        </table>
    </td>

{/block}