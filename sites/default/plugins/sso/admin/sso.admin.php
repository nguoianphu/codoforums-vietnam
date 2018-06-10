<?php

//$tpl = \CODOF\Smarty\Single::get_instance('');

$tpl=Admin_tpl::get();






//$smarty= \CODOF\Smarty\Single::get_instance();

$db = \DB::getPDO();


$query="SELECT * FROM " . PREFIX . "codo_config";



if(isset($_POST['sso_secret'])){
    
    $cfgs=array();
    foreach($_POST as $key=>$value){
        
        
    $query="UPDATE ".PREFIX."codo_config SET option_value=:value WHERE option_name=:key";
    $ps=$db->prepare($query);
    $ps->execute(array(':key'=>$key,':value'=>$value));
    //echo $query."<br>\n";    
    
    }
    
    
}


CODOF\Util::get_config($db);

echo Admin_tpl::render('sso/admin/sso.admin.tpl');