<?php

/**
*
* Loads admin section of the requested plugin
*
*/


class Admin_tpl{

    static $smarty = false;

    public static function get(){
        
	self::$smarty = \CODOF\Smarty\Single::get_instance();  
	return self::$smarty;
    }
        
    public static function render($tpl) {
    
	if(!self::$smarty) {
	  
	  self::get();
	}
	
 	return self::$smarty->fetch(PLUGIN_DIR.$tpl);
    }
        
}


class ploader{


  public $plugin="";

  function __construct($plugin){


    $this->plugin=$plugin;


  }


//--------------------------------------------
  function load_plugin(){
  
  
      $contents="";
      
      $plg_file=DATA_PATH.'plugins/'.$this->plugin.'/' . ADMIN .$this->plugin.'.admin.php';
      
      ob_start();
      
      if(is_file($plg_file)){
      
	require $plg_file;
      
      }
      else{
      
      echo 'ERROR: '.$plg_file.' NOT FOUND!';
      
      }

      $contents= ob_get_clean();

      return $contents;
  }



}

$plg = htmlspecialchars($_GET['plugin'], ENT_QUOTES, 'UTF-8');
$ploader=new ploader($plg);

$bcrumb='<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
         <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
         <li class="active"><a href="index.php?page=plugins/plugins"><i class="fa fa-gears"></i> Plugins</a></li>
         <li class="active"><i class="fa fa-wrench"></i> '. $plg .'</li>
    </ol>
    
</section>';



$content=$bcrumb.$ploader->load_plugin();