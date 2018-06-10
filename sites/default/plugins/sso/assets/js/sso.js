CODOF.authenticator = {
    login: function(response) {

        $('.codo_login_loading').show();

        if (response.name) {
            jQuery.post(codo_defs.url + 'sso/authorize', {
                token: codo_defs.token,
                sso: response,
                timestamp: codo_defs.time

            }, function(response) {

                if (response !== "error")
                    window.location.reload();
            });
        } 
    }
};

jQuery(document).ready(function($) {

    //check if user is logged in codoforum
    if (codo_defs.logged_in === 'no') {
        //check if user is logged in master site
        // Using JSONP
        $.ajax({
            url: codo_defs.get('sso_get_user_path'),
            jsonp: "callback",
            // tell jQuery we're expecting JSONP
            dataType: "jsonp",
            data: {
                format: "json",
                client_id: codo_defs.get('sso_client_id'),
                timestamp: codo_defs.time,
                token: codo_defs.get('sso_token')
            },
            // work with the response
            success: function(response) {

                if (response.name) {
                    CODOF.authenticator.login(response);
                    return false;
                }

            }
        });
    }

    $('#codo_login_with_sso').on('click', function() {

        window.location.href = codo_defs.get('sso_login_user_path');
    });

});

