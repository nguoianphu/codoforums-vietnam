jQuery(document).ready(function ($) {


    $('.codo_menu_user').before('<li class="nav-item codo_pmx_link">\n' +
        '                                    <a class="nav-link"><i class="icon icon-mail"></i></a>\n' +
        '                                </li>');

    $('.codo_inline_notifications_show_all').after('<li class="nav-item codo_pmx_link"><a class="nav-link">' + codo_defs.trans.pmx_title + '</a></li>');

    $('.codo_pmx_link').on('click', function () {
        window.location = codo_defs.url + 'pmx';
    });
});