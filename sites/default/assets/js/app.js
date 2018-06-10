/*
 * @CODOLICENSE
 */

'use strict';
//Why MS ? ;)
if (!console) {

    console = {
        log: function () {
        }
    };
}

//backup
CF = CODOF;

jQuery.fn.visible = function (partial) {

    var $t = $(this),
        $w = $(window),
        viewTop = $w.scrollTop(),
        viewBottom = viewTop + $w.height(),
        _top = $t.offset().top,
        _bottom = _top + $t.height(),
        compareTop = partial === true ? _bottom : _top,
        compareBottom = partial === true ? _top : _bottom;
    return ((compareBottom <= viewBottom) && (compareTop >= viewTop));
};
//workaroud for $.browser for jQuery 1.9
jQuery.browser = {};
jQuery.browser.mozilla = /mozilla/.test(navigator.userAgent.toLowerCase()) && !/webkit/.test(navigator.userAgent.toLowerCase());
jQuery.browser.webkit = /webkit/.test(navigator.userAgent.toLowerCase());
jQuery.browser.opera = /opera/.test(navigator.userAgent.toLowerCase());
jQuery.browser.msie = /msie/.test(navigator.userAgent.toLowerCase());
codo_defs.get = function (key) {

    var id = "_codo_" + key;
    return jQuery('#' + id).html();
};
var CODOF = {
    oembed: {placeHolders: [], inProgress: false, mapLongUrls: {}},
    //Thanks to SO
    abbr_num: function (number, decPlaces) {

        // 2 decimal places => 100, 3 => 1000, etc
        decPlaces = Math.pow(10, decPlaces);
        // Enumerate number abbreviations
        var abbrev = ["k", "m", "b", "t"];
        // Go through the array backwards, so we do the largest first
        for (var i = abbrev.length - 1; i >= 0; i--) {

            // Convert array index to "1000", "1000000", etc
            var size = Math.pow(10, (i + 1) * 3);
            // If the number is bigger or equal do the abbreviation
            if (size <= number) {
                // Here, we multiply by decPlaces, round, and then divide by decPlaces.
                // This gives us nice rounding to a particular decimal place.
                number = Math.round(number * decPlaces / size) / decPlaces;
                // Handle special case where we round up to the next abbreviation
                if ((number == 1000) && (i < abbrev.length - 1)) {
                    number = 1;
                    i++;
                }

                // Add the letter for the abbreviation
                number += abbrev[i];
                // We are done... stop
                break;
            }
        }

        return number;
    },
    inc_num: function (id) {

        var div = jQuery('.' + id);
        var inc_no = parseInt(div.data('number')) + 1; //contains non-abbrev. no.
        var abbrev_no = CODOF.abbr_num(inc_no, 2);

        div.each(function () {
            var t = jQuery(this);

            t.fadeOut(function () {

                t.text(abbrev_no).fadeIn();
            })
        });
    },
    ret_pagination: function (curr_page, num_pages, constants) {

        var times = 5 + (curr_page - 2),
            cnt = 1,
            i;
        var pages = {
            page: []
        };
        num_pages = parseInt(num_pages);
        if (num_pages < times) {

            times = num_pages;
        }

        if (curr_page > 5) {

            pages.page.push({
                page: cnt,
                first: true
            });
            cnt += (curr_page - 4);
        }

        var active;
        for (i = cnt; i <= times; i++) {

            active = false;
            if (curr_page === i) {
                active = true;
            }

            pages.page.push({
                page: i,
                active: active
            });
        }

        if (num_pages > times) {
            pages.page.push({
                page: num_pages,
                last: true
            });
        }

        pages.constants = constants;
        return pages;
    },
    getTemplateData: function (tpl) {

        $.getJSON(codo_defs.url + 'template/' + tpl, function (response) {

            CODOF.templateLoaded = true;
            if (CODOF.fetchTopics) {

                CODOF.fetchTopics();
                CODOF.fetchTopics = false;
            }

            CODOF.template = Handlebars.compile(response.tpl);

            if (response.paginateTpl !== '') {
                CODOF.paginateTemplate = Handlebars.compile(response.paginateTpl);
            }

            Handlebars.registerHelper('const', function (str) {

                return new Handlebars.SafeString(response.data.const[str]);
            });
            Handlebars.registerHelper('i18n', function (str) {

                return new Handlebars.SafeString(response.data.i18n[str]);
            });
            Handlebars.registerHelper('hide', function (str) {

                if (CODOF.hide_msg_switch === 'on') {

                    if (str === 'hide_msg')
                        return new Handlebars.SafeString('hide');
                    else
                        return new Handlebars.SafeString('article_msg_hidden');

                } else
                    return '';
            });

            CODOF.hook.call('on_tpl_loaded');
        });
    },
    util: {
        simpleNotify: function (text, onlyOnce) {

            var onlyOnce = onlyOnce || false;

            if (onlyOnce && localStorage.getItem(onlyOnce) != null) return;
            if (onlyOnce) localStorage.setItem(onlyOnce, true);

            if ($('.simple_head_notify').length > 0) return;

            $('.CODOFORUM').append("<div class='simple_head_notify'>" + text + "</div>");

            setTimeout(function () {
                $('.simple_head_notify').slideUp();
            }, 2000);
        },
        alert: function (message, title) {

            if (typeof title === "undefined")
                title = "Warning";

            if ($('#codo_alert').length > 0) {
                $('#codo_alert').modal('hide').data('bs.modal', null).remove();
                $('.modal-backdrop').remove();
            }
            console.log($('#codo_alert').length);
            var dialog_content = '<div id="codo_alert" class="modal fade">' +
                '<div class="modal-dialog">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                '<h4 class="modal-title">' + title + '</h4>' +
                '</div>' +
                '<div class="modal-body">' +
                '<p>' + message + '</p>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';

            $('#alert_placeholder').html(dialog_content);
            $('#codo_alert').modal('show').on('hidden.bs.modal', function () {

                $('#codo_alert').remove();
            });
        },
        /**
         *  Adds codo_input_error class to blank input field else removes them
         * @param {type} id
         * @returns {undefined}
         */
        add_error_class_if_blank: function (id) {

            var el = jQuery('#' + id);
            if (el.val() === '') {

                el.addClass('codo_input_error');
            } else {

                el.removeClass('codo_input_error');
            }
        },
        update_response_status: function (response, el, saccade) {

            //default to error notification class
            var addclass = 'codo_notification_error', removeclass = 'codo_notification_success';
            if (response.status === "success") {

                //swap with success notification class
                var swap = addclass;
                addclass = removeclass;
                removeclass = swap;
            } else {

                var len = response.msg.length, msg = "<ol>";
                while (len--) {

                    msg += "<li>" + response.msg[len] + "</li>";
                }

                msg += "</ol>";
                response.msg = msg;
            }

            el.html(response.msg)
                .addClass(addclass)
                .removeClass(removeclass)
                .show('slow');
            if (typeof saccade !== "undefined") {
                //shake it 
                CODOF.ui.saccade(el);
            }
        },
        windowInFocus: function () {

            return localStorage.getItem('windowInFocus') === 'true';
        },
        generatePostUrl: function (tid, pid) {

            var url = codo_defs.url + "topic/" + tid;

            if (typeof pid !== "undefined" && pid !== null) {

                url += "/post-" + pid + "/#post-" + pid;
            }

            return url;
        },
        generatePostLink: function (tid, pid, title) {

            var url = CODOF.util.generatePostUrl(tid, pid);
            if (!title)
                title = url;

            return "<a href='" + url + "'>" + title + "</a>";
        },
        isImage: function (url, callback) {

            var img = new Image();
            img.onload = function () {
                callback(url, true);
            };
            img.onerror = function () {
                callback(url, false);
            };
            img.src = codo_defs.url + url;
        },
        isRemote: function (path) {

            return path.indexOf('http://') === 0 || path.indexOf('https://') === 0;
        },
        getProfileIcon: function (path) {

            return codo_defs.duri + 'assets/img/profiles/icons/' + path;
        }
    },
    cache: {
        validImages: [],
        invalidImages: []
    },
    ui: {
        animating: false,
        /**
         *
         * @param {jQuery object} el
         * @returns {undefined}
         */
        saccade: function (el) {

            if (!this.animating) {

                this.animating = true;
                el.css('position', 'relative')
                    .animate({"left": "+=30px"})
                    .animate({"left": "-=60px"})
                    .animate({"left": "+=30px"},
                        {
                            complete: function () {
                                CODOF.ui.animating = false;
                            }
                        });
            }
        },
        scrollToBottom: function () {

            $("html, body").animate({scrollTop: $(document).height() - $(window).height()});
        },
        scrollToDiv: function (id) {

            $("html, body").animate({scrollTop: $("#" + id).offset().top});

        }

    },
    /*modal: {
     show: function (id) {
     
     var modal = jQuery('#' + id);
     modal.show();
     if (modal.hasClass('animated')) {
     modal.removeClass('bounceOutUp')
     .addClass('bounceInDown');
     }
     jQuery('.codo_modal_bg').show();
     },
     hide: function (id, callback) {
     
     if (typeof callback === "undefined")
     callback = CODOF.callback;
     var modal = jQuery('#' + id);
     if (modal.hasClass('animated')) {
     modal.removeClass('bounceInDown')
     .addClass('bounceOutUp')
     }
     modal.fadeOut('slow', callback);
     jQuery('.codo_modal_bg').hide();
     }
     },*/
    callback: function () {
    },
    make_url: function (name) {

        return codo_defs.duri + codo_defs.smiley_path + name;
    },
    smiley: {
        smileylist: function (smileys) {
            var i = 0;
            var sm_array = [], len = smileys.length;
            for (i = 0; i < len; i++) {

                sm_array[i] = smileys[i].symbol[0];
            }

            var str;
            str = '<span class="smileylist">' + this.mksmileyurl(sm_array) + '</span>';
            return str;
        },
        mksmileyurl: function (name) {
            var namelen = name.length;
            var i = 0;
            var str = '';
            var j = 0;
            for (i = 0; i <= namelen; i++) {
                if (name[i] === null || typeof name[i] === "undefined") {
                    break;
                }

                str += '<li><div class="frei_smiley_image">' + this.gen_smiley(name[i]) + '</div></li>';
                j++;
            }

            return '<ul>' + str + '</ul>';
        },
        gen_smiley: function (name, no_click) {

            var replaced_mesg = name;
            if (typeof no_click === "undefined") {
                no_click = false;
            }


            var smileys = JSON.parse(JSON.stringify(CODOFVAR.smileys)), symbols, len;

            /**
             * This is how you would add smileys to the forum without adding them
             * in the smiley list in the editor gui
             smileys.push({
             
             symbol: ["hello"],
             image_name: "cool.gif"
             });
             smileys.push({
             
             symbol: ["bye"],
             image_name: "angry.gif"
             });
             */

            var i = 0;
            for (i = 0; i < smileys.length; i++) {

                symbols = smileys[i].symbol;
                len = symbols.length;
                while (len--) {
                    if (no_click) {

                        replaced_mesg = replaced_mesg.codo_smiley_replace(symbols[len],
                            '<img src="' + CODOF.make_url(smileys[i].image_name, codo_defs.smiley_path) + '" alt="smile" />');
                    } else {

                        replaced_mesg = replaced_mesg.codo_smiley_replace(symbols[len],
                            '<img onclick="CODOF.smiley.add_smiley(\'' + symbols[len].replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&") + '\')" src="'
                            + CODOF.make_url(smileys[i].image_name, codo_defs.smiley_path) + '" alt="smile" />');
                    }
                }
                //   break;
            }
            return replaced_mesg;
        },
        add_smiley: function (name) {

            jQuery.markItUp(
                //{replaceWith: '!['+name+'](' + codo_defs.url + 'serve/smiley?path=' + name + ') '}
                {replaceWith: name + ' '}
            );
            CODOF.mark.smiley.hide();
        }

    },
    BBcode2html: function (s) {

        function rep(re, str) {

            s = s.replace(re, str);
        }
        ;
        rep(/\[\/li\]\n/gi, "[/li]");
        rep(/\n/gi, "<br />");
        rep(/\[b\](.*?)\[\/b\]/gi, "<strong>$1</strong>");
        rep(/\[i\](.*?)\[\/i\]/gi, "<em>$1</em>");
        rep(/\[u\](.*?)\[\/u\]/gi, "<u>$1</u>");
        rep(/\[s\](.*?)\[\/s\]/gi, "<strike>$1</strike>");
        rep(/\[pre\](.*?)\[\/pre\]/gi, "<pre>$1</pre>");
        rep(/\[sup\](.*?)\[\/sup\]/gi, "<sup>$1</sup>");
        rep(/\[sub\](.*?)\[\/sub\]/gi, "<sub>$1</sub>");
        rep(/\[move\](.*?)\[\/move\]/gi, "<marquee>$1</marquee>");
        rep(/\[center\](.*?)\[\/center\]/gi, "<center>$1</center>");
        rep(/\[left\](.*?)\[\/left\]/gi, "<div style='text-align: left'>$1</div>");
        rep(/\[right\](.*?)\[\/right\]/gi, "<div style='text-align: right'>$1</div>");
        rep(/\[list type=decimal\](.*?)\[\/list\]/gi, "<ol>$1</ol>");
        rep(/\[list\](.*?)\[\/list\]/gi, "<ul>$1</ul>");
        rep(/\[li\](.*?)\[\/li\]/gi, "<li>$1</li>");
        rep(/\[url=([^\]]+)\](.*?)\[\/url\]/gi, "<a href=\"$1\">$2</a>");
        rep(/\[url\](.*?)\[\/url\]/gi, "<a href=\"$1\">$1</a>");
        rep(/\[img\](.*?)\[\/img\]/gi, "<img src=\"$1\" />");
        rep(/\[color=(.*?)\](.*?)\[\/color\]/gi, "<font color='$1'>$2</font>");
        rep(/\[code\](.*?)\[\/code\]/gi, "<span class=\"codeStyle\">$1</span>&nbsp;");
        rep(/\[quote.*?\](.*?)\[\/quote\]/gi, "<span class=\"quoteStyle\">$1</span>&nbsp;");
        return s;
    },
    moderation: {
        active: false,
        confirm_delete: function (me) {

            //use local mod instead of relying on the unreliable this
            var mod = this;
            if (mod.active)
                return;
            var $that = $(me);
            //activity started
            this.codo_spinner = $that.find('.codo_spinner');
            this.codo_spinner.show();
            if ($that.hasClass('codo_post_this_is_topic')) {

                //if (CODOF.topics.topic_active)
                //  return;

                //CODOF.topics.topic_active = true;
                mod.codo_spinner.hide();
                if (mod.last_confirm_popover !== me.id) {


                    if (typeof mod.confirm_popover !== "undefined") {
                        mod.confirm_popover.popover('hide');
                    }

                    mod.last_confirm_popover = me.id;
                    mod.confirm_popover = $that.popover({
                        html: true,
                        placement: 'bottom',
                        content: function () {
                            return $('#codo_delete_topic_confirm_html').html();
                        }
                    }).on('shown.bs.popover', function () {

                        //-207px
                        if (document.documentElement.clientWidth < 320) {

                            //popover is always appended so it becomes the next element
                            var popover = $(this).next();
                            popover.css('left', '-207px');
                            mod.arrow = popover.find('.arrow')
                                .hide();
                        }
                    });
                    $that.parent().on('click', '.codo_modal_delete_topic_cancel', function () {

                        mod.confirm_popover.popover('hide');
                        mod.codo_spinner.hide();
                        //CODOF.topics.topic_active = false;

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
                        mod.delete_topic($that, isSpam);
                    });
                    mod.confirm_popover.popover('show');
                }

            }

        },
        delete_topic: function ($that, isSpam) {

            //use local mod instead of relying on the unreliable this
            var mod = this;
            $('.codo_posts_topic_delete .codo_spinner').show();
            var id = parseInt($that.attr('id').replace('codo_posts_trash_', ''));
            mod.codo_spinner = $that.find('.codo_spinner');
            mod.codo_spinner.show();
            jQuery.post(codo_defs.url + 'Ajax/topic/' + id + '/delete', {
                token: codo_defs.token,
                isSpam: isSpam ? 'yes' : 'no'
            }, function (resp) {

                if (resp === "success") {

                    mod.codo_spinner.hide();
                    $that.parents('article').fadeOut();
                }
            });
        }

    },
    switch: {
        get: function (id) {

            var el = $('#' + id);
            if (el.hasClass('codo_switch_off')) {

                return false;
            }

            return true;
        },
        set: function (id, status) {

            var el = $('#' + id);
            if (el.hasClass('codo_switch_off') && status) {

                el.removeClass('codo_switch_off').addClass('codo_switch_on');
            }

            if (el.hasClass('codo_switch_on') && !status) {

                el.removeClass('codo_switch_on').addClass('codo_switch_off');
            }

        }
    },
    mentions: {
        cache: [],
        manned: [],
        wrong: [],
        notMentionable: [],
        mutedMentions: [],
        spec: '',
        extractAndAddToManned: function (text) {

            var mentions = text.match(/\B@[a-z0-9_-]+/gi);

            if (mentions !== null) {

                var len = mentions.length, mention;

                while (len--) {

                    mention = mentions[len].replace("@", "");
                    if (CODOF.mentions.manned.indexOf(mention) === -1) {

                        CODOF.mentions.manned.push(mention);
                    }
                }
            }

        },
        warnForNonMentions: function () {

            var pattern = /\B@[a-z0-9_-]+/gi;
            var imesg = $('#codo_new_reply_textarea').val();
            var mentions = imesg.match(pattern);

            if (mentions === null) {

                return false;
            }

            var mutedMentions = [];

            for (var i = 0; i < mentions.length; i++) {

                if (CODOF.mentions.mutedMentions.indexOf(mentions[i].replace("@", "")) > -1) {

                    mutedMentions.push(mentions[i]);
                }
            }

            if (mutedMentions.length) {

                CODOF.mentions.mutedMentions = mutedMentions;
                CODOF.hook.call('on_muted_mention_change', mutedMentions);
                CODOF.editor_reply_post_btn.text(CODOFVAR.trans.continue_mesg);
                return true;
            } else {
                $('#codo_non_mentionable').hide();
            }

            return false;
        },
        checkForNonMentions: function () {

            //check if there were any mentions in the message
            var pattern = /\B@[a-z0-9_-]+/gi;
            var imesg = $('#codo_new_reply_textarea').val();
            var mentions = imesg.match(pattern);

            if (mentions !== null && mentions.length) {

                //we need to check if these mentions/usernames are mentionable or not
                CODOF.request.get({
                    url: codo_defs.url + 'Ajax/mentions/mentionable/' + CODOFVAR.cid,
                    data: {
                        mentions: mentions
                    },
                    done: function (mutedMentions) {

                        //will replace if category is changed
                        CODOF.mentions.mutedMentions = mutedMentions;
                        //CODOF.hook.call('on_muted_mention_change');
                    }
                });
            } else {


                CODOF.mentions.mutedMentions = [];
                //CODOF.hook.call('on_muted_mention_change');
            }

        },
        updateSpec: function (cid, tid) {

            if (cid) {

                this.spec = '/' + cid;
            }

            if (tid) {

                this.spec += '/' + tid;
            }

        },
        validate: function () {

            var unmanned = []
            CODOF.editor.new_reply_preview.find('.codo_unmanned_mention').each(function () {

                var $this = $(this);
                var mention = $this.text();
                if (!CODOF.mentions.exists(mention) &&
                    CODOF.mentions.wrong.indexOf(mention.replace("@", "")) === -1) {
                    unmanned.push(mention.replace("@", ""));
                }
            });
            if (unmanned.length) {
                CODOF.request.get({
                    hook: 'validate_mentions',
                    url: codo_defs.url + 'Ajax/mentions/validate',
                    data: {mentions: unmanned},
                    done: function (validMentions) {

                        var len = validMentions.length;
                        while (len--) {

                            CODOF.mentions.manned.push(validMentions[len].username);
                        }


                        len = unmanned.length;
                        while (len--) {

                            if (!CODOF.mentions.exists(unmanned[len])) {

                                //this is a wrong mention
                                //add it to wrong array to prevent DOS
                                CODOF.mentions.wrong.push(unmanned[len]);
                            }
                        }


                    }
                });
            }
            // CODOF.mentions.decorate();

        },
        decorate: function () {

            CODOF.editor.new_reply_preview.find('.codo_unmanned_mention').each(function () {

                var $this = $(this);
                var mention = $this.text();
                if (CODOF.mentions.isValid(mention)) {

                    $this.addClass('manned-mention');
                }

            });
        },
        exists: function (mention) {

            return this.manned.indexOf(mention.replace("@", "")) > -1;
        }

    },
    /**
     *
     * Hook system to manage events .
     * @object type
     *
     */
    hook: {
        hooks: CF.hook.hooks,
        add: CF.hook.add,
        /**
         *
         * args must be an object
         *
         * @param {type} myhook
         * @param {Object} args
         * @returns {undefined}
         */
        call: function (myhook, args, func) {

            if (typeof args === 'object') {

                var ret = {};
            } else {

                ret = args;
            }
            var temp_ret;

            if (typeof CODOF.hook.hooks[myhook] !== "undefined") {

                var len = CODOF.hook.hooks[myhook].length,
                    curr;

                CODOF.hook.hooks[myhook].sort(function (a, b) {

                    return a.weight - b.weight;
                });

                for (var i = 0; i < len; i++) {

                    curr = CODOF.hook.hooks[myhook][i];
                    jQuery.extend(args, args, curr.args);
                    temp_ret = curr.func(args);

                    if (typeof temp_ret === 'object')
                        ret = $.extend(ret, temp_ret || {});
                    else
                        ret = temp_ret;
                }
            }

            if (typeof func !== "undefined") {

                temp_ret = func(args);
                if (typeof args === "object")
                    ret = $.extend(ret, temp_ret || {}); //since javascript is asynchronous we defer the function 
                else
                    ret = temp_ret;
            }

            return ret;
        }
    },
    req: {
        data: {}
    },
    request: {
        requests: [],
        updatePageTime: function (time) {

            var globalPageTime = localStorage.getItem('pageTime') || 0;

            if (time > globalPageTime) {

                localStorage.setItem('pageTime', time);
            }
        },
        getPageTime: function () {

            return localStorage.getItem('pageTime') || 0;
        },
        req: function (_options) {

            if (_options.preventParallel) {

                if (typeof this.requests[_options.hook] !== 'undefined'
                    && this.requests[_options.hook] !== 'completed') {

                    //prevent duplicate request before completion
                    return false;
                }
            }

            this.requests[_options.hook] = 'incomplete';

            var options = {
                type: _options.type,
                url: _options.url,
                hook: _options.hook || false,
                data: _options.data || {},
                done: _options.done || false,
                fail: _options.fail || false,
                always: _options.always || false
            };
            //hook is defined so let others modify headers before request
            if (options.hook) {

                CODOF.hook.call('before_req_' + options.hook, options.data);
            }

            //Add CSRF token to the header
            options.data._token = codo_defs.token;
            //make the request
            var jqxhr = $.ajax({
                type: options.type,
                url: options.url,
                data: options.data,
                dataType: 'json',
                hook: options.hook
            });
            jqxhr.done(function (data) {

                if (options.done) {
                    options.done(data);
                }

                //used for notifications
                if (data !== null && typeof data._new !== "undefined") {

                    CODOF.hook.call('on_req_new_stuff', data._new);
                }

            });
            if (options.fail) {

                jqxhr.fail(options.fail);
            }

            jqxhr.always(function (data) {

                CODOF.request.requests[_options.hook] = 'completed';

                if (options.always) {

                    options.always(data);
                }

                if (codo_defs.logged_in === 'yes') {
                    //delay the request by set-interval
                    CODOF.events.restartTimer();
                }

                //on request hook complete
                if (options.hook) {

                    CODOF.hook.call('on_req_' + options.hook, data);
                }

            });
        },
        /**
         *
         * @param {
         * 
         *    url <string> [required]
         *    hook <string> [optional]
         *    data <object> [optional]
         *    done <function> [optional]
         *    fail <function> [optional]
         *    always <function> [optional]
         * } options
         * @returns {undefined}
         */
        get: function (options) {

            options.type = "GET";
            this.req(options);
        },
        post: function (options) {

            options.type = "POST";
            this.req(options);
        }

    }

};
$(window).focus(function () {

    localStorage.setItem('windowInFocus', true);
}).blur(function () {

    localStorage.setItem('windowInFocus', false);
});


jQuery(window).bind('storage', function (e) {

    if (e.originalEvent.key === 'codo_storage_inter_tab') {

        CODOF.hook.call('codo_storage_inter_tab_event', e.originalEvent.newValue);
    }
});

CODOF.interTab = {
    broadcast: function (mesg) {

        localStorage, setItem('codo_storage_inter_tab', mesg);
    },
    listen: function (callback) {

        CODOF.hook.add('codo_storage_inter_tab_event', callback);
    }
};

CODOF.notify = {
    notification: false,
    notifications: false,
    useDesktopNotification: false,
    tags: [],
    /**
     * if window is not in focus and desktop notifications are supported and enabled
     * @returns {Boolean}
     */
    canDesktopNotify: function () {

        return this.useDesktopNotification && !CODOF.util.windowInFocus();
    },
    /**
     * Creates an in browser notification
     * @param {string} title
     * @param {object} options
     * @returns {undefined}
     */
    windowNotify: function (options) {

        var notification = jQuery('.bottom-right').notify({
            message: {html: options.body},
            type: 'blackgloss',
            fadeOut: {delay: options.timeout}
        });
        notification.$note.on('click', options.notifyClick);
        return notification;
    },
    /**
     * Creates a desktop notification
     * @param {string} title
     * @param {object} options
     * @returns {undefined}
     */
    desktopNotify: function (options) {

        return new Notify(options.title, {
            body: options.textBody,
            icon: options.icon,
            //  tag: options.title,
            notifyClick: options.notifyClick,
            timeout: options.timeout
        });
    },
    /**
     * Initialize notifications
     * @returns {undefined}
     */
    init: function () {

        if (codo_defs.preferences.notify.desktop === 'yes'
            && Notify.isSupported) {

            //take permission from user if required
            if (Notify.needsPermission) {

                Notify.requestPermission(function () {

                    CODOF.notify.useDesktopNotification = true;
                });
            } else {

                CODOF.notify.useDesktopNotification = true;
            }
        }

    },
    /**
     * Creates a in browser or desktop notfication
     * @param {object} options
     * @returns {undefined}
     */
    create: function (options) {

        var notification;
        if (this.canDesktopNotify()) {

            notification = this.desktopNotify(options);
        } else {

            var src;
            if (CODOF.util.isRemote(options.icon)) {

                src = options.icon;
            } else {

                src = CODOF.util.getProfileIcon(options.icon);
            }

            options.body = "<div class='codo_rt_notification'>\n\
                    <div class='codo_rt_icon'><img src='" + src + "' /></div>\n\
                    <div class='codo_rt_container'>\n\
                        <div class='codo_rt_head'>" + options.title + "</div>\n\
                        <div class='codo_rt_body'>" + options.textBody + "</div>\n\
                    </div>\n\
                </div>";
            notification = this.windowNotify(options);
        }

        this.notification = {
            content: notification,
            tag: options.tag
        };
    },
    show: function () {

        if (this.notification) {

            this.notification.content.show();
        }
    },
    generateNotification: function (event) {

        var data = event.data;
        var action = data.action;
        if (data.title.length > 100) {

            //get first 100 chars
            data.title = data.title.substr(0, 100) + "...";
        }

        var textBody = data.actor.username + " " + action + " " + data.title;
        var notifyClick = function () {

            window.open(CODOF.util.generatePostUrl(data.tid, data.pid) + "&page=from_notify");
        };
        CODOF.notify.create(
            {
                title: data.label,
                icon: data.actor.avatar,
                richBody: textBody,
                textBody: textBody,
                tag: event.type,
                notifyClick: notifyClick,
                timeout: 10000
            }
        );
    }
};
CODOF.notify.init();
jQuery(document).mouseup(function (e) {

    var container = jQuery('#codo_markitup_smileys')
    if (container.has(e.target).length === 0) {
        container.hide();
    }


}).ready(function ($) {

    $('.codo_switch').click(function () {

        var el = $(this);

        if (el.hasClass('codo_switch_on')) {

            el.trigger('switch_off');
        } else {
            el.trigger('switch_on');
        }
        el.toggleClass('codo_switch_on').toggleClass('codo_switch_off');
    });


    var menu = $('#nav');
    jQuery('.codo_back_to_top_arrow').click(function () {
        $('body,html').animate({
            scrollTop: 0
        }, 800);
        return false;
    });
}).scroll(function () {

    jQuery('.codo_back_to_top_arrow').toggleClass('codo_show_back_to_top', jQuery(document).scrollTop() >= 1000);
});
// Stop the animation if the user scrolls. Defaults on .stop() should be fine
jQuery('html, body').bind("scroll mousedown DOMMouseScroll mousewheel keyup", function (e) {
    if (e.which > 0 || e.type === "mousedown" || e.type === "mousewheel") {
        jQuery('html, body').stop().unbind('scroll mousedown DOMMouseScroll mousewheel keyup'); // This identifies the scroll as a user action, stops the animation, then unbinds the event straight after (optional)
    }
});
CODOF.hook.add('on_req_new_stuff', function (data) {

    /**
     * type: [new_topic|new_reply|mention]
     * created: [UNIX_TIMESTAMP]
     * data: {cid,tid,pid,mentions:[],title}
     */
    var events = data.events;
    var no_events = events.length,
        event, data;
    CODOF.request.updatePageTime(data.time);

    for (var i = 0; i < no_events; i++) {

        event = events[i];

//        data = JSON.parse(event.data);
        CODOF.notify.generateNotification(event);
        //CODOF.notify.create(data.actor.username, {body: event.type, tag: event.type});
        CODOF.notify.show();
    }

});
jQuery(document).ready(function ($) {

//run crons in ajax
    if (codo_defs.logged_in === 'yes') {
        $.get(codo_defs.url + 'Ajax/cron/run', {token: codo_defs.token});
    }

    CODOF.events = {
        getTimer: false,
        firstTime: true, //to remove interval delay
        firstRequest: true,
        getter: function () {

            CODOF.request.get({
                hook: 'new_stuff',
                url: codo_defs.url + 'Ajax/data/new',
                data: {
                    firstTime: CODOF.events.firstRequest,
                    time: CODOF.request.getPageTime()
                }
            });

            CODOF.events.firstRequest = false;
        },
        createTimer: function () {

            if (!CODOF.events.getTimer) {

                var interval;
                if (CODOF.events.firstTime) {

                    interval = CODOF.events.timer.delay;
                    CODOF.events.firstTime = false;
                } else {

                    interval = CODOF.events.timer.interval;
                }

                CODOF.events.getTimer = setTimeout(CODOF.events.getter, interval);
            }
        },
        restartTimer: function () {

            clearTimeout(CODOF.events.getTimer);
            CODOF.events.getTimer = false;
            CODOF.events.createTimer();
        },
        timer: {
            delay: 5000,
            interval: 20000,
            inactiveAfter: 30000 //stop timer if inactive for
        }

    };
    //only for logged in users
    if (codo_defs.logged_in === 'yes' &&
        codo_defs.preferences.notify.real_time === 'yes') {
        //delay the server polling by pre set interval
        setTimeout(CODOF.events.createTimer, 300);
    }

    CODOF.hasSetLastReadTime = false;

    $('#codo_inline_notifications').on('click', function () {

        if (codo_defs.logged_in !== 'yes')
            return false;

        $('.codo_inline_notifications_unread_no').fadeOut();
        var notifications = [];
        var showNotifications = function () {

            $('.codo_inline_notification_header > .codo_load_more_bar_black_gif')
                .hide();
            $('#codo_inline_notification_header_content').show();
            $('#codo_inline_notifications_mark_read').tooltip();
            $('#codo_inline_notifications_preferences').tooltip();
            var source = $("#codo_inline_notifications_template").html();
            var template = Handlebars.compile(source);
            var context = {
                objects: notifications,
                url: codo_defs.url,
                duri: codo_defs.duri,
                caught_up: codo_defs.trans.notify.caught_up,
                rolled_up_trans: codo_defs.trans.notify.rolled_up_trans
            };
            var html = template(context);
            $('#codo_inline_notification_body').html(html).show();

            $('#codo_inline_notification_body .codo_inline_notification_el_rolled').tooltip();

            if (!CODOF.hasSetLastReadTime && codo_defs.unread_notifications > 0) {

                CODOF.hasSetLastReadTime = true;
                CODOF.request.post({url: codo_defs.url + 'Ajax/set/lastNotificationRead'});
            }

        };
        var getNotifications = function () {

            if (notifications.length === 0) {

                CODOF.request.get({
                    url: codo_defs.url + 'Ajax/notifications/new',
                    data: {
                        time: CODOF.request.getPageTime()
                    },
                    always: function (response) {

                        CODOF.request.updatePageTime(response.time);
                        var _notifications = response.events;

                        var len = _notifications.length, notification, data;
                        var uniquity = [], unique;
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

                            if (uniquity.indexOf(unique) === -1) {

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

                                uniquity.push(unique);
                            } else {

                                var nLen = notifications.length;

                                while (nLen--) {

                                    if (notifications[nLen].unique === unique) {

                                        //if not defined, this is the second one, else + 1
                                        notifications[nLen].rolledX =
                                            (typeof notifications[nLen].rolledX === 'undefined')
                                                ? 2
                                                : notifications[nLen].rolledX + 1;
                                        break;
                                    }
                                }
                            }
                        }

                        showNotifications();
                    }
                });
            } else {

                showNotifications();
            }

        };
        setTimeout(getNotifications, 20);
    });
    $('#codo_inline_notifications_preferences').on('click', function () {

        if (window.location.hash) {
            CODOF.gotoHashTab('#preferences');
        } else {
            window.location = codo_defs.url + 'user/profile/' + codo_defs.uid + '/edit#preferences';
        }
    });
    $('.codo_inline_notifications_show_all').on('click', function () {

        if (window.location.hash) {
            CODOF.gotoHashTab('#notifications');
        } else {
            window.location = codo_defs.url + 'user/profile/' + codo_defs.uid + '/edit#notifications';
        }

        return false;
    });

    setTimeout(function () {

        if (localStorage.getItem('reply_' + codo_defs.uid) !== null && !CODOF.draftShown) {

            $('.codo_editor_draft').slideDown();
        }
    }, 1000);

    CODOF.autoDraft = {};
    CODOF.autoDraft.resume = function () {

        var draft = localStorage.getItem('reply_' + codo_defs.uid);

        if (draft !== null) {

            var obj = JSON.parse(localStorage.getItem('reply_' + codo_defs.uid));

            if (typeof obj.cat !== 'undefined') {

                //the draft is a topic because only category is saved only
                //for topic drafts

                /*if (CODOF.inTopic) {
                 //in same page
                 CODOF.restoreFromDraft();
                 } else {
                 */
                if (obj.tid !== false) {

                    //the draft was when topic was being edited
                    window.location = codo_defs.url + 'topic/' + obj.tid + '/edit#draft';
                } else {
                    window.location = codo_defs.url + 'new_topic#draft';
                }
                //              }

            } else {

                if (CODOFVAR && CODOFVAR.tid && CODOFVAR.tid == obj.tid) {

                    var textbox = $('#codo_new_reply_textarea');

                    var obj = JSON.parse(localStorage.getItem('reply_' + codo_defs.uid));
                    if (localStorage.getItem('reply_' + codo_defs.uid) !== null) {

                        if (!CODOF.autoDraft.test(obj, textbox)) {

                            return false;
                        }

                        CODOF.mentions.extractAndAddToManned(obj.text);
                        CODOF.showEditor(textbox, true);
                    }
                } else {

                    window.location = codo_defs.url + 'topic/' + obj.tid + '/' + obj.safe_title + '#draft';
                }
            }
        }

    };

    CODOF.autoDraft.remove = function () {

        localStorage.removeItem('reply_' + codo_defs.uid);
        $('.codo_editor_draft').hide();
    };

    CODOF.autoDraft.recycle = function () {

        this.remove();
        $('#codo_draft_pending').modal('hide');
        localStorage.removeItem('reply_' + codo_defs.uid);
        if (!CODOF.inTopic) {
            //to open the editor
            $('.codo_reply_btn:first').trigger('click');
            //in case of new topic, its already visible
        }
    };

    $('#codo_pending_text').on('click', function () {

        CODOF.autoDraft.resume();
    });

    $('.codo_delete_draft').on('click', function () {

        CODOF.autoDraft.remove();
    });

    $('.codo_breadcrumb_list > a').tooltip();
    $('.codo_tooltip').tooltip();

    Handlebars.registerHelper('isRemote', function (val, options) {

        if (CODOF.util.isRemote(val)) {

            return options.fn();
        }

        return options.inverse();
    });

});
String.prototype.codo_smiley_replace = function (name, value) {
    name = name.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
    var re = new RegExp(name, "g");
    return this.replace(re, value);
};

function codo_create_topic() {

    window.location.href = codo_defs.url + 'new_topic';
}


jQuery(document).ready(function ($) {

    $('#mmenu').show();

    var three_bars_visible = !$('#codo_is_xs').is(':visible');
    if (three_bars_visible) {

        CODOF.mmenu = $('#mmenu').mmenu({
            dragOpen: {
                open: true,
                threshold: 10

            }
        });

    } else {
        CODOF.mmenu = $('#mmenu').mmenu();
    }

    /*$('.codo_topics').on('click', ".codo_readmore", function () {
     
     window.location = $(this).data('href');
     });*/


    $('#codo_global_search').on('click', function () {

        var input = $('.codo_global_search_head_input');
        input.show();
        input.focus();

        if (input.val() !== "") {
            CODOF.globalSearch(input.val());
        }
    });

    $('.codo_global_search_input').keypress(function (e) {

        if (e.which == 13) {
            CODOF.globalSearch(this.value);
        }
    });


    //footer pagination scroll above footer
    var footerHeight = $('.footer').height();
    var offset = 2;
    var $paginationDiv = $(".codo_topics_loadmore_div");
    var applyDelayedAdjustment = false;
    var startedApplyingDelayedAdjustment = false;
    (CODOF.adjustPaginationPosition = function () {

        var scrolled = $(window).scrollTop() + (footerHeight - offset);
        var fullHieght = $(document).height() - $(window).height();

        if (scrolled > fullHieght) {

            $paginationDiv.css("bottom", (scrolled - fullHieght) + "px");
            applyDelayedAdjustment = true;
        } else {

            //apply a delayed adjustment
            if (applyDelayedAdjustment && !startedApplyingDelayedAdjustment) {

                startedApplyingDelayedAdjustment = true;
                setTimeout(function () {


                    var scrolled = $(window).scrollTop() + (footerHeight - offset);
                    var fullHieght = $(document).height() - $(window).height();
                    if (scrolled < fullHieght) {
                        $paginationDiv.animate({"bottom": "0px"});
                    }
                    startedApplyingDelayedAdjustment = false;
                    applyDelayedAdjustment = false;

                }, 100);
            }
        }

    })();

    $(window).scroll(function () {

        CODOF.adjustPaginationPosition();
    });
});