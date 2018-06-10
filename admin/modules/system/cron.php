<?php

/*
 * @CODOLICENSE
 */

$smarty= \CODOF\Smarty\Single::get_instance();

$db = \DB::getPDO();


$query="SELECT * FROM " . PREFIX . "codo_config";

if(isset($_POST['name']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

    $interval = $_POST['interval'];
    if($_POST['interval'] == null || $_POST['interval'] == '') {
        
        $interval = $_POST['e_interval'];
    }
    
    $cron = new \CODOF\Cron\Cron();
    $cron->reset($_POST['name'], $_POST['type'], $interval);
}


$qry = "SELECT * FROM ".PREFIX."codo_crons";
$obj = $db->query($qry);
$res = $obj->fetchAll();

$crons = array();

function get_readable_time($t) {
    
    switch($t) {
        
        case 3600 : return 'hourly';
        case 3600 * 24 : return 'daily';
        case 3600 * 24 * 7 : return 'weekly';
        case 3600 * 24 * 30 : return 'monthly';
        default : return $t . 's';    
    }
}
$i = 0;
foreach($res as $r) {
    
    $crons[$i]['cron_name'] = $r['cron_name'];
    $crons[$i]['cron_type'] = $r['cron_type'];
    
    $crons[$i]['cron_interval'] = get_readable_time($r['cron_interval']);
    $crons[$i]['cron_started'] = \CODOF\Time::get_pretty_time($r['cron_started']);
    $crons[$i]['cron_last_run'] = \CODOF\Time::get_pretty_time($r['cron_last_run']);

    if($r['cron_status'] == 0) {
        
        $crons[$i]['cron_status'] = 'not running';
    }else{
        
        $crons[$i]['cron_status'] = '<span style="color:green">running</span>';
    }
    
    $i++;
}


CODOF\Util::get_config($db);
$smarty->assign('crons', $crons);
$content=$smarty->fetch('system/cron.tpl');
