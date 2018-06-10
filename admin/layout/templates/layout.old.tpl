{*

/*
* @CODOLICENSE
*/

*}

{*Smarty*}
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Dashboard - CODOFORUM</title>

    <!-- Bootstrap core CSS -->
    <link href="{$A_RURI}css/bootstrap.css" rel="stylesheet">

    <!-- Add custom CSS here -->
    <link href="{$A_RURI}css/sb-admin.css" rel="stylesheet">
    <link rel="stylesheet" href="{$A_RURI}font-awesome/css/font-awesome.min.css">
    <!-- Page Specific CSS -->
    <link rel="stylesheet" href="{$A_RURI}css/morris-0.4.3.min.css">
    
    <link rel="shortcut icon" type="image/x-icon" href="http://codoforum.com/img/favicon.ico?v=1">
    <link rel="apple-touch-icon" sizes="57x57" href="http://codoforum.com/img/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="114x114" href="http://codoforum.com/img/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="72x72" href="http://codoforum.com/img/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="144x144" href="http://codoforum.com/img/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="60x60" href="http://codoforum.com/img/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="120x120" href="http://codoforum.com/img/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="76x76" href="http://codoforum.com/img/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="152x152" href="http://codoforum.com/img/apple-touch-icon-152x152.png">
    <link rel="icon" type="image/png" href="http://codoforum.com/img/favicon-196x196.png" sizes="196x196">
    <link rel="icon" type="image/png" href="http://codoforum.com/img/favicon-160x160.png" sizes="160x160">
    <link rel="icon" type="image/png" href="http://codoforum.com/img/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="http://codoforum.com/img/favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="http://codoforum.com/img/favicon-32x32.png" sizes="32x32">
    
    <script src="{$A_RURI}js/jquery-1.10.2.js"></script>
    
  </head>

  <body>

    <div id="wrapper">

      <!-- Sidebar -->
      <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">CF Admin</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div id="codo_admin_nav" class="collapse navbar-collapse navbar-ex1-collapse">
          <ul class="nav navbar-nav side-nav">
              <li class='nav_label'> General</li>
            <li class="{$active.index}"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li  class="{$active.users}"><a href="index.php?page=users"><i class="fa fa-users"></i> Users</a></li>
            <li  class="{$active.categories}"><a href="index.php?page=categories"><i class="fa fa-table"></i> Categories</a></li>
            <!--<li><a href="forms.html"><i class="fa fa-edit"></i> lllll</a></li>
            <li><a href="typography.html"><i class="fa fa-font"></i> Typography</a></li>
            <li><a href="bootstrap-elements.html"><i class="fa fa-desktop"></i> Bootstrap Elements</a></li>-->
            <li  class="{$active.config}"><a href="index.php?page=config"><i class="fa fa-wrench"></i> Global Settings</a></li>
            <li  class="{$active['plugins/plugins']}"><a href="index.php?page=plugins/plugins"><i class="fa fa-cogs"></i> Plugins</a></li>            
            <li class='nav_label'> Moderation </li>
            <li  class="{$active['moderation/ban_user']}"><a href="index.php?page=moderation/ban_user"><i class="fa fa-ban"></i> Ban user</a></li>

            <li class='nav_label'> System </li>
            <li  class="{$active['system/importer']}"><a href="index.php?page=system/importer"><i class="fa fa-archive"></i> Importer</a></li>                  
            <li  class="{$active['system/cron']}"><a href="index.php?page=system/cron"><i class="fa fa-calendar"></i> Cron</a></li>
            
            
          </ul>

          <ul class="nav navbar-nav navbar-right navbar-user">
              <!--
            <li class="dropdown messages-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-envelope"></i> Messages <span class="badge">7</span> <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li class="dropdown-header">7 New Messages</li>
                <li class="message-preview">
                  <a href="#">
                    <span class="avatar"><img src="http://placehold.it/50x50"></span>
                    <span class="name">John Smith:</span>
                    <span class="message">Hey there, I wanted to ask you something...</span>
                    <span class="time"><i class="fa fa-clock-o"></i> 4:34 PM</span>
                  </a>
                </li>
                <li class="divider"></li>
                <li class="message-preview">
                  <a href="#">
                    <span class="avatar"><img src="http://placehold.it/50x50"></span>
                    <span class="name">John Smith:</span>
                    <span class="message">Hey there, I wanted to ask you something...</span>
                    <span class="time"><i class="fa fa-clock-o"></i> 4:34 PM</span>
                  </a>
                </li>
                <li class="divider"></li>
                <li class="message-preview">
                  <a href="#">
                    <span class="avatar"><img src="http://placehold.it/50x50"></span>
                    <span class="name">John Smith:</span>
                    <span class="message">Hey there, I wanted to ask you something...</span>
                    <span class="time"><i class="fa fa-clock-o"></i> 4:34 PM</span>
                  </a>
                </li>
                <li class="divider"></li>
                <li><a href="#">View Inbox <span class="badge">7</span></a></li>
              </ul>
            </li>
            <li class="dropdown alerts-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i> Alerts <span class="badge">3</span> <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="#">Default <span class="label label-default">Default</span></a></li>
                <li><a href="#">Primary <span class="label label-primary">Primary</span></a></li>
                <li><a href="#">Success <span class="label label-success">Success</span></a></li>
                <li><a href="#">Info <span class="label label-info">Info</span></a></li>
                <li><a href="#">Warning <span class="label label-warning">Warning</span></a></li>
                <li><a href="#">Danger <span class="label label-danger">Danger</span></a></li>
                <li class="divider"></li>
                <li><a href="#">View All</a></li>
              </ul>
            </li>-->
            <li class="dropdown user-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i>{if isset($A_username) }
                                                                                                                
                                                                                                            {$A_username}
                                                                                                    {else}
                                                                                                        Hello
                                                                                                    {/if}<b class="caret"></b></a>
              {if isset($logged_in) && $logged_in eq "yes" }
              <ul class="dropdown-menu">
                <li><a href="index.php?page=login&logout=true"><i class="fa fa-user"></i> Logout</a></li>
             
              </ul>
              {/if}
            </li>
          </ul>
        </div><!-- /.navbar-collapse -->
      </nav>

      <div id="page-wrapper">

            {$content}

      </div><!-- /#page-wrapper -->

    </div><!-- /#wrapper -->

    <!-- JavaScript -->
    <script src="{$A_RURI}js/bootstrap.js"></script>

    <!-- Page Specific Plugins -->
    <!--
    <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="{$A_RURI}js/raphael-min.js"></script>-->
    <script src="{$A_RURI}js/morris-0.4.3.min.js"></script>
    <script src="{$A_RURI}js/morris/chart-data-morris.js"></script>
    <script src="{$A_RURI}js/tablesorter/jquery.tablesorter.js"></script>
    <script src="{$A_RURI}js/tablesorter/tables.js"></script>
    <script src="{$A_RURI}js/Nestable/jquery.nestable.js"></script>


    <script type="text/javascript">
        
        
        jQuery(document).ready(function($) {
        
        
            //$('#codo_admin_nav .dropdown').addClass('open');
        });
    
    </script>
  </body>
</html>
