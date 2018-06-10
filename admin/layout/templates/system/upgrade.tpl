<style type="text/css">

    .error {

        background: #770000;
        color: white;
        border: 1px solid #600;
        padding: 6px;
        margin-top: 15px;
    }

    .info{

        background: rgb(100,100,100);
        color: white;
        border: 1px solid #600;
        padding: 6px;
        margin-top: 15px;
    }
    .success {

        background: #428bca;
        color: white;
        border: 1px solid #1471af;
        padding: 6px;        
        margin-top: 15px;

    }

    .warn {

        padding: 6px;
        background: rgb(170, 15, 1);
        color: white;
        margin-bottom: 10px;
    }

</style>
<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class=""><i class="fa fa-desktop"></i> System</li> 
        <li class="active"><i class="fa fa-level-up"></i> Upgrade</li>
    </ol>

</section>


<div class="row col-md-12">

    <div class="warn">Note: Backup your database and files before you start the upgrade process!</div>


    <div><button class="btn btn-primary" onclick="begin_upgrade()">Start Upgrade Process</button></div>
    <div style="" class="info" id="codo_import_status"></div>

</div>

<div class="row col-md-12" style="margin:15px;display: none" id="ftp">

    <div class="col-lg-8">
        <div class="box box-primary">

            <div class="box-body">

                <fieldset>
                    <div class="form-group">
                        <label>FTP server</label>
                        <input id="fserver" type="text" name="ftpserver" class="form-control" value="localhost"  title="Example: ftp.server.com, 192.123.45.67">


                    </div>
                    <!--
                    <div class="form-group">
                        <label>Port:</label> 
                        <input type="text" id="fport"   class="form-control" name="ftpserverport" value="21" style="" maxlength="5">

                    </div>-->
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" id="fusername" value="" class="form-control" title="Enter your username">

                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="fpassword" value="" class="form-control" title="Enter your password">

                    </div>

                    <div class="form-group">
                        <input type="button" onclick="step_ftp()" name="Login" value="Submit" alt="Login">
                        </fieldset>

                    </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">






        function begin_upgrade() {

            sts = $('#codo_import_status');
            setTimeout(step_one, 1000);

            sts = $('#codo_import_status');

            sts.html("1.0> Checking for latest version<br>");
            sts.append("1.1> Connecting to server: codoforum.com ...<br>");


        }
        



        function step_one() {



            jQuery.get('index.php?page=system/upgrade&upgrade=true&checklatest=true',{
                
                CSRF_token: '{$token}'
            }, function (data) {

                sts = $('#codo_import_status');

                if (data.indexOf("####:") > 500) {
                    sts.html("Aborted: bad response from this server!");
                    return;
                }
                sts.append(data);

                if (data.indexOf("2.0") > 0) {

                    step_two();
                }


            });


        }


        function step_two() {

            var sts = $('#codo_import_status');
            sts.append("2.0> Initiated.<br>");
            sts.append("2.1> Downloading the latest version, Please wait...<br>");

            jQuery.get('index.php?page=system/upgrade&upgrade=true&download=true',{
                
                CSRF_token: '{$token}'
            }, function (data) {

                sts.append(data);


                if (data.indexOf(":)") > 0) {

                    sts.append("3.0> File upgrade process initiating... <br>\n");
                    step_three();
                }



            });
        }


        function step_three() {


            jQuery.get('index.php?page=system/upgrade&upgrade=true&file_upgrade=true',{
                
                CSRF_token: '{$token}'
            }, function (data) {

                sts.append(data);


                if (data.indexOf("not writable") > 0) {

                    sts.append("Let us try changing permissions using FTP!<br>Enter Your FTP crendentails below:<br>");

                    $('#ftp').show();
                } else{
                    step_directupgrade();
                }
            });



            }

        


        function step_directupgrade() {


            jQuery.get('index.php?page=system/upgrade&upgrade=true&direct_upgrade=true',{
                
                CSRF_token: '{$token}'
            }, function (data) {

                sts.append(data);




            });


        }

        function step_ftp() {

            var fserver = $('#fserver').val();
            var fport = $('#fport').val();
            var fusername = $('#fusername').val();
            var fpassword = $('#fpassword').val();

            $('#ftp').hide();

            jQuery.get('index.php?page=system/upgrade&upgrade=true&ftp_step=true',
                    {
                        fserver: fserver,
                        fport: fport,
                        fusername: fusername,
                        fpassword: fpassword,
                        CSRF_token: '{$token}'
                    },
            function (data) {

                sts.append(data);


                if (data.indexOf("not writable") > 0) {

                    // sts.append("Let us try changing permissions using FTP!<br>Enter Your FTP crendentails below:<br>");


                }



            });



        }

    </script>