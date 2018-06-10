{*
/*
* @CODOLICENSE
*/
*}
{* Smarty *}
{$site_title} - {$dayInfo.day}{$dayInfo.ordinal} {$dayInfo.month} {$dayInfo.year}

{if $nothing_new}

{_t('Nothing new happened in your absence.')}
{_t("Why not create something new for others ?")}:{$smarty.const.RURI}new_topic
{else}

{$username}, {_t("here's what's new for you since yesterday.")}

--------------------------------------------

    Statistics : 
        {$new_posts} {_t("New posts")}
        {$new_topics} {_t("New topics")}

--------------------------------------------


{if !empty($events.rawMentions)}
===== {_t("Mentions")} =====

{foreach from=$events.rawMentions item=mention}
{$mention.actor.username} {_t("in")} {print_post_url tid=$mention.tid title=$mention.title pid=$mention.pid}
{_t("on")} {$mention.time.month} {$mention.time.day} {_t("at")} {$mention.time.hour}:{$mention.time.minute} {$mention.time.meridiem} 
{/foreach}
{/if}


{if !empty($events.myTopics)}
===== {_t("What's new in my topics ?")} =====
    
{foreach from=$events.myTopics item=topic}
{_t("Title")}: {$topic.meta.title}
{_t("Replies")}:
{foreach from=$topic.replies item=reply}
{$reply.actor.username} {if $reply.mention}{_t("mentioned you in the reply")}{else}{_t("replied you")}{/if} {_t("at")} {$reply.time.hour}:{$reply.time.minute} {$mention.time.meridiem}

{$reply.message}

{/foreach}
{_t("You can reply to this topic by going to: ")}{print_post_url tid=$topic.meta.tid title=$topic.meta.title}
{/foreach}
{/if}


{if !empty($events.following)}
===== {_t("New stuff in content that I follow")} =====
    
{foreach from=$events.following item=topic}
{_t("Title")}: {$topic.meta.title}
{_t("Replies")}:
{foreach from=$topic.replies item=reply}
{$reply.actor.username} {if $topic.meta.new_topic_pid eq $reply.pid}{if $reply.mention}{_t("mentioned you in the topic")}{else}{_t("created a new topic")}{/if}{else}{if $reply.mention}{_t("mentioned you in the reply")} {else}{_t("replied you")}{/if}{/if} at {$reply.time.hour}:{$reply.time.minute} {$mention.time.meridiem}

{$reply.message}

{/foreach}
{_t("You can reply to this topic by going to: ")}{print_post_url tid=$topic.meta.tid title=$topic.meta.title}
{/foreach}
{/if}
{/if}