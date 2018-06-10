<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.get_children.php
 * Type:     function
 * Name:     get_children
 * Purpose:  gets all sub categories of a parent category
 * -------------------------------------------------------------
 */

/*
 * @CODOLICENSE
 */

function smarty_function_get_children($params) {

    if (property_exists($params['cat'], 'children')) {

        $children = $params['cat']->children;
        if (!empty($children)) {

            $view = 'block';

            if ($params['cat']->show_children == 0) {

                $view = 'none';
            }

            echo '<ul style="display: ' . $view . '">';
            $cnt = count((array) $children);
            foreach ($children as $child) {

                $cls = '';
                if (property_exists($child, 'children')) {

                    $grandchild = $child->children;

                    if (empty($grandchild) && $cnt == 1) {

                        $cls = 'codo_last_level_li';
                    }
                }
                echo '<li class=' . $cls . '>';
                codo_cat_build_structure($child, $params['new_topics']);
                echo smarty_function_get_children(array("cat" => $child, "new_topics" => $params['new_topics']));
                echo '</li>';
                $cnt--;
            }
            echo '</ul>';
        }
    }
}

function codo_cat_build_structure($cat, $new_topics) {

    $DURI = DURI;
    $CAT_IMGS = CAT_ICON_IMGS;
    $no_topics_title = _t('No. of topics');
    $new_no = '';

    if (isset($new_topics[$cat->cat_id])) {

        $new_no = '<a title="' . _t('new topics') . '"><span class="codo_new_topics_count">' . $new_topics[$cat->cat_id] . '</span></a>';
    }

    if ($cat->granted) {

        $no_topics = \CODOF\Util::abbrev_no($cat->no_topics, 2);
    } else {

        $no_topics = '-';
    }

    $url = RURI . 'category/' . $cat->cat_alias;
    echo <<<EOD
    <div class="row"> 
   <div class="codo_categories_category">
        <a href="$url"><div class="codo_category_title">$cat->cat_name</div></a>
        <span data-toggle="tooltip" data-placement="bottom" title="$no_topics_title" class="codo_category_num_topics codo_bs_tooltip">$no_topics</span>            
        $new_no    
   </div>
    </div>  
EOD;

    static $num_topics = 0;
    $num_topics++;
}
