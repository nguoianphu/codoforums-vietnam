/*
 * @CODOLICENSE
 */

jQuery('document').ready(function ($) {

    CODOF.templateLoaded = false;

    //hide all sub categories of parent categories with show_children 0
    $('.codo_category_toggle').parent().parent().find('ul:first').hide();

    $('.codo_category_toggle').on('click', function () {

        $(this).parent().parent().find('ul:first').slideToggle();
    });

    $.ajaxSetup({cache: true});
    $.getScript('//connect.facebook.net/en_US/sdk.js', function () {
        FB.init({
            appId: '633890129979455',
            version: 'v2.7' // or v2.1, v2.2, v2.3, ...
        });
    });

    /**
     * The icon above the topics trigger this method
     * It will  toggle the visibility of topics and
     * categories
     */
    CODOF.toggleTopicsAndCategories = function () {

        $('.codo_topics').toggle();
        $('.codo_categories').toggle();
        $('#codo_topics_load_more').toggle();
        $('#codo_mobile_top_search').toggle();
        CODOF.util.simpleNotify($('#icon-books-click-trans').text());
    };


    CODOF.topics = {
        body: $('.codo_topics_body'),
        articles: $('.codo_topics_body > article'),
        img_shown: false,
        req_started: false,
        has_built_topics: false,
        built_topics: '',
        from: parseInt(CODOFVAR.num_posts_per_page),
        ended: false,
        active: false,
        selected_topics: [],
        build_topics: function (context) {


            this.built_topics = CODOF.template(context);
            return this.built_topics;
        },
        reading_time: [],
        insert: function () {

            if (CODOF.topics.has_built_topics) {

                var page = CODOF.topics.from / CODOFVAR.num_posts_per_page;
                var canShowLoadMore = !CODOF.infiniteScrolling;

                if (page > 1) {


                    var pageInfo = $('#codo_topic_page_info').clone();
                    var pagesToGo = Math.floor(CODOFVAR.total / CODOFVAR.num_posts_per_page) - (page - 1);

                    if (pagesToGo === 0) {

                        canShowLoadMore = false;
                    }

                    pageInfo.find('#codo_page_info_time_spent')
                        .html(CODOF.topics.reading_time[page - 1] + ' s');
                    pageInfo.find('#codo_page_info_page_no')
                        .html(page);
                    pageInfo.find('#codo_page_info_pages_to_go')
                        .html((pagesToGo > 0) ? pagesToGo : CODOFVAR.last_page);

                    pageInfo.appendTo(CODOF.topics.body);

                    pageInfo.find('span').tooltip({container: 'body'});

                    CODOF.topics.reading_time[page] = 0;
                    CODOF.topics.page_being_read = page;

                    $('.codo_page_' + page).mouseenter(function () {

                        if (!CODOF.topics.reading_timers[page]) {

                            CODOF.topics.reading_timers[page] = setInterval(function () {

                                CODOF.topics.reading_time[page]++;
                            }, 1000);
                        }
                        CODOF.mouseInsideTopic = true;

                    }).mouseleave(function () {

                        clearInterval(CODOF.topics.reading_timers[page]);
                        CODOF.topics.reading_timers[page] = false;
                        CODOF.mouseInsideTopic = false;

                    });
                }

                CODOF.topics.body.append(this.built_topics);

                CODOF.topics.has_built_topics = false;
                CODOF.req_started = false;
                //after inserting remove the loader image
                $('.codo_load_more_gif').remove();
                CODOF.img_shown = false;

                if (canShowLoadMore) {
                    $('#codo_topics_load_more').show();
                }
                //$('.codo_oembed').oembed(null, {placeholder: false, embedMethod: 'fill'});
            }
        },
        cInsert: function (load) {

            //load if infinite scrolling is true or if forced by passing load=true
            load = load || CODOF.infiniteScrolling;

            if (!CODOF.img_shown && !CODOF.topics.ended && load) {
                CODOF.topics.body.append("<div class='codo_load_more_gif'></div>");
                CODOF.img_shown = true;
                $('#codo_topics_load_more').hide();
                if (CODOF.topics.has_built_topics) {

                    //has loaded topics before reaching bottom
                    CODOF.topics.insert();
                }
            }

            if (CODOF.topics.ended) {

                CODOF.ui.saccade(CODOF.topics.end);
            }

        },
        fetch: function () {

            if (!CODOF.templateLoaded) {

                CODOF.fetchTopics = CODOF.topics.fetch;
                return false;
            }

            if (!CODOF.req_started) {

                CODOF.req_started = true;

                var type = "newest";

                CODOF.req.data = {
                    from: CODOF.topics.from,
                    type: $('#page_sort_option').val(),
                    token: codo_defs.token
                };

                CODOF.req.url = 'Ajax/topics/get_topics';

                CODOF.hook.call('before_req_fetch_topics', {}, function () {

                    $.getJSON(
                        codo_defs.url + CODOF.req.url,
                        CODOF.req.data,
                        function (response) {

                            if (response.num_posts) {

                                CODOF.topics.from += response.num_posts;
                                if (response.num_pages > 0) {

                                    CODOFVAR.total = response.num_pages;
                                }

                                //build the next N results but don't show them yet
                                //because we don't know if user will scroll to
                                //bottom or not
                                CODOF.context = response;
                                CODOF.topics.build_topics(CODOF.context);
                                CODOF.topics.has_built_topics = true;

                                if (CODOF.img_shown) {

                                    //this means user has reached end of page
                                    //so you can now show the next N results
                                    CODOF.topics.insert();
                                }

                            }

                            if (response.topics.length === 0 && !CODOF.topics.ended) {

                                if (CODOFVAR.total > 0) {

                                    if ($('#codo_topics_body .codo_topics_topic_message').length > 0) {
                                        CODOF.topics.body.append('<article class="codo_topics_end">' + CODOFVAR.no_more_posts + '</article>');
                                    } else {
                                        CODOF.topics.body.append('<article class="codo_topics_end">' + CODOFVAR.no_posts + '</article>');
                                    }
                                    $('#codo_topics_load_more').hide();
                                }
                                CODOF.topics.end = $('.codo_topics_end');
                                CODOF.topics.end.fadeIn('slow');
                                CODOF.topics.ended = true;
                            }

                        }
                    );

                });

            }

        }
    };

    CODOF.hide_msg_switch = 'off';
    setTimeout(function () {
        CODOF.getTemplateData('forum/topics');
    }, 100);

    //if (!CODOF.context.topics.length) {
    $('#codo_no_topics_display').show();
    //}


    CODOF.topics.reading_time[1] = 0;
    CODOF.topics.reading_timers = [];
    CODOF.topics.page_being_read = 1;
    $('.codo_page_1').mouseenter(function () {

        if (!CODOF.topics.reading_timers[1]) {

            CODOF.topics.reading_timers[1] = setInterval(function () {

                CODOF.topics.reading_time[1]++;
            }, 1000);
        }
        CODOF.mouseInsideTopic = true;

    }).mouseleave(function () {

        clearInterval(CODOF.topics.reading_timers[1]);
        CODOF.topics.reading_timers[1] = false;

        CODOF.mouseInsideTopic = false;

    });

    $('#codo_categories_ul').mouseenter(function () {

        CODOF.inside_categories_container = true;
    }).mouseleave(function () {

        CODOF.inside_categories_container = false;
    });

    //$('.codo_badge_new').show('slow');

    $('.codo_categories_category').on('click', function () {

        var href = $(this).find('a').attr('href');
        if (href)
            window.location.href = $(this).find('a').attr('href');
    });

    $('.codo_category_img').on('click', function () {

        $(this).next().trigger('click');
    });

    $('#codo_topics_body').on('click', ".codo_posts_trash_post", function () {

        CODOF.moderation.confirm_delete(this);
        return false;
    });

    $('#codo_topics_body').on('click', ".codo_posts_report_post", function () {

        $('#codo_report_topic').modal();
        CODOF.reported_topic_id = this.id.replace('codo_posts_report_', '');
        return false;
    });

    $('#codo_topics_body').on('click', '.codo_fb_share', function () {


        var tid = $(this).data('tid');
        var url = codo_defs.url + "topic/" + tid;

        FB.ui({
            method: 'share',
            href: url,
        }, function (response) {
        });

    });

    $('#codo_report_select').on('change', function () {

        if (this.value == '3') {

            $('#report_reason').show();
        } else {

            $('#report_reason').hide();
        }
    });

    CODOF.report_topic = function () {


        var tid = CODOF.reported_topic_id;
        var type = $('#codo_report_select').val();


        $('.codo_loading').show();
        jQuery.post(codo_defs.url + 'Ajax/topic/report', {
            token: codo_defs.token,
            tid: tid,
            type: type,
            details: $('#report_details').val()
        }, function (resp) {

            $('.codo_loading').hide();
            $('#codo_report_topic').modal('hide');
        });
    };


    $('#codo_topics_body').on('change', ".codo_posts_select_post", function () {

        var tid = this.id.replace('codo_posts_select_', '');

        if (this.checked) {

            CODOF.topics.selected_topics.push(tid);
        } else {

            var index = CODOF.topics.selected_topics.indexOf(tid);
            if (index > -1) {

                CODOF.topics.selected_topics.splice(index, 1);
            }
        }

        var len = CODOF.topics.selected_topics.length;

        $('#codo_number_selected').html(len);

        if (len > 0) {

            $('#codo_topics_multiselect').show();
        } else {

            $('#codo_topics_multiselect').hide();
        }

        if (len > 1) {

            $('#codo_topics_multiselect_select option[value=merge]').prop('disabled', false);
        } else {

            $('#codo_topics_multiselect_select option[value=merge]').prop('disabled', true);
        }
    });

    $('#codo_multiselect_deselect').on('click', function () {

        multiselect.deselect();
    });


    $('#codo_topics_multiselect_select').on('change', function () {


        var action = $(this).val();

        switch (action) {

            case 'delete':
                multiselect.show_delete_modal();
                break;

            case 'merge':
                multiselect.show_merge_modal();
                break;
            case 'move':
                multiselect.show_move_modal();
                break;

        }

    });


    multiselect = {
        deselect: function () {

            $('#codo_topics_body .codo_posts_select_post').prop('checked', false);
            $('#codo_topics_multiselect').hide();
            CODOF.topics.selected_topics = [];
        },
        show_delete_modal: function () {

            $('#codo_multiselect_delete').modal();

            var links = $('#codo_topics_body .codo_posts_select_post:checked').parents('article').find('.codo_topics_topic_title');
            var len = links.length, link;

            var text = "<ul>";

            for (var i = 0; i < len; i++) {

                link = links[i].innerHTML.replace("href=", "target='_blank' href=");

                text += "<li>" + link + "</li>";
            }

            text += "</ul>";

            $('#codo_multiselect_delete_links').html(text);
        },
        show_merge_modal: function () {

            $('#codo_multiselect_merge').modal();

            var text = "<form>", link, first = true, checked = 'checked';

            var links = $('#codo_topics_body .codo_posts_select_post:checked').parents('article').find('.codo_topics_topic_title')
                .each(function () {

                    var el = $(this);
                    link = el.html().replace("href=", "target='_blank' href=");

                    var id = el.children('a').prop('id').replace('codo_topic_link_', '');


                    text += "<div style='padding: 4px;'> <input " + checked + "  name='multiselect' id='multiselect_merge_" + id + "' style='margin:0;margin-right:5px;position:relative;top:2px' type='radio'/>" + link + "</div>";

                    if (first) {

                        first = false;
                        checked = '';
                    }

                });


            text += "</form>";

            $('#codo_multiselect_merge_links').html(text);
        },
        show_move_modal: function () {

            $('#codo_multiselect_move').modal();

        },
        delete_topics: function () {

            $('.codo_loading').show();
            jQuery.post(codo_defs.url + 'Ajax/topic/deleteAll', {
                token: codo_defs.token,
                isSpam: 'no',
                tids: CODOF.topics.selected_topics
            }, function (resp) {


                $('.codo_loading').hide();
                $('#codo_multiselect_delete').modal('hide');
            });
        },
        merge_topics: function () {

            var dest = $('#codo_multiselect_merge_links input:checked').prop('id').replace('multiselect_merge_', '');

            $('.codo_loading').show();
            jQuery.post(codo_defs.url + 'Ajax/topic/merge', {
                token: codo_defs.token,
                dest: dest,
                tids: CODOF.topics.selected_topics
            }, function (resp) {

                $('.codo_loading').hide();
                $('#codo_multiselect_merge').modal('hide');
                window.location.reload();
            });
        },
        move_topics: function () {

            var cid = $('#codo_multiselect_move_category_select').val();

            $('.codo_loading').show();
            jQuery.post(codo_defs.url + 'Ajax/topic/move', {
                token: codo_defs.token,
                dest: cid,
                tids: CODOF.topics.selected_topics
            }, function (resp) {

                $('.codo_loading').hide();
                $('#codo_multiselect_move').modal('hide');
                //window.location.reload();
            });

        }


    };

    $('#codo_topics_body').on('click', ".codo_posts_spam_post", function () {

        var tid = this.id.replace('codo_posts_spam_', '');


        return false;
    });

    $('.codo_posts_topic_delete').on('click', function () {

        return false;
    });

    $('#codo_topics_body').on('click', ".codo_posts_edit_post", function () {

        var id = parseInt(this.id.replace('codo_posts_edit_', ''));
        window.location.href = codo_defs.url + 'topic/' + id + '/edit';
    });

    CODOF.cache.sideBarMenu = {
        el: $('.codo_sidebar_fixed'),
        pos: 'static',
        top: 0
    };

    $('#codo_category_all_topics').on('click', function () {

        $('#codo_categories_ul').slideToggle(200);
        $(this).find('i').toggleClass('icon-arrow-up icon-arrow-down')
    });

    $('#codo_sidebar_hide_msg_switch').on({
        switch_on: function () {

            CODOF.hide_msg_switch = 'on';
            localStorage.setItem('hide_msg_switch', 'on');
            $('#codo_topics_body').find('.codo_topics_topic_message').addClass('hide');
            $('article').addClass('article_msg_hidden');
        },
        switch_off: function () {

            CODOF.hide_msg_switch = 'off';
            localStorage.setItem('hide_msg_switch', 'off');
            $('#codo_topics_body').find('.codo_topics_topic_message').removeClass('hide');
            $('article').removeClass('article_msg_hidden');
        }
    });

    var hide_msg_switch = localStorage.getItem('hide_msg_switch');

    if (hide_msg_switch == 'on') {

        $('#codo_sidebar_hide_msg_switch').trigger('switch_on').trigger('click');
    } else {

        $('#codo_sidebar_hide_msg_switch').trigger('switch_off');
    }

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

    (CODOF.applySideBarPosition = function () {

        if (CODOF.cache.sideBarMenu.top && CODOF.cache.sideBarMenu.top < $(window).scrollTop()) {

            if (CODOF.cache.sideBarMenu.pos === 'static') {

                CODOF.cache.sideBarMenu.el.css({
                    // position: 'fixed',
                    top: '60px'
                })
                    .addClass('codo_sidebar_fixed_width').removeClass('codo_sidebar_static_width')
                    .find('.codo_sidebar_fixed_els').show();

                if (CODOF.cache.sideBarMenu.el.is(':visible'))
                    CODOF.cache.sideBarMenu.el.css('width', (CODOF.cache.sideBarMenu.el.parent().innerWidth() - 60) + 'px');
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
    })();


    $(window).scroll(function () {


        var offset = 500;
        if ($(window).scrollTop() + offset > $(document).height() - $(window).height()) {

            //request and get data before theu user even reaches end of page
            CODOF.topics.fetch();
        }
        if ($(window).scrollTop() >= $(document).height() - $(window).height()) {

            CODOF.topics.cInsert();
        }

        CODOF.applySideBarPosition();

        if (!CODOF.readScrollTimeout && !CODOF.mouseInsideTopic) {
            CODOF.readScrollTimeout = setTimeout(function () {

                CODOF.topics.reading_time[CODOF.topics.page_being_read]++;
                CODOF.readScrollTimeout = false;
            }, 1000);
        }
    });


    $('#codo_topics_load_more_btn').on('click', function () {

        CODOF.topics.cInsert(true);
        return false; //prevent link 
    });

    /*CODOF.getTagged = function(el) {
     
     
     var tag = el.innerHTML;
     
     CODOF.topics.from = 0; //reset page no.
     CODOF.topics.insert();
     $('#codo_topics_body').html('');
     CODOF.topics.body.append("<div class='codo_load_more_gif'></div>");
     CODOF.img_shown = true;
     CODOF.topics.ended = false;
     
     CODOF.hook.add('before_req_fetch_topics', function() {
     
     CODOF.req.url = 'Ajax/tags/' + tag;
     });
     
     CODOF.topics.fetch();
     
     return false;
     };
     
     
     */

    CODOF.globalSearch = function (val, sort_on) {

        if ($.trim(val) == "")
            return;

        if (typeof sort_on === "undefined")
            sort_on = "post_created"

        $('#codo_topics_load_more').hide();
        var data = {
            str: val,
            cats: '',
            search_subcats: 'Yes',
            match_titles: 'Yes',
            sort: sort_on,
            order: 'Desc',
            search_within: 'anytime'

        };

        CODOF.topics.from = 0; //reset page no.
        CODOF.topics.insert();
        $('#codo_topics_body').html('');
        CODOF.topics.body.append("<div class='codo_load_more_gif'></div>");
        CODOF.img_shown = true;
        CODOF.topics.ended = false;

        CODOF.search_data = data;
        CODOF.topics.fetch();
    }


    CODOF.search_data = {};

    CODOF.hook.add('before_req_fetch_topics', function () {
        CODOF.req.url = 'Ajax/topics/get_topics';
        $.extend(CODOF.req.data, CODOF.search_data);
    });


    $('#codo_category_select li').on('click', function () {

        window.location = codo_defs.url + "category/" + $(this).find('a').data('alias');
    });

    $('#codo_mark_all_read').click(function () {


        $('.codo_new_topics_count').fadeOut();
        $('.codo_badge_new').fadeOut();
        $('.codo_unread_replies').fadeOut();

        $(this).hide();

        $.get(codo_defs.url + 'Ajax/topics/mark_read', {token: codo_defs.token});

        return false;
    });


    $('#page_sort_option').on('change', function () {

        var $el = $(this);

        if ($el.val() === "popular") {

            $('.codo_topics_title_header_left').html($('#most-popular-txt-trans').html());
        } else if ($el.val() === "newest") {

            $('.codo_topics_title_header_left').html($('#newest-txt-trans').html());
        }
        if ($el.val() === "commented") {

            $('.codo_topics_title_header_left').html($('#most-commented-txt-trans').html());
        }


        CODOF.topics.from = 0; //reset page no.
        $('#codo_topics_body').html('');
        CODOF.topics.body.append("<div class='codo_load_more_gif'></div>");
        CODOF.img_shown = true;
        CODOF.topics.ended = false;
        CODOF.req_started = false;
        CODOF.topics.fetch();
    });


    $('body').on({
        click: function () {

            var $me = $(this);
            var $repContainer = $me.parent();

            var rep_counter = $repContainer.find('.codo_reputation_points');
            var prev_count = rep_counter.html();
            rep_counter.html('-');

            var pid = $repContainer.attr('id').replace('codo_posts_rep_', '');
            var tid = $repContainer.data('tid');

            CODOF.request.get({
                hook: 'post_rep_up',
                url: codo_defs.url + 'Ajax/reputation/' + tid + '/' + pid + '/up',
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

            var $me = $(this);
            var $repContainer = $me.parent();

            var rep_counter = $repContainer.find('.codo_reputation_points');
            var prev_count = rep_counter.html();
            rep_counter.html('-');

            var pid = $repContainer.attr('id').replace('codo_posts_rep_', '');
            var tid = $repContainer.data('tid');


            CODOF.request.get({
                hook: 'post_rep_up',
                url: codo_defs.url + 'Ajax/reputation/' + tid + '/' + pid + '/down',
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


    /**
     * Changes the page to previous or next based on current page
     * @param {type} el
     * @param {type} currPage
     * @param {type} action
     * @returns {Boolean}
     */
    CODOF.changePage = function (el, currPage, action) {

        var $el = $(el);

        if (!$el.hasClass("active_page_controls"))
            return false;

        var nextPage = currPage + 1;

        if (action === 'prev')
            nextPage = currPage - 1;

        var searchStr = '';
        var searchVal = $('.codo_global_search_input').val();
        if (searchVal !== '') {

            searchStr = '&str=' + searchVal;
        }

        var url = codo_defs.url + 'topics/' + nextPage + searchStr;

        window.location.href = url;
    };

});
