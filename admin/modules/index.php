<?php
/*
 * 
 * @CODOLICENSE
 * 
 */

$db=\DB::getPDO();

global $CONF;
$smarty= \CODOF\Smarty\Single::get_instance();
//--------------------get no of posts and topics--------------------------------
$qry = 'SELECT SUM(no_topics) AS no_topics, SUM(no_posts) AS no_posts '
        . 'FROM '.PREFIX.'codo_categories';
$res = $db->query($qry);

if($res) {
    
    $res = $res->fetch();
}

$smarty->assign('no_topics',$res['no_topics']);
$smarty->assign('no_posts',$res['no_posts']);
//------------------------------get no of users---------------------------------
$qry = 'SELECT COUNT(id) AS no_users FROM '.PREFIX.'codo_users';
$res2 = $db->query($qry);

if($res2) {
    
    $users = $res2->fetch();
}

$smarty->assign('no_users',$users['no_users']);

//--------------------------------get no of views-------------------------------
$qry = 'SELECT SUM(no_views) AS no_views FROM '.PREFIX.'codo_topics';
$res3 = $db->query($qry);

if($res3) {
    
    $topics = $res3->fetch();
}
$smarty->assign('no_views',$topics['no_views']);
//------------------------------------------------------------------------

$content=$smarty->fetch('dashboard.tpl');

