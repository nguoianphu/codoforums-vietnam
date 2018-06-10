<?php

/*
 * @CODOLICENSE
 */

$db = \DB::getPDO();
$tpl = 'categories.tpl';
if (isset($_GET['action'])) {

    if ($_GET['action'] == 'reorder' && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

        $obj = json_decode($_POST['data']);
        //var_dump($obj);
        $buff = array();
        $i = 0;
        $arr = $obj;
        $p_id = 0;
        linearize($arr, $buff, $i, $p_id);

        modify_permissions($buff);


        $query = "UPDATE " . PREFIX . 'codo_categories '
                . 'SET cat_order=:cat_order,cat_pid=:cat_pid WHERE cat_id=:cat_id';
        $stmt = $db->prepare($query);
        foreach ($buff as $value) {


            $stmt->execute($value);
        }
        echo "Order Has Been Saved!";
    } else if ($_GET['action'] == 'delete' && isset($_POST['CSRF_token']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

        delete_category($_POST['del_cat_id'], $_POST['del_cat_children']);
    } else if ($_GET['action'] == 'edit') {

        $tpl = 'category_edit.tpl';
        $smarty->assign('cat_id', $_GET['cat_id']);


        if (isset($_POST['mode']) && $_POST['mode'] == 'edit' && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

            if (!isset($_POST['show_children'])) {

                $_POST['show_children'] = 0;
            }
            
            $show_children = "on" === $_POST['show_children'] ? 1 : 0;

            $query = "UPDATE " . PREFIX . "codo_categories SET cat_name=:cat_name,is_label=:is_label,"
                    . "cat_description=:cat_description, show_children=:show_children, cat_img=:cat_img";
            $imgql = "";
            $cond = " WHERE cat_id=:cat_id";

            $arr[':cat_name'] = $_POST['cat_name'];
            $arr[':is_label'] = $_POST['is_label'] == 'yes' ? 1  : 0;
            $arr[':cat_description'] = $_POST['cat_description'];
            $arr[':show_children'] = $show_children;
            $arr[':cat_id'] = $_GET['cat_id'];
            $arr[":cat_img"] = $_POST['cat_img'];

            $stmt = $db->prepare($query . $imgql . $cond);
            $stmt->execute($arr);
        }



        $query = "SELECT * FROM " . PREFIX . "codo_categories WHERE cat_id=:cat_id";
        $stmt = $db->prepare($query);
        $res = $stmt->execute(array(':cat_id' => $_GET['cat_id']));
        if ($res) {
            $row = $stmt->fetch();
            $smarty->assign('cat', $row);
        }
    }
}

function delete_category($id, $delete_children) {

    $cids = array();
    if ($delete_children === 'yes') {

        $category = new CODOF\Forum\Category(\DB::getPDO());
        $cats_tree = $category->generate_tree($category->get_categories());
        $children = $category->get_sub_categories($cats_tree, $id);
        $cids = get_cids($children);

        if ($cids !== NULL) {
            DB::table(PREFIX . 'codo_categories')
                    ->whereIn('cat_id', $cids)
                    ->delete();
        } else {

            $cids = array();
        }
    } else {

        DB::table(PREFIX . 'codo_categories')
                ->where('cat_pid', '=', $id)
                ->update(array('cat_pid' => 0));
    }

    DB::table(PREFIX . 'codo_categories')->where('cat_id', $id)->delete();
    //delete all topics

    if ($delete_children !== 'yes') {

        $ids = array($id);
    } else {

        $ids = array_merge(array($id), $cids);
    }

    DB::table(PREFIX . 'codo_topics')->whereIn('cat_id', $ids)->delete();
    DB::table(PREFIX . 'codo_unread_topics')->whereIn('cat_id', $ids)->delete();
    DB::table(PREFIX . 'codo_unread_categories')->whereIn('cat_id', $ids)->delete();
    //DB::table(PREFIX . 'codo_tags AS g')
    //        ->join(PREFIX . 'codo_topics AS t', 't.topic_id', '=', 'g.topic_id')
    //        ->whereIn('t.cat_id', $ids)->delete();
    $q = 'DELETE codo_tags FROM ' . PREFIX . 'codo_tags '
            . ' LEFT JOIN ' . PREFIX . 'codo_topics ON '
            . PREFIX . 'codo_tags.topic_id=' . PREFIX . 'codo_topics.topic_id '
            . ' WHERE ' . PREFIX . 'codo_topics.cat_id IN (' . implode(',', $ids) . ')';

    \DB::delete($q);

    DB::table(PREFIX . 'codo_notify_subscribers')->whereIn('cid', $ids)->delete();

    DB::table(PREFIX . 'codo_permissions')->whereIn('cid', $ids)->delete();
    $qry = 'UPDATE ' . PREFIX . 'codo_users AS u,' . PREFIX . 'codo_posts As p SET no_posts=no_posts-'
            . '(SELECT COUNT(post_id) FROM codo_posts WHERE cat_id=' . $id . ' AND post_status <> 0 AND uid=u.id) 
            WHERE p.cat_id=' . $id . ' AND u.id=p.uid';

    DB::getPDO()->query($qry);

    DB::table(PREFIX . 'codo_posts')->whereIn('cat_id', $ids)->delete();
}

/**
 * 7 loops
 * 3 + n queries where n no. of changed permissions .
 * @param array $cats linear array of categories [cat_id, cat_pid, cat_order]
 */
function modify_permissions($cats) {

    //we know that permissions only need to be modified if the cat_pid 
    //of any category changes the cat_order is unrelated.
    //first we get the original categories from the database
    $o_cats = DB::table(PREFIX . 'codo_categories')->select('cat_id', 'cat_pid')->get();

    $old_cat_pids = array();
    foreach ($o_cats as $cat) {

        $old_cat_pids[$cat['cat_id']] = $cat['cat_pid'];
    }

    $changed_cats = array();
    foreach ($cats as $cat) {

        if (isset($old_cat_pids[$cat['cat_id']]) && $old_cat_pids[$cat['cat_id']] != $cat['cat_pid']) {

            //this category's parent has been changed
            $changed_cats[] = $cat;
        }
    }

    if (empty($changed_cats)) {

        return false;
    }

    $cat_pids = array_column($changed_cats, 'cat_pid');
    $cat_ids = array_column($changed_cats, 'cat_id');

    $parent_perms = DB::table(PREFIX . 'codo_permissions')->select('cid', 'rid', 'permission', 'granted')
                    ->whereIn('cid', $cat_pids)->get();
    $cat_perms = DB::table(PREFIX . 'codo_permissions')->select('pid', 'cid', 'rid', 'permission', 'granted', 'inherited')
                    ->whereIn('cid', $cat_ids)->get();

    $cat_inherited_perms = array();
    foreach ($cat_perms as $perm) {

        if ($perm['inherited'] === 1) {

            $cat_inherited_perms[$perm['cid']][] = $perm;
        }
    }

    $parent_granted = array();
    foreach ($parent_perms as $perm) {

        $parent_granted[$perm['cid']][$perm['permission'] . $perm['rid']] = $perm['granted'];
    }

    $changed_permissions = array();
    foreach ($changed_cats as $cat) {

        $cid = $cat['cat_id'];
        $pid = $cat['cat_pid'];
        foreach ($cat_inherited_perms[$cid] as $perm) {

            if (isset($parent_granted[$pid]) && $perm['granted'] != $parent_granted[$pid][$perm['permission'] . $perm['rid']]) {
                $changed_permissions[] = array(
                    "update" => array('granted' => $parent_granted[$pid][$perm['permission'] . $perm['rid']]),
                    "where" => array('pid' => $perm['pid'])
                );
            }
        }
    }

    //now finally update these changed permission
    foreach ($changed_permissions as $changed_permission) {

        DB::table(PREFIX . 'codo_permissions')
                ->where($changed_permission['where'])
                ->update($changed_permission['update']);
    }
}

function get_cids($cats, $cids = array()) {

    if (!empty($cats)) {
        foreach ($cats as $cat) {

            $cids[] = $cat->cat_id;

            if (property_exists($cat, 'children')) {

                return get_cids($cat->children, $cids);
            } else {

                return $cids;
            }
        }
    }
}

function linearize($arr, &$buff, &$i, $p_id) {

    foreach ($arr as $ray) {

        $buff[$i] = array('cat_id' => $ray->id, 'cat_pid' => $p_id, 'cat_order' => $i);
        $i++;
        if (isset($ray->children)) {

            linearize($ray->children, $buff, $i, $ray->id);
        }
    }
}

$smarty = \CODOF\Smarty\Single::get_instance();



$smarty->assign('msg', '');
$smarty->assign('err', 0);

if (isset($_POST['mode'])) {

    if ($_POST['mode'] == 'new' && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {


        $qry = 'INSERT INTO ' . PREFIX . 'codo_categories'
                . '(cat_pid,cat_name,cat_alias,cat_description,cat_img,no_topics,no_posts,cat_order, is_label)'
                . 'VALUES(:cat_pid,:cat_name,:cat_alias,:cat_description,:cat_img,:no_topics,:no_posts,:cat_order, :is_label)';
        $stmt = $db->prepare($qry);

        $arr[":cat_pid"] = 0;
        $arr[":cat_name"] = $_POST['cat_name'];
        $arr[":cat_alias"] = CODOF\Filter::URL_safe($_POST['cat_name']); //
        $arr[":cat_img"] = $_POST['cat_img']; //
        $arr[":cat_description"] = $_POST['cat_description'];
        $arr[":no_topics"] = 0;
        $arr[":no_posts"] = 0;
        $arr[":cat_order"] = 0;
        $arr[':is_label'] = $_POST['is_label'] == 'yes' ? 1  : 0;

        //$stmt->execute($arr);

        $stmt->execute($arr);
        $cid = $db->lastInsertId('cat_id');
        $manager = new \CODOF\Permission\Manager();
        $manager->copyCategoryPermissionsFromRole($cid);
        $smarty->assign('msg', 'New Category Created!');

    }
}

$qry = 'SELECT *  FROM ' . PREFIX . 'codo_categories ORDER BY cat_order';
$res = $db->query($qry);

if ($res) {

    $res = $res->fetchAll(PDO::FETCH_CLASS);
}


$frm = new CODOF\Forum\Forum();

$obj = $frm->generate_tree($res);
//var_dump($obj);

$buffer = "";

//$tree = new stdClass();
//$res=(object)$res;
//$obj = gen_tree($res, 0, $tree);



function print_children($cat, &$buffer) {
    //return; //for the timebeing no sub categories allowed


    $buffer.= "\n\n" . '<li  class="dd-item dd3-item" data-id="' . $cat->cat_id . '">'
            . '<div class="dd-handle">' . $cat->cat_alias . '</div><span class="dd-options">'
            . '<a class="btn btn-default" href="index.php?page=permission/categories&cat_id=' . $cat->cat_id . '"><i class="fa fa-key"></i> Permissions</a> '
            . '                                                             <a class="btn btn-default" href="index.php?page=categories&action=edit&cat_id=' . $cat->cat_id . '"><i class="fa fa-edit"></i> Edit</a> '
            . '                                                           &nbsp;&nbsp; <a class="btn btn-danger" href="javascript:void(0)" onclick="delete_cat(' . $cat->cat_id . ', \'' . $cat->cat_name . '\');"><i class="fa fa-trash-o"></i></a></span>';

    if (property_exists($cat, 'children')) {

        foreach ($cat->children as $child) {

            $buffer.="\n<ol  class='dd-list'>";
            print_children($child, $buffer);
            $buffer.="\n</ol>";
        }
    } else {
        
    }
    $buffer.= "\n</li>";
}

$buffer.="\n<div class='dd'  id='nestable3'>\n<ol  class='dd-list'>";

foreach ($obj as $o) {


    print_children($o, $buffer);
}
$buffer.="\n</ol>\n</div>";
$smarty->assign("cats", $buffer);
$smarty->assign('A_RURI', A_RURI);

$content = $smarty->fetch($tpl);
