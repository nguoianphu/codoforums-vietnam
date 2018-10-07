/*!
 * jquery oembed plugin
 *
 * Copyright (c) 2009 Richard Chamorro
 * Licensed under the MIT license
 * 
 * Orignal Author: Richard Chamorro 
 * Forked by Andrew Mee to Provide a slightly diffent kind of embedding experience
 * Forked again by Adesh D' Silva to provide powerful embedding
 * with caching & preview features
 * 
 */



//; => some people just don't understand the price of an optional ";"
;
(function ($) {

    //for individual urls
    $.oembed = function (url, options) {

        var resourceURL = url;

    };

    var cache = {
        longURLs: {}, //map short url to corresponding long url
        embedData: [] //array of objects of embed data of urls    
    };

    var settings;

    //Here comes the jQuery function that does everything !
    $.fn.oembed = function (url, options) {

        options = options || {}; //the power of defaults

        settings = $.extend(true, $.fn.oembed.defaults, options);

        return this.each(function () {

            var container = $(this),
                    resourceURL = (url && (!url.indexOf('http://') || !url.indexOf('https://'))) ? url : container.attr("href"),
                    provider;

            //return;
            /*if (embedAction) {
                settings.onEmbed = embedAction;
            } else*/
            if (!settings.onEmbed) {
                settings.onEmbed = function (oembedData) {
                    $.fn.oembed.insertCode(this, settings.embedMethod, oembedData);
                };
            }

            if (resourceURL !== null && resourceURL !== undefined) {
                //Check if shorten URL

                if (typeof cache.longURLs[resourceURL] !== 'undefined') {

                    //cached AJAXed long url
                    var data = cache.longURLs[resourceURL];
                    embedCode(container, data.url, data.provider);

                }

                var work = function (resourceURL) {

                    provider = $.fn.oembed.getOEmbedProvider(resourceURL);

                    if (provider !== null) {
                        provider.params = getNormalizedParams(settings[provider.name]) || {};
                        provider.maxWidth = settings.maxWidth;
                        provider.maxHeight = settings.maxHeight;
                        embedCode(container, resourceURL, provider);
                    } else {
                        settings.onProviderNotFound.call(container, resourceURL);
                    }
                };

                expandURL(resourceURL, {
                    found: function (url) {

                        //got an expanded url
                        //cache this
                        cache.longURLs[resourceURL] = {
                            url: url, //expanded url
                            provider: provider
                        };

                        work(url);
                    },
                    notFound: function (url) {

                        //url was not shortened
                        work(url);
                    }
                });

            }

            return container;
        });


    };

    /* Private functions */

    //TODO: optimize this
    function expandURL(resourceURL, callbacks) {

        var found = false; //url present in one of the url shortners     

        for (var j = 0, l = shortURLList.length; j < l; j++) {
            var regExp = new RegExp('://' + shortURLList[j] + '/', "i");

            if (resourceURL.match(regExp) !== null) {

                //AJAX to http://api.longurl.org/v2/expand?url=http://bit.ly/JATvIs&format=json&callback=hhh
                var ajaxopts = $.extend({
                    url: "http://api.longurl.org/v2/expand",
                    dataType: 'jsonp',
                    data: {
                        url: resourceURL,
                        format: "json"
                                //callback: "?"
                    },
                    success: function (data) {

                        //the long url obtained from the API
                        callbacks.found(data['long-url']);
                    }
                }, settings.ajaxOptions || {});

                $.ajax(ajaxopts);

                found = true;
                break;
            }
        }

        if (!found) {

            callbacks.notFound(resourceURL);
        }
    }

    function rand(length, current) { //Found on http://stackoverflow.com/questions/1349404/generate-a-string-of-5-random-characters-in-javascript
        current = current ? current : '';
        return length ? rand(--length, "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz".charAt(Math.floor(Math.random() * 60)) + current) : current;
    }

    function getRequestUrl(provider, externalUrl) {
        var url = provider.apiendpoint,
                qs = "",
                i;
        url += (url.indexOf("?") <= 0) ? "?" : "&";
        url = url.replace('#', '%23');

        if (provider.maxWidth !== null && (typeof provider.params.maxwidth === 'undefined' || provider.params.maxwidth === null)) {
            provider.params.maxwidth = provider.maxWidth;
        }

        if (provider.maxHeight !== null && (typeof provider.params.maxheight === 'undefined' || provider.params.maxheight === null)) {
            provider.params.maxheight = provider.maxHeight;
        }

        for (i in provider.params) {
            // We don't want them to jack everything up by changing the callback parameter
            if (i == provider.callbackparameter)
                continue;

            // allows the options to be set to null, don't send null values to the server as parameters
            if (provider.params[i] !== null)
                qs += "&" + escape(i) + "=" + provider.params[i];
        }

        url += "format=" + provider.format + "&url=" + escape(externalUrl) + qs;
        if (provider.dataType != 'json')
            url += "&" + provider.callbackparameter + "=?";

        return url;
    }

    function success(oembedData, externalUrl, container) {

        //cache the data 
        cache.embedData[externalUrl] = oembedData.code;
        //$('#jqoembeddata').data(externalUrl, oembedData.code);

        /*(if (settings.placeholder && CODOF.oembed.placeHolders.indexOf(externalUrl) === -1) {
         
         CODOF.oembed.placeHolders.push(externalUrl);
         }*/
        //settings.beforeEmbed.call(container, oembedData);
        settings.onEmbed.call(container, oembedData);
        //settings.afterEmbed.call(container, oembedData);
    }

    /*function embed_exists(url) {
     
     if (!settings.placeholder) {
     
     return true;
     }
     
     return CODOF.oembed.placeHolders.indexOf(url) > -1;
     }*/

    function embedCode(container, externalUrl, embedProvider) {

        if (typeof cache.embedData[externalUrl] !== 'undefined' && embedProvider.embedtag.tag != 'iframe'/* && embed_exists(externalUrl)*/) {

            var oembedData = {code: cache.embedData[externalUrl]};
            success(oembedData, externalUrl, container);
        } else if (embedProvider.yql) {

            var from = embedProvider.yql.from || 'htmlstring';
            var url = embedProvider.yql.url ? embedProvider.yql.url(externalUrl) : externalUrl;

            var query = 'SELECT * FROM '
                    + from
                    + ' WHERE url="' + (url) + '"'
                    + " and " + (/html/.test(from) ? 'xpath' : 'itemPath') + "='" + (embedProvider.yql.xpath || '/') + "'";
            if (from == 'html')
                query += " and compat='html5'";
            var ajaxopts = $.extend({
                url: "http://query.yahooapis.com/v1/public/yql",
                dataType: 'jsonp',
                data: {
                    q: query,
                    format: "json",
                    env: 'store://datatables.org/alltableswithkeys',
                    callback: "?"
                },
                success: function (data) {
                    var result;

                    if (embedProvider.yql.xpath || embedProvider.yql.xpath == '//meta|//title|//link|//img') {
                        var meta = {};
                        if (data.query.results == null) {
                            data.query.results = {"meta": []};
                        }

                        for (var i = 0, l = data.query.results.meta.length; i < l; i++) {
                            var name = data.query.results.meta[i].name || data.query.results.meta[i].property || null;
                            if (name == null)
                                continue;
                            meta[name.toLowerCase()] = data.query.results.meta[i].content;
                        }

                        if (!meta.hasOwnProperty("title") || !meta.hasOwnProperty("og:title")) {
                            if (data.query.results.title != null) {
                                meta.title = data.query.results.title;
                            }
                        }


                        //load a low quality icon
                        if (!meta.hasOwnProperty("og:image") && data.query.results.hasOwnProperty("link")) {
                            for (var i = 0, l = data.query.results.link.length; i < l; i++) {
                                if (data.query.results.link[i].hasOwnProperty("rel")) {
                                    if (data.query.results.link[i].rel == "apple-touch-icon") {
                                        if (data.query.results.link[i].href.charAt(0) == "/") {
                                            meta["og:image"] = url.match(/^(([a-z]+:)?(\/\/)?[^\/]+\/).*$/)[1] + data.query.results.link[i].href;
                                        } else {
                                            meta["og:image"] = data.query.results.link[i].href;
                                        }
                                    }
                                }
                            }
                        }

                        var process = function (lowQuality) {

                            if (lowQuality) {
                                var img;
                                //first check array because Array returns true for instanceof Object
                                if (data.query.results.img instanceof Array) {

                                    img = data.query.results.img[0];
                                } else if (data.query.results.img instanceof Object) {

                                    img = data.query.results.img;
                                }

                                meta["og:image"] = img.src ? img.src : meta["og:image"];
                            }


                            //console.log(meta)
                            result = embedProvider.yql.datareturn(meta);
                            var oembedData = $.extend({}, result);
                            oembedData.code = $(result);

                            success(oembedData, externalUrl, container);
                        };

                        if ("og:image" in meta) {

                            var img = new Image();

                            img.onload = function () {

                                if (img.naturalWidth < settings.minWidth
                                        || img.naturalHeight < settings.minHeight) {

                                    process(true);
                                } else {

                                    process(false);
                                }
                            };
                            img.onerror = function () {

                                process(true);
                            };
                            img.src = meta["og:image"];
                            //console.log(meta["og:image"])
                        } else {

                            process(true);
                        }


                    } else {
                        result = embedProvider.yql.datareturn ? embedProvider.yql.datareturn(data.query.results) : data.query.results.result;
                        var oembedData = $.extend({}, result);
                        oembedData.code = $(result);

                        success(oembedData, externalUrl, container);

                    }
                    if (result === false)
                        return;


                },
                error: settings.onError.call(container, externalUrl, embedProvider)
            }, settings.ajaxOptions || {});

            $.ajax(ajaxopts);
        } else if (embedProvider.templateRegex) {
            if (embedProvider.embedtag.tag !== '') {
                var flashvars = embedProvider.embedtag.flashvars || '';
                var tag = embedProvider.embedtag.tag || 'embed';
                var width = embedProvider.embedtag.width || 'auto';
                var nocache = embedProvider.embedtag.nocache || 0;
                var height = embedProvider.embedtag.height || 'auto';
                var src = externalUrl.replace(embedProvider.templateRegex, embedProvider.apiendpoint);

                //console.log(externalUrl);
                //console.log(embedProvider);
                if (!embedProvider.nocache)
                    src += '&jqoemcache=' + rand(5);
                if (embedProvider.apikey)
                    src = src.replace('_APIKEY_', settings.apikeys[embedProvider.name]);


                var placeit = false;
                var supportedPreview = false;
                var badTags = ['iframe', 'embed'];
                var oldTag = tag;

                if (badTags.indexOf(tag) > -1 && settings.placeholder) {

                    var names = ['youtube', 'funnyordie', 'metacafe'];
                    tag = 'div';


                    if (names.indexOf(embedProvider.name) > -1) {

                        supportedPreview = true;
                        tag = 'img';
                    }

                    placeit = true;
                }

                var generdateCodeTag = function (tag) {
                    var code = $('<' + tag + '/>')
                            .attr('src', src)
                            .attr('width', width)
                            .attr('height', height)
                            .attr('allowfullscreen', embedProvider.embedtag.allowfullscreen || 'true')
                            .attr('allowscriptaccess', embedProvider.embedtag.allowfullscreen || 'always')
                            .css('max-height', settings.maxHeight || 'auto')
                            .css('max-width', settings.maxWidth || 'auto');
                    if (tag == 'embed')
                        code
                                .attr('type', embedProvider.embedtag.type || "application/x-shockwave-flash")
                                .attr('flashvars', externalUrl.replace(embedProvider.templateRegex, flashvars));
                    if (tag == 'iframe')
                        code
                                .attr('scrolling', embedProvider.embedtag.scrolling || "no")
                                .attr('frameborder', embedProvider.embedtag.frameborder || "0");

                    return code;
                };

                var code = generdateCodeTag(tag);
                var oldCode = generdateCodeTag(oldTag);

                if (placeit) {

                    // .attr('data-src', src)
                    // .attr('data-origsrc', externalUrl);

                    if (!supportedPreview) {

                        code.html("<span>" + embedProvider.name + "</span>").addClass("codo_embed_placeholder");
                    } else {

                        if (embedProvider.name === 'youtube') {

                            if (externalUrl.indexOf('watch') > -1) {

                                code.attr('src', 'https://i1.ytimg.com/vi/' + externalUrl.split("?v=")[1].replace("&feature=youtu.be", "") + '/hqdefault.jpg');
                            } else {

                                code.attr('src', 'https://i1.ytimg.com/vi/' + externalUrl.split("embed/")[1] + '/hqdefault.jpg');
                            }
                        } else if (embedProvider.name === 'funnyordie') {

                            var url = externalUrl.split('videos/')[1];
                            url = url.split('/')[0]
                            code.attr('src', 'http://r.fod4.com/s=w750,pd1/http://t.fod4.com/t/' + url + '/c1280x720_9.jpg');

                        }
                    }

                    code.addClass('codo_embedded_content');
                    code.wrap("<div class='codo_embed_placeholder_container'></div>");
                    code = code.parent();

                    //thumbnail exists but onclick original should load
                    code.callback = function (el) {

                        var $container = $(el);
                        var $el = $container.find('.codo_embedded_content');
                        $el.css('visibility', 'hidden');
                        var loader = $container.find('.codo_loading_embed_gif');
                        var playBtn = $container.find('i');
                        playBtn.hide();
                        loader.show();


                        oldCode.load(function () {

                            loader.hide();
                            $el.css('visibility', 'visible');
                            if ($el.is('div')) {

                                $container.css('display', 'block'); //make iframe fullwidth   
                                //here div is checked, because placeholder of iframe
                                //is a div
                            }

                        });
                        setTimeout(function () {
                            loader.hide();
                            //loader.css('left', Math.floor(div.attr('width') / 2) + "px");
                            if ($el.is('div')) {

                                $container.css('display', 'block'); //make iframe fullwidth   
                                //here div is checked, because placeholder of iframe
                                //is a div
                            }

                        }, 5000); //some unknown reason if resource not loaded

                        $el.replaceWith(oldCode);
                    };


                }

                var oembedData = {code: code};
                success(oembedData, externalUrl, container);
            } else if (embedProvider.apiendpoint) {
                //Add APIkey if true
                if (embedProvider.apikey)
                    embedProvider.apiendpoint = embedProvider.apiendpoint.replace('_APIKEY_', settings.apikeys[embedProvider.name]);
                ajaxopts = $.extend({
                    url: externalUrl.replace(embedProvider.templateRegex, embedProvider.apiendpoint),
                    dataType: 'jsonp',
                    success: function (data) {
                        var oembedData = $.extend({}, data);
                        oembedData.code = embedProvider.templateData(data);
                        success(oembedData, externalUrl, container);
                    },
                    error: settings.onError.call(container, externalUrl, embedProvider)
                }, settings.ajaxOptions || {});

                $.ajax(ajaxopts);
            } else {
                var oembedData = {code: externalUrl.replace(embedProvider.templateRegex, embedProvider.template)};
                success(oembedData, externalUrl, container);
            }
        } else {

            var requestUrl = getRequestUrl(embedProvider, externalUrl),
                    ajaxopts = $.extend({
                        url: requestUrl,
                        dataType: embedProvider.dataType || 'jsonp',
                        success: function (data) {
                            var oembedData = $.extend({}, data);
                            switch (oembedData.type) {
                                case "file": //Deviant Art has this
                                case "photo":
                                    oembedData.code = $.fn.oembed.getPhotoCode(externalUrl, oembedData);
                                    break;
                                case "video":
                                case "rich":
                                    oembedData.code = $.fn.oembed.getRichCode(externalUrl, oembedData);
                                    break;
                                default:
                                    oembedData.code = $.fn.oembed.getGenericCode(externalUrl, oembedData);
                                    break;
                            }
                            //console.log(oembedData);
                            success(oembedData, externalUrl, container);
                        },
                        error: settings.onError.call(container, externalUrl, embedProvider)
                    }, settings.ajaxOptions || {});

            $.ajax(ajaxopts);
        }
    }
    ;

    function getNormalizedParams(params) {
        if (params === null)
            return null;
        var key, normalizedParams = {};
        for (key in params) {
            if (key !== null)
                normalizedParams[key.toLowerCase()] = params[key];
        }
        return normalizedParams;
    }

    /* Public functions */
    $.fn.oembed.insertCode = function (container, embedMethod, oembedData) {

        if (oembedData === null)
            return;
        if (embedMethod == 'auto' && container.attr("href") !== null)
            embedMethod = 'append';
        else if (embedMethod == 'auto')
            embedMethod = 'replace';
        switch (embedMethod) {
            case "replace":
                container.replaceWith(oembedData.code);
                break;
            case "fill":
                container.html(oembedData.code);
                break;
            case "append":
                container.wrap('<div class="oembedall-container"></div>');
                var oembedContainer = container.parent();
                if (settings.includeHandle) {

                    if (settings.loadOnClick) {

                        oembedData.code.prepend('<div class="codo_loading_embed_gif"></div><i class="icon-play"></i>')
                    }

                    $('<span class="oembedall-closehide icon-play"></span>').insertBefore(container).click(function () {
                        var encodedString = encodeURIComponent($(this).text());
                        $(this).html((encodedString == '%E2%86%91') ? '&darr;' : '&uarr;');
                        $(this).parent().children().last().toggle();
                    });
                }
                oembedContainer.append('<br/>');

                var el;
                try {
                    el = oembedData.code.clone().appendTo(oembedContainer);
                } catch (e) {
                    el = oembedContainer.append(oembedData.code);
                }

                el.click(function () {

                    oembedData.code.callback(this);
                });

                /* Make videos semi-responsive
                 * If parent div width less than embeded iframe video then iframe gets shrunk to fit smaller width
                 * If parent div width greater thans embed iframe use the max widht
                 * - works on youtubes and vimeo
                 */
                if (settings.maxWidth) {
                    var post_width = oembedContainer.parent().width();
                    if (post_width < settings.maxWidth)
                    {
                        var iframe_width_orig = $('iframe', oembedContainer).width();
                        var iframe_height_orig = $('iframe', oembedContainer).height();
                        var ratio = iframe_width_orig / post_width;
                        $('iframe', oembedContainer).width(iframe_width_orig / ratio);
                        $('iframe', oembedContainer).height(iframe_height_orig / ratio);
                    } else {
                        if (settings.maxWidth) {
                            $('iframe', oembedContainer).width(settings.maxWidth);
                        }
                        if (settings.maxHeight) {
                            $('iframe', oembedContainer).height(settings.maxHeight);
                        }
                    }
                }
                break;
        }

    };

    $.fn.oembed.getPhotoCode = function (url, oembedData) {
        var code, alt = oembedData.title ? oembedData.title : '';
        alt += oembedData.author_name ? ' - ' + oembedData.author_name : '';
        alt += oembedData.provider_name ? ' - ' + oembedData.provider_name : '';
        if (oembedData.url) {
            code = '<div><a href="' + url + '" target=\'_blank\'><img src="' + oembedData.url + '" alt="' + alt + '"/></a></div>';
        } else if (oembedData.thumbnail_url) {
            var newURL = oembedData.thumbnail_url.replace('_s', '_b');
            code = '<div><a href="' + url + '" target=\'_blank\'><img src="' + newURL + '" alt="' + alt + '"/></a></div>';
        } else {
            code = '<div>Error loading this picture</div>';
        }
        if (oembedData.html)
            code += "<div>" + oembedData.html + "</div>";
        return code;
    };

    $.fn.oembed.getRichCode = function (url, oembedData) {

        if (!settings.placeholder || oembedData.html.indexOf('iframe') === -1) {

            var code = $(oembedData.html);
            code.attr({'width': '695px', height: '390px'});
        } else {
            var tag = "<div/>", tn = false;
            if (typeof oembedData.thumbnail_url !== "undefined") {

                tag = "<img/>";
                tn = true;
            }

            //code.replaceWith($(tag));
            var code = $(tag);


            if (oembedData.thumbnail_width && oembedData.thumbnail_height) {

                code.attr('width', oembedData.thumbnail_width)
                        .attr('height', oembedData.thumbnail_height);
            }

            code.attr('src', (tn) ? oembedData.thumbnail_url : url);

            if (settings.loadOnClick) {

                code.addClass('codo_embedded_content');
                code.wrap("<div class='codo_embed_placeholder_container'></div>");
                code = code.parent();

                //thumbnail exists but onclick original should load
                code.callback = function (el) {

                    var $container = $(el);
                    var $el = $container.find('.codo_embedded_content');
                    $el.css('visibility', 'hidden');
                    var loader = $container.find('.codo_loading_embed_gif');
                    loader.show();
                    var div = $(oembedData.html);
                    var playBtn = $container.find('i');
                    playBtn.hide();

                    //loader.css('left', Math.floor(div.attr('width') / 2) + "px");


                    div.load(function () {

                        loader.hide();
                        $el.css('visibility', 'visible');
                    });
                    setTimeout(function () {
                        loader.hide();
                    }, 5000); //some unknown reason if resource not loaded

                    $el.replaceWith(div);
                };


                //code.text(embedProvider.name).addClass("codo_embed_placeholder");
            }

        }
        return code;
    };

    $.fn.oembed.getGenericCode = function (url, oembedData) {
        var title = (oembedData.title !== null) ? oembedData.title : url,
                code = '<a href="' + url + '" target="_blank">' + title + '</a>';
        if (oembedData.html)
            code += "<div>" + oembedData.html + "</div>";
        return code;
    };

    $.fn.oembed.getOEmbedProvider = function (url) {
        for (var i = 0; i < $.fn.oembed.providers.length; i++) {
            for (var j = 0, l = $.fn.oembed.providers[i].urlschemes.length; j < l; j++) {
                var regExp = new RegExp($.fn.oembed.providers[i].urlschemes[j], "i");
                if (url.match(regExp) !== null) {
                    return $.fn.oembed.providers[i];
                }
            }
        }
        return null;
    };

    $.fn.oembed.OEmbedProvider = function (name, type, urlschemesarray, apiendpoint, extraSettings) {
        this.name = name;
        this.type = type; // "photo", "video", "link", "rich", null
        this.urlschemes = urlschemesarray;
        this.apiendpoint = apiendpoint;
        this.maxWidth = 500;
        this.maxHeight = 400;
        extraSettings = extraSettings || {};

        if (extraSettings.useYQL) {

            if (extraSettings.useYQL == 'xml') {
                extraSettings.yql = {xpath: "//oembed/html", from: 'xml'
                    , apiendpoint: this.apiendpoint
                    , url: function (externalurl) {
                        return this.apiendpoint + '?format=xml&url=' + externalurl
                    }
                    , datareturn: function (results) {
                        return results.html.replace(/.*\[CDATA\[(.*)\]\]>$/, '$1') || ''
                    }
                };
            } else {
                extraSettings.yql = {from: 'json'
                    , apiendpoint: this.apiendpoint
                    , url: function (externalurl) {
                        return this.apiendpoint + '?format=json&url=' + externalurl
                    }
                    , datareturn: function (results) {
                        if (results.json.type != 'video' && (results.json.url || results.json.thumbnail_url)) {
                            return '<img src="' + (results.json.url || results.json.thumbnail_url) + '" />';
                        }
                        return results.json.html || ''
                    }
                };
            }
            this.apiendpoint = null;
        }


        for (var property in extraSettings) {
            this[property] = extraSettings[property];
        }

        this.format = this.format || 'json';
        this.callbackparameter = this.callbackparameter || "callback";
        this.embedtag = this.embedtag || {tag: ""};


    };

    /*
     * Function to update existing providers
     *
     * @param  {String}    name             The name of the provider
     * @param  {String}    type             The type of the provider can be "file", "photo", "video", "rich"
     * @param  {String}    urlshemesarray   Array of url of the provider
     * @param  {String}    apiendpoint      The endpoint of the provider
     * @param  {String}    extraSettings    Extra settings of the provider
     */
    $.fn.updateOEmbedProvider = function (name, type, urlschemesarray, apiendpoint, extraSettings) {
        for (var i = 0; i < $.fn.oembed.providers.length; i++) {
            if ($.fn.oembed.providers[i].name === name) {
                if (type !== null) {
                    $.fn.oembed.providers[i].type = type;
                }
                if (urlschemesarray !== null) {
                    $.fn.oembed.providers[i].urlschemes = urlschemesarray;
                }
                if (apiendpoint !== null) {
                    $.fn.oembed.providers[i].apiendpoint = apiendpoint;
                }
                if (extraSettings !== null) {
                    $.fn.oembed.providers[i].extraSettings = extraSettings;
                    for (var property in extraSettings) {
                        if (property !== null && extraSettings[property] !== null) {
                            $.fn.oembed.providers[i][property] = extraSettings[property];
                        }
                    }
                }
            }
        }
    };
    /* Native & common providers */
    $.fn.oembed.providers = [
        //Video
        new $.fn.oembed.OEmbedProvider("youtube", "video", ["youtube\\.com/watch.+v=[\\w-]+&?", "youtu\\.be/[\\w-]+", "youtube.com/embed"], '//www.youtube.com/embed/$1?wmode=transparent', {
            templateRegex: /.*(?:v\=|be\/|embed\/)([\w\-]+)&?.*/, embedtag: {tag: 'iframe', width: '425', height: '349'}
        }),
        //new $.fn.oembed.OEmbedProvider("youtube", "video", ["youtube\\.com/watch.+v=[\\w-]+&?", "youtu\\.be/[\\w-]+"], '//www.youtube.com/oembed', {useYQL:'json'}),
        //new $.fn.oembed.OEmbedProvider("youtubeiframe", "video", ["youtube.com/embed"],  "$1?wmode=transparent",
        //  {templateRegex:/(.*)/,embedtag : {tag: 'iframe', width:'425',height: '349'}}), 
        //new $.fn.oembed.OEmbedProvider("wistia", "video", ["wistia.com/m/.+", "wistia.com/embed/.+", "wi.st/m/.+", "wi.st/embed/.+"], '//fast.wistia.com/oembed', {useYQL: 'json'}),
        //new $.fn.oembed.OEmbedProvider("xtranormal", "video", ["xtranormal\\.com/watch/.+"], "//www.xtranormal.com/xtraplayr/$1/$2", {
        //    templateRegex: /.*com\/watch\/([\w\-]+)\/([\w\-]+).*/, embedtag: {tag: 'iframe', width: '320', height: '269'}}),
        new $.fn.oembed.OEmbedProvider("scivee", "video", ["scivee.tv/node/.+"], "//www.scivee.tv/flash/embedCast.swf?", {
            templateRegex: /.*tv\/node\/(.+)/, embedtag: {width: '480', height: '400', flashvars: "id=$1&type=3"}}),
        new $.fn.oembed.OEmbedProvider("veoh", "video", ["veoh.com/watch/.+"], "//www.veoh.com/swf/webplayer/WebPlayer.swf?version=AFrontend.5.7.0.1337&permalinkId=$1&player=videodetailsembedded&videoAutoPlay=0&id=anonymous", {
            templateRegex: /.*watch\/([^\?]+).*/, embedtag: {width: '410', height: '341'}}),
        //new $.fn.oembed.OEmbedProvider("gametrailers", "video", ["gametrailers\\.com/videos/.+"], "//media.mtvnservices.com/mgid:moses:video:gametrailers.com:$2", {
        //    templateRegex: /.*com\/video\/([\w\-]+)\/([\w\-]+).*/, embedtag: {width: '512', height: '288'}}),
        new $.fn.oembed.OEmbedProvider("funnyordie", "video", ["funnyordie\\.com/videos/.+"], "//player.ordienetworks.com/flash/fodplayer.swf?", {
            templateRegex: /.*videos\/([^\/]+)\/([^\/]+)?/, embedtag: {width: 512, height: 328, flashvars: "key=$1"}}),
        new $.fn.oembed.OEmbedProvider("colledgehumour", "video", ["collegehumor\\.com/video/.+"], "//www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=$1&use_node_id=true&fullscreen=1",
                {templateRegex: /.*video\/([^\/]+).*/, embedtag: {width: 600, height: 338}}),
        //new $.fn.oembed.OEmbedProvider("metacafe", "video", ["metacafe\\.com/watch/.+"], "//www.metacafe.com/fplayer/$1/$2.swf",
        //        {templateRegex: /.*watch\/(\d+)\/(\w+)\/.*/, embedtag: {width: 400, height: 345}}),
        new $.fn.oembed.OEmbedProvider("bambuser", "video", ["bambuser\\.com\/v\/.*"], "//embed.bambuser.com/broadcast/$1",
                {templateRegex: /.*bambuser\.com\/v\/(\w+).*/, embedtag: {tag: 'iframe', width: 512, height: 339}}),
        new $.fn.oembed.OEmbedProvider("twitvid", "video", ["twitvid\\.com/.+"], "//www.twitvid.com/embed.php?guid=$1&autoplay=0",
                {templateRegex: /.*twitvid\.com\/(\w+).*/, embedtag: {tag: 'iframe', width: 480, height: 360}}),
        new $.fn.oembed.OEmbedProvider("aniboom", "video", ["aniboom\\.com/animation-video/.+"], "//api.aniboom.com/e/$1",
                {templateRegex: /.*animation-video\/(\d+).*/, embedtag: {width: 594, height: 334}}),
        new $.fn.oembed.OEmbedProvider("vzaar", "video", ["vzaar\\.com/videos/.+", "vzaar.tv/.+"], "//view.vzaar.com/$1/player?",
                {templateRegex: /.*\/(\d+).*/, embedtag: {tag: 'iframe', width: 576, height: 324}}),
        new $.fn.oembed.OEmbedProvider("snotr", "video", ["snotr\\.com/video/.+"], "//www.snotr.com/embed/$1",
                {templateRegex: /.*\/(\d+).*/, embedtag: {tag: 'iframe', width: 400, height: 330, nocache: 1}}),
        new $.fn.oembed.OEmbedProvider("youku", "video", ["v.youku.com/v_show/id_.+"], "//player.youku.com/player.php/sid/$1/v.swf",
                {templateRegex: /.*id_(.+)\.html.*/, embedtag: {width: 480, height: 400, nocache: 1}}),
        new $.fn.oembed.OEmbedProvider("tudou", "video", ["tudou.com/programs/view/.+\/"], "//www.tudou.com/v/$1/v.swf",
                {templateRegex: /.*view\/(.+)\//, embedtag: {width: 480, height: 400, nocache: 1}}),
        new $.fn.oembed.OEmbedProvider("embedr", "video", ["embedr\\.com/playlist/.+"], "//embedr.com/swf/slider/$1/425/520/default/false/std?",
                {templateRegex: /.*playlist\/([^\/]+).*/, embedtag: {width: 425, height: 520}}),
        new $.fn.oembed.OEmbedProvider("blip", "video", ["blip\\.tv/.+"], "//blip.tv/oembed/"),
        new $.fn.oembed.OEmbedProvider("minoto-video", "video", ["//api.minoto-video.com/publishers/.+/videos/.+", "//dashboard.minoto-video.com/main/video/details/.+", "//embed.minoto-video.com/.+"], "//api.minoto-video.com/services/oembed.json", {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("animoto", "video", ["animoto.com/play/.+"], "//animoto.com/services/oembed"),
        new $.fn.oembed.OEmbedProvider("hulu", "video", ["hulu\\.com/watch/.*"], "//www.hulu.com/api/oembed.json"),
        new $.fn.oembed.OEmbedProvider("ustream", "video", ["ustream\\.tv/recorded/.*"], "//www.ustream.tv/oembed", {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("videojug", "video", ["videojug\\.com/(film|payer|interview).*"], "//www.videojug.com/oembed.json", {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("sapo", "video", ["videos\\.sapo\\.pt/.*"], "//videos.sapo.pt/oembed", {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("vodpod", "video", ["vodpod.com/watch/.*"], "//vodpod.com/oembed.js", {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("vimeo", "video", ["www\.vimeo\.com\/groups\/.*\/videos\/.*", "www\.vimeo\.com\/.*", "vimeo\.com\/groups\/.*\/videos\/.*", "vimeo\.com\/.*"], "//vimeo.com/api/oembed.json"),
        new $.fn.oembed.OEmbedProvider("dailymotion", "video", ["dailymotion\\.com/.+"], '//www.dailymotion.com/services/oembed'),
        new $.fn.oembed.OEmbedProvider("5min", "video", ["www\\.5min\\.com/.+"], '//api.5min.com/oembed.xml', {useYQL: 'xml'}),
        new $.fn.oembed.OEmbedProvider("National Film Board of Canada", "video", ["nfb\\.ca/film/.+"], '//www.nfb.ca/remote/services/oembed/', {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("qik", "video", ["qik\\.com/\\w+"], '//qik.com/api/oembed.json', {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("revision3", "video", ["revision3\\.com"], "//revision3.com/api/oembed/"),
        new $.fn.oembed.OEmbedProvider("dotsub", "video", ["dotsub\\.com/view/.+"], "//dotsub.com/services/oembed", {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("clikthrough", "video", ["clikthrough\\.com/theater/video/\\d+"], "//clikthrough.com/services/oembed"),
        new $.fn.oembed.OEmbedProvider("Kinomap", "video", ["kinomap\\.com/.+"], "//www.kinomap.com/oembed"),
        new $.fn.oembed.OEmbedProvider("VHX", "video", ["vhx.tv/.+"], "//vhx.tv/services/oembed.json"),
        new $.fn.oembed.OEmbedProvider("bambuser", "video", ["bambuser.com/.+"], "//api.bambuser.com/oembed/iframe.json"),
        new $.fn.oembed.OEmbedProvider("justin.tv", "video", ["justin.tv/.+"], '//api.justin.tv/api/embed/from_url.json', {useYQL: 'json'}),
        //Audio 
        new $.fn.oembed.OEmbedProvider("official.fm", "rich", ["official.fm/.+"], '//official.fm/services/oembed', {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("chirbit", "rich", ["chirb.it/.+"], '//chirb.it/oembed.json', {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("Huffduffer", "rich", ["huffduffer.com/[-.\\w@]+/\\d+"], "//huffduffer.com/oembed"),
        new $.fn.oembed.OEmbedProvider("Spotify", "rich", ["open.spotify.com/(track|album|user)/"], "https://embed.spotify.com/oembed/"),
        new $.fn.oembed.OEmbedProvider("shoudio", "rich", ["shoudio.com/.+", "shoud.io/.+"], "//shoudio.com/api/oembed"),
        new $.fn.oembed.OEmbedProvider("mixcloud", "rich", ["mixcloud.com/.+"], '//www.mixcloud.com/oembed/', {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("rdio.com", "rich", ["rd.io/.+", "rdio.com"], "//www.rdio.com/api/oembed/"),
        new $.fn.oembed.OEmbedProvider("Soundcloud", "rich", ["soundcloud.com/.+", "snd.sc/.+"], "//soundcloud.com/oembed", {format: 'js'}),
        new $.fn.oembed.OEmbedProvider("bandcamp", "rich", ["bandcamp\\.com/album/.+"], null,
                {yql: {xpath: "//meta[contains(@content, \\'EmbeddedPlayer\\')]", from: 'html'
                        , datareturn: function (results) {
                            return results.meta ? '<iframe width="400" height="100" src="' + results.meta.content + '" allowtransparency="true" frameborder="0"></iframe>' : false;
                        }
                    }
                }),
        //Photo
        new $.fn.oembed.OEmbedProvider("deviantart", "photo", ["deviantart.com/.+", "fav.me/.+", "deviantart.com/.+"], "//backend.deviantart.com/oembed", {format: 'jsonp'}),
        new $.fn.oembed.OEmbedProvider("skitch", "photo", ["skitch.com/.+"], null,
                {yql: {xpath: "json", from: 'json'
                        , url: function (externalurl) {
                            return '//skitch.com/oembed/?format=json&url=' + externalurl
                        }
                        , datareturn: function (data) {
                            return $.fn.oembed.getPhotoCode(data.json.url, data.json);
                        }
                    }
                }),
        new $.fn.oembed.OEmbedProvider("mobypicture", "photo", ["mobypicture.com/user/.+/view/.+", "moby.to/.+"], "//api.mobypicture.com/oEmbed"),
        new $.fn.oembed.OEmbedProvider("flickr", "photo", ["flickr\\.com/photos/.+"], "//flickr.com/services/oembed", {callbackparameter: 'jsoncallback'}),
        new $.fn.oembed.OEmbedProvider("photobucket", "photo", ["photobucket\\.com/(albums|groups)/.+"], "//photobucket.com/oembed/"),
        new $.fn.oembed.OEmbedProvider("instagram", "photo", ["instagr\\.?am(\\.com)?/.+"], "//api.instagram.com/oembed"),
        //new $.fn.oembed.OEmbedProvider("yfrog", "photo", ["yfrog\\.(com|ru|com\\.tr|it|fr|co\\.il|co\\.uk|com\\.pl|pl|eu|us)/.+"], "//www.yfrog.com/api/oembed",{useYQL:"json"}),
        new $.fn.oembed.OEmbedProvider("SmugMug", "photo", ["smugmug.com/[-.\\w@]+/.+"], "//api.smugmug.com/services/oembed/"),
        new $.fn.oembed.OEmbedProvider("dribbble", "photo", ["dribbble.com/shots/.+"], "//api.dribbble.com/shots/$1?callback=?",
                {templateRegex: /.*shots\/([\d]+).*/,
                    templateData: function (data) {
                        if (!data.image_teaser_url)
                            return false;
                        return  '<img src="' + data.image_teaser_url + '"/>';
                    }
                }),
        new $.fn.oembed.OEmbedProvider("chart.ly", "photo", ["chart\\.ly/[a-z0-9]{6,8}"], "//chart.ly/uploads/large_$1.png",
                {templateRegex: /.*ly\/([^\/]+).*/, embedtag: {tag: 'img'}, nocache: 1}),
        //new $.fn.oembed.OEmbedProvider("stocktwits.com", "photo", ["stocktwits\\.com/message/.+"], "//charts.stocktwits.com/production/original_$1.png?",
        //	{ templateRegex: /.*message\/([^\/]+).*/, embedtag: { tag: 'img'},nocache:1 }),
        new $.fn.oembed.OEmbedProvider("circuitlab", "photo", ["circuitlab.com/circuit/.+"], "https://www.circuitlab.com/circuit/$1/screenshot/540x405/",
                {templateRegex: /.*circuit\/([^\/]+).*/, embedtag: {tag: 'img'}, nocache: 1}),
        new $.fn.oembed.OEmbedProvider("23hq", "photo", ["23hq.com/[-.\\w@]+/photo/.+"], "//www.23hq.com/23/oembed", {useYQL: "json"}),
        new $.fn.oembed.OEmbedProvider("img.ly", "photo", ["img\\.ly/.+"], "//img.ly/show/thumb/$1",
                {templateRegex: /.*ly\/([^\/]+).*/, embedtag: {tag: 'img'}, nocache: 1
                }),
        new $.fn.oembed.OEmbedProvider("twitgoo.com", "photo", ["twitgoo\\.com/.+"], "//twitgoo.com/show/thumb/$1",
                {templateRegex: /.*com\/([^\/]+).*/, embedtag: {tag: 'img'}, nocache: 1}),
        new $.fn.oembed.OEmbedProvider("imgur.com", "photo", ["imgur\\.com/gallery/.+"], "//imgur.com/$1l.jpg",
                {templateRegex: /.*gallery\/([^\/]+).*/, embedtag: {tag: 'img'}, nocache: 1}),
        new $.fn.oembed.OEmbedProvider("visual.ly", "rich", ["visual\\.ly/.+"], null,
                {yql: {xpath: "//a[@id=\\'gc_article_graphic_image\\']/img", from: 'htmlstring'}
                }),
        //Rich
        new $.fn.oembed.OEmbedProvider("twitter", "rich", ["twitter.com/.+"], "https://api.twitter.com/1/statuses/oembed.json"),
        new $.fn.oembed.OEmbedProvider("gmep", "rich", ["gmep.imeducate.com/.*", "gmep.org/.*"], "//gmep.org/oembed.json"),
        new $.fn.oembed.OEmbedProvider("urtak", "rich", ["urtak.com/(u|clr)/.+"], "//oembed.urtak.com/1/oembed"),
        new $.fn.oembed.OEmbedProvider("cacoo", "rich", ["cacoo.com/.+"], "//cacoo.com/oembed.json"),
        new $.fn.oembed.OEmbedProvider("dailymile", "rich", ["dailymile.com/people/.*/entries/.*"], "//api.dailymile.com/oembed"),
        new $.fn.oembed.OEmbedProvider("dipity", "rich", ["dipity.com/timeline/.+"], '//www.dipity.com/oembed/timeline/', {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("sketchfab", "rich", ["sketchfab.com/show/.+"], '//sketchfab.com/oembed', {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("speakerdeck", "rich", ["speakerdeck.com/.+"], '//speakerdeck.com/oembed.json', {useYQL: 'json'}),
        new $.fn.oembed.OEmbedProvider("popplet", "rich", ["popplet.com/app/.*"], "//popplet.com/app/Popplet_Alpha.swf?page_id=$1&em=1",
                {templateRegex: /.*#\/([^\/]+).*/, embedtag: {width: 460, height: 460}}),
        new $.fn.oembed.OEmbedProvider("pearltrees", "rich", ["pearltrees.com/.*"], "//cdn.pearltrees.com/s/embed/getApp?",
                {templateRegex: /.*N-f=1_(\d+).*N-p=(\d+).*/, embedtag: {width: 460, height: 460,
                        flashvars: "lang=en_US&amp;embedId=pt-embed-$1-693&amp;treeId=$1&amp;pearlId=$2&amp;treeTitle=Diagrams%2FVisualization&amp;site=www.pearltrees.com%2FF"}
                }),
        new $.fn.oembed.OEmbedProvider("prezi", "rich", ["prezi.com/.*"], "//prezi.com/bin/preziloader.swf?",
                {templateRegex: /.*com\/([^\/]+)\/.*/, embedtag: {width: 550, height: 400,
                        flashvars: "prezi_id=$1&amp;lock_to_path=0&amp;color=ffffff&amp;autoplay=no&amp;autohide_ctrls=0"
                    }
                }),
        new $.fn.oembed.OEmbedProvider("tourwrist", "rich", ["tourwrist.com/tours/.+"], null,
                {templateRegex: /.*tours.([\d]+).*/,
                    template: function (wm, tourid) {
                        setTimeout(function () {
                            if (loadEmbeds)
                                loadEmbeds();
                        }, 2000);
                        return "<div id='" + tourid + "' class='tourwrist-tour-embed direct'></div> <script type='text/javascript' src='//tourwrist.com/tour_embed.js'></script>";
                    }
                }),
        new $.fn.oembed.OEmbedProvider("meetup", "rich", ["meetup\\.(com|ps)/.+"], "//api.meetup.com/oembed"),
        new $.fn.oembed.OEmbedProvider("ebay", "rich", ["ebay\\.*"], "//togo.ebay.com/togo/togo.swf?2008013100",
                {templateRegex: /.*\/([^\/]+)\/(\d{10,13}).*/, embedtag: {width: 355, height: 300,
                        flashvars: "base=//togo.ebay.com/togo/&lang=en-us&mode=normal&itemid=$2&query=$1"
                    }
                }),
        new $.fn.oembed.OEmbedProvider("imdb", "rich", ["imdb.com/title/.+"], "//www.imdbapi.com/?i=$1&callback=?",
                {templateRegex: /.*\/title\/([^\/]+).*/,
                    templateData: function (data) {
                        if (!data.Title)
                            return false;
                        return '<div id="content"><h3><a class="nav-link" href="//imdb.com/title/' + data.imdbID + '/">' + data.Title + '</a> (' + data.Year + ')</h3><p>Rating: ' + data.imdbRating + '<br/>Genre: ' + data.Genre + '<br/>Starring: ' + data.Actors + '</p></div>  <div id="view-photo-caption">' + data.Plot + '</div></div>';
                    }
                }),
        new $.fn.oembed.OEmbedProvider("livejournal", "rich", ["livejournal.com/"], "//ljpic.seacrow.com/json/$2$4?jsonp=?"
                , {templateRegex: /(http:\/\/(((?!users).)+)\.livejournal\.com|.*users\.livejournal\.com\/([^\/]+)).*/,
                    templateData: function (data) {
                        if (!data.username)
                            return false;
                        return '<div><img src="' + data.image + '" align="left" style="margin-right: 1em;" /><span class="oembedall-ljuser"><a href="//' + data.username + '.livejournal.com/profile"><img src="//www.livejournal.com/img/userinfo.gif" alt="[info]" width="17" height="17" /></a><a href="//' + data.username + '.livejournal.com/">' + data.username + '</a></span><br />' + data.name + '</div>';
                    }
                }),
        new $.fn.oembed.OEmbedProvider("circuitbee", "rich", ["circuitbee\\.com/circuit/view/.+"], "//c.circuitbee.com/build/r/schematic-embed.html?id=$1",
                {templateRegex: /.*circuit\/view\/(\d+).*/, embedtag: {tag: 'iframe', width: '500', height: '350'}
                }),
        new $.fn.oembed.OEmbedProvider("googlecalendar", "rich", ["www.google.com/calendar/embed?.+"], "$1",
                {templateRegex: /(.*)/, embedtag: {tag: 'iframe', width: '800', height: '600'}
                }),
        new $.fn.oembed.OEmbedProvider("jsfiddle", "rich", ["jsfiddle.net/[^/]+/?"], "//jsfiddle.net/$1/embedded/result,js,resources,html,css/?",
                {templateRegex: /.*net\/([^\/]+).*/, embedtag: {tag: 'iframe', width: '100%', height: '300'}
                }),
        new $.fn.oembed.OEmbedProvider("jsbin", "rich", ["jsbin.com/.+"], "//jsbin.com/$1/?",
                {templateRegex: /.*com\/([^\/]+).*/, embedtag: {tag: 'iframe', width: '100%', height: '300'}
                }),
        new $.fn.oembed.OEmbedProvider("jotform", "rich", ["form.jotform.co/form/.+"], "$1?",
                {templateRegex: /(.*)/, embedtag: {tag: 'iframe', width: '100%', height: '507'}
                }),
        new $.fn.oembed.OEmbedProvider("reelapp", "rich", ["reelapp\\.com/.+"], "//www.reelapp.com/$1/embed",
                {templateRegex: /.*com\/(\S{6}).*/, embedtag: {tag: 'iframe', width: '400', height: '338'}
                }),
        new $.fn.oembed.OEmbedProvider("linkedin", "rich", ["linkedin.com/pub/.+"], "https://www.linkedin.com/cws/member/public_profile?public_profile_url=$1&format=inline&isFramed=true",
                {templateRegex: /(.*)/, embedtag: {tag: 'iframe', width: '368px', height: 'auto'}
                }),
        new $.fn.oembed.OEmbedProvider("timetoast", "rich", ["timetoast.com/timelines/[0-9]+"], "//www.timetoast.com/flash/TimelineViewer.swf?passedTimelines=$1",
                {templateRegex: /.*timelines\/([0-9]*)/, embedtag: {width: 550, height: 400, nocache: 1}
                }),
        new $.fn.oembed.OEmbedProvider("pastebin", "rich", ["pastebin\\.com/[\\S]{8}"], "//pastebin.com/embed_iframe.php?i=$1",
                {templateRegex: /.*\/(\S{8}).*/, embedtag: {tag: 'iframe', width: '100%', height: 'auto'}
                }),
        new $.fn.oembed.OEmbedProvider("mixlr", "rich", ["mixlr.com/.+"], "//mixlr.com/embed/$1?autoplay=ae",
                {templateRegex: /.*com\/([^\/]+).*/, embedtag: {tag: 'iframe', width: '100%', height: 'auto'}
                }),
        new $.fn.oembed.OEmbedProvider("pastie", "rich", ["pastie\\.org/.+"], null, {yql: {xpath: '//pre[@class="textmate-source"]'}}),
        new $.fn.oembed.OEmbedProvider("github", "rich", ["gist.github.com/.+"], "https://github.com/api/oembed"),
        new $.fn.oembed.OEmbedProvider("github", "rich", ["github.com/[-.\\w@]+/[-.\\w@]+"], "https://api.github.com/repos/$1/$2?callback=?"
                , {templateRegex: /.*\/([^\/]+)\/([^\/]+).*/,
                    templateData: function (data) {
                        if (!data.data.html_url)
                            return false;
                        return  '<div class="oembedall-githubrepos"><ul class="oembedall-repo-stats"><li>' + data.data.language + '</li><li class="oembedall-watchers"><a title="Watchers" href="' + data.data.html_url + '/watchers">&#x25c9; ' + data.data.watchers + '</a></li>'
                                + '<li class="oembedall-forks"><a title="Forks" href="' + data.data.html_url + '/network">&#x0265; ' + data.data.forks + '</a></li></ul><h3><a href="' + data.data.html_url + '">' + data.data.name + '</a></h3><div class="oembedall-body"><p class="oembedall-description">' + data.data.description + '</p>'
                                + '<p class="oembedall-updated-at">Last updated: ' + data.data.pushed_at + '</p></div></div>';
                    }
                }),
        new $.fn.oembed.OEmbedProvider("facebook", "rich", ["facebook.com/(people/[^\\/]+/\\d+|[^\\/]+$)"], "https://graph.facebook.com/$2$3/?callback=?"
                , {templateRegex: /.*facebook.com\/(people\/[^\/]+\/(\d+).*|([^\/]+$))/,
                    templateData: function (data) {
                        if (!data.id)
                            return false;
                        var out = '<div class="oembedall-facebook1"><div class="oembedall-facebook2"><a href="//www.facebook.com/">facebook</a> ';
                        if (data.from)
                            out += '<a href="//www.facebook.com/' + data.from.id + '">' + data.from.name + '</a>';
                        else if (data.link)
                            out += '<a href="' + data.link + '">' + data.name + '</a>';
                        else if (data.username)
                            out += '<a href="//www.facebook.com/' + data.username + '">' + data.name + '</a>';
                        else
                            out += '<a href="//www.facebook.com/' + data.id + '">' + data.name + '</a>';
                        out += '</div><div class="oembedall-facebookBody"><div class="contents">';
                        if (data.picture)
                            out += '<a href="' + data.link + '"><img src="' + data.picture + '"></a>';
                        else
                            out += '<img src="https://graph.facebook.com/' + data.id + '/picture">';
                        if (data.from)
                            out += '<a href="' + data.link + '">' + data.name + '</a>';
                        if (data.founded)
                            out += 'Founded: <strong>' + data.founded + '</strong><br>'
                        if (data.category)
                            out += 'Category: <strong>' + data.category + '</strong><br>';
                        if (data.website)
                            out += 'Website: <strong><a href="' + data.website + '">' + data.website + '</a></strong><br>';
                        if (data.gender)
                            out += 'Gender: <strong>' + data.gender + '</strong><br>';
                        if (data.description)
                            out += data.description + '<br>';
                        out += '</div></div>';
                        return out;
                    }
                }),
        new $.fn.oembed.OEmbedProvider("stackoverflow", "rich", ["stackoverflow.com/questions/[\\d]+"], "//api.stackoverflow.com/1.1/questions/$1?body=true&jsonp=?"
                , {templateRegex: /.*questions\/([\d]+).*/,
                    templateData: function (data) {
                        if (!data.questions)
                            return false;
                        var q = data.questions[0];
                        var body = $(q.body).text();
                        var out = '<div class="oembedall-stoqembed"><div class="oembedall-statscontainer"><div class="oembedall-statsarrow"></div><div class="oembedall-stats"><div class="oembedall-vote"><div class="oembedall-votes">'
                                + '<span class="oembedall-vote-count-post"><strong>' + (q.up_vote_count - q.down_vote_count) + '</strong></span><div class="oembedall-viewcount">vote(s)</div></div>'
                                + '</div><div class="oembedall-status"><strong>' + q.answer_count + '</strong>answer</div></div><div class="oembedall-views">' + q.view_count + ' view(s)</div></div>'
                            + '<div class="oembedall-summary"><h3><a class="oembedall-question-hyperlink" href="//stackoverflow.com/questions/' + q.question_id + '/">' + q.title + '</a></h3>'
                                + '<div class="oembedall-excerpt">' + body.substring(0, 100) + '...</div><div class="oembedall-tags">';
                        for (i in q.tags)
                            out += '<a title="" class="oembedall-post-tag" href="//stackoverflow.com/questions/tagged/' + q.tags[i] + '">' + q.tags[i] + '</a>';
                        out += '</div><div class="oembedall-fr"><div class="oembedall-user-info"><div class="oembedall-user-gravatar32"><a href="//stackoverflow.com/users/' + q.owner.user_id + '/' + q.owner.display_name + '">'
                            + '<img width="32" height="32" alt="" src="//www.gravatar.com/avatar/' + q.owner.email_hash + '?s=32&amp;d=identicon&amp;r=PG"></a></div><div class="oembedall-user-details">'
                            + '<a href="//stackoverflow.com/users/' + q.owner.user_id + '/' + q.owner.display_name + '">' + q.owner.display_name + '</a><br><span title="reputation score" class="oembedall-reputation-score">'
                                + q.owner.reputation + '</span></div></div></div></div></div>';
                        return out;
                    }
                }),
        new $.fn.oembed.OEmbedProvider("wordpress", "rich", ["wordpress\\.com/.+", "blogs\\.cnn\\.com/.+", "techcrunch\\.com/.+", "wp\\.me/.+"], "//public-api.wordpress.com/oembed/1.0/?for=jquery-oembed-all"),
        new $.fn.oembed.OEmbedProvider("screenr", "rich", ["screenr\.com"], "//www.screenr.com/embed/$1",
                {templateRegex: /.*\/([^\/]+).*/
                    , embedtag: {tag: 'iframe', width: '650', height: 396}
                }),
        new $.fn.oembed.OEmbedProvider("gigpans", "rich", ["gigapan\\.org/[-.\\w@]+/\\d+"], "//gigapan.org/gigapans/$1/options/nosnapshots/iframe/flash.html",
                {templateRegex: /.*\/(\d+)\/?.*/,
                    embedtag: {tag: 'iframe', width: '100%', height: 400}
                }),
        new $.fn.oembed.OEmbedProvider("scribd", "rich", ["scribd\\.com/.+"], "//www.scribd.com/embeds/$1/content?start_page=1&view_mode=list",
                {templateRegex: /.*doc\/([^\/]+).*/,
                    embedtag: {tag: 'iframe', width: '100%', height: 600}
                }),
        new $.fn.oembed.OEmbedProvider("kickstarter", "rich", ["kickstarter\\.com/projects/.+"], "$1/widget/card.html",
                {templateRegex: /([^\?]+).*/,
                    embedtag: {tag: 'iframe', width: '220', height: 380}
                }),
        new $.fn.oembed.OEmbedProvider("amazon", "rich", ["amzn.com/B+", "amazon.com.*/(B\\S+)($|\\/.*)"], "//rcm.amazon.com/e/cm?t=_APIKEY_&o=1&p=8&l=as1&asins=$1&ref=qf_br_asin_til&fc1=000000&IS2=1&lt1=_blank&m=amazon&lc1=0000FF&bc1=000000&bg1=FFFFFF&f=ifr"
                , {apikey: true, templateRegex: /.*\/(B[0-9A-Z]+)($|\/.*)/,
                    embedtag: {tag: 'iframe', width: '120px', height: '240px'}
                }),
        new $.fn.oembed.OEmbedProvider("slideshare", "rich", ["slideshare\.net"], "//www.slideshare.net/api/oembed/2", {format: 'jsonp'}),
        new $.fn.oembed.OEmbedProvider("roomsharejp", "rich", ["roomshare\\.jp/(en/)?post/.*"], "//roomshare.jp/oembed.json"),
        new $.fn.oembed.OEmbedProvider("lanyard", "rich", ["lanyrd.com/\\d+/.+"], null,
                {yql: {xpath: '(//div[@class="primary"])[1]', from: 'htmlstring'
                        , datareturn: function (results) {
                            if (!results.result)
                                return false;
                            return '<div class="oembedall-lanyard">' + results.result + '</div>';
                        }
                    }
                }),
        new $.fn.oembed.OEmbedProvider("asciiartfarts", "rich", ["asciiartfarts.com/\\d+.html"], null,
                {yql: {xpath: '//pre/font', from: 'htmlstring'
                        , datareturn: function (results) {
                            if (!results.result)
                                return false;
                            return '<pre style="background-color:000;">' + results.result + '</div>';
                        }
                    }
                }),
        //Use Open Graph Where applicable
        new $.fn.oembed.OEmbedProvider("opengraph", "rich", ["mangafox.me", "myanimelist.net", "nodebb.org"], null,
                {yql: {xpath: "//meta|//title|//link|//img", from: 'html'
                        , datareturn: function (results) {
                            if (!results['og:title'] && results['title'] && results['description'])
                                results['og:title'] = results['title'];
                            if (!results['og:title'] && !results['title'])
                                return false;
                            var code = $('<p/>');
                            if (results['og:video']) {
                                var embed = $('<embed src="' + results['og:video'] + '"/>');
                                embed
                                        .attr('type', results['og:video:type'] || "application/x-shockwave-flash")
                                        .css('max-height', settings.maxHeight || 'auto')
                                        .css('max-width', settings.maxWidth || 'auto');
                                if (results['og:video:width'])
                                    embed.attr('width', results['og:video:width']);
                                if (results['og:video:height'])
                                    embed.attr('height', results['og:video:height']);
                                code.append(embed);
                            } else if (results['og:image']) {
                                var img = $('<img src="' + results['og:image'] + '">');
                                img.css('max-height', settings.maxHeight || 'auto').css('max-width', settings.maxWidth || 'auto');
                                if (results['og:image:width'])
                                    img.attr('width', results['og:image:width']);
                                if (results['og:image:height'])
                                    img.attr('height', results['og:image:height']);
                                code.append(img);
                            }
                            if (results['og:title'])
                                code.append('<b>' + results['og:title'] + '</b><br/>');
                            if (results['og:description'])
                                code.append(results['og:description'] + '<br/>');
                            else if (results['description'])
                                code.append(results['description'] + '<br/>');
                            return code;
                        }
                    }
                })

    ];


    /** Here comes the things that do not tell you much about the working of this plugin */

    //List of short urls that will be mapped and expanded to long urls using the long-url.org API 
    var shortURLList = ["0rz.tw", "1link.in", "1url.com", "2.gp", "2big.at", "2tu.us", "3.ly", "307.to", "4ms.me", "4sq.com", "4url.cc", "6url.com", "7.ly", "a.gg", "a.nf", "aa.cx", "abcurl.net",
        "ad.vu", "adf.ly", "adjix.com", "afx.cc", "all.fuseurl.com", "alturl.com", "amzn.to", "ar.gy", "arst.ch", "atu.ca", "azc.cc", "b23.ru", "b2l.me", "bacn.me", "bcool.bz", "binged.it",
        "bit.ly", "bizj.us", "bloat.me", "bravo.ly", "bsa.ly", "budurl.com", "canurl.com", "chilp.it", "chzb.gr", "cl.lk", "cl.ly", "clck.ru", "cli.gs", "cliccami.info",
        "clickthru.ca", "clop.in", "conta.cc", "cort.as", "cot.ag", "crks.me", "ctvr.us", "cutt.us", "dai.ly", "decenturl.com", "dfl8.me", "digbig.com",
        "http:\/\/digg\.com\/[^\/]+$", "disq.us", "dld.bz", "dlvr.it", "do.my", "doiop.com", "dopen.us", "easyuri.com", "easyurl.net", "eepurl.com", "eweri.com",
        "fa.by", "fav.me", "fb.me", "fbshare.me", "ff.im", "fff.to", "fire.to", "firsturl.de", "firsturl.net", "flic.kr", "flq.us", "fly2.ws", "fon.gs", "freak.to",
        "fuseurl.com", "fuzzy.to", "fwd4.me", "fwib.net", "g.ro.lt", "gizmo.do", "gl.am", "go.9nl.com", "go.ign.com", "go.usa.gov", "goo.gl", "goshrink.com", "gurl.es",
        "hex.io", "hiderefer.com", "hmm.ph", "href.in", "hsblinks.com", "htxt.it", "huff.to", "hulu.com", "hurl.me", "hurl.ws", "icanhaz.com", "idek.net", "ilix.in", "is.gd",
        "its.my", "ix.lt", "j.mp", "jijr.com", "kl.am", "klck.me", "korta.nu", "krunchd.com", "l9k.net", "lat.ms", "liip.to", "liltext.com", "linkbee.com", "linkbun.ch",
        "liurl.cn", "ln-s.net", "ln-s.ru", "lnk.gd", "lnk.ms", "lnkd.in", "lnkurl.com", "lru.jp", "lt.tl", "lurl.no", "macte.ch", "mash.to", "merky.de", "migre.me", "miniurl.com",
        "minurl.fr", "mke.me", "moby.to", "moourl.com", "mrte.ch", "myloc.me", "myurl.in", "n.pr", "nbc.co", "nblo.gs", "nn.nf", "not.my", "notlong.com", "nsfw.in",
        "nutshellurl.com", "nxy.in", "nyti.ms", "o-x.fr", "oc1.us", "om.ly", "omf.gd", "omoikane.net", "on.cnn.com", "on.mktw.net", "onforb.es", "orz.se", "ow.ly", "ping.fm",
        "pli.gs", "pnt.me", "politi.co", "post.ly", "pp.gg", "profile.to", "ptiturl.com", "pub.vitrue.com", "qlnk.net", "qte.me", "qu.tc", "qy.fi", "r.ebay.com", "r.im", "rb6.me", "read.bi",
        "readthis.ca", "reallytinyurl.com", "redir.ec", "redirects.ca", "redirx.com", "retwt.me", "ri.ms", "rickroll.it", "riz.gd", "rt.nu", "ru.ly", "rubyurl.com", "rurl.org",
        "rww.tw", "s4c.in", "s7y.us", "safe.mn", "sameurl.com", "sdut.us", "shar.es", "shink.de", "shorl.com", "short.ie", "short.to", "shortlinks.co.uk", "shorturl.com",
        "shout.to", "show.my", "shrinkify.com", "shrinkr.com", "shrt.fr", "shrt.st", "shrten.com", "shrunkin.com", "simurl.com", "slate.me", "smallr.com", "smsh.me", "smurl.name",
        "sn.im", "snipr.com", "snipurl.com", "snurl.com", "sp2.ro", "spedr.com", "srnk.net", "srs.li", "starturl.com", "stks.co", "su.pr", "surl.co.uk", "surl.hu", "t.cn", "t.co", "t.lh.com",
        "ta.gd", "tbd.ly", "tcrn.ch", "tgr.me", "tgr.ph", "tighturl.com", "tiniuri.com", "tiny.cc", "tiny.ly", "tiny.pl", "tinylink.in", "tinyuri.ca", "tinyurl.com", "tk.", "tl.gd",
        "tmi.me", "tnij.org", "tnw.to", "tny.com", "to.ly", "togoto.us", "totc.us", "toysr.us", "tpm.ly", "tr.im", "tra.kz", "trunc.it", "twhub.com", "twirl.at",
        "twitclicks.com", "twitterurl.net", "twitterurl.org", "twiturl.de", "twurl.cc", "twurl.nl", "u.mavrev.com", "u.nu", "u76.org", "ub0.cc", "ulu.lu", "updating.me", "ur1.ca",
        "url.az", "url.co.uk", "url.ie", "url360.me", "url4.eu", "urlborg.com", "urlbrief.com", "urlcover.com", "urlcut.com", "urlenco.de", "urli.nl", "urls.im",
        "urlshorteningservicefortwitter.com", "urlx.ie", "urlzen.com", "usat.ly", "use.my", "vb.ly", "vevo.ly", "vgn.am", "vl.am", "vm.lc", "w55.de", "wapo.st", "wapurl.co.uk", "wipi.es",
        "wp.me", "x.vu", "xr.com", "xrl.in", "xrl.us", "xurl.es", "xurl.jp", "y.ahoo.it", "yatuc.com", "ye.pe", "yep.it", "yfrog.com", "yhoo.it", "yiyd.com", "youtu.be", "yuarel.com",
        "z0p.de", "zi.ma", "zi.mu", "zipmyurl.com", "zud.me", "zurl.ws", "zz.gd", "zzang.kr", "›.ws", "✩.ws", "✿.ws", "❥.ws", "➔.ws", "➞.ws", "➡.ws", "➨.ws", "➯.ws", "➹.ws", "➽.ws"];



    //Default options
    $.fn.oembed.defaults = {
        maxWidth: null,
        maxHeight: null,
        minWidth: 100, //minimum thumbnail width
        minHeight: 100, //minimum thumbnail height
        includeHandle: true,
        embedMethod: 'auto',
        // "auto", "append", "fill"	
        onEmbed: false,
        onProviderNotFound: function () {
        },
        onError: function () {
        },
        ajaxOptions: {},
        placeholder: false
    };

})(jQuery);
