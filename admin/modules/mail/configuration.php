<?php

/*
 * @CODOLICENSE
 */
$smarty = \CODOF\Smarty\Single::get_instance();
$db = \DB::getPDO();
$flash = array();
CODOF\Util::get_config($db);

if (isset($_POST['mail_type']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

    $cfgs = array();
    $flash['flash'] = true;
    foreach ($_POST as $key => $value) {
        $query = "UPDATE " . PREFIX . "codo_config SET option_value=:value WHERE option_name=:key";
        $ps = $db->prepare($query);
        $ps->execute(array(':key' => $key, ':value' => $value));
    }

    $flash['message'] = 'Settings saved successfully!';
    CODOF\Util::get_config($db);
    if (isset($_POST['testSend'])) {

        $randomNames = array('Han Solo', 'Darth Vader', 'Luke Skywalker',
                        'Princess Leia', 'Chewbacca', 'Boba Fett', 'R2-D2',
                        'Yoda','Obi-Wan Kenobi','Anakin Skywalker','Padmé','C-3PO');
        $name = $randomNames[rand(0,count($randomNames)-1)];
        $mail = new \CODOF\Forum\Notification\Mail();
        $mail->to = $_POST['testToMail'];
        $mail->subject = "Test Mail - By $name at " . time();
        $mail->message = "Test Mail Body \n  $name: May the force be with you.";
        $mail->send_mail();

        if (!$mail->sent) {
            $errors[] = $mail->error;
            $flash['warning'] = true;
            $flash['message'] = implode("<br/>", $errors);
        } else {
            $flash['message'] = 'Mail sent successfully!';
        }
    }


}


$smarty->assign('flash', $flash);
$content = $smarty->fetch('mail/configuration.tpl');
