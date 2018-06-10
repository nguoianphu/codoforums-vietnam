{*

/*
* @CODOLICENSE
*/

*}

{*Smarty*}
<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>
        Dashboard
        <small>It all starts here.</small>
    </h1>

    <div class="new-update" style="padding: 10px 0; display: none">
        
        <div>Current version:  V.<span id="current_version"</span></div>
        <b>New version available:</b> V.<span id="latest_version"></span> <a style="cursor: pointer;" href="index.php?page=system/upgrade">Upgrade now!</a>
    </div>
    
    <script type="text/javascript">
        
        function compare(a, b) {
        
            if (a === b) {
                return 0;
             }

             var a_components = a.split(".");
             var b_components = b.split(".");

             var len = Math.min(a_components.length, b_components.length);

             // loop while the components are equal
             for (var i = 0; i < len; i++) {
                 // A bigger than B
                 if (parseInt(a_components[i]) > parseInt(b_components[i])) {
                     return 1;
                 }

                 // B bigger than A
                 if (parseInt(a_components[i]) < parseInt(b_components[i])) {
                     return -1;
                 }
             }

             // If one's a prefix of the other, the longer one is greater.
             if (a_components.length > b_components.length) {
                 return 1;
             }

             if (a_components.length < b_components.length) {
                 return -1;
             }

             // Otherwise they are the same.
             return 0;
        }
        
        $.ajax({
            url: "https://codoforum.com/latest_version",
            dataType: "jsonp",
            jsonp: 'showVersion',
            success: function(data) {

                var currentVersion = '{$version}';
                var latestVersion = data.version;

                if(compare(latestVersion, currentVersion) === 1) {

                    $('#latest_version').html(latestVersion);
                    $('#current_version').html(currentVersion);
                    $('.new-update').show();
        
                }

            }
        });   
    </script>    
    
</section>



<div class="row">
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>
                    {$no_posts|abbrev_no}
                </h3>
                <p>
                    Posts Made
                </p>
            </div>
            <div class="icon">
                <i class="fa fa-comments"></i>
            </div>
            <a href="../" target="_blank" class="small-box-footer">
                View All <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>    


    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>
                    {$no_users|abbrev_no}
                </h3>
                <p>
                    User Registrations
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-person-add"></i>
            </div>
            <a href="index.php?page=users" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <h3>
                    {$no_topics|abbrev_no}
                </h3>
                <p>
                    Topics Created
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="../" target="_blank" class="small-box-footer">
                View All <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div> 
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-red">
            <div class="inner">
                <h3>
                    &nbsp; {$no_views|abbrev_no}
                </h3>
                <p>
                    Total Views
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="../" target="_blank" class="small-box-footer">
                Visit Forum <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>                

</div><!-- /.row -->

<div class="row" style="">
    <div class="col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> Latest News & Alerts</h3>
            </div>
            <div class="panel-body">

                <iframe style="width:100%;height:400px" src="https://codologic.com/todo/php/todo.php">
                </iframe>

            </div>
        </div>
    </div>
</div><!-- /.row -->
