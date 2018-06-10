<?php

$smarty= \CODOF\Smarty\Single::get_instance();
$smarty->assign('msg','');

global $CONF;

if(isset($_GET['logout'])){
    
    session_destroy();
    $smarty->assign('logged_in','no');
    $smarty->assign('A_username','Hello');
    
}

if(isset($_POST['username'])){
    
    $login=new \CODOF\User\Login(\DB::getPDO());
    
    
    $login->username=$_POST['username'];
    $login->password=$_POST['password'];
   
    $result=$login->process_login();
    $uobj=  json_decode($result);
   
    if($uobj->msg=='success'){

        $user = CODOF\User\User::get();
        
        if(!$user->hasRoleId(ROLE_ADMIN)){
            
            $smarty->assign('msg','You do not have enough permissions');
        }else{
                        
            $avatar=  str_replace(ADMIN , "", $user->avatar);
            
            $_SESSION[UID.'A_loggedin_created']=date("F j, Y",$user->created); 
            $_SESSION[UID.'A_loggedin_avatar']=$avatar;
            $_SESSION[UID.'A_loggedin_username']=$login->username;
            $_SESSION[UID.'A_loggedin']='admin';//($_SESSION[UID.'USER']['id']);
            //var_dump($_SESSION);
            header("Location: index.php");
        }
        
    }else{
    $smarty->assign('msg','Invalid Username or Password');
    }
    
}


$content=$smarty->fetch('login.tpl');

