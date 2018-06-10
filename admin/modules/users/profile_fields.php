<?php

$smarty = \CODOF\Smarty\Single::get_instance();
$db = \DB::getPDO();

if (!isset($_GET['action'])) {

    $res = $db->query('SELECT * FROM ' . PREFIX . 'codo_fields ORDER BY weight');
    $fields = $res->fetchAll();


    $smarty->assign('msg', '');
    $smarty->assign('fields', $fields);
    $content = $smarty->fetch('users/profile_fields.tpl');
} else {

    if ($_GET['action'] == 'addnewfield') {
        $res = $db->query('SELECT * FROM ' . PREFIX . 'codo_roles');
        $roles = $res->fetchAll();

        $smarty->assign('roles', $roles);
        $smarty->assign('msg', '');
        $field = array('type' => '',
            'show_reg' => 1,
            'show_profile' => 1,
            'is_mandatory' => 1,
            'data' => '',
            'hide_not_set' => 1,
            'def_value' => '',
            'input_type' => 'input'
        );


        $smarty->assign('field', $field);

        $content = $smarty->fetch('users/profile_fields_edit.tpl');
    } else if ($_GET['action'] == 'update_order' && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

        $wt = 0;
        foreach ($_POST['ids'] as $id) {

            $id = (int) $id;

            \DB::table(PREFIX . 'codo_fields')
                    ->where('id', '=', $id)
                    ->update(array('weight' => $wt++));
        }
        exit;
    } else if ($_GET['action'] == 'updatevisibility' && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

        \DB::table(PREFIX . 'codo_fields')
                ->where('id', $_POST['id'])
                ->update(array('enabled' => $_POST['enabled']));

        exit;
    } else if ($_GET['action'] == 'newfield' && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

        $action = 'new';
        if (isset($_GET['id'])) {

            $action = 'edit';
            $id = $_GET['id'];
        }

        if (!($_POST['field_type'] == 'input' || $_POST['field_type'] == 'textarea')) {

            $_POST['input_type'] = null;
        }

        $arr = array(
            "name" => $_POST['name'],
            "title" => $_POST['title'],
            "type" => $_POST['field_type'],
            "show_reg" => isset($_POST['show_reg']) && $_POST['show_reg'] == 'on' ? 1 : 0,
            "is_mandatory" => isset($_POST['mandatory']) && $_POST['mandatory'] == 'on' ? 1 : 0,
            "show_profile" => isset($_POST['show_profile']) && $_POST['show_profile'] == 'on' ? 1 : 0,
            "hide_not_set" => isset($_POST['hide_not_set']) && $_POST['hide_not_set'] == 'on' ? 1 : 0,
            "def_value" => $_POST['def_value'],
            "output_format" => $_POST['format'],
            "input_type" => $_POST['input_type'],
            "input_length" => $_POST['input_length'],
            "data" => $_POST['options_data']
        );

        if ($action == 'new') {
            $arr['enabled'] = 1;
            $id = \DB::table(PREFIX . 'codo_fields')
                    ->insertGetId($arr);
        } else {

            \DB::table(PREFIX . 'codo_fields')
                    ->where('id', $_GET['id'])
                    ->update($arr);
        }


        if (isset($_POST['roles'])) {

            DB::table(PREFIX . "codo_fields_roles")->where('fid', '=', $id)->delete();
            foreach ($_POST['roles'] as $rid) {

                \DB::table(PREFIX . 'codo_fields_roles')
                        ->insert(array(
                            'fid' => $id,
                            "rid" => $rid
                ));
            }
        }

        header('Location: index.php?page=users/profile_fields&action=editfield&mode=' . $action . '&id=' . $id);
    } else if ($_GET['action'] == 'delete_field' && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

        $fid = (int) $_POST['fid'];

        DB::table(PREFIX . 'codo_fields')
                ->where('id', '=', $fid)
                ->delete();

        DB::table(PREFIX . 'codo_fields_roles')
                ->where('fid', '=', $fid)
                ->delete();

        DB::table(PREFIX . 'codo_fields_values')
                ->where('fid', '=', $fid)
                ->delete();

        $res = $db->query('SELECT * FROM ' . PREFIX . 'codo_fields ORDER BY weight');
        $fields = $res->fetchAll();

        $smarty->assign('fields', $fields);
        $smarty->assign('msg', 'Field was successfully deleted');

        $content = $smarty->fetch('users/profile_fields.tpl');
    } else if ($_GET['action'] == 'editfield') {

        //no authentication required
        $id = (int) $_GET['id'];


        $field = \DB::table(PREFIX . 'codo_fields AS f')
                ->select('f.name', 'f.title', 'f.type', 'f.show_reg', 'f.is_mandatory', 'f.output_format', 'f.show_profile', 'f.input_type', 'f.input_length', 'f.data', 'f.hide_not_set', 'f.def_value')
                ->where('f.id', '=', $id)
                ->first();

        $fid = $id;
        $query = "SELECT role.rname, role.rid, f.fid "
                . "FROM codo_roles AS role "
                . "LEFT JOIN codo_fields_roles AS f ON role.rid = f.rid "
                . "AND f.fid =$fid";
        $res = $db->query($query);
        $roles = $res->fetchAll();


        $smarty->assign('field', $field);
        $smarty->assign('roles', $roles);
        $smarty->assign('id', $id);


        if (!isset($_GET['mode'])) {

            $msg = '';
        } else if ($_GET['mode'] == 'new') {

            $msg = 'Field created succesfully';
        } else {

            $msg = 'Field saved succesfully';
        }

        $smarty->assign('msg', $msg);


        $content = $smarty->fetch('users/profile_fields_edit.tpl');
    }
}