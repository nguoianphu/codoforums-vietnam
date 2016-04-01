<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Search;

/**
 * NOTE:
 * Below search is INEFFICIENT due to use of LIKE without indexing
 *
 * TODO: use FULL-TEXT indexed search or build indexes for all posts
 *       or use a 3rd-party like sphinx
 */

/**
 *
 * Description
 * -----------
 *
 * Searching posts and topics (and users)
 *
 * Search parameters
 *
 *  -<Keywords>  To exculde a string from search
 *  category <Category names> To search in categories (comma separated)
 *  tags <Tags> To search topics containing tags (comma separated) NOT implemented
 *  sort <Sort type>
 *  order <ASC/DESC>
 *
 *
 *  But the actual search string can have
 *  -    exclude operator
 *  AND  make sure two or more keywords exist together
 *  OR   make sure any of these keywords exist
 *
 *  for eg.  dog -cat will search for "dog" not including "cat"
 *           dog OR cat -bat will search for posts containing dog or cat
 *                           but will exclude bat
 *           dog AND cat -rat will search for posts containing dog and cat
 *                           in the same post but exclude rat
 *
 *  All other search parameters are provided as POST data for simplicity
 *
 *  By default , when two search terms are separated by space i.e cat dog
 *  It is considered as AND . so cat dog rat is equivalent to cat AND dog AND rat
 *
 */
class Search {

    /**
     * Raw search string expression provided by the user
     * @var <String>
     */
    public $str = '';

    /**
     * $str greater than this length will be clipped
     * @var <int>
     */
    public $max_str_len = 100;

    /**
     * No. of results to be retreived from the database
     * @var <int>
     */
    public $num_results = 10;

    /**
     * Used in pagination
     * @var <int>
     */
    public $from = 0;

    /**
     * Should the search keywords be matched with topic titles ?
     * @var string Yes / No
     */
    public $match_titles = 'Yes';

    /**
     *
     * Comma separated category ids
     * @var type
     */
    public $cats = null;

    /**
     * 
     * Topic id 
     * @var int
     */
    public $tid = null;
    public $pid = null;

    /**
     * Whether search results should be ordered in ascending
     * or descending order ?
     * @var <string>
     */
    public $order = 'ASC';

    /**
     * How should the results be sorted ?
     *  - 'post_created': Sorts by post created
     *  - 'no_posts': Sorts by no. of replies to the topic
     *  - 'no_views': Sorts by no. of views to the topic
     *  - 'last_post_time': Sorts by latest reply time to the topic
     *  - 'message': Sorts by relevance i.e keywords matching post
     *  - 'name': Sorts by author creating the topic
     *  - 'title': Sorts by topic title
     *
     * Note: The order of each respective sort is <$order>
     * @var <table field>
     */
    public $sort = 'post_created';

    /**
     * Can be hour, day , week, month, year
     * @var type
     */
    public $time_within = 'anytime';

    /**
     * If set to true, it will count the total number of rows that will be
     * returned by search without limit clause .
     * This count can then be later fetched by using get_total_count()
     * @var boolean
     */
    public $count_rows = false;

    /**
     * Results must include following tags
     * @var <string>
     */
    protected $tags = '';

    /**
     * PDO object
     * @var <object>
     */
    private $db;
    private $keys;
    private $values;
    private $sort_keys = array(
        "post_created" => "p.",
        "message" => "p.o",
        "title" => "t.",
        "last_post_time" => "t.",
        "no_posts" => "t.",
        "no_views" => "t.",
        "name" => "u."
    );

    /**
     * Total no. of rows without limit clause
     * @var int
     */
    private $count = 0;
    private $isMySQL;

    public function __construct() {

        $this->db = \DB::getPDO();

        $conf = get_codo_db_conf();
        $this->isMySQL = in_array($conf['driver'], array('mysql', 'mysqli'));
    }

    /**
     *
     * @param type $from
     */
    public function search() {

        //whitelisting to prevent sql injection
        if ($this->order != 'Asc' && $this->order != 'Desc') {

            $this->order = 'Desc';
        }

        if (!in_array($this->sort, array_keys($this->sort_keys))) {

            $this->sort = "post_created";
        }

        //$t1 = microtime(true);
        $base_qry = $this->build_query();
        $qry = $this->setSelectors($base_qry, $this->getSelectors());
        $obj = $this->db->prepare($qry);

        if (!empty($this->cats)) {
            $this->values = array_merge($this->values, array($this->cats));
        }
        $values = $this->values;
        $obj->execute($this->values);
        $res = $obj->fetchAll();

        if ($this->count_rows) {

            if ($this->isMySQL) {
                $cnt_qry = 'SELECT  FOUND_ROWS();';
                $cnt_obj = $this->db->prepare($cnt_qry);
                $cnt_obj->execute();
            } else {

                $cnt_qry = $this->setSelectors($base_qry, 'COUNT(p.post_id)');
                $cnt_obj = $this->db->prepare($cnt_qry);
                $cnt_obj->execute($values);
            }

            $rows = $cnt_obj->fetch();
            $this->count = $rows[0];
        }

        return $res;

        //echo microtime(true) - $t1;
    }

    public function get_total_count() {

        return $this->count;
    }

    public function get_values() {

        return $this->values;
    }

    /**
     *
     * Removes exclusion words i.e -cat and creates a 1d array of all words
     * @param type $item
     * @param type $key
     */
    private function linearize_array($item, $key) {

        if ($item[0] != '-') {
            $this->_keys[] = $item;
        }
    }

    public function strpos($haystack, $needle, $offset) {

        $pos = strpos($haystack, $needle, $offset);

        if ($pos === FALSE) {

            return $offset;
        }

        return $pos;
    }

    /**
     *
     * Splits string at spaces into sizes of about $chunk_size
     * @param type $str
     * @param type $chunk_size
     *
     */
    public function chunk_split($str, $chunk_size) {


        $chunks = array();
        $start = 0;
        $end = strlen($str);
        
        while (true) {

            if($start + $chunk_size > $end) break;
            $pos = $this->strpos($str, " ", $start + $chunk_size);
            $chunks[] = substr($str, $start, $pos - $start);

            if ($pos == $start + $chunk_size) {
                break;
            }

            $start += $pos;
        }

        return $chunks;
    }

    public function highlight($output) {

        foreach ($this->_keys as $keyword) {
            //now add highlighting to all keywords
            $output = preg_replace("/$keyword/i", "<span class='codo_search_highlight'>\$0</span>", $output);
        }

        return $output;
    }

    public function get_matching_str($str) {

        $chunk_size = 300;
       // var_dump(debug_backtrace())
        $this->_keys = array();
        array_walk_recursive($this->keys, array($this, 'linearize_array'));
        $first_positions = $last_positions = array();
        $no_matches = array();

        $l_str = strtolower($str);

        //get first position of each keyword
        foreach ($this->_keys as $key) {

            $key = strtolower($key);
            $first_positions[$key] = strpos($l_str, $key);
            $last_positions[$key] = strrpos($l_str, $key);
        }
        
        $start = min(($first_positions)); //removed array_filter CF 3.0
        $end = max($last_positions);
        //if the length between first keyword and last
        //is less than chunk size , it means we will get only
        //one chunk

        $str_len = strlen($str);
        if ($str_len <= $chunk_size) {

            $start = 0;
            $end = strlen($str);
            $output = $str;
        } else if (($end - $start) <= $chunk_size || $end === FALSE) {

            $end = $this->strpos($l_str, " ", $chunk_size);
            $output = substr($str, $start, $end + 1);
        } else {

            $end = $this->strpos($l_str, " ", $end);
            $diff = $end - $start + 1;
            //Get part of string starting from first keyword and
            //ending at first space after last keyword
            $imp_str = substr($str, $start, $diff);

            //Divide the above string into chunks of about 200.
            $chunks = $this->chunk_split($imp_str, $chunk_size);

            //now find which chunk is the most important
            //by counting no. of matches of all keywords
            foreach ($chunks as $chunk) {

                $cnt = 0;
                //add counts of all keywords in a chunk
                foreach ($this->_keys as $key) {

                    $key = strtolower($key);
                    $cnt += substr_count($chunk, $key);
                }

                $no_matches[] = $cnt;
            }

            $max_key = array_keys($no_matches, max($no_matches));

            $most_imp_chunk = $chunks[$max_key[0]];

            $output = $most_imp_chunk;
        }

        $output = $this->highlight($output);
        //echo $output;

        if ($start > 0) {

            $output = " ... <br/>" . $output;
        }

        if ($end < strlen($str)) {

            $output = $output . " ... ";
        }


        return nl2br($output);
    }

    private function getSelectors() {

        return 'p.post_id, p.imessage AS message,p.imessage, p.post_created,p.post_modified,p.reputation,last_post_id, '
                . 'u.id, u.username as name, u.avatar, u.signature,r.rid, c.cat_img, c.cat_alias, t.topic_created,'
                . 't.topic_id, t.uid, t.title,t.cat_id, t.no_posts, t.no_views, t.last_post_time, '
                . 't.last_post_uid, t.last_post_name AS last_post_name, t.topic_status';
    }

    private function setSelectors($qry, $selectors) {

        return str_replace("#SELECTORS#", $selectors, $qry);
    }

    /*
     * Returns a base query which is then manipulated based on the
     * search conditions and keywords
     * @return <string>
     */

    private function base_query() {


        if ($this->count_rows && $this->isMySQL) {

            $count = 'SQL_CALC_FOUND_ROWS';
        } else {

            $count = '';
        }

        $qry = 'SELECT ' . $count . ' #SELECTORS# '
                . 'FROM codo_posts AS p '
                . 'LEFT JOIN codo_topics AS t ON t.topic_id=p.topic_id '
                . 'LEFT JOIN codo_users AS u ON u.id=p.uid '
                . 'LEFT JOIN codo_categories AS c ON c.cat_id=t.cat_id '
                . 'LEFT JOIN codo_user_roles AS r ON r.uid=u.id AND r.is_primary=1 '
                . 'WHERE t.topic_status<>0 '
                . '      AND p.post_status=1'
                . '      #CONDITIONS# ';

        if ($this->cats != null) {

            $qry .= ' AND p.cat_id IN (?) ';
        }

        if ($this->tid != null) {

            if(strpos($this->tid, '=') === FALSE) {
                
                $this->tid = ' = ' . $this->tid;
            }
            $qry .= ' AND p.topic_id ' . $this->tid;
        }

        if ($this->pid != null) {

            $qry .= ' AND p.post_id ' . $this->pid;
        }

        if ($this->time_within != 'anytime') {

            $time = new \CODOF\Time();
            $error = false;

            if ($this->time_within == 'hour') {

                $this->time_within = $time->unix_get_time_hour();
            } else if ($this->time_within == 'day') {

                $this->time_within = $time->unix_get_time_day();
            } else if ($this->time_within == 'week') {

                $this->time_within = $time->unix_get_time_day(7);
            } else if ($this->time_within == 'month') {

                $this->time_within = $time->unix_get_time_day(31);
            } else if ($this->time_within == 'year') {

                $this->time_within = $time->unix_get_time_day(365);
            } else {

                $error = true;
            }

            if (!$error) {
                $qry .= ' AND p.post_created > ' . $this->time_within;
            }
        }

        $topic = new \CODOF\Forum\Topic(false);
        $qry .= ' AND ' . $topic->getViewTopicPermissionConditions();
        $qry .= ' ORDER BY #SORT# #ORDER# LIMIT  ' . $this->num_results . ' OFFSET ' . $this->from;

        return $qry;
    }

    private function strip_double_quotes($word) {

        return substr($word, 1, -1);
    }

    private function build_query() {

        $this->str = substr($this->str, 0, $this->max_str_len);

        $this->build_keys();
        $conditions = $this->get_conditions();

        $base_qry = $this->base_query();
        $base_qry = str_replace("#CONDITIONS#", $conditions, $base_qry);

        $sort = $this->get_sort_by();
        $base_qry = str_replace("#SORT#", $sort, $base_qry);

        $order = $this->get_order_by();
        $base_qry = str_replace("#ORDER#", $order, $base_qry);

        return $base_qry;
    }

    private function get_sort_by() {

        return $this->sort_keys[$this->sort] . $this->sort;
    }

    private function get_order_by() {

        return $this->order;
    }

    private function get_conditions() {

        $str = '';
        $title_str = '';

        if ($this->match_titles == 'Yes') {

            $title_str = ' OR t.title LIKE ?';
        }

        foreach ($this->keys as $key) {

            if (is_array($key)) {

                $str .= ' AND (';
                $arr = array();
                foreach ($key as $a_key) {

                    $arr[] = "(p.imessage LIKE ?$title_str)";
                    if ($title_str != '') {
                        $this->values[] = '%' . $a_key . '%';
                    }
                    $this->values[] = '%' . $a_key . '%';
                }

                $str .= implode(' OR ', $arr) . ' ) ';
            } else if ($key[0] == "-") {

                $word = substr($key, 1);

                //exclusions are not applied to title for better searching
                $str .= " AND (p.imessage NOT LIKE ?) ";
                $this->values[] = '%' . $word . '%';
            } else {
                $str .= " AND (p.imessage LIKE ?$title_str) ";

                if ($title_str != '') {
                    $this->values[] = '%' . $key . '%';
                }
                $this->values[] = '%' . $key . '%';
            }
        }

        return $str;
    }

    private function build_keys() {

        $keywords = array();
        preg_match_all('/(-?)([^" ]+|"[^"]+")/i', $this->str, $keywords, PREG_SET_ORDER);

        $prev = null; //stores keyword of previous iteration
        $add_next_as_or = false;

        foreach ($keywords as $keyword) {

            $word = $keyword[2];

            //remove quotes if exists
            if ($keyword[2][0] == '"') {

                $word = $this->strip_double_quotes($keyword[2]);
            }
            //make sure the keys contains atleast one key
            if (($keyword[0] == 'OR' || $keyword[0] == 'or') && count($this->keys)) {

                $prev = array_pop($this->keys);
                //if this was the first or (not a consecutive one)
                if (!is_array($prev)) {

                    $prev = array($prev);
                }
                $this->keys[] = $prev;
                $add_next_as_or = true;
            } else if ($keyword[0] == 'AND' || $keyword[0] == 'and') {

                //ignore it
            } else if ($keyword[1] == '-') {

                //make the word as excluded word
                $word = '-' . $word;
                $this->keys[] = $word;
            } else {

                //normal
                if ($add_next_as_or) {
                    $last_index = count($this->keys) - 1;
                    $this->keys[$last_index] = array_merge($this->keys[$last_index], array($word));
                    $add_next_as_or = false;
                } else {

                    $this->keys[] = $word;
                }
            }
        }
    }

}

