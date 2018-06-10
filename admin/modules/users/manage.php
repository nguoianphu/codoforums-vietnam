<?php

$smarty = \CODOF\Smarty\Single::get_instance();
$db = \DB::getPDO();

$smarty->assign('err', 0);
$smarty->assign('msg', "");

$filters = "";
$filter_array = array();
$filter_url = "";

CODOF\Util::get_config($db);

if (!isset($_GET['pno'])) {
    $_GET['pno'] = 1;
}

function getPost($key, $default) {
    if (isset($_GET[$key]))
        return htmlspecialchars($_GET[$key], ENT_QUOTES, 'UTF-8');
    return $default;
}

//-----------get roles
function get_roles() {

    $db = \DB::getPDO();
    $query = "SELECT * FROM " . PREFIX . "codo_roles";
    $res = $db->query($query);
    $roles = $res->fetchAll();
    $sroles = array();

    foreach ($roles as $role) {
        $sroles[$role['rid']] = $role['rname'];
    }
    return $sroles;
}

//EDIT
if (isset($_GET['action']) && $_GET['action'] == 'edit') {



    if (isset($_POST['user_name']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {


        $query = "SELECT * FROM " . PREFIX . "codo_users WHERE (username=:username OR mail=:mail) AND id!=:id";
        $stmt = $db->prepare($query);
        $arr['username'] = $_POST['user_name'];
        $arr['mail'] = $_POST['email'];
        $arr['id'] = $_POST['id'];

        $res = $stmt->execute($arr);

        $arr['name'] = $_POST['display_name'];
        // $arr['rid'] = $_POST['role'];
        $arr['signature'] = $_POST['signature'];

        unset($arr['id']);
        $err = 0;
        $msg = "";

        if ($stmt->fetch()) {

            $err = 1;
            $msg = "username or email has already been taken!<br>";
        } else {


            if ($_POST['p1'] != "") {
                if ($_POST['p1'] != $_POST['p2']) {

                    $err = 1;
                    $msg = "The passwords do not match!";
                } else {


                    $hasher = new \CODOF\Pass(8, false);
                    $hash = $hasher->HashPassword($_POST['p1']);
                    $arr['pass'] = $hash;
                }
            }


            if (isset($_FILES['user_img']) && !empty($_FILES['user_img']['name'])) {

                $image = $_FILES['user_img'];

                \CODOF\File\Upload::$width = 128;
                \CODOF\File\Upload::$height = 128;
                \CODOF\File\Upload::$resizeImage = true;
                \CODOF\File\Upload::$resizeIconPath = DATA_PATH . PROFILE_ICON_PATH;
                $file_info = \CODOF\File\Upload::do_upload($image, PROFILE_IMG_PATH);

                if (\CODOF\File\Upload::$error) {

                    $err = 1;
                    $msg = "Error While uploading the image, try with a different image.";
                } else {

                    $arr["avatar"] = $file_info["name"];
                }
            }

            $arr['user_status'] = 0;

            if (isset($_POST['user_status'])) {

                $arr['user_status'] = 1;
            }

            //update
            $u = CODOF\User\User::get((int) $_GET['user_id']);
            if ($err == 0)
                $msg.="Updates have been applied.";

            $u->set($arr);
            $u->deleteAllRoles();

            $_POST['roles'][] = $_POST['primary_role'];

            $_POST['roles'] = array_unique($_POST['roles']);



            if (!empty($_POST['roles'])) {
                $u->addRoles($_POST['roles']);
            }


            //make role primary

            DB::table(PREFIX . "codo_user_roles")
                    ->where('uid', '=', $u->id)
                    ->update(array('is_primary' => 0));


            DB::table(PREFIX . "codo_user_roles")
                    ->where('uid', '=', $u->id)
                    ->where('rid', '=', $_POST['primary_role'])
                    ->update(array('is_primary' => 1));
        }
        $smarty->assign('err', $err);
        $smarty->assign('msg', $msg);
    }





    $user_id = (int) $_GET['user_id'];
    $u = CODOF\User\User::get($user_id);
    $res = $u->getInfo();

    $res['avatar'] = str_replace(ADMIN , "", $res['avatar']);


    $sroles = get_roles();

    $smarty->assign('prole_selected', $u->rid);
    $smarty->assign('role_options', $sroles);
    $role = $u->rids;
    $smarty->assign('role_selected', $role);



    $smarty->assign('user', $res);




    $content = $smarty->fetch('users/edit.tpl');
} else {




//NEW
    if (isset($_POST['a_username']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {


        if (CODOF\Util::is_field_present($_POST['a_username'], 'username') === TRUE) {
            
        } else if (CODOF\Util::is_field_present($_POST['a_email'], 'mail') === TRUE) {
            
        } else {

            if (CODOF\User\User::usernameExists($_POST['a_username']) || CODOF\User\User::mailExists($_POST['a_email'])) {

                $msg = 'username or email already exists!';
            } else {


                $reg = new CODOF\User\Register($db);
                $reg->username = $_POST['a_username'];
                $reg->name = $_POST['a_username'];
                $reg->mail = $_POST['a_email'];
                $reg->password = $_POST['a_password'];
                $reg->user_status = 1;
                $errors = $reg->register_user();

                $avatar = new \CODOF\User\Avatar();
                $avatar->generate($reg->userid, false);                

                $msg = implode('<br>', $errors);
            }
            //$msg = $errors[0];
            $err = 1;
            $smarty->assign("msg", $msg);
        }
    }



    //DELETE
    if (isset($_POST['delete_type']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

        $msg = "";

        $user = CODOF\User\User::get((int) $_POST['delete_id']);

        if ($user == false) {

            $_POST['delete_type'] = 'error';
            $msg.="User does not exist.<br>";
        } else if ($user->hasRoleId(ROLE_ADMIN) || $user->mail == 'anonymous@localhost') {
            $_POST['delete_type'] = 'error';
            $msg.="User with admin role/anonynous user cannot be deleted.";
        }

        $smarty->assign('msg', $msg);

        if ($_POST['delete_type'] == 'ban_and_keep') {



            $user->banAccount();
        } else if ($_POST['delete_type'] == 'ban_and_delete') {


            $user->banAccount();
            $user->deleteContent();
        } else if ($_POST['delete_type'] == 'delete_and_anonymous') {

            $user->makeContentAnonymous();
            $user->deleteAccount();
        } else if ($_POST['delete_type'] == 'delete_and_delete') {

            $user->deleteContent();
            $user->deleteAccount();
        } else {
            //do nothing.
        }
    }



//SELECT
//set roles
    $sroles = get_roles();
    $sroles[-1] = 'All roles';
    $smarty->assign('role_options', $sroles);
    $role = getPost('role', '-1');

    $smarty->assign('role_selected', $role);

    if ($role != -1) {
        $filters.=" AND r.rid=:rid ";
        $filter_array[':rid'] = $role;
        $filter_url.="&role=" . $role;
    } else {
        $filter_url.="&role=" . $role;
    }


//-------------------------status
    $smarty->assign('status_options', array(
        99 => 'All users',
        1 => 'Active users',
        0 => 'Blocked users',
            )
    );
    $status = getPost('status', 99);
    $smarty->assign('status_selected', $status);
    if ($status != 99) {
        $filters.=" AND u.user_status=:user_status ";
        $filter_array[':user_status'] = $status;
        $filter_url.="&status=" . $status;
    }


//------------------------name
    $username = getPost('username', "");
    $smarty->assign('entered_username', $username);
    if ($username != "") {
        $filters.=" AND (u.username LIKE :username OR u.mail LIKE :mail) ";
        $filter_array[':username'] = '%' . $username . '%';
        $filter_array[':mail'] = '%' . $username . '%';
        $filter_url.="&username=" . $username;
    }







//----------------sort URL

    $sort_column = "";
    $sort = getPost('sort_by', 'created');

    if ($sort == 'username') {
        $sort_column = 'u.username';
    } else if ($sort == 'status') {
        $sort_column = 'u.user_status';
    } else if ($sort == 'no_posts') {
        $sort_column = 'u.no_posts';
    } else {
        $sort_column = 'u.created';
    }

    $sort_order = htmlentities(getPost('sort_order', 'ASC'), ENT_QUOTES);


    $isor = 'DESC';
    if ($sort_order == 'DESC') //invert sort order for link
        $isor = 'ASC';

    $sort_url = "index.php?page=users/manage&sort_order=" . $isor . $filter_url . '&pno=' . (int) $_GET['pno']; //put inverted link only for table heading
    $smarty->assign('sort_url', $sort_url);



    $filter_url.="&sort_order=" . $sort_order . '&sort_by=' . $sort; //put normal sort order for other links
//-------------count no of users
    $query = "SELECT count(u.id) as user_count "
            . " FROM " . PREFIX . "codo_users as u, " . PREFIX . "codo_user_roles as r"
            . " WHERE u.id=r.uid AND r.is_primary=1 " . $filters;
    $stmt = $db->prepare($query);
    $stmt->execute($filter_array);
    $r = $stmt->fetch();

    $per_page = 10;
    $no_of_pages = ceil($r['user_count'] / $per_page);

    $fobj = new \CODOF\Forum\Forum();
    $pages = $fobj->paginate($no_of_pages, $_GET['pno'], A_RURI . "index.php?page=users/manage" . $filter_url . "&pno=", true);
    $smarty->assign('pagination_links', $pages);

    $pno = $_GET['pno'];
    $pno--; //starts from 0
    $offset = (int) $per_page * $pno;



//------------------------get users

    $query = "SELECT u.id,u.username,u.user_status,role.rname as role,u.created,u.no_posts "
            . " FROM " . PREFIX . "codo_users as u, " . PREFIX . "codo_user_roles as r, " . PREFIX . "codo_roles AS role "
            . " WHERE u.id=r.uid AND r.is_primary=1 AND role.rid=r.rid " . $filters
            . " ORDER BY $sort_column $sort_order "
            . " LIMIT 10 OFFSET $offset";

    $stmt = $db->prepare($query);
    $stmt->execute($filter_array);

    $smarty->assign('users', $stmt->fetchAll());





    $content = $smarty->fetch('users/manage.tpl');
}