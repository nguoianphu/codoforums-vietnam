/*
 * @CODOLICENSE
 */

//avoid alt+e and alt+f key combinations

function saveToLocalStorage() {

    if (CODOF.inTopic) {

        localStorage.setItem('reply_' + codo_defs.uid, JSON.stringify({
            'text': $("#codo_new_reply_textarea").val(),
            'title': $('#codo_topic_title').val(),
            'tags': $('#codo_tags').tagsinput('items'),
            'poll_title': $('#poll_question').val(),
            'poll_options': $('#codo_poll_inputs input').map(function () {
                return this.value;
            }).get(),
            'cat': CODOF.draftCurrentCategory ? CODOF.draftCurrentCategory : $.trim($('#codo_topic_cat').val()), //selected category
            'tid': CODOF.edit_topic_id //false -> if new topic                            
        }));

    } else {

        localStorage.setItem('reply_' + codo_defs.uid, JSON.stringify({
            'text': $("#codo_new_reply_textarea").val(),
            'title': CODOFVAR.full_title,
            'safe_title': CODOFVAR.title,
            'tid': CODOFVAR.tid,
            'pid': CODOF.topic_creator.edit_post_id

        }));
    }

}

CODOF.mark = {
    linker: {
        eventsAttached: false,
        show: function (markItUp) {

            CODOF.mark.linker.bind_events(markItUp);
            jQuery('#codo_modal_link_text').val('');
            jQuery('#codo_modal_link_url').val('');
            jQuery('#codo_modal_link_title').val('');
            setTimeout(function () {
                jQuery('#codo_modal_link_url').focus();
            }, 100);
            $('#codo_modal_link').modal();

        },
        bind_events: function (markItUp) {


            if (CODOF.mark.linker.eventsAttached) {
                return;
            }
            CODOF.mark.linker.eventsAttached = true;

            jQuery('#codo_modal_link_submit').bind('click', function (event) {
                event.stopPropagation();
                event.preventDefault();


                var text = jQuery('#codo_modal_link_text').val();
                var url = jQuery('#codo_modal_link_url').val();
                var title = jQuery('#codo_modal_link_title').val();

                if (url === "") {

                    jQuery('#codo_modal_link_url').addClass('boundary-error').focus();
                    return;
                }

                var md = '';

                if (url.indexOf('://') === -1) {

                    url = "http://" + url;
                }

                if (title === "" && text === "") {

                    md = url;
                } else if (title === "") {

                    md = '[' + text + '](' + url + ')';
                } else if (text === "") {

                    md = '[' + title + '](' + url + ' "' + title + '")';
                } else {

                    md = '[' + text + '](' + url + ' "' + title + '")';

                }

                md += " ";
                jQuery(markItUp.textarea).trigger('insertion', [{replaceWith: md}]);
                $('#codo_modal_link').modal('hide');

            });
        }
    },
    upload: {
        show: function (markitup) {

            $('#codo_modal_upload').modal().on('hidden.bs.modal', function () {

                if (typeof CODOF.dz !== "undefined") {

                    CODOF.dz.removeAllFiles(true);
                }
            });

            CODOF.markitup = markitup;
            //jQuery('.dropzone.dz-clickable .dz-message span').fitText(1, {minFontSize: '20px', maxFontSize: '40px'});
            $('.dropzone.dz-clickable .dz-message span ').replaceWith(function () {
                return $("<h2 />", {html: $(this).html()});
            });
        }
    },
    smiley: {
        show: function (markitup) {

            $('#codo_markitup_smileys').slideToggle();
            CODOF.markitup = markitup;

        },
        hide: function () {
            $('#codo_markitup_smileys').slideToggle();
        }

    }
};


CODOF.editor_settings = {
    // onEnter: {keepDefault: true, replaceWith: '  \n'},
    onShiftEnter: {keepDefault: false, openWith: '\n\n'},
    onTab: {keepDefault: false, replaceWith: '    '},
    markupSet: [
        {name: codo_defs.trans.editor.bold, key: 'B', openWith: '**', closeWith: '**'},
        {name: codo_defs.trans.editor.italic, key: 'I', openWith: '_', closeWith: '_'},
        //{name: 'Underline', key: 'U', openWith: '[u]', closeWith: '[/u]'},
        {separator: '---------------'},
        {name: codo_defs.trans.editor.bulleted_list, openWith: '- '},
        {name: codo_defs.trans.editor.numeric_list, openWith: function (markItUp) {
                return markItUp.line + '. ';
            }},
        {separator: '---------------'},
        //{name: 'Picture', key: 'P', replaceWith: '![[![Alternative text]!]]([![Url:!:http://]!] "[![Title]!]")'},
        //{name: 'Link', key: 'L', openWith: '[', closeWith: ']([![Url:!:http://]!] "[![Title]!]")', placeHolder: 'Your text to link here...'},
        {name: codo_defs.trans.editor.picture, key: 'U', replaceWith: function (markItUp) {
                CODOF.mark.upload.show(markItUp);
                return false;
            }
        },
        {name: codo_defs.trans.editor.link, replaceWith: function (markItUp) {
                CODOF.mark.linker.show(markItUp);
                return false;
            }
        },
        {separator: '---------------'},
        {name: codo_defs.trans.editor.quotes, key: 'Q', openWith: '> '},
        {name: 'Code Block / Code', openWith: '\n```` \r', closeWith: '\r\n````'},
        {separator: '---------------'},
        {name: 'Smiley', beforeInsert: function (markItUp) {
                CODOF.mark.smiley.show(markItUp);
                return false;
            }
        },        
        {name: 'Preview', call: 'preview', className: "preview"},
        {name: 'Headers', className: "heading", dropMenu: [
                {name: 'Header 1', key: '1', className: "header1", placeHolder: 'Your title here...', closeWith: function (markItUp) {
                        return CODOF.editor.markdowntitle(markItUp, '=')
                    }},
                {name: 'Header 2', key: '2', className: "header2", placeHolder: 'Your title here...', closeWith: function (markItUp) {
                        return CODOF.editor.markdowntitle(markItUp, '-')
                    }},
                {name: 'Header 3', key: '3', className: "header3", openWith: '### ', placeHolder: 'Your title here...'},
                {name: 'Header 4', key: '4', className: "header4", openWith: '#### ', placeHolder: 'Your title here...'},
                {name: 'Header 5', key: '5', className: "header5", openWith: '##### ', placeHolder: 'Your title here...'},
                {name: 'Header 6', key: '6', className: "header6", openWith: '###### ', placeHolder: 'Your title here...'}
            ]
        }
        

    ],
    previewInElement: jQuery('#codo_new_reply_preview'),
    getImageHtml: function(href, title, text) {

        var src = href;
        if(href.indexOf("serve/attachment") > -1) {

            src = src.replace('serve/attachment', 'serve/attachment/preview');
        }

        if(title == null) title = "";
        if(text == null) text = "";

        return '<a title="'+codo_defs.trans.editor.clickToViewFull+'" class="codo_lightbox_container" href="' + href + '"><img alt="' + text + '" src="' + src + '" title="' + title + '" /></a>';
    },
    previewParser: function (content) {

        var renderer = new marked.Renderer();
        var xRenderer = new marked.Renderer();
        var editor = this;

        renderer.link = function (href, title, text) {

            var embed = false;

            //if ends with some img extension, image intended
            if (/\.(?:jpg|jpeg|gif|png)$/i.test(href)) {

                //may be an image
				
				// nguoianphu alt is more important than title
				if (text=="" || text === undefined || text===null || text==href) {
					text = $('#codo_topic_title').val();
					if (text=="" || text === undefined || text===null) {
						text = $('.codo_widget_header_title').text();
					}
					if (text=="" || text === undefined || text===null) {
						text = href;
					}
				}

				if (title=="" || title=== undefined || title===null) {
					title = text;
				}
				
                return '<img src="' + href + '" alt="' + text + '" />';
            }

            if (CODOF.cache.validImages.indexOf(href.replace(/&amp;/g, '&')) > -1) {
				// nguoianphu
                return '<img src="' + href + '" alt="' + text + '" />';
            }

            if (CODOF.cache.invalidImages.indexOf(href.replace(/&amp;/g, '&')) === -1) {

                //sometime
                CODOF.util.isImage(href.replace(/&amp;/g, '&'), function (src, isImage) {

                    if (isImage) {

                        CODOF.cache.validImages.push(src);
                    } else {

                        CODOF.cache.invalidImages.push(src);
                    }
                });
            }

            $('#jquery-oembed-me').oembed(href,
                    {
                        embedMethod: 'append',
                        placeholder: true,
                        onEmbed: function (c, data) {

                            var el = $(c.code);
                            embed = $(c.code).prop('outerHTML');

                            var no_preview_div = '';
                            if (el.prop('tagName') === 'IFRAME' || el.prop('tagName') === 'VIDEO' || el.prop('tagName') === 'EMBED') {

                                no_preview_div = "<div class='codo_embed_info'>" + codo_defs.trans.embed_no_preview + "</div>";
                            }
                            embed = "<a href='" + href + "' class='codo_oembed' target='_blank'><div class='codo_embed_container'>" + embed + no_preview_div + "</div></a>";
                        }/*,
                         onEmbed: function () {
                         console.log('call me instead')
                         }, afterEmbed: function () {
                         }*/
                    }
            );

            var patt = new RegExp("http://imgur.com/(.+)");

            if (patt.test(href)) {

                var part = patt.exec(href);
				// nguoianphu
                return '<a href="' + href + '"><img src="http://i.imgur.com/' + part[1] + '.jpg" alt="' + text + '" /></a>';
            }

            patt = new RegExp("http://i.imgur.com/(.+).jpg");

            if (patt.test(href)) {
				// nguoianphu
                return '<img src="' + href + '" alt="' + text + '" />';
            }


            //An uploaded file
            if(href.indexOf("serve/attachment") !== -1) {

                return '<a href="'+codo_defs.url + href+'" title="'+codo_defs.trans.editor.download_file+'"><i class="glyphicon glyphicon-file"></i>'+text+'</a>';
            }

            return (embed) ? embed : xRenderer.link(href, title, text);

        };

        renderer.paragraph = function (text) {

            //console.log("text=" + text);
            var mentions = text.match(/\B@[a-z0-9_-]+/gi);
            text = CODOF.hook.call('on_render_paragraph', text);

            if (mentions && mentions.length) {

                var no_mentions = mentions.length;

                text = CODOF.smiley.gen_smiley(text, true);
                text = CODOF.BBcode2html(text);

                var mention;

                while (no_mentions--) {

                    mention = mentions[no_mentions].replace("@", "");

                    var re = new RegExp("@" + mention, "g");

                    if (CODOF.mentions.manned.indexOf(mention) > -1) {

                        text = text.replace(re, "<a class='codo_mentions_a' href='" + codo_defs.url + "user/profile/" + mention + "'>@" + mention + "</a>");
                    } else {

                        text = text.replace(re, "<span class='codo_unmanned_mention' href='" + codo_defs.url + "user/profile/" + mention + "'>@" + mention + "</span>");

                    }
                }

                return "<p>" + text + "</p>";
            }
            ;

            text = text.replace(/^:+/g, function (match) {
                return Array(match.length + 1).join('&nbsp;');
            });
            text = CODOF.smiley.gen_smiley(text, true);
            text = CODOF.BBcode2html(text);


            return xRenderer.paragraph(text);
        };

        var markdown = marked(content, {
            highlight: function (code, lang) {

                var languages = hljs.listLanguages();

                if (languages.indexOf(lang) > -1) {

                    return hljs.highlight(lang, code).value;
                }

                return hljs.highlightAuto(code).value;
            },
            sanitize: true,
            renderer: renderer
        });

        return markdown;
    },
    afterInsert: function () {

        CODOF.editor.calc_chars($("#codo_new_reply_textarea").val().length);
        //save post to localstorage as draft
        if (codo_defs.preferences.drafts.autosave === 'yes'
                && CODOF.editor.canSave
                && CODOF.editor.ignoreFirstCall) {

            CODOF.editor.firstTimeSave = true;
            $('.codo_draft_status_saving').show();
            $('.codo_draft_status_saved').hide();
            if (!CODOF.editor.savingTimer) {
                CODOF.editor.savingTimer = setTimeout(function () {

                    $('.codo_draft_status_saving').hide();
                    $('.codo_draft_status_saved').show();
                    CODOF.editor.savingTimer = false;
                }, 1000);
            }

            saveToLocalStorage();
        }

        CODOF.editor.ignoreFirstCall = true;
    }
};

// jQuery plugin: PutCursorAtEnd 1.0
// http://plugins.jquery.com/project/PutCursorAtEnd
// by teedyay
//
// Puts the cursor at the end of a textbox/ textarea

// codesnippet: 691e18b1-f4f9-41b4-8fe8-bc8ee51b48d4
(function ($)
{
    jQuery.fn.putCursorAtEnd = function ()
    {
        return this.each(function ()
        {
            $(this).focus()

            // If this function exists...
            if (this.setSelectionRange)
            {
                // ... then use it
                // (Doesn't work in IE)

                // Double the length because Opera is inconsistent about whether a carriage return is one character or two. Sigh.
                var len = $(this).val().length * 2;
                this.setSelectionRange(len, len);
            } else
            {
                // ... otherwise replace the contents with itself
                // (Doesn't work in Google Chrome)
                $(this).val($(this).val());
            }

            // Scroll to the bottom, in case we're in a tall textarea
            // (Necessary for Firefox and Google Chrome)
            this.scrollTop = 999999;
        });
    };
})(jQuery);



jQuery('document').ready(function ($) {

    CODOF.editor = {
        chars_left_div: $('#codo_reply_min_chars_left'),
        chars_left_div_hidden: false,
        color_change: false,
        canSave: false,
        new_reply_preview: $("#codo_new_reply_preview"),
        add_file_tomarkup: function () {


            var up = '';
            var file;
            for (var k in CODOF.files) {

                file = CODOF.files[k];

                if (file.type.match(/image.*/) && !file.type.match(/image.bmp/))
                    up += '![' + file.name + '](serve/attachment&path=' + file.name + ')  \n';
                else
                    up += '[' + file.realname + '](serve/attachment&path=' + file.name + ')  \n';
            }

            CODOF.files = [];
            $(CODOF.markitup.textarea).trigger('insertion', [{replaceWith: up}]);

        },
        calc_chars: function (val_len) {

            var chars_left = CODOFVAR.reply_min_chars - val_len;

            if (chars_left > 0) {

                CODOF.editor.chars_left_div.html(chars_left);

                if (CODOF.editor.chars_left_div_hidden) {

                    CODOF.editor.chars_left_div.parent().show();
                    CODOF.editor.chars_left_div_hidden = false;
                    CODOF.editor_reply_post_btn.removeClass('codo_btn_primary');
                    CODOF.editor.canSave = false;
                }
            } else {

                CODOF.editor.chars_left_div.parent().hide();
                CODOF.editor.chars_left_div_hidden = true;
                CODOF.editor_reply_post_btn.addClass('codo_btn_primary');
                CODOF.editor.canSave = true;

            }


        },
        markdowntitle: function (markItUp, char) {

            var heading = '';
            var n = $.trim(markItUp.selection || markItUp.placeHolder).length;
            for (var i = 0; i < n; i++) {
                heading += char;
            }
            return '\n' + heading;
        },
        recalc_ht: function () {

            var win_ht = $(window).outerHeight();
            var editor_ht = $('#codo_new_reply').outerHeight();

            if (win_ht < editor_ht) {

                //var diff = editor_ht - win_ht;
                $('#codo_reply_box').css('height', (win_ht - 100) + "px").css('min-height', "0px");
            }

        }

    };

    CODOF.editor.callembed = function () {

        /*CODOF.editor.new_reply_preview.find('.codo_oembed').oembed('https://www.youtube.com/watch?v=8dwB1AexJeM',
         {
         embedMethod: 'fill',
         }
         );*/

        $('#codo_new_reply_preview .codo_oembed').oembed(null, {placeholder: true, embedMethod: 'fill'});
    };



    $("#codo_new_reply_textarea").markItUp(CODOF.editor_settings).bind('input propertychange', function () {

        CODOF.editor_trigger_preview($(this));
    }).css({
        width: '100%',
        height: '100%'
    });

    //reset height of editor if window is small

    $('#codo_post_preview_btn_resp').click(function () {

        $('#markItUpCodo_new_reply_textarea').slideToggle();
        $(this).toggleClass('codo_post_preview_bg codo_post_preview_bg_hide');
    });

    CODOF.editor.calc_chars($("#codo_new_reply_textarea").val().length);

    function is_touching_bottom(el) {

        var sc_top = el.scrollTop;
        var sc_ht = el.scrollHeight;
        var off_ht = el.offsetHeight;

        return (sc_ht <= (sc_top + off_ht));

    }

    CODOF.editor_trigger_preview = function (me) {

        $('#codo_markitup_smileys').html(CODOF.smiley.smileylist(CODOFVAR.smileys));

        CODOF.editor.preview.trigger('mouseup');

        var $textarea = $("#codo_new_reply_textarea");

        if (is_touching_bottom($textarea[0]) ||
                is_touching_bottom(CODOF.editor.new_reply_preview[0])) {
            CODOF.editor.new_reply_preview.stop().animate({scrollTop: CODOF.editor.new_reply_preview[0].scrollHeight}, 500);
        }

    };

    /*
     
     (CODOF.sync = function() {
     
     var $textarea = $('#codo_new_reply_textarea');
     var $preview = CODOF.editor.new_reply_preview[0];
     
     $textarea.on('scroll', function() {
     
     var sc_top = this.scrollTop;
     var sc_ht = this.scrollHeight;
     var off_ht = this.offsetHeight;
     var percentage;
     
     if (sc_ht <= (sc_top + off_ht)) {
     
     percentage = 1;
     } else {
     
     percentage = sc_top / (sc_ht - off_ht);
     percentage = (percentage > 1) ? 1 : percentage;//dont let it excced 1
     }
     
     if(percentage === 1) {
     
     $preview.scrollTop = $preview.scrollHeight;
     }else{
     
     $preview.scrollTop = percentage * ($preview.scrollHeight - $preview.offsetHeight);                
     }
     });
     })();
     
     */
    CODOF.editor.preview = $('a[title="Preview"]');


    $('#codo_new_reply_textarea').show();


    $('a[title="Preview"]').trigger('mouseup');

    $('#codo_reply_box').gripHandler({
        cursor: 'ns-resize',
        gripClass: 'codo_reply_resize_handle'
    });

    CODOF.editor_preview_btn.click(function () {

        $(this).toggleClass('codo_post_preview_bg codo_post_preview_bg_hide');
        $('#codo_new_reply_preview_container').toggle();
        $('#markItUpCodo_new_reply_textarea').toggleClass('markitUp_width_half markitUp_width_full');
        return false;
    });


    CODOF.editor_form.submit(function () {
        return false;
    });

    CODOF.editor_reply_post_btn.on('click', function () {

        if (!CODOF.editor.chars_left_div_hidden) {

            clearTimeout(CODOF.editor.color_change);

            CODOF.editor.chars_left_div.css('color', '#800000');

            CODOF.editor.color_change = setTimeout(function () {
                CODOF.editor.chars_left_div.css('color', 'grey');
            }, 800);

            return false;

        }

        CODOF.submitted();
    });

    Dropzone.autoDiscover = false;
    $('#codomyawesomedropzone').dropzone({
        url: codo_defs.url + "Ajax/topic/upload",
        dictDefaultMessage: CODOFVAR.dropzone.dictDefaultMessage,
        dictFallbackMessage: "",
        paramName: "file",
        maxFilesize: CODOFVAR.dropzone.max_file_size, //MB
        //acceptedFiles: CODOFVAR.dropzone.allowed_file_mimetypes,
        autoProcessQueue: false,
        addRemoveLinks: true,
        uploadMultiple: false, // CODOFVAR.dropzone.forum_attachments_multiple,
        parallelUploads: CODOFVAR.dropzone.forum_attachments_parallel,
        maxFiles: CODOFVAR.dropzone.forum_attachments_max,
        init: function () {
            var dz = this;
            CODOF.dz = dz;
            CODOF.files = [];


            dz.on("addedfile", function () {

                $("#codo_modal_upload_submit").addClass('codo_btn_primary');
            });

            dz.on("removedfile", function () {

                if (!CODOF.dz.files.length)
                    $("#codo_modal_upload_submit").removeClass('codo_btn_primary');

            });

            $("#codo_modal_upload_submit").on('click', function () {

                if (!CODOF.dz.files.length)
                    return false;

                var me = $(this);

                if (!me.hasClass('codo_btn_primary'))
                    return false;

                me.removeClass('codo_btn_primary');
                if (dz.filesQueue) {
                    for (var i = 0; i < dz.files.length; i++) {
                        dz.filesQueue.push(dz.files[i]);
                    }
                }

                dz.processQueue();

            });


            dz.on("success", function (file, response) {

                response = JSON.parse(response);

                for (var i = 0; i < response.length; i++) {

                    // if (!CODOF.files[response[i].name])
                    CODOF.files.push({
                        name: response[i].name,
                        type: file.type,
                        realname: file.name
                    });

                }

            });


            dz.on("complete", function (file) {

                if (!dz.filesQueue
                        && dz.getUploadingFiles().length === 0 && dz.getQueuedFiles().length > 0) {

                    dz.processQueue();
                }

                if (dz.getQueuedFiles().length === 0 && dz.getUploadingFiles().length === 0
                        && !$("#codo_modal_upload_submit").hasClass('codo_btn_primary')) {
                    // File finished uploading, and there aren't any left in the queue.

                    $('#codo_modal_upload').modal('hide');
                    //CODOF.modal.hide('codo_modal_upload');
                    dz.removeAllFiles();

                    CODOF.editor.add_file_tomarkup();
                    $("#codo_modal_upload_submit").addClass('codo_btn_primary');
                }
            });


        }
    });



    (CODOF.editor.oembed = function () {

        $('.codo_oembed').oembed(null, {placeholder: true, loadOnClick: true, embedMethod: 'append'});
        /*CODOF.editor.new_reply_preview.on({
         'click': function() {
         
         var originalUrl = $(this).prev().data('origsrc');
         
         var link = "<a href='" + originalUrl + "'>" + originalUrl + "</a>";
         
         $(this).parent().replaceWith(link);
         
         var str = $('#codo_new_reply_textarea').val();
         
         $('#codo_new_reply_textarea').val(str.replace(originalUrl, originalUrl + "__NOBOX__"));
         
         }
         }, '.codo_embed_close');*/

    })();

    //add to valid mentions
    CODOF.hook.add('on_req_fetch_mentions', function (data) {

        //there will allways be maximum of 10 items in this array
        var len = data.length;

        while (len--) {

            if (CODOF.mentions.manned.indexOf(data[len].username) === -1) {

                CODOF.mentions.manned.push(data[len].username);
            }
        }

    });

    $('#codo_new_reply_textarea').textcomplete([
        {// mention strategy
            match: /(^|\s)@(\w*)$/,
            search: function (term, callback) {

                if (CODOF.mentions.cache[term]) {
                    callback(CODOF.mentions.cache[term], true);
                }

                if (term) {


                    CODOF.mentions.updateSpec(CODOFVAR.cid, CODOFVAR.tid);

                    CODOF.request.get({
                        hook: 'fetch_mentions',
                        url: codo_defs.url + 'Ajax/mentions/' + term + CODOF.mentions.spec,
                        done: function (data) {

                            CODOF.mentions.cache[term] = data;
                            var len = data.length;
                            while (len--) {

                                if (data[len].mentionable && data[len].mentionable === 'no') {

                                    //wont work if mention is copy/pasted from somewhere. "TODO"
                                    CODOF.mentions.mutedMentions.push(data[len].username);
                                }
                            }
                            callback(data);
                        },
                        fail: function () {
                            callback([]);
                        }
                    });
                } else {

                    callback([]);
                }
            },
            replace: function (res) {

                if (res.mentionable && res.mentionable === 'no') {

                    //CODOF.mentions.mutedMentions.push(res.username);
                    //CODOF.hook.call('on_muted_mention_change')
                }

                return '$1@' + res.username + ' ';
            },
            template: function (res) {

                return '<div><img src=' + res.avatar + ' /><div>' + res.username + '</div></div>';
            },
            cache: true
        }
    ], {maxCount: 20, debounce: 50, zIndex: 1030}).on({
        'textComplete:select': function (e, value, strategy) {

            CODOF.editor.preview.trigger('mouseup');
        },
        paste: function () {

            setTimeout(CODOF.mentions.validate, 300);
        }
    });

    $('#codo_new_reply_textarea').keyup(function (e) {

        if (e.keyCode == 8 || e.keyCode == 46) {

            if (CODOF.editor.firstTimeSave)
                saveToLocalStorage();
        }
    });

    //sometimes mention is not used by pressing enter from autocomplete
    //but the whole mention/username is directly typed
    //below tells you of such occurence
    /*$("#codo_new_reply_textarea").keypress(function (event) {
     if (event.which == "@".charCodeAt(0)) {
     
     //wait for space or replace
     CODOF.mentions.atPressed = true;
     }
     
     if (event.keyCode == " ".charCodeAt(0) && CODOF.mentions.atPressed) {
     
     CODOF.mentions.atPressed = false;
     //check for bad mentions
     //check if there were any mentions in the message
     var pattern = /\B@[a-z0-9_-]+/gi;
     var imesg = $('#codo_new_reply_textarea').val();
     var mentions = imesg.match(pattern);
     var username;
     
     for(var i = 0; i<mentions.length; i++) {
     
     username = mentions[i].replace("@", "");
     if(CODOF.mentions.notMentionable.indexOf(username) > -1) {
     
     //this mention cannot be mentioned
     if(CODOF.mentions.mutedMentions.indexOf(username) === -1) {
     
     //if it is not yet pushed 
     CODOF.mentions.mutedMentions.push(username);
     }
     }
     }
     
     CODOF.hook.call('on_muted_mention_change');
     }
     });*/

    CODOF.hook.add('on_muted_mention_change', function () {

        var mentions = CODOF.mentions.mutedMentions;
        var users = [], mentioned = [];

        for (var i = 0; i < mentions.length; i++) {


            if (mentioned.indexOf(mentions[i]) === -1) {
                users.push("<a target='_blank' href='" + codo_defs.url + "user/profile/" + mentions[i].replace("@", "") + "'>" + mentions[i] + "</a>");
                mentioned.push(mentions[i]);
            }
        }

        if (users.length) {

            $('#codo_nonmentionable_users').html(users.join());
            $('#codo_non_mentionable').show();
        } else {

            $('#codo_non_mentionable').hide();
        }

    });

    if (window.location.hash === '#draft') {

        $('.codo_reply_btn:first').trigger('click');
        CODOF.draftShown = true;
    }

});