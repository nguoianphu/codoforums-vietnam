<?php

/*
 * @CODOLICENSE
 */

namespace Controller;

class user {

    public $view = false;
    public $css_files = array();
    public $js_files = array();
    private $smarty;

    public function __construct() {

        $this->db = \DB::getPDO();
        $this->smarty = \CODOF\Smarty\Single::get_instance();
    }

    public function login() {

        \CODOF\Hook::call('before_login');

        $this->view = 'user/login';
        if (isset($_SESSION[UID . 'USER']['id'])) {
            header('Location: ' . \CODOF\User\User::getProfileUrl());
            exit;
        }
        
        $user = \CODOF\User\User::get();
        $this->smarty->assign('can_view_forum', $user->can('view forum'));

        \CODOF\Hook::call('after_login');
        \CODOF\Store::set('sub_title', _t('User login'));
        $this->smarty->assign('came_from_topic', isset($_GET['page']) ? 'yes' : 'no');
    }

    public function logout() {

        $user = \CODOF\User\User::get();
        $user->logout();

        if (\CODOF\Plugin::is_active('sso')) {

            header('Location: ' . \CODOF\Util::get_opt('sso_logout_user_path'));
            exit;
        }

        header('Location: ' . RURI);
    }

    public function profile($id, $action) {

        $this->view = 'user/profile/view';

        \CODOF\Store::set('meta:robots', 'noindex, follow');

        if ($id == null) {

            $id = 0;
        }

        if ($action == null) {

            $action = 'view';
        }


        $profile = new \CODOF\User\Profile();

        $uid = $profile->get_uid($id);

        $currUser = \CODOF\User\User::get();
        if (!$currUser->can('view user profiles') && $uid != $currUser->id) {

            //if current user cannot view user profiles and if he is trying
            //to view a profile that is not his, we need to deny him permission
            $action = 'deny';
        }


        $user = \CODOF\User\User::getByIdOrUsername($uid, $uid);

        if ($user) {

            $user->avatar = $user->getAvatar();
            //pass user object to template
            $this->smarty->assign('user', $user);
            $this->smarty->assign('rname', \CODOF\User\User::getRoleName($user->rid));


            \CODOF\Store::set('sub_title', $user->username);
            $can_edit = $this->can_edit_profile($uid);

            $cf = new \CODOF\User\CustomField();

            if ($action == 'edit' && $can_edit) {

                $this->view = 'user/profile/edit';
                $this->css_files = array('profile_edit');
                $this->js_files = array(
                    array(DATA_PATH . 'assets/js/user/profile/edit.js', array('type' => 'defer')),
                    array('bootstrap-slider.js', array('type' => 'defer'))
                );

                $subscriber = new \CODOF\Forum\Notification\Subscriber();

                $categories = $subscriber->getCategorySubscriptions($uid);
                $topics = $subscriber->getTopicSubscriptions($uid);

                $this->smarty->assign('categories', $categories);
                $this->smarty->assign('topics', $topics);
                $this->smarty->assign('signature_char_lim', \CODOF\Util::get_opt('signature_char_lim'));
                $this->smarty->assign('custom_fields', $cf->getEditFields($uid));
            } else if ($action == 'view') {

                $this->view = 'user/profile/view';
                if ($uid != $currUser->id) {

                    $user->incProfileViews();
                }

                $this->smarty->assign('user_not_confirmed', $uid == $currUser->id && !$user->isConfirmed());

                $reg_req_admin = \CODOF\Util::get_opt('reg_req_admin') == 'yes';
                $this->smarty->assign('user_not_approved', $uid == $currUser->id && (int) $user->rid == ROLE_UNVERIFIED && $reg_req_admin);

                $this->smarty->assign('can_edit', $can_edit);

                $this->css_files = array('profile_view');
                $this->js_files = array(
                    array(DATA_PATH . 'assets/js/user/profile/view.js', array('type' => 'defer'))
                );
                \CODOF\Hook::call('before_profile_view', $user);
                $this->smarty->assign('custom_fields', $cf->getViewFields($uid));
            } else {

                $this->view = 'access_denied';
            }
        } else {
            $this->view = 'not_found';
        }
    }

    public function can_edit_profile($id) {

        $user = \CODOF\User\User::get();
        if (!isset($id) || !$id) {
            //if id is not passed
            return false;
        } else {

            //if not editing own profile and does not have permission to do so.
            //if ($id != $user->id && !$user->can('edit others profiles')) {
            //    return false;
            //}
        }

        return $id == $user->id && $user->can('edit profile');
    }

    public function edit_profile($id_being_edited) {

        $user = \CODOF\User\User::get();
        $id = (int) $id_being_edited;

        if (!$this->can_edit_profile($id)) {

            $this->view = 'access_denied';
            return false;
        }

        $values = array(
            "name" => \CODOF\Filter::msg_safe($_POST['name']),
            "signature" => \CODOF\Format::omessage($_POST['signature'])
        );

        $success = true;

        if (isset($_FILES) && $_FILES['avatar']['error'] != UPLOAD_ERR_NO_FILE) {

            $success = false;
            \CODOF\File\Upload::$width = 128;
            \CODOF\File\Upload::$height = 128;
            \CODOF\File\Upload::$resizeImage = true;
            \CODOF\File\Upload::$resizeIconPath = DATA_PATH . PROFILE_ICON_PATH;
            $result = \CODOF\File\Upload::do_upload($_FILES['avatar'], PROFILE_IMG_PATH);

            if (\CODOF\File\Upload::$error) {

                $this->smarty->assign('file_upload_error', $result);
            } else {

                $values["avatar"] = $result['name'];
                $success = true;
            }
        }

        $edited = $user->set($values);

        //get editable fields, because not all fields are editable...
        $cf = new \CODOF\User\CustomField();
        $fields = $cf->getEditFields($id);
        
        $input_names = array_keys($_POST);
        $fids = array();
        foreach($input_names as $name) {
            
            //even if there is a conflict with some other name, it won't matter
            //as the result will only be used for processing custom fields
            $fids[] = str_replace("input_", "", $name);
        }
        
        //TODO: improve by adding default value for every custom field
        //then just remove the row if the new value is same as default
        //value and only add row if the new value is different than the
        //default value
        
        foreach($fields as $field) {
            
            if(in_array($field['id'], $fids)) {
                
                //a custom field
                //lets first check if a row has already been made for it.
                $count = \DB::table(PREFIX . 'codo_fields_values')
                        ->where('uid', '=', $id) //id of the profile being edited
                        ->where('fid', '=', $field['id'])->count();
                
                if($count > 0) {
                    
                    //there is already a field with that name.
                    
                    \DB::table(PREFIX . 'codo_fields_values')
                            ->where('uid', '=', $id)
                            ->where('fid', '=', $field['id'])
                            ->update(array(
                                'value' => $_POST['input_' . $field['id']]
                            ));
                }else {
                    
                    \DB::table(PREFIX . 'codo_fields_values')
                            ->insert(array(
                                'uid' => $id,
                                'fid' => $field['id'],
                                'value' => $_POST['input_' . $field['id']]
                            ));
                    
                }
            }
        }

        if (!$edited) {

            Util::log("Failed to update user details profile/id/edit");
            $success = false;
        }

        $this->smarty->assign('user_profile_edit', $success);


        $this->profile($id, 'edit');
    }

    public function register($do) {

        if (isset($_SESSION[UID . 'USER']['id'])) {
            header('Location: ' . \CODOF\User\User::getProfileUrl());
            exit;
        }

        $this->view = 'user/register';
        $set_fields = array('username', 'password', 'mail');
        $req_fields = array('username', 'password', 'mail');

        $this->js_files = array(
            array(DATA_PATH . 'assets/js/user/register.js', array('type' => 'defer'))
        );

        $cf = new \CODOF\User\CustomField();
        $custom_fields = $cf->getRegistrationFields();
        $this->smarty->assign('custom_fields', $custom_fields);

        if (\CODOF\Util::is_set($_REQUEST, $set_fields) && !\CODOF\Util::is_empty($_REQUEST, $req_fields) && $do) {

            $register = new \CODOF\User\Register($this->db);

            $register->username = str_replace('"', '&quot;', $_REQUEST['username']);
            $register->name = null; //$_REQUEST['name'];
            $register->password = $_REQUEST["password"];
            $register->mail = $_REQUEST['mail'];
            $register->rid = ROLE_UNVERIFIED;

            $errors = $register->get_errors();

            if (empty($errors)) {

                $errors = $register->register_user();
                $cf->setRegistrationFields($register->userid);
                $register->login();
                header('Location: ' . \CODOF\User\User::getProfileUrl());
                exit;
            }

            $this->smarty->assign('errors', $errors);
        } else {

            $register = new \stdClass();
            $register->username = null;
            $register->name = null; //$_REQUEST['name'];
            $register->password = null;
            $register->mail = null;
        }

        if (\CODOF\Util::get_opt('captcha') == "enabled") {

            $publickey = \CODOF\Util::get_opt('captcha_public_key'); // you got this from the signup page
            $this->smarty->assign('recaptcha', '<div class="g-recaptcha col-md-6" data-sitekey="' . $publickey . '"></div>');
            // nguoianphu Adding Vietnamese language
            $this->js_files[] = array('https://www.google.com/recaptcha/api.js?hl=vi', array('type' => 'remote'));
        }

        $this->smarty->assign('min_pass_len', \CODOF\Util::get_opt('register_pass_min'));
        $this->smarty->assign('min_username_len', \CODOF\Util::get_opt('register_username_min'));
        $this->smarty->assign('register', $register);

        \CODOF\Store::set('sub_title', 'Register');
    }

    public function confirm() {

        $this->view = 'user/confirm';
        $action = array();

        if (empty($_GET['user']) || empty($_GET['token'])) {
            $action['result'] = 'VAR_NOT_PASSED';
            //$action['text'] = 'We are missing variables. Please double check your email.';
        } else {

            //cleanup the variables
            $username = $_GET['user'];
            $token = $_GET['token'];

            //check if the key is in the database
            $qry = "SELECT username FROM  " . PREFIX . "codo_signups WHERE username=:username AND token=:token LIMIT 1 OFFSET 0";
            $stmt = $this->db->prepare($qry);
            $result = $stmt->execute(array("username" => $username, "token" => $token));

            if ($result) {

                //get the confirm info
                $res = $stmt->fetch();

                $reg_req_admin = \CODOF\Util::get_opt('reg_req_admin');

                $user_status = 1;
                if ($reg_req_admin == 'yes') {

                    $user_status = 2;
                }
                //confirm the email and update the users database
                $qry = "UPDATE " . PREFIX . "codo_users SET user_status=$user_status WHERE username=:username";
                $stmt = $this->db->prepare($qry);
                $stmt->execute(array("username" => $username));

                if ($reg_req_admin == 'no') {

                    $user = \CODOF\User\User::getByUsername($username);
                    $qry = "UPDATE " . PREFIX . "codo_user_roles SET rid=:rid WHERE uid=" . $user->id;
                    $stmt = $this->db->prepare($qry);
                    $stmt->execute(array("rid" => ROLE_USER));
                }

                //delete the signup rows associated with the selected username
                $qry = "DELETE FROM " . PREFIX . "codo_signups WHERE username = '" . $res['username'] . "'";
                $this->db->query($qry);

                $action['result'] = 'SUCCESS';
            } else {

                $action['result'] = 'VAR_NOT_FOUND';
            }
        }

        \CODOF\Store::set('sub_title', _t('Confirm user'));
        $this->smarty->assign('result', $action['result']);
    }

    public function forgot() {

        $this->view = 'user/forgot';
        \CODOF\Store::set('sub_title', _t('Forgot passsword'));
    }

    public function reset() {

        $this->view = 'user/reset';
        \CODOF\Store::set('sub_title', _t('Reset passsword'));
    }

    public static function access_denied() {

        $this->view = 'access_denied';
        \CODOF\Store::set('sub_title', _t('Access Denied'));
    }

    public static function not_found() {

        $this->view = 'not_found';
        \CODOF\Store::set('sub_title', _t('Not found'));
    }

}
