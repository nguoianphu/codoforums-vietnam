<?php

$smarty = \CODOF\Smarty\Single::get_instance();

$db = \DB::getPDO();

$qry = "SELECT rid,rname FROM codo_roles";
$res = $db->query($qry);

if ($res) {

    $roles = $res->fetchAll();
}


if (isset($_POST['subject']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

    $subject = html_entity_decode($_POST['subject'], ENT_NOQUOTES, "UTF-8");
    $body = html_entity_decode($_POST['body'], ENT_NOQUOTES, "UTF-8");

    $condition = "";

    /* if (strpos($body, '[username]') !== FALSE ||
      strpos($body, '[userid]')) {
     */
    $users = \DB::table(PREFIX . 'codo_users')
            ->select('id', 'name', 'mail');

    if (isset($_POST['roles'])) {

        $users = $users->join(PREFIX . 'codo_user_roles', 'uid', '=', 'id')
                ->whereIn('rid', $_POST['roles']);
    }

    $users = $users->get();

    $mails = array();

    foreach ($users as $user) {

        $_body = str_replace('[username]', $user['name'], $body);
        $__body = str_replace('[userid]', $user['id'], $_body);

        $mails[] = array(
            "to_address" => $user['mail'],
            "mail_subject" => $subject,
            "body" => $__body
        );
    }

    if(count($mails) > 0) {

      \DB::table(PREFIX . 'codo_mail_queue')->insert($mails);
    }
    /* } else {

      if (isset($_POST['roles'])) {

      $condition = " INNER JOIN " . PREFIX . "codo_user_roles AS r ON r.uid=u.id "
      . " WHERE r.rid IN  (" . implode($_POST['roles']) . ")";
      }

      $qry = "INSERT INTO " . PREFIX . "codo_mail_queue (to_address, mail_subject, body)"
      . " SELECT mail, :subject, '$body' FROM " . PREFIX . "codo_users AS u"
      . $condition;

      $db->prepare($qry);

      } */

  $smarty->assign('sent', 'success');
}
else {

  $smarty->assign('sent', false);
}

$smarty->assign('roles', $roles);
$content = $smarty->fetch('system/massmail.tpl');
