<?php

/*
 * @CODOLICENSE
 */

$smarty = \CODOF\Smarty\Single::get_instance();
$smarty->assign('msg', "");

Class A_Smile {

    public $smarty = null;

    function __construct($tpl) {

        $this->smarty = $tpl;
    }

    public function get_smilies() {

        $smilies = DB::table(PREFIX . "codo_smileys")->orderBy("weight", "asc")->get();


        $ret = array();

        foreach ($smilies as $smile) {

            $tmp = $smile;
            $tmp['image_name'] = A_DURI . 'assets/img/smileys/' . $smile['image_name'];
            $ret[] = $tmp;
        }

        return $ret;
    }

    public function clean_lines($str) {


        $order = array("\r\n", "\n", "\r");
        $str = str_replace($order, "\n", $str);
        $bits=explode("\n",$str);
        
        $nbits=array();
        
        foreach($bits as $bit){
            
            $bit=trim($bit);
            
            if(strlen($bit)>0){
                $nbits[]=$bit;
            }
        }
        
        return implode("\n",$nbits);
    }

    public function add_smiley() {


        if (isset($_POST['smiley_code'])  &&  CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {



            if (isset($_FILES['smiley_image'])) {

                $image = $_FILES['smiley_image'];
                if (
                        !\CODOF\File\Upload::valid($image) OR ! \CODOF\File\Upload::not_empty($image) OR ! \CODOF\File\Upload::type($image, array('jpg', 'jpeg', 'png', 'gif', 'pjpeg', 'bmp'))) {
                    $this->smarty->assign('err', 1);
                    $this->smarty->assign('msg', "Error While uploading the image.");
                } else {

                    $file_info = \CODOF\File\Upload::save($image, NULL, DATA_PATH . 'assets/img/smileys', 0777);
                    $arr["image_name"] = $file_info["name"];
                    $arr["symbol"] = $this->clean_lines($_POST['smiley_code']);
                    $arr["weight"] = (int) $_POST['weight'];

                    DB::table(PREFIX . "codo_smileys")->insert(
                            $arr
                    );
                    $this->smarty->assign('msg', "Smiley added successfully.");
                }
            }
        }
    }

    public function display_page() {

        $this->smarty->assign('smilies', $this->get_smilies());
    }

    public function delete_smiley() {

        if (isset($_GET['id']) &&  CODOF\Access\CSRF::valid($_GET['CSRF_token'])) {

            $id = (int) $_GET['id'];
            DB::table(PREFIX . 'codo_smileys')->where('id', $id)->delete();
            $this->smarty->assign('msg', "Smiley deleted successfully.");
        }
    }

    public function edit_smiley() {

        $id = (int) $_REQUEST['id'];

        if (isset($_POST['smiley_code']) &&  CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

            $arr = array();
            if (isset($_FILES['smiley_image'])) {

                $image = $_FILES['smiley_image'];

                if (
                        !\CODOF\File\Upload::valid($image) OR ! \CODOF\File\Upload::not_empty($image) OR ! \CODOF\File\Upload::type($image, array('jpg', 'jpeg', 'png', 'gif', 'pjpeg', 'bmp'))) {
                    $this->smarty->assign('err', 1);
                    $this->smarty->assign('msg', "Error While saving the image.");
                } else {

                    $file_info = \CODOF\File\Upload::save($image, NULL, DATA_PATH . 'assets/img/smileys', 0777);
                    $arr["image_name"] = $file_info["name"];
                }
            }

            $arr['symbol'] = $this->clean_lines($_POST['smiley_code']);
            $arr['weight'] = $_POST['weight'];

            DB::table(PREFIX . "codo_smileys")->where("id", $id)->update(
                    $arr
            );
            $this->smarty->assign('msg', "Smiley saved successfully.");
        }


        $smiley = DB::table(PREFIX . "codo_smileys")->where("id", $id)->first();

        $smiley['image_name'] = A_DURI . 'assets/img/smileys/' . $smiley['image_name'];
        $this->smarty->assign('smiley', $smiley);
    }

}

$smile = new A_Smile($smarty);


if (isset($_GET['action']) && $_GET['action'] == 'add') {

    $smile->add_smiley();
    $smile->display_page();
    $content = $smarty->fetch('ui/smileys.tpl');
} else if (isset($_GET['action']) && $_GET['action'] == 'delete') {

    $smile->delete_smiley();
    $smile->display_page();
    $content = $smarty->fetch('ui/smileys.tpl');
} else if (isset($_GET['action']) && $_GET['action'] == 'edit') {

    $smile->edit_smiley();
    $content = $smarty->fetch('ui/smiley_edit.tpl');
} else {
    $smile->display_page();
    $content = $smarty->fetch('ui/smileys.tpl');
}




