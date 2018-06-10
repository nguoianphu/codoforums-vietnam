<?php

/*
 * @CODOLICENSE
 */
$smarty= \CODOF\Smarty\Single::get_instance();

$db = \DB::getPDO();



if(isset($_POST['await_approval_subject']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])){
    
    $cfgs=array();
    foreach($_POST as $key=>$value){
        
        
    $query="UPDATE ".PREFIX."codo_config SET option_value=:value WHERE option_name=:key";
    $ps=$db->prepare($query);
    $ps->execute(array(':key'=>$key,':value'=>$value));
    //echo $query."<br>\n";    
    
    }
    
    
}


CODOF\Util::get_config($db);


$content=$smarty->fetch('mail/templates.tpl');
