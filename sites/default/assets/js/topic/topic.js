/*
 * @CODOLICENSE
 */

'use strict';


jQuery('document').ready(function ($) {

    $('#codo_posts_container > article .codo_lightbox_container').simpleLightbox();
    console.log( $('#codo_posts_container > article .codo_lightbox_container'))    
    CODOF.editor_form = $('#codo_new_reply_post');
    CODOF.editor_preview_btn = $('#codo_post_preview_btn');
    CODOF.editor_reply_post_btn = $('#codo_post_new_reply');
    CODOF.container = $('#codo_posts_container');

    $.ajaxSetup({cache: true});
    $.getScript('//connect.facebook.net/en_US/sdk.js', function () {
        FB.init({
            appId: '633890129979455',
            version: 'v2.7' // or v2.1, v2.2, v2.3, ...
        });
    });
    
    /*if ($('.codo_posts_select_post').length === 0) {

        $('.codo_posts_post_reputation div').each(function () {
            var el = $(this);
            el.animate({left: el.position().left - 30 + "px"});
        });
    }*/

    CODOF.search_data = JSON.parse(CODOFVAR.search_data);

    /**
     * The icon above the topics trigger this method
     * It will  toggle the visibility of topics and
     * categories
     */
    CODOF.toggleTopicsAndCategories = function () {


        $('.codo_posts').toggle();
        $('.top-custom-container-profile').toggle();
        $('#codo_topic_sidebar').toggle();
        CODOF.util.simpleNotify($('#icon-books-click-trans').text());
    }

    CODOF.topic_creator = {
        body: $('#codo_posts_container'),
        from: parseInt(CODOFVAR.curr_page),
        //template: Handlebars.compile($("#codo_template").html()),
        //paginate: Handlebars.compile($("#codo_pagination").html()),
        container: $('#codo_posts_container'),
        edit_post_id: false,
        search_switch: false,
        build_topic: function (context) {

            this.built_topics = CODOF.template(context);
            return this.built_topics;
        },
        update_head_navigation: function () {

            var pagination = CODOF.paginateTemplate(CODOF.ret_pagination(CODOFVAR.curr_page, CODOFVAR.num_pages, {
                cls: 'codo_head_navigation',
                url: CODOFVAR.url,
                search: CODOF.search_data.str
            }));

            $('.codo_head_navigation').replaceWith(pagination);

            this.search_switch = false;

        },
        fetch: function () {

            $('.codo_load_more_gif').remove();
            CODOF.topic_creator.body.append("<div class='codo_load_more_gif'></div>");


            CODOF.request.get({
                hook: 'fetch_topics',
                url: codo_defs.url + 'Ajax/topic/' + CODOFVAR.tid + '/' + CODOF.topic_creator.from + '/get_posts',
                done: function (response) {

                    CODOF.req.data.get_page_count = 'no';
                    if (response.posts.length > 0) {

                        var html;

                        if (response.num_pages > 0) {

                            CODOFVAR.num_pages = response.num_pages;
                        }

                        html = CODOF.topic_creator.build_topic(response);
                        CODOF.topic_creator.container.append(html);

                        var curr_page = CODOF.topic_creator.from + 1;

                        var pagination = CODOF.editor_ret_pagination(curr_page);


                        if (CODOF.topic_creator.search_switch) {

                            CODOF.topic_creator.update_head_navigation();
                        }

                        CODOF.topic_creator.container.append(pagination);

                        CODOF.req_started = false;
                        CODOF.topic_creator.from++; //next page
                        $('.codo_load_more_gif').remove();

                        CODOF.hook.call('after_posts_added');

                    } else {

                        $('.codo_load_more_gif').remove();
                        CODOFVAR.num_pages = 0;
                        $('.codo_head_navigation').css('visibility', 'hidden');
                        $('#codo_no_topics_display').show();

                    }
                }
            });
        },
        refresh: function () {

            //$('#codo_posts_container > article').remove();
            //$('.codo_topic_separator').remove();
            $('#codo_posts_container').html('');
            //set page 1
            this.from = 0;
            CODOF.req_started = true;

            this.fetch(true);
        }
    };

    CODOF.getTemplateData('forum/topic');


    CODOF.autoDraft.test = function (obj, textbox, pid) {

        if (obj.tid != CODOFVAR.tid ||
                (pid && obj.pid != pid)) {
            //draft is not of topic in current page
            //or while editing post, the post being edited is
            //not that of the draft

            $('#codo_draft_topic_title').html(obj.title);
            $('#codo_draft_pending').modal();
            return false;
        } else {

            if (!(pid && obj.pid == pid)) {
                $('.codo_editor_draft').hide();

                var html = $('<textarea />').html(obj.text).text();
                textbox.val(html);
            }
        }

        //draft of in edit post was saved
        if (obj.pid) {

            CODOF.topic_creator.edit_post_id = obj.pid;
            CODOF.post_being_edited = true;
        }

        return true;
    };

    CODOF.showEditor = function (textbox, moveCursorBottom) {

        CODOF.editor.recalc_ht();
        CODOF.editor_trigger_preview(textbox);
        $('.codo_editor_draft').hide();

        if ($('#codo_is_xs').is(':visible')) {

            //if visible the width is less than 767 px
            $('#codo_new_reply').slideDown(400, function () {

                CODOF.container.css('padding-bottom', $('#codo_new_reply').outerHeight(true));
            });

        } else {

            $('#codo_new_reply').show();
            CODOF.container.css('padding-bottom', '10px');
            setTimeout(CODOF.ui.scrollToBottom, 200);
        }

        if (moveCursorBottom) {

            textbox.putCursorAtEnd();
        }

    };

    CODOF.replyTopic = function (canReply) {

        if (!canReply) {

            window.location.href = codo_defs.login_url + "&page=post";
            return false;
        }

        var textbox = $('#codo_new_reply_textarea');

        var obj = JSON.parse(localStorage.getItem('reply_' + codo_defs.uid));
        if (localStorage.getItem('reply_' + codo_defs.uid) !== null) {

            if (!CODOF.autoDraft.test(obj, textbox)) {

                return false;
            }

            if (obj.pid) {

                $('#codo_draft_topic_title').html(obj.title);
                $('#codo_draft_pending').modal();
                return false;
            }

        } else {

            textbox.val('');
        }

        CODOF.showEditor();
        CODOF.post_being_edited = false;
        $("#mmenu").trigger("close.mm");

        return false;
    };

    $('.container').on({
        'click': function () {

            CODOF.replyTopic($(this).hasClass('codo_can_reply'));
        }
    }, '.codo_reply_btn');

    $('#codo_posts_container').on({
        'click': function () {

            if (!$(this).hasClass('codo_can_reply')) {

                window.location.href = codo_defs.login_url;
                return false;
            }

            var textbox = $('#codo_new_reply_textarea');

            var obj = JSON.parse(localStorage.getItem('reply_' + codo_defs.uid));
            if (localStorage.getItem('reply_' + codo_defs.uid) !== null) {

                if (!CODOF.autoDraft.test(obj, textbox)) {

                    return false;
                }

                if (obj.pid) {

                    $('#codo_draft_topic_title').html(obj.title);
                    $('#codo_draft_pending').modal();
                    return false;
                }

            }

            var content = $(this).parent().parent().parent().prev().children().eq(1);

            var text = content.text();

            var lines = text.split('\n');
            var len = lines.length;

            for (var i = 0; i < len; i++) {

                if ($.trim(lines[i]) !== '') {

                    //not a blank line
                    lines[i] = '>' + lines[i];
                }
            }

            var textbox = $('#codo_new_reply_textarea');
            var def_val = textbox.val();

            if (def_val !== '') {

                text = '\n' + lines.join('\n');
            } else {

                text = lines.join('\n');
            }

            var len = text.length, lb = '';

            if (text[len - 1] !== '\n') {

                lb = '\n';
            }


            CODOF.textarea = textbox;
            var html = $('<textarea />').html(def_val + text + '\n' + lb).text();
            textbox.val(html);

            CODOF.showEditor(textbox, true);
            CODOF.post_being_edited = false;

            return false;
        }
    }, '.codo_quote_btn');

    $('#codo_posts_container').on('click', '.codo_fb_share', function () {


        var tid = $(this).data('tid');
        var pid = $(this).data('pid');

        var url = codo_defs.url + "topic/" + tid + "/#post-" + pid;

        console.log(url);
        FB.ui({
            method: 'share',
            href: url,
        }, function (response) {});

    });


    $('body').on({
        click: function () {

            var me = $(this);

            var rep_counter = me.parent().find('.codo_reputation_points');

            var prev_count = rep_counter.html();

            rep_counter.html('-');

            var pid = me.parent().attr('id').replace('codo_posts_rep_', '');

            CODOF.request.get({
                hook: 'post_rep_up',
                url: codo_defs.url + 'Ajax/reputation/' + CODOFVAR.tid + '/' + pid + '/up',
                done: function (result) {

                    if (result.done) {

                        rep_counter.html(result.rep);
                    } else {

                        rep_counter.html(prev_count);
                        alert(result.errors);
                    }
                }
            });
        }
    }, '.codo_rep_up_btn');

    $('body').on({
        click: function () {

            var me = $(this);

            var rep_counter = me.parent().find('.codo_reputation_points');

            var prev_count = rep_counter.html();

            rep_counter.html('-');

            var pid = me.parent().attr('id').replace('codo_posts_rep_', '');

            CODOF.request.get({
                hook: 'post_rep_up',
                url: codo_defs.url + 'Ajax/reputation/' + CODOFVAR.tid + '/' + pid + '/down',
                done: function (result) {

                    if (result.done) {

                        rep_counter.html(result.rep);
                    } else {

                        rep_counter.html(prev_count);
                        alert(result.errors);
                    }
                }
            });

        }
    }, '.codo_rep_down_btn');




    CODOF.topic_creator.active = false;

    $('#codo_posts_container').on('click', ".codo_posts_history", function () {

        var me = this;

        var pid = parseInt(me.id.replace('codo_posts_history_', ''));

        CODOF.request.get({
            hook: 'get_post_history',
            url: codo_defs.url + 'Ajax/history/posts',
            data: {pid: pid},
            done: function (history) {

                $('#codo_history_modal').modal();


                var str = "<table class='table table-responsive'><tr><th>username</th><th>time</th><th>action</th></tr>";
                for (var i = 0; i < history.length; i++) {


                    var edit = history[i];

                    str += '<tr class="codo_history_row"><td><a href="' + codo_defs.url + 'user/' + edit.uid + '">' + edit.username + '</a></td><td>' + edit.time + '</td><td><button class="codo_btn codo_btn_def codo_btn_sm">show/hide message</button></td></tr><tr style="display:none"><td colspan="100">' + edit.text + '</td></tr>';

                }

                str += "</table>";

                if (history.length === 0) {

                    str = "No edits made yet";
                }

                $('#codo_history_table').html(str);

                $('.codo_history_row .codo_btn_sm').on('click', function () {

                    $(this).parents('.codo_history_row').next().toggle();
                });
            }

        });

    });


    $('#codo_posts_container').on('click', ".codo_posts_trash_post", function () {

        if (CODOF.topic_creator.active)
            return;

        var $that = $(this);
        //activity started
        CODOF.codo_spinner = $that.find('.codo_spinner');
        CODOF.codo_spinner.show();

        if ($that.hasClass('codo_post_this_is_topic')) {

            if (CODOF.topic_creator.topic_active)
                return;

            CODOF.topic_creator.topic_active = true;
            CODOF.codo_spinner.hide();
            if (typeof CODOF.confirm_popover === "undefined") {

                CODOF.confirm_popover = $that.popover({
                    html: true,
                    placement: 'bottom',
                    container: $that,
                    content: function () {
                        return $('#codo_delete_topic_confirm_html').html();
                    }
                }).on('shown.bs.popover', function () {

                    //-207px
                    if (document.documentElement.clientWidth < 320) {

                        //popover is always appended so it becomes the next element
                        var popover = $(this).next();
                        popover.css('left', '-207px');
                        CODOF.topic_creator.arrow = popover.find('.arrow')
                                .hide();
                    }
                });

                $that.parent().on('click', '.codo_modal_delete_topic_cancel', function () {

                    CODOF.confirm_popover.popover('hide');
                    CODOF.codo_spinner.hide();
                    CODOF.topic_creator.topic_active = false;

                });
                $that.parent().on('click', '.codo_posts_topic_delete', function (e) {

                    if ($(e.target).hasClass('codo_spam_checkbox')) {

                        var checkbox = $('.codo_consider_as_spam input[type=checkbox]');
                        checkbox.prop('checked', !checkbox.prop("checked"));
                    }
                    e.stopPropagation();
                });


                $that.parent().on('click', '.codo_modal_delete_topic_submit', function () {

                    var isSpam = $('.codo_consider_as_spam input[type=checkbox]').prop('checked');
                    CODOF.topic_creator.delete_topic(isSpam);
                });

            }

            CODOF.confirm_popover.popover('toggle');
        } else {

            CODOF.topic_creator.active = true;
            CODOF.topic_creator.delete_post(this);
        }

        return false;

    });


    $('#codo_posts_container').on('click', ".codo_posts_edit_post", function () {

        var me = this;
        var $that = $(this);

        if ($that.hasClass('codo_post_this_is_topic')) {

            window.location.href = codo_defs.url + 'topic/' + CODOFVAR.tid + '/edit';
        } else {

            var textbox = $('#codo_new_reply_textarea');
            var edit_post_id = parseInt(me.id.replace('codo_posts_edit_', ''));

            var value = $that.closest('#post-' + edit_post_id).find('.codo_posts_post_imessage').text();

            var obj = JSON.parse(localStorage.getItem('reply_' + codo_defs.uid));


            if (localStorage.getItem('reply_' + codo_defs.uid) !== null) {

                if (!CODOF.autoDraft.test(obj, textbox, edit_post_id)) {

                    return false;
                }

            }

            var html = $('<textarea />').html(value).text();
            textbox.val(html);

            CODOF.mentions.extractAndAddToManned(html);

            CODOF.showEditor(textbox, true);
            CODOF.topic_creator.edit_post_id = edit_post_id;
            CODOF.post_being_edited = true;
            setTimeout(CODOF.editor.callembed, 100); //sadly markitup does not provide onload event
        }
    });


    CODOF.topic_creator.delete_topic = function (isSpam) {

        $('.codo_posts_topic_delete .codo_spinner').show();

        var id = CODOFVAR.tid;

        CODOF.codo_spinner = $('.popover .codo_posts_topic_delete .codo_spinner');
        CODOF.codo_spinner.show();

        jQuery.post(codo_defs.url + 'Ajax/topic/' + id + '/delete', {
            token: codo_defs.token,
            isSpam: isSpam ? 'yes' : 'no'

        }, function (resp) {

            if (resp === "success") {

                CODOF.codo_spinner.hide();
                window.location.href = codo_defs.url + 'category/' + CODOFVAR.cat_alias;
            }
        });

    };

    CODOF.topic_creator.delete_post = function (me) {

        var id = parseInt(me.id.replace('codo_posts_trash_', ''));

        jQuery.post(codo_defs.url + 'Ajax/post/' + id + '/delete', {
            token: codo_defs.token

        }, function (resp) {

            if (resp === "success") {

                var article = $('#post-' + id);
                article.slideUp();
                CODOF.codo_spinner.hide();

                $('<div class="codo_deleted_post"><div class="codo_spinner"></div>' + CODOFVAR.deleted_msg + '<b>' + CODOFVAR.deleted + '</b>\n\
                        <div id="codo_deleted_post_' + id + '"><span>undo</span><div></div></div>\n\
                </div>').insertBefore(article);

                $('#codo_deleted_post_' + id).on('click', function () {

                    if (CODOF.topic_creator.active)
                        return;

                    CODOF.topic_creator.active = true;
                    var that = $(this);
                    var codo_spinner = $(this).parent().find('.codo_spinner');
                    codo_spinner.show();

                    jQuery.post(codo_defs.url + 'Ajax/post/' + id + '/undelete', {
                        token: codo_defs.token

                    }, function (resp) {

                        if (resp === "success") {

                            codo_spinner.hide();
                            that.off(); //remove event handler immediately
                            article.slideDown();
                            that.parent().remove();
                            //activity ended
                        }
                        CODOF.topic_creator.active = false;
                    });
                });

            }

            CODOF.topic_creator.active = false;
        });

    };

    $('#codo_post_cancel').on('click', function () {

        var textbox = $('#codo_new_reply_textarea');
        $('.codo_editor_draft').hide();
        textbox.val('');
        CODOF.editor_trigger_preview(textbox);
        CODOF.container.css('padding-bottom', 0);
        $('#codo_new_reply').slideUp(400);

        localStorage.removeItem('reply');
        return false;
    });

    var str = $('#codo_non_mentionable').html();
    $('#codo_non_mentionable').html(str.replace('%MENTIONS%', '<span id="codo_nonmentionable_users"></span>'));

    CODOF.reply_posted = false;
    CODOF.submitted = function () {

        //$('#codo_reply_replica').val($('#codo_new_reply_preview').html());
        if (CODOF.reply_posted)
            return false;

        CODOF.reply_posted = true;
        var warned = false;
        if (CODOF.editor_reply_post_btn.hasClass('codo_btn_primary')) {


            if (!warned) {

                if (CODOF.mentions.warnForNonMentions()) {

                    warned = true;
                    return false;
                }
            }

            //CODOF.editor_reply_post_btn.removeClass('codo_btn_primary');
            $('#codo_new_reply_loading').show();


            var action = 'Ajax/topic/reply';
            if (CODOF.post_being_edited) {

                action = 'Ajax/post/edit';
            }

            $('#codo_reply_box').append('<div id="codo_reply_html_playground"></div>');

            $('#codo_reply_html_playground').html($('#codo_new_reply_preview').html());

            $('#codo_reply_html_playground .codo_embed_container').remove();
            $('#codo_reply_html_playground .codo_embed_placeholder').remove();


            $('#codo_reply_html_playground .codo_oembed').each(function () {

                var href = $(this).attr('href');
                $(this).html(href);
            });

            CODOF.req.data = {
                input_txt: $('#codo_new_reply_textarea').val(),
                output_txt: $('#codo_reply_html_playground').html().replace(/\</g, 'STARTCODOTAG'),
                tid: CODOFVAR.tid,
                end_of_line: $('#end_of_line').val(),
                token: codo_defs.token,
                pid: CODOF.topic_creator.edit_post_id

            };

            CODOF.hook.call('before_req_send');

            $.post(
                    codo_defs.url + action,
                    CODOF.req.data,
                    function (msg) {

                        if (CODOF.post_being_edited) {

                            if (msg === 'success') {

                                CODOF.autoDraft.remove();
                                window.location.hash = "#post-" + CODOF.topic_creator.edit_post_id;
                                window.location.reload();
                            } else
                                alert(msg);
                        } else {


                            var is_json = true;
                            try
                            {
                                var response = $.parseJSON(msg);
                            } catch (err)
                            {
                                is_json = false;
                            }


                            if (is_json) {

                                CODOF.autoDraft.remove();
                                var page_no = CODOFVAR.num_pages, reload = false;

                                if (CODOFVAR.new_page === 'yes') {

                                    page_no++;
                                }

                                if (response.spam)
                                    confirm('Your reply has been detected as spam!')
                                window.location.href = codo_defs.url + 'topic/' + CODOFVAR.tid + '/' + CODOFVAR.title + '/' + page_no + "#post-" + response.pid;
                                if (CODOFVAR.curr_page === page_no) {

                                    window.location.hash = "#post-" + response.pid;
                                    window.location.reload();
                                }


                            } else {
                                alert(msg);
                                CODOF.editor_reply_post_btn.addClass('codo_btn_primary');
                            }
                        }

                        $('#codo_new_reply_loading').hide();
                        CODOF.reply_posted = false;
                    }
            );


        }

        return false;
    };

    CODOF.cache.sideBarMenu = {
        el: $('.codo_sidebar_fixed'),
        pos: 'static',
        top: 0
    };
    $('.codo_sidebar_fixed').append($('.codo_topic_statistics')[0].outerHTML);

    if ($('.codo_reply_div').length > 0) {

        $('.codo_sidebar_fixed').append($('.codo_reply_div')[0].outerHTML);
    }

    (CODOF.applySideBarPosition = function () {

        if (CODOF.cache.sideBarMenu.top && CODOF.cache.sideBarMenu.top < $(window).scrollTop()) {

            if (CODOF.cache.sideBarMenu.pos === 'static') {
                CODOF.cache.sideBarMenu.el.css({
                    position: 'fixed',
                    top: '60px'
                })
                        .addClass('codo_sidebar_fixed_width').removeClass('codo_sidebar_static_width')
                        .find('.codo_sidebar_fixed_els').show();

                if (CODOF.cache.sideBarMenu.el.is(':visible'))
                    CODOF.cache.sideBarMenu.el.css('width', (CODOF.cache.sideBarMenu.el.parent().innerWidth() - 15) + 'px');
                CODOF.cache.sideBarMenu.pos = 'fixed';
            }
        } else {

            if (CODOF.cache.sideBarMenu.pos === 'fixed' || !CODOF.cache.sideBarMenu.top) {

                CODOF.cache.sideBarMenu.el.css('position', 'static')
                        .addClass('codo_sidebar_static_width').removeClass('codo_sidebar_fixed_width')
                        .find('.codo_sidebar_fixed_els').hide();

                CODOF.cache.sideBarMenu.pos = 'static';
                CODOF.cache.sideBarMenu.top = CODOF.cache.sideBarMenu.el.offset().top;
            }
        }
    });


    $(window).scroll(function () {

        var offset = 200;
        if ($(window).scrollTop() + offset > $(document).height() - $(window).height()) {

            //request and get data before the user even reaches end of page

            if (!CODOF.req_started && CODOFVAR.num_pages > CODOF.topic_creator.from) {

                CODOF.req_started = true;
                CODOF.topic_creator.fetch();
            }
        }
        CODOF.applySideBarPosition();

    });


    CODOF.editor_ret_pagination = function (curr_page) {

        var constants = {
            cls: 'codo_topics_pagination',
            url: CODOFVAR.url,
            search: CODOF.search_data.str
        };


        var pages = CODOF.ret_pagination(curr_page, CODOFVAR.num_pages, constants);

        return CODOF.paginateTemplate(pages);
    };

    /*CODOF.hook.add('on_tpl_loaded', function() {
     if (CODOFVAR.num_pages > 1) {
     
     var pagination = CODOF.paginateTemplate(CODOF.ret_pagination(CODOFVAR.curr_page, CODOFVAR.num_pages, {
     cls: 'codo_head_navigation',
     url: CODOFVAR.url
     //search: JSON.stringify(CODOF.topic_creator.search_data)
     }));
     
     //$('#codo_topic_title_pagination').append(pagination);
     
     /*if (!$.isEmptyObject(CODOF.topic_creator.search_data)) {
     
     //in search mode
     $('.codo_topics_pagination').remove();
     var pagination = CODOF.editor_ret_pagination(CODOFVAR.curr_page);
     CODOF.topic_creator.container.append(pagination);
     }
     
     }
     });*/


    CODOF.hook.add('before_req_fetch_topics', function (settings) {

        return $.extend(settings, CODOF.search_data);
    });


    /*$('.codo_topics_search_icon').click(function () {
     
     search_triggered($(this).prev().val());
     });
     
     function search_triggered(val) {
     
     $('.codo_topics_search_input').val(val);
     codo_create_filter(val);
     }
     
     $('.codo_topics_search_input').keypress(function (e) {
     
     if (e.which == 13) {
     
     search_triggered(this.value);
     }
     });*/

    (CODOF.notify.selector = function () {

        var putText = function (value, sendRequest) {

            switch (value) {

                case 1:

                    $('#codo_notification_block_text').html($('.codo_notification_block_muted').html());
                    break;
                case 2:

                    $('#codo_notification_block_text').html($('.codo_notification_block_default').html());
                    break;

                case 3:

                    $('#codo_notification_block_text').html($('.codo_notification_block_following').html());
                    break;

                case 4:

                    $('#codo_notification_block_text').html($('.codo_notification_block_notified').html());

            }

            if (typeof sendRequest === 'undefined') {
                CODOF.request.get({
                    hook: 'update_notification_level',
                    url: codo_defs.url + 'Ajax/subscribe/' + CODOFVAR.cid + "/" + CODOFVAR.tid + "/" + value
                });
            }
        };

        $('.codo_notification_block').css('visibility', 'hidden').show();
        $('#codo_notification_selector').slider()
                .on('slideStop', function (ev) {

                    putText(ev.value);
                });

        var defValue = $('#codo_notification_selector').data('slider-value');

        putText(defValue, false);

        // Position the labels
        for (var i = 0; i <= 3; i++) {

            // Create a new element and position it with percentages
            var el = $('<label>' + (i) + '</label>').css('left', ((i / 3 * 100) - 1) + '%');

            // Add the element inside #slider
            $(".slider").append(el);

        }

        $('.slider-selection').addClass('white-slider-selection');

        if ($('.codo_notification_block').length === 0 && $('#codo_topic_sidebar').height() < 120) {

            $('.codo_topic_closed').addClass('codo_topic_closed_darkbg')
        }

        $('.codo_notification_block').hide().css('visibility', 'visible').slideDown(function () {


            if ($('#codo_topic_sidebar').height() < 120) {

                $('.codo_topic_closed').addClass('codo_topic_closed_darkbg')
            }

        });


    })();
    //setTimeout(function(){$('#codo_search_keywords').focus()},10);

    jQuery.get(codo_defs.url + 'Ajax/topic/inc_view', {
        topic_id: CODOFVAR.tid,
        token: codo_defs.token

    }, function (resp) {

        if (resp === "success") {

            CODOF.inc_num('codo_topic_views');
        }
    });

    $('#codo_breadcrumb_select').on('change', function () {

        var el = this;

        if (el.value !== '') {

            window.location = el.value;
        }
    });

    CODOF.movedPost = function (postIds, oldTid, newTid, oldCid, newCid, oldTopicTitle) {

        this.postIds = postIds;
        this.postIdOldTid = null;
        this.numPostsOldTid = 0;
        this.oldTid = oldTid;
        this.newTid = newTid;
        this.oldCid = oldCid;
        this.newCid = newCid;
        this.oldTopicTitle = oldTopicTitle;
    };

    $('.codo_posts').on('change', ".codo_posts_select_post", function () {

        multiselect.select(this);
    });

    $('#codo_multiselect_deselect').on('click', function () {

        multiselect.deselect();
    });

    //called whenever new posts are added from ajax
    CODOF.hook.add('after_posts_added', function () {

        multiselect.selectAnyUnchecked();
    });

    $('#codo_topics_multiselect_select').on('change', function () {

        var movedPost = JSON.parse(localStorage.getItem('movedPost'));
        var selectedTid;

        if (movedPost) {

            selectedTid = movedPost.oldTid;
        }

        if (this.value === 'move' && selectedTid === CODOFVAR.tid) {

            $('#codo_cannot_move_posts_same_topic').modal();
            return false;
        }

        if (this.value === 'move') {


            var movedPost = JSON.parse(localStorage.getItem('movedPost'));
            $('#codo_move_posts_confirm_number').html(movedPost.postIds.length);
            $('.codo_move_posts_confirm_old_topic').html(CODOF.util.generatePostLink(movedPost.oldTid, null, movedPost.oldTopicTitle));
            $('#codo_move_posts_confirm_new_topic').html(CODOF.util.generatePostLink(CODOFVAR.tid, null, CODOFVAR.title));

            if (movedPost.postIds.indexOf(movedPost.postIdOldTid) > -1) {

                //post ids contain topic
                if (movedPost.postIds.length === movedPost.numPostsOldTid) {

                    //only post is being moved
                    $('#codo_move_posts_confirm_deleting_old_topic').show();

                } else {

                    $('#codo_move_posts_confirm_post_topic').html(CODOF.util.generatePostLink(movedPost.oldTid, movedPost.postIdOldTid, movedPost.oldTopicTitle));
                    $('#codo_move_posts_confirm_moving_main_post').show();
                }
            }

            $('#codo_move_posts_confirm').modal();

        }

    });

    $('#codo_check_new_posts_modal_btn_yes').on('click', function () {

        var movedPost = new CODOF.movedPost([], CODOFVAR.tid, null, CODOFVAR.cid, null, CODOFVAR.title);
        localStorage.setItem('movedPost', JSON.stringify(movedPost));
        $('#codo_posts_select_' + multiselect.checkedPostId).prop('checked', true);

        multiselect.select(document.getElementById('codo_posts_select_' + multiselect.checkedPostId));
    });

    $('#codo_multiselect_show_selected').on('click', function () {

        var movedPost = JSON.parse(localStorage.getItem('movedPost'));

        $('#codo_check_selected_posts_modal_title').html(CODOF.util.generatePostLink(movedPost.oldTid, null, movedPost.oldTopicTitle));

        var posts = movedPost.postIds;
        var tid = movedPost.oldTid;
        var lis = "", url;

        for (var i = 0; i < posts.length; i++) {

            url = CODOF.util.generatePostUrl(tid, posts[i]);
            lis += "<li><a href='" + url + "'>" + url + "</a>"
        }

        $('#codo_check_new_posts_modal_list').html(lis);

        $('#codo_check_show_selected_posts_modal').modal();
    });

    $('#codo_move_posts_confirm_yes').on('click', function () {

        $('#codo_move_posts_confirm .codo_load_more_bar_blue_gif').show();
        multiselect.movePosts(function () {

            window.location.reload();
        });
    });

    window.multiselect = {
        checkedPostId: null,
        select: function (el) {

            var pid = parseInt(el.id.replace('codo_posts_select_', ''));
            var movedPost = JSON.parse(localStorage.getItem('movedPost'));

            if (!movedPost) {

                movedPost = new CODOF.movedPost([], CODOFVAR.tid, null, CODOFVAR.cid, null, CODOFVAR.title);
                movedPost.postIdOldTid = CODOFVAR.post_id;
                movedPost.numPostsOldTid = CODOFVAR.num_posts;
            } else if (CODOFVAR.tid !== movedPost.oldTid) {

                $('#codo_check_new_posts_modal').modal();
                $('#codo_check_new_posts_modal_title').html(CODOF.util.generatePostLink(movedPost.oldTid, null, movedPost.oldTopicTitle));

                this.checkedPostId = pid;
                $(el).prop('checked', false);
                return false;
            }

            if (el.checked) {

                movedPost.postIds.push(pid);
            } else {

                var index = movedPost.postIds.indexOf(pid);
                if (pid === CODOFVAR.post_id) {

                    movedPost.postIdOldTid = 0;
                }

                if (index > -1) {

                    movedPost.postIds.splice(index, 1);
                }
            }

            var len = movedPost.postIds.length;

            $('#codo_number_selected').html(len);

            if (len > 0) {

                $('#codo_topics_multiselect').show();
            } else {

                $('#codo_topics_multiselect').hide();
            }

            localStorage.setItem('movedPost', JSON.stringify(movedPost));
        },
        deselect: function () {

            $('.codo_posts .codo_posts_select_post').prop('checked', false);
            $('#codo_topics_multiselect').hide();
            localStorage.setItem('movedPost', null);
        },
        selectAnyUnchecked: function () {

            var movedPost = JSON.parse(localStorage.getItem('movedPost'));
            var checkedCounter = 0;
            if (movedPost) {

                var postId, numPosts = movedPost.postIds.length;

                for (var i = 0; i < numPosts; i++) {

                    postId = movedPost.postIds[i];

                    $('#codo_posts_select_' + postId).prop('checked', true);
                    checkedCounter++;
                }

                if (checkedCounter) {

                    $('#codo_number_selected').html(checkedCounter);
                    $('#codo_topics_multiselect').show();
                }
            }
        },
        movePosts: function (callback) {


            var movedPost = JSON.parse(localStorage.getItem('movedPost'));
            movedPost.newCid = CODOFVAR.cid;
            movedPost.newTid = CODOFVAR.tid;

            jQuery.post(codo_defs.url + 'Ajax/posts/move', {
                token: codo_defs.token,
                movedPost: movedPost
            }, function (resp) {

                if (resp === 'move access denied') {

                    $('#codo_load_more_bar_blue_gif').hide();
                    $('#codo_move_posts_confirm').modal('hide');
                    $('#codo_cannot_move_posts_this_topic').modal();
                } else if (resp === 'access denied') {

                    $('#codo_load_more_bar_blue_gif').hide();
                    $('#codo_move_posts_confirm').modal('hide');
                    alert("You do not have permission to delete the old topic");
                } else {

                    localStorage.clear();
                    callback();
                }
            });

        }
    };

    //initially check any posts from localstorage
    multiselect.selectAnyUnchecked();

    $('.poll_option').on('click', function () {

        $('.poll_option_selected').removeClass('poll_option_selected');
        $(this).addClass('poll_option_selected');
        $('#codo_poll_vote_btn').addClass('codo_btn_blue');
    });

    $('#codo_poll_vote_btn').on('click', function () {

        var selected = $('.poll_option_selected');

        if (selected.length === 0) {

            CODOF.util.alert("Select an option first!", "Cannot vote");
            return false;
        }

        var poll_id = $('.poll_container').attr('id').replace("poll_", "");
        var option_id = selected.attr('id').replace("poll_option_", "");

        $('#codo_vote_loading').show();

        $.post(codo_defs.url + "Ajax/poll/vote/" + poll_id + "/" + option_id, {
            token: codo_defs.token
        }, function () {

            $('#codo_vote_loading').hide();
            window.location.reload();
        });
    });

    $('#codo_poll_revote_btn').on('click', function () {

        $('.poll_result').slideToggle();
        $('.poll_vote').slideToggle();
    });


    $('#codo_poll_view_result_btn').on('click', function () {

        $('.poll_result').slideToggle();
        $('.poll_vote').slideToggle();
    });

});


function codo_smooth_scroll() {

    var id = window.location.hash;

    //is hashbang?
    if(id.indexOf("!") > -1) return false;

    var div = jQuery(id);
    if (div.length > 0) {

        var origBg = div.css('background');

        jQuery('html, body').animate({
            scrollTop: div.offset().top,
        }, 500, function () {

            div.css('background', '#ffff99');
            setTimeout(function () {
                div.css('background', origBg);
            }, 2000);
        });
    }

    return false;
}

CODOF.globalSearch = function (val) {

    CODOF.search_data = {
        str: val,
        cats: CODOFVAR.catid,
        match_titles: 'Yes',
        sort: 'post_created',
        order: 'Desc',
        search_within: 'anytime',
        get_page_count: 'yes'
    };

    $('.codo_topics_pagination').remove();
    $('#codo_no_topics_display').hide();

    CODOF.topic_creator.search_switch = true;
    CODOF.topic_creator.refresh();
}

codo_smooth_scroll(); //call once after html is loaded
window.onhashchange = function () {

    codo_smooth_scroll();
};

