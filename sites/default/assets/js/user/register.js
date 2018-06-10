/*
 * @CODOLICENSE
 */

'use strict';


var codo_register = (function() {



    var register = {
        errors: {
            name: false,
            mail: false,
            pass: false
        }
    };
    function error_exists(errors) {

        for (var key in errors) {

            if (errors[key]) {
                return true;
            }
        }

        return false;
    }

    jQuery('document').ready(function($) {

        $('#reg_username').focus();

        $('#password').attr("minlength", codo_defs.register.pass_min);

        $('#codo_register').on('click', function() {

            if (error_exists(register.errors)) {
                $('#reg_username').focus();
                return false;
            } else {
                $(this).submit();
            }
        });

        $("#codo_reg_pass").append("<div id='letterViewer'>");

        $("#password").keypress(function(e) {
            $("#letterViewer")
                    .html(String.fromCharCode(e.which))
                    .fadeIn(200, function() {
                        $(this).fadeOut(200);
                    });
        });

        $('#reg_username').blur(function() {

            var that = $(this);
            var username = $.trim($('#reg_username').val());
            if (username.length && username.length < codo_defs.register.username_min) {
                
                register.errors.name = true;
                that.next().html(CODOFVAR.trans.username_short).slideDown();
            } else
            {

                $.getJSON(codo_defs.url + "Ajax/user/register/username_exists",
                        {
                            username: username,
                            token: codo_defs.token
                        }, function(response) {

                    if (response.exists) {
                        register.errors.name = true;
                        that.next().html(CODOFVAR.trans.username_exists).slideDown();
                    } else {
                        register.errors.name = false;
                        that.next().slideUp();
                    }
                });
            }
        });

        $('#reg_mail').blur(function() {

            var that = $(this);


            $.getJSON(codo_defs.url + "Ajax/user/register/mail_exists",
                    {
                        mail: $.trim($('#reg_mail').val()),
                        token: codo_defs.token
                    }, function(response) {

                if (response.exists) {
                    register.errors.mail = true;
                    that.next().html(CODOFVAR.trans.mail_exists).slideDown();
                } else {
                    register.errors.mail = false;
                    that.next().slideUp();
                }
            });
        });

        $('#password').blur(function() {

            var
                    pass = $.trim($(this).val()),
                    len = codo_defs.register.pass_min;

            if (pass.length && pass.length < len) {

                $(this).next().html(CODOFVAR.trans.password_short).slideDown();
            } else {
                $(this).next().slideUp();
            }
        });

        /*$('#cpassword').blur(function() {
         
         var pass1 = $('#password').val();
         var pass2 = $(this).val();
         
         if (pass1 !== pass2) {
         register.errors.pass = true;
         alert('passwords are not equal');
         } else {
         register.errors.pass = false;
         }
         });*/

    });

}());


