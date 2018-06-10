var AdvRole = {

    codoforumContainer: $('.CODOFORUM')
};

AdvRole.showUserInfoPopover = function ($el) {


    var userId = $el.data('userid');

    CODOF.request.get({
        url: codo_defs.url + 'plugin/adv_role/user/' + userId,
        done: function (data) {


        }
    });
};


AdvRole.codoforumContainer.on('mouseover', '.codo_topics_topic_avatar', function () {

    AdvRole.showUserInfoPopover($(this));
});

AdvRole.codoforumContainer.on('mouseover', '.codo_posts_post_avatar', function () {


    AdvRole.showUserInfoPopover($(this));
});

