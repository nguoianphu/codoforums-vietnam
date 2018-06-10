
/*
 * @CODOLICENSE
 */

jQuery(document).ready(function($) {

    (CODOF.modify_topics_arr = function() {

        var len = CODOFVAR.topics.topics.length;

        while (len--) {

            CODOFVAR.topics.topics[len].tags = CODOFVAR.tags[CODOFVAR.topics.topics[len].topic_id];

        }
        return CODOFVAR.topics;

    })();

    CODOF.context = CODOFVAR.topics;

    var template = Handlebars.compile($("#codo_template").html());
    var html = template(CODOF.context);
    $('.codo_load_more_gif').hide();
    $('#codo_topics_body').append(html);
    
    
    if (CODOFVAR.num_pages > 1) {

        var paginate = Handlebars.compile($("#codo_pagination").html());

        var pagination = paginate(CODOF.ret_pagination(parseInt(CODOFVAR.curr_page), parseInt(CODOFVAR.num_pages), {
            cls: 'codo_head_navigation',
            url: CODOFVAR.url
        }));

        $('#codo_topic_title_pagination').append(pagination);

    }
    
    
});