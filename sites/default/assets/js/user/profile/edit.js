/*
 * @CODOLICENSE
 */

jQuery(document).ready(function ($) {

    // Javascript to enable link to tab
    /*var url = document.location.toString();
     if (url.match('#')) {
     $('.nav-tabs a[href=#' + url.split('#')[1] + ']').tab('show');
     }
     
     // Change hash for page-reload
     $('.nav-tabs a').on('shown', function (e) {
     window.location.hash = e.target.hash;
     });*/

    var hash = window.location.hash;
    hash && $('ul.nav a[href="' + hash + '"]').tab('show');

    $('.nav-tabs a').click(function (e) {
        $(this).tab('show');
        var scrollmem = $('body').scrollTop();
        window.location.hash = this.hash;
        $('html,body').scrollTop(scrollmem);
    });

    CODOF.gotoHashTab = function (customHash) {
        var hash = customHash || location.hash;
        var hashPieces = hash.split('?'),
                activeTab = $('[href=' + hashPieces[0] + ']');
        activeTab && activeTab.tab('show');
        window.location.hash = hash;
    };


    $('#codo_display_name').focus();

    $(document).keypress(function (e) {

        if (e.which === 13 && $('#confirm_pass').is(":focus")) {

            $('#change_pass').trigger('click');
        }
    });

    $('#change_pass').click(function () {

        CODOF.req.data = {
            curr_pass: $('#curr_pass').val(),
            new_pass: $('#new_pass').val(),
            confirm_pass: $('#confirm_pass').val(),
            token: codo_defs.token
        };

        CODOF.util.add_error_class_if_blank('curr_pass');
        CODOF.util.add_error_class_if_blank('new_pass');
        CODOF.util.add_error_class_if_blank('confirm_pass');

        var no_pass_txt = $('#codo_pass_no_match_txt');

        if (CODOF.req.data.new_pass !== CODOF.req.data.confirm_pass) {

            if (no_pass_txt.hasClass('codo_pass_no_match_txt_twice')) {

                CODOF.ui.saccade(no_pass_txt);
            }

            else if (no_pass_txt.hasClass('codo_pass_no_match_txt_again')) {

                no_pass_txt.addClass('codo_pass_no_match_txt_twice');
            }

            else if (no_pass_txt.is(":visible")) {

                no_pass_txt.addClass('codo_pass_no_match_txt_again');
            } else {

                no_pass_txt.show();
            }

            return false;
        }
        CODOF.hook.call('before_change_pass');

        $.post(
                codo_defs.url + 'Ajax/user/edit/change_pass',
                CODOF.req.data,
                function (response) {

                    CODOF.util.update_response_status(response, $('#profile_edit_status'), true);
                }, "json"
                );

        return false;
    });

    function calculate_len(textarea) {

        var len = textarea.val().length;

        var allowed_len = parseInt(CODOFVAR.signature_char_limit);

        var count = allowed_len - len;

        $('#codo_countdown_signature_characters').html(count);

    }

    $('#codo_signature_textarea').keyup(function () {

        calculate_len($(this));
    });

    function readURL(input) {

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#codo_avatar_preview').show().attr('src', e.target.result);
                $('#codo_right_arrow').show('slow');
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    $('#codo_avatar_file').change(function () {

        if (window.File && window.FileReader && window.FileList) {

            //can show file preview
            readURL($(this)[0]);
        } else {

            $('#codo_new_avatar_selected_name').html($(this).val().match(/[^\/\\]+$/)).show('slow');
        }



    });
    calculate_len($('#codo_signature_textarea'));
    CODOF.notification_levels = {
        on_create_topic: 0,
        on_reply_topic: 0
    };

    (CODOF.notify.selector = function () {

        var putText = function (value, id) {

            switch (value) {

                case 1:

                    $('#preferences #codo_notification_block_text' + id).html($('.codo_notification_block_muted').html());
                    break;
                case 2:

                    $('#preferences #codo_notification_block_text' + id).html($('.codo_notification_block_default').html());
                    break;

                case 3:

                    $('#preferences #codo_notification_block_text' + id).html($('.codo_notification_block_following').html());
                    break;

                case 4:

                    $('#preferences #codo_notification_block_text' + id).html($('.codo_notification_block_notified').html());

            }

            if (id === 1) {

                CODOF.notification_levels.on_create_topic = value;
            } else {

                CODOF.notification_levels.on_reply_topic = value;
            }

        };

        $('#preferences #codo_notification_selector1').slider()
                .on('slideStop', function (ev) {

                    putText(ev.value, 1);
                });

        $('#preferences #codo_notification_selector2').slider()
                .on('slideStop', function (ev) {

                    putText(ev.value, 2);
                });

        var defValue1 = $('#preferences #codo_notification_selector1').data('slider-value');
        putText(defValue1, 1);

        var defValue2 = $('#preferences #codo_notification_selector2').data('slider-value');
        putText(defValue2, 2);


        // Position the labels
        for (var i = 0; i <= 3; i++) {

            // Create a new element and position it with percentages
            var el = $('<label>' + (i) + '</label>').css('left', ((i / 3 * 100) - 1) + '%');

            // Add the element inside #slider
            $("#preferences .slider").append(el);

        }

        $('#preferences .slider-selection').addClass('white-slider-selection');

        $('#preferences .codo_notification_block_slider').addClass('col-sm-3');
        $('#preferences .codo_notification_block_desc').addClass('col-sm-6');
    }

    )();

    /**
     * 
     * Subscriptions tab
     */


    (CODOF.notify.selectorSubscriptions = function () {

        var putText = function (value, id, sendRequest) {

            var input = $(id);
            var block = input.parent().parent().parent().find('.codo_notification_block_desc span:first');

            switch (value) {

                case 1:

                    block.html($('.codo_notification_block_muted').html());
                    break;
                case 2:

                    block.html($('.codo_notification_block_default').html());
                    break;

                case 3:

                    block.html($('.codo_notification_block_following').html());
                    break;

                case 4:

                    block.html($('.codo_notification_block_notified').html());

            }


            if (typeof sendRequest === 'undefined') {

                if (input.data('iscategory') === 'yes') {
                    CODOF.request.get({
                        hook: 'update_notification_level',
                        url: codo_defs.url + 'Ajax/subscribe/' + input.data('cid') + "/" + value,
                        done: function () {

                            notify(CODOFVAR.trans.subscriptions.title, CODOFVAR.trans.subscriptions.text);
                        }
                    });
                } else {

                    CODOF.request.get({
                        hook: 'update_notification_level',
                        url: codo_defs.url + 'Ajax/subscribe/' + input.data('cid') + "/" + input.data('tid') + "/" + value,
                        done: function () {

                            notify(CODOFVAR.trans.subscriptions.title, CODOFVAR.trans.subscriptions.text);
                        }

                    });
                }
            }

        };

        $('#subscriptions .codo_notification_selector').slider()
                .on('slideStop', function (ev) {

                    putText(ev.value, this);
                });


        $('#subscriptions .codo_notification_selector').each(function () {

            var input = $(this);
            var defValue = input.data('slider-value');
            putText(defValue, input, false);

        });



        // Position the labels
        for (var i = 0; i <= 3; i++) {

            // Create a new element and position it with percentages
            var el = $('<label>' + (i) + '</label>').css('left', ((i / 3 * 100) - 1) + '%');

            // Add the element inside #slider
            $("#subscriptions .slider").append(el);

        }

        $('#subscriptions .slider-selection').addClass('white-slider-selection');

        $('#subscriptions .codo_notification_block_slider').addClass('col-sm-3');
        $('#subscriptions .codo_notification_block_desc').addClass('col-sm-6');
    }

    )();


    /**
     * 
     * Notifications tab
     * TODO: Remove duplicated code
     */

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {

        if (e.target.href.indexOf('#notifications') > -1) {

            var notifications = [];
            var last = CODOFVAR.lim_notifications;

            var showNotifications = function () {

                var source = $("#codo_inline_notifications_template").html();
                var template = Handlebars.compile(source);
                var context = {
                    objects: notifications,
                    url: codo_defs.url,
                    duri: codo_defs.duri,
                    caught_up: codo_defs.trans.notify.caught_up
                };
                var html = template(context);

                if (notifications.length === 0) {

                    $('#codo_all_notifications').html(html).show();
                } else {

                    $('#codo_all_notifications').html(html).show();
                }

                $('#codo_all_notifications .codo_inline_notification_el_rolled').tooltip();
                notifications = [];
            };

            $(window).scroll(function () {

                var offset = 10;
                if ($(window).scrollTop() + offset > $(document).height() - $(window).height()) {

                    CODOF.request.get({
                        hook: 'get_all_notifications',
                        url: codo_defs.url + 'Ajax/notifications/all',
                        data: {offset: last},
                        done: function (_notifications) {

                            last += CODOFVAR.lim_notifications;
                            var len = _notifications.length, notification, data,
                                    unique, location;

                            for (var i = 0; i < len; i++) {

                                notification = _notifications[i];
                                data = notification.data;
                                //unique = parseInt(data.tid);//"(" + data.actor.id + "," + data.tid + ")";

                                var link, unique;
                                if (data.tid) {

                                    //this is <v.3.7 notification so link needs to be built manually
                                    link = 'topic/' + data.tid + '/post-' + data.pid +
                                            '&page=from_notify&nid=' + notification.id + '/#post-' + data.pid;
                                    unique = parseInt(data.tid);

                                } else {

                                    link = data.link.replace("[NID]", notification.id);
                                    unique = parseInt(notification.status_link);
                                }
                                
                                notifications.push({
                                    created: notification.created,
                                    actor: data.actor,
                                    body: notification.body,
                                    is_read: notification.is_read,
                                    id: notification.id,
                                    unique: unique,
                                    title: data.label,
                                    link: link
                                    
                                });


                            }

                            if (notifications.length)
                                showNotifications();
                        }
                    });

                }
            });
            CODOF.request.get({
                hook: 'get_all_notifications',
                url: codo_defs.url + 'Ajax/notifications/all',
                done: function (_notifications) {

                    var len = _notifications.length, notification, data,
                            unique, location;

                    for (var i = 0; i < len; i++) {

                        notification = _notifications[i];
                        data = notification.data;

                        var link, unique;
                        if (data.tid) {

                            //this is <v.3.7 notification so link needs to be built manually
                            link = 'topic/' + data.tid + '/post-' + data.pid +
                                    '&page=from_notify&nid=' + notification.id + '/#post-' + data.pid;
                            unique = parseInt(data.tid);

                        } else {

                            link = data.link.replace("[NID]", notification.id);
                            unique = parseInt(notification.status_link);
                        }
                        
                        notifications.push({
                            created: notification.created,
                            actor: data.actor,
                            body: notification.body,
                            is_read: notification.is_read,
                            id: notification.id,
                            unique: unique,
                            title: data.label,
                            link: link
                            
                        });

                    }

                    showNotifications();
                }
            });
        }

    });


    /**
     * 
     * Preferences tab
     * 
     */

    $('#codo_form_user_preferences').submit(function () {

        return false;
    });

    $('#codo_update_preferences').on('click', function () {

        $('.codo_load_more_bar_blue_gif').show();

        var switch_result = function (id) {

            return $('#' + id).hasClass('codo_switch_on') ? 'yes' : 'no';
        };


        CODOF.request.post({
            hook: 'update_preferences',
            url: codo_defs.url + 'Ajax/user/profile/update_preferences',
            preventParallel: true,
            data: {
                notification_frequency: $('#codo_notification_frequency').val(),
                send_emails_when_online: switch_result('codo_send_emails_when_online'),
                real_time_notifications: switch_result('real_time_notifications'),
                desktop_notifications: switch_result('desktop_notifications'),
                notification_levels: CODOF.notification_levels
            },
            done: function () {

                $('.codo_load_more_bar_blue_gif').hide();
                notify(CODOFVAR.trans.preferences.title, CODOFVAR.trans.preferences.text);

            }
        });
    });

    var notify = function (title, txt) {

        var tickImg = codo_defs.def_theme + "img/tick.png";
        CODOF.notify.create({
            title: title,
            textBody: txt,
            icon: tickImg,
            notifyClick: false,
            timeout: 2000
        });
        CODOF.notify.show();
    };


});


