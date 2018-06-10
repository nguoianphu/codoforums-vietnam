{*
/*
* @CODOLICENSE
*/
*}
{* Smarty *}
<!DOCTYPE><!-- html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">-->
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>{$subject}</title>
        <style type="text/css">
            #outlook a {
                padding:0;
            }
            .ExternalClass {
                width:100%;
            }
            .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
                line-height: 100%;
            }


            /* Some sensible defaults for images
            Bring inline: Yes. */
            a img {
                border:none;
            }

            /* Yahoo paragraph fix
            Bring inline: Yes. */
            p {
                margin: 1em 0;
            }

            /* Hotmail header color reset
            Bring inline: Yes. */
            h1, h2, h3, h4, h5, h6 {
                color: black !important;
            }

            h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {
                color: blue !important;
            }

            h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {
                color: red !important; /* Preferably not the same color as the normal header link color.  There is limited support for psuedo classes in email clients, this was added just for good measure. */
            }

            h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {
                color: purple !important; /* Preferably not the same color as the normal header link color. There is limited support for psuedo classes in email clients, this was added just for good measure. */
            }

            /* Outlook 07, 10 Padding issue fix
            Bring inline: No.*/
            table td {
                border-collapse: collapse;
            }


            /* Styling your links has become much simpler with the new Yahoo.  In fact, it falls in line with the main credo of styling in email and make sure to bring your styles inline.  Your link colors will be uniform across clients when brought inline.
            Bring inline: Yes. */
            a {
                color: orange;
            }


            @media only screen and (max-device-width: 480px) {
                a[href^="tel"], a[href^="sms"] {
                    text-decoration: none;
                    color: blue;
                    pointer-events: none;
                    cursor: default;
                }

                .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
                    text-decoration: default;
                    color: orange !important;
                    pointer-events: auto;
                    cursor: default;
                }

            }


            @media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
                a[href^="tel"], a[href^="sms"] {
                    text-decoration: none;
                    color: blue;
                    pointer-events: none;
                    cursor: default;
                }

                .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
                    text-decoration: default;
                    color: orange !important;
                    pointer-events: auto;
                    cursor: default;
                }
            }
            
            {$body_style="width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;"}
            {$table_style="border-collapse:collapse;width:100% !important;mso-table-lspace:0pt; mso-table-rspace:0pt;"}
            {$img_style="outline:none; text-decoration:none; -ms-interpolation-mode: bicubic;display: block"}
            {$h1_style="color: #555 !important; font-size: 24px; margin: 0; padding: 0;text-shadow: 1px 1px 1px #ccc;"}
            {$h2_style="color: #555 !important; font-size: 20px; margin: 0; padding: 0;text-shadow: 1px 1px 1px #ccc;"}
            
            {$link_style="color: #3f8cc3;text-decoration:none;font-weight:bold;"}
            {$sec_text_color="color: #777;"}
            
            
        </style>

    </head>
    <body style="{$body_style}">
        <table cellpadding="0" cellspacing="0" border="0" style="{$table_style}margin:0; padding:0;line-height: 100% !important; background: #eee;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:13px;">
            <tr>
                <td valign="top" align="center" style="padding: 32px 0">
                    <table style="width: 600px;border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;" cellpadding="0" cellspacing="0" border="0" align="center">
                        <tr>
                            
                            {block name="body"}{/block}                            
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
