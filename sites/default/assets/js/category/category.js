/*
 * @CODOLICENSE
 */

'use strict';

jQuery('document').ready(function ($) {

    if ($('#datetimepicker').length > 0) {
        $datepicker = $('#datetimepicker').pickadate();
        CODOF.datepicker = $datepicker.pickadate('picker');

        $('#topic_auto_close').on('switch_on', function () {

            $('#content_toggle_topic_auto_close').show();
        }).on('switch_off', function () {

            $('#content_toggle_topic_auto_close').hide();
        });
    }

    CODOF.editor_form = $('#codo_new_topic_form');
    CODOF.editor_preview_btn = $('#codo_post_preview_btn');
    CODOF.editor_reply_post_btn = $('.codo_new_reply_action_post');
    CODOF.topic_creator = {
        container: $('#codo_topics_create'),
        widget: $('#codo_topics_create > .codo_widget'),
        textarea: $('#codo_topic_desc'),
        desc_div: $('#codo_topic_desc_div'),
        title: $('#codo_topic_title'),
        onfocus: $('.codo_topics_on_focus_show'),
        from: parseInt(CODOFVAR.curr_page) + 1,
        has_built_topics: false,
        ended: false,
        body: $('#codo_topics_list'),
        unique: 1,
        containers: 2,
        animate_top: 200,
        search_data: {},
        search_switch: false,
        build_topic: function (context) {

            this.built_topics = CODOF.template(context);
            return this.built_topics;
        },
        reading_time: [],
        insert: function () {

            if (CODOF.topic_creator.has_built_topics) {

                var page = CODOF.topic_creator.from - 1; /// CODOFVAR.num_posts_per_page;
                if (page > 1) {
                    var pageInfo = $('#codo_topic_page_info').clone();
                    var pagesToGo = Math.floor(CODOFVAR.total / CODOFVAR.num_posts_per_page) - (page - 1);
                    pageInfo.find('#codo_page_info_time_spent')
                            .html(CODOF.topic_creator.reading_time[page - 1] + ' s');
                    pageInfo.find('#codo_page_info_page_no')
                            .html(page);
                    pageInfo.find('#codo_page_info_pages_to_go')
                            .html((pagesToGo > 0) ? pagesToGo : CODOFVAR.last_page);

                    pageInfo.appendTo(CODOF.topic_creator.body);

                    pageInfo.find('span').tooltip({container: 'body'});

                    CODOF.topic_creator.reading_time[page] = 0;
                    CODOF.topic_creator.page_being_read = page;

                    $('.codo_page_' + page).mouseenter(function () {

                        if (!CODOF.topic_creator.reading_timers[page]) {

                            CODOF.topic_creator.reading_timers[page] = setInterval(function () {

                                CODOF.topic_creator.reading_time[page]++;
                            }, 1000);
                        }
                        CODOF.mouseInsideTopic = true;

                    }).mouseleave(function () {

                        clearInterval(CODOF.topic_creator.reading_timers[page]);
                        CODOF.topic_creator.reading_timers[page] = false;
                        CODOF.mouseInsideTopic = false;

                    });
                }


                CODOF.topic_creator.body.append(this.built_topics);

                CODOF.topic_creator.has_built_topics = false;
                CODOF.req_started = false;
                //after inserting remove the loader image
                $('.codo_load_more_gif').remove();
                CODOF.img_shown = false;
                if (!CODOF.infiniteScrolling) {
                    $('#codo_topics_load_more').show();
                }
                //$('.codo_oembed').oembed(null, {placeholder: false, embedMethod: 'fill'});

            }
        },
        cInsert: function (load) {

            //load if infinite scrolling is true or if forced by passing load=true
            load = load || CODOF.infiniteScrolling;

            if (!CODOF.img_shown && !CODOF.topic_creator.ended && load) {

                CODOF.topic_creator.body.append("<div class='codo_load_more_gif'></div>");
                CODOF.img_shown = true;
                $('#codo_topics_load_more').hide();
                if (CODOF.topic_creator.has_built_topics) {

                    //has loaded topics before reaching bottom
                    CODOF.topic_creator.insert();
                }
            }

            if (CODOF.topic_creator.ended) {

                CODOF.ui.saccade(CODOF.topic_creator.end);
            }

        },
        fetch: function (search_mode) {

            if (typeof search_mode === "undefined") {

                search_mode = false;
            }

            CODOF.req.data = {
                page: CODOF.topic_creator.from,
                cat_alias: CODOFVAR.cat_alias,
                catid: CODOFVAR.cid,
                token: codo_defs.token
            };
            if (CODOF.topic_creator.search_switch) {

                $('#codo_upper_container').append("<div class='codo_load_more_gif'></div>");
                CODOF.topic_creator.search_switch = false;
            }


            CODOF.hook.call('before_req_fetch_topics', {}, function () {

                $.getJSON(
                        codo_defs.url + 'Ajax/category/get_topics',
                        CODOF.req.data,
                        function (response) {

                            if (response.topics.length > 0) {

                                var html, constants = [];
                                if (response.num_pages !== 'not_passed') {

                                    CODOFVAR.total = response.num_pages;
                                    CODOF.req.data.get_page_count = 'no';
                                }


                                CODOF.topic_creator.build_topic(response);
                                CODOF.topic_creator.has_built_topics = true;
                                //CODOF.topic_creator.add_readmore(CODOF.topic_creator.unique);
                                //CODOF.topic_creator.unique++;


                                CODOF.req_started = false;
                                CODOF.topic_creator.from++; //next page

                                if (CODOF.img_shown) {

                                    //this means user has reached end of page
                                    //so you can now show the next N results
                                    CODOF.topic_creator.insert();
                                }

                            }

                            if (response.topics.length === 0 && !CODOF.topic_creator.ended) {

                                if (CODOFVAR.total > 0) {

                                    if ($('#codo_topics_body .codo_topics_topic_message').length > 0) {
                                        CODOF.topic_creator.body.append('<article class="codo_topics_end">' + CODOFVAR.no_more_posts + '</article>');
                                    } else {
                                        CODOF.topic_creator.body.append('<article class="codo_topics_end">' + CODOFVAR.no_posts + '</article>');
                                    }
                                }
                                CODOF.topic_creator.end = $('.codo_topics_end');
                                CODOF.topic_creator.end.fadeIn('slow');
                                CODOF.topic_creator.ended = true;
                                $('#codo_topics_load_more').hide();

                            }


                            $('.codo_load_more_gif').remove();
                            CODOF.hook.call('after_topics_fetched');
                        }
                );
            }
            );
        },
        refresh: function () {


            //$('#codo_upper_container > article').remove();
            $('#codo_topics_list').html('');

            CODOF.topic_creator.body.append("<div class='codo_load_more_gif'></div>");
            CODOF.img_shown = true;
            CODOF.topic_creator.ended = false;
            CODOF.req_started = true;
            //reassign ids from first
            this.unique = 1;
            //set page 1
            this.from = 1;
            //fetch all posts/topics
            this.fetch(true);
        }

    };


    CODOF.hide_msg_switch = 'off';
    CODOF.getTemplateData('forum/category');

    CODOF.topic_creator.reading_time[1] = 0;
    CODOF.topic_creator.reading_timers = [];
    CODOF.topic_creator.page_being_read = 1;
    $('.codo_page_1').mouseenter(function () {

        if (!CODOF.topic_creator.reading_timers[1]) {

            CODOF.topic_creator.reading_timers[1] = setInterval(function () {

                CODOF.topic_creator.reading_time[1]++;
            }, 1000);
        }
        CODOF.mouseInsideTopic = true;

    }).mouseleave(function () {

        clearInterval(CODOF.topic_creator.reading_timers[1]);
        CODOF.topic_creator.reading_timers[1] = false;

        CODOF.mouseInsideTopic = false;

    });

    $('#codo_sidebar_hide_msg_switch').on({
        switch_on: function () {

            CODOF.hide_msg_switch = 'on';
            $('#codo_topics_list').find('.codo_topics_topic_message').addClass('hide');
            $('article').addClass('article_msg_hidden');
        },
        switch_off: function () {

            CODOF.hide_msg_switch = 'off';
            $('#codo_topics_list').find('.codo_topics_topic_message').removeClass('hide');
            $('article').removeClass('article_msg_hidden');
        }
    });

    $('#codo_sidebar_inf_scroll_switch').on({
        switch_on: function () {

            CODOF.infiniteScrolling = true;
        },
        switch_off: function () {

            CODOF.infiniteScrolling = false;
            $('#codo_topics_load_more').show();
        }
    });

    CODOF.infiniteScrolling = false;


    Handlebars.registerHelper('msg', function () {

        return new Handlebars.SafeString(this.message.replace(/\n/g, "<br/>"));
    });


    if (!CODOF.helper) {
        CODOF.helper = {};
    }


    //Do not show description div if empty
    if ($.trim($('.codo_cat_desc').text()) === "") {

        $('.codo_cat_desc').css("display", "none");
    }

    jQuery('input[name=sticky]').on('change', function () {


        if (jQuery(this).is(':checked')) {

            jQuery('#show_frontpage').css('display', 'inline-block');
        } else {
            jQuery('#show_frontpage').hide();

        }
    });


    CODOF.topic_creator.category = $('.codo_categories');
    $('#codo_topic_desc_div').click(function () {

        if (codo_defs.logged_in === 'no') {

            window.location.href = CODOFVAR.login_url;
            return false;
        }


        $('#breadcrumb').hide();
        CODOF.topic_creator.onfocus.show();
        $('#codo_new_reply').show();
        CODOF.topic_creator.title.focus();
        CODOF.topic_creator.desc_div.hide();
        CODOF.topic_creator.textarea.show();
        CODOF.topic_creator.container.parent().addClass('codo_full_width').removeClass('col-md-8');

        $('.perm_sticky_auto_close, .perm_auto_close').show();


        $('#codo_topics_create').animate({
            marginLeft: "-6%",
            width: "100%"
        }, {
            duration: 500,
            specialEasing: {marginLeft: "easeInOutBack"},
            complete: function () {

                $(this).animate({marginLeft: 0}, 500);
                CODOF.cache.sideBarMenu.top = CODOF.cache.sideBarMenu.el.offset().top;
            }
        });
    });
    $('.codo_create_topic_cancel').click(function () {


        CODOF.topic_creator.container.parent().removeClass('codo_full_width').addClass('col-md-8');

        $('#breadcrumb').show();
        $('#codo_new_reply').hide();

        CODOF.topic_creator.onfocus.slideUp();

        $('.perm_sticky_auto_close, .perm_auto_close').hide();

        CODOF.topic_creator.category.animate({top: '200'}).animate({top: '0'}, {
            duration: 600,
            specialEasing: {top: "easeOutCubic"}
        });

        CODOF.topic_creator.title.val('');
        CODOF.topic_creator.desc_div.show();
        CODOF.topic_creator.textarea.hide().val('').css('height', 'auto');
        CODOF.topic_creator.container[0].style.height = "auto";
        CODOF.cache.sideBarMenu.top = CODOF.cache.sideBarMenu.el.offset().top;

        return false;
    });

    $('#codo_tags').tagsinput({
        maxTags: codo_defs.forum_tags_num,
        maxChars: codo_defs.forum_tags_len,
        trimValue: true
    });

    //tell the auto save draft that this is a topic save
    CODOF.inTopic = true;

    //tell the auto save draft the current category
    CODOF.draftCurrentCategory = CODOFVAR.cid;

    //tell the auto save draft that you are creating a topic
    CODOF.edit_topic_id = false;


    if ($('#codo_non_mentionable').length > 0) {
        var str = $('#codo_non_mentionable').html();
        $('#codo_non_mentionable').html(str.replace('%MENTIONS%', '<span id="codo_nonmentionable_users"></span>'));
    }
    
    CODOF.submitted = function () {

        //$('#codo_reply_replica').val($('#codo_new_reply_preview').html());

        var warned = false;
        if (CODOF.editor_reply_post_btn.hasClass('codo_btn_primary') && !CODOF.is_error()) {

            if (!warned) {

                if (CODOF.mentions.warnForNonMentions()) {

                    warned = true;
                    return false;
                }
            }

            CODOF.editor_reply_post_btn.removeClass('codo_btn_primary');
            $('#codo_new_reply_loading').show();

            $('#codo_reply_box').append('<div id="codo_reply_html_playground"></div>');

            $('#codo_reply_html_playground').html($('#codo_new_reply_preview').html());

            $('#codo_reply_html_playground .codo_embed_container').remove();
            $('#codo_reply_html_playground .codo_embed_placeholder').remove();


            $('#codo_reply_html_playground .codo_oembed').each(function () {

                var href = $(this).attr('href');
                $(this).html(href);
            });



            var title = $.trim($('#codo_topic_title').val());
            CODOF.req.data = {
                title: title,
                cat: CODOFVAR.cid,
                imesg: $('#codo_new_reply_textarea').val(),
                omesg: $('#codo_reply_html_playground').html().replace(/\</g, 'STARTCODOTAG'),
                end_of_line: $('.end-of-line').val(),
                tags: $('#codo_tags').tagsinput('items'),
                token: codo_defs.token,
                sticky: $('input[name=topic_status]:checked').length > 0 ? $('input[name=topic_status]:checked').val() : 'no',
                is_open: CODOF.switch.get('is_topic_open') ? 'yes' : 'no',
            };

            if ($('#datetimepicker').length === 0) {

                CODOF.req.data.is_auto_close = 'no';
                CODOF.req.data.auto_close_date = null;
            } else {

                CODOF.req.data.is_auto_close = CODOF.switch.get('topic_auto_close') ? 'yes' : 'no';
                CODOF.req.data.auto_close_date = CODOF.datepicker.get();
            }

            CODOF.hook.call('before_req_topic_create');
            $.post(
                    codo_defs.url + 'Ajax/topic/create',
                    CODOF.req.data,
                    function (response) {

                        var obj;
                        try {
                            obj = JSON.parse(response);
                        } catch (e) {
                            obj = response;
                        }
                        if (obj.tid) {

                            CODOF.autoDraft.remove();
                            window.location.href = codo_defs.url + 'topic/' + obj.tid + '/' + title;
                        } else {
                            alert(response);
                            CODOF.editor_reply_post_btn.addClass('codo_btn_primary');
                        }

                        $('#codo_new_topic_loader').hide();
                    }
            );
        }

        return false;
    };
    CODOF.is_error = function () {

        var error = false;
        $('#codo_new_topic_form :input[required=""],#codo_new_topic_form :input[required]').each(function () {

            var val = $(this).val();
            if ($.trim(val) === "") {

                $(this).addClass('boundary-error').focus();
                error = true;
                return false;
            } else {
                $(this).removeClass('boundary-error')
            }
        });
        return error;
    };
    $('#codo_category_topics').on('click', ".codo_posts_trash_post", function () {

        CODOF.moderation.confirm_delete(this);
        return false;
    });
    $('#codo_category_topics').on('click', ".codo_posts_edit_post", function () {

        var id = parseInt(this.id.replace('codo_posts_edit_', ''));
        //if ($(this).hasClass('codo_post_this_is_topic')) {

        window.location.href = codo_defs.url + 'topic/' + id + '/edit';
        //}
    });


    CODOF.cache.sideBarMenu = {
        el: $('.codo_sidebar_fixed'),
        pos: 'static',
        top: 0
    };

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

                //CODOF.cache.sideBarMenu.el.css('width', '100%');   
                CODOF.cache.sideBarMenu.pos = 'static';
                CODOF.cache.sideBarMenu.top = CODOF.cache.sideBarMenu.el.offset().top;
            }
        }
    })();

    $(window).scroll(function () {

        var offset = 500;
        if ($(window).scrollTop() + offset > $(document).height() - $(window).height()) {

            if (!CODOF.req_started && !CODOF.topic_creator.has_built_topics) {

                CODOF.req_started = true;
                CODOF.topic_creator.fetch();
            }
        }
        if ($(window).scrollTop() >= $(document).height() - $(window).height()) {

            CODOF.topic_creator.cInsert();
        }

        CODOF.applySideBarPosition();

        if (!CODOF.readScrollTimeout && !CODOF.mouseInsideTopic) {
            CODOF.readScrollTimeout = setTimeout(function () {

                CODOF.topic_creator.reading_time[CODOF.topic_creator.page_being_read]++;
                CODOF.readScrollTimeout = false;
            }, 1000);
        }


    });

    $('#codo_topics_load_more').on('click', function (e) {

        CODOF.topic_creator.cInsert(true);
        if (!CODOF.req_started && !CODOF.topic_creator.has_built_topics) {

            CODOF.req_started = true;
            CODOF.topic_creator.fetch();
        }

        return false; //prevent link 
    });

    CODOF.search_data = {};
    CODOF.hook.add('before_req_fetch_topics', function () {
        //CODOF.req.url = 'Ajax/topics/get_topics';
        $.extend(CODOF.req.data, CODOF.search_data);
    });

    function codo_create_filter(val) {

        $('#codo_topics_load_more').hide();
        CODOF.search_data = {
            str: val,
            cats: '',
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


    $('.codo_topics_search_icon').click(function () {

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
    });

    /*setTimeout(function() {
     $('#codo_search_keywords').focus();
     }, 10);*/

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
                    url: codo_defs.url + 'Ajax/subscribe/' + CODOFVAR.cid + "/" + value
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

        $('.codo_notification_block').css('visibility', 'visible').fadeIn('slow');

        $('.slider-selection').addClass('white-slider-selection');

    })();


    $('#codo_category_select li').on('click', function () {

        window.location = codo_defs.url + "category/" + $(this).find('a').data('alias');
    });

    $('#mark_all_read').click(function () {

        $('.codo_badge_new').fadeOut();
        $('.codo_category_options').fadeOut();

        $.get(codo_defs.url + 'Ajax/topics/mark_read/' + CODOFVAR.cid, {token: codo_defs.token});
    });

    $('.codo_categories_category').on('click', function () {

        var href = $(this).find('a').attr('href');
        if (href)
            window.location.href = $(this).find('a').attr('href');
    });

    $('.codo_category_img').on('click', function () {

        $(this).next().trigger('click');
    });

    $('#codo_breadcrumb_select').on('change', function () {

        var el = this;

        if (el.value !== '') {

            window.location = el.value;
        }
    });

    $('#codo_zero_topics').click(function () {

        $('#codo_topic_desc_div').trigger('click');
        return false;
    });

});
