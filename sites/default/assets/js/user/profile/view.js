CODOF.hook.add('added_li', function () {

    $('a[href=#' + CODOFVAR.tab + ']').tab('show')
});

jQuery(document).ready(function ($) {

    $(window).scroll(function () {

        $('.codo_profile').css('top', $(window).scrollTop());

    });


    $('#codo_mail_resent').hide();
    $('#codo_email_sending_img').hide();
    $('#codo_edit_profile').on('click', function () {
        window.location.href = codo_defs.url + 'user/profile/' + CODOFVAR.userid + '/edit';
    });
    $('#codo_resend_mail').on('click', function () {

        $('#codo_email_sending_img').show();
        $.get(
                codo_defs.url + 'Ajax/user/register/resend_mail',
                {
                    token: codo_defs.token
                },
                function (response) {

                    if (response === "success") {

                        $('#codo_mail_resent').fadeIn('slow');
                    } else {

                        $('#codo_resend_mail_failed').html(response).show('slow');
                    }

                    $('#codo_email_sending_img').hide();
                }
        );
    });

    CODOF.req.data = {
        token: codo_defs.token
    };

    CODOF.template = Handlebars.compile($("#codo_template").html());

    CODOF.hook.call('before_req_fetch_recent_posts', {}, function () {

        $.getJSON(
                codo_defs.url + 'Ajax/user/profile/' + CODOFVAR.userid + '/get_recent_posts',
                CODOF.req.data,
                function (response) {

                    CODOF.context = response;
                    var topics = CODOF.template(CODOF.context);
                    $('.codo_load_more_gif').remove();

                    //console.log(topics);
                    $('#recent_posts').append(topics);

                    var widths = $('#recent_posts  .codo_topics_last_post').map(function () {
                        return $(this).outerWidth();
                    }).get();

                    var max_width = Math.max.apply(null, widths);

                    $('.codo_topics_last_post').css('width', max_width + "px");
                }
        );
    });


});
