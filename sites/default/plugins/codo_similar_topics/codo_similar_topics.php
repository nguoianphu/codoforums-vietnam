<?php

/*
 * @CODOLICENSE
 */

/**
 * 
 * There is no restriction whether to use OOP or procedural 
 * 
 * preferred pattern
 * assets/ your static resources
 *         js/  your javascript
 *         css/ your css files
 *         img/ your images
 *         tpl/ your .tpl OO
 * 
 * you are free to follow your own style.
 */
/**
 * All files should include below line
 * 
 */
defined('IN_CODOF') or die();

class plg_similar_topics{

	function get_similar_topics($info){


			
			require 'common_words.php';
			$common_words = plg_get_common_words();

		
			$search = new \CODOF\Search\Search();
			// $title=$info[0]['title'];
			
			// nguoianphu
			// $pid='t.post_id';
			// $info = $this->\CODOF\Forum\get_post_info(post_id);
			// $title=html_entity_decode($info[0]['title']);
			$title=$info[0]['full_title'];
			// $title='me-kong-ky-su-dvd-16-phan-qua-huyen-an-phu';
			// $title = str_replace("-", " ",$title);
			// $title=html_entity_decode($title);
			// nguoianphu

			//replace double spaces with single space
			$title = str_replace("  ", " ",$title);

			//get all the words in an array
			$words=explode(" ",$title);

			//remove the common words
			$tarray= array_diff($words, $common_words);

			//join the new array
			$title=implode(" OR ", $tarray);			

			$search->str=$title;
			$search->tid = '!= ' . $info[0]['topic_id'];
			$search->pid = '= t.post_id';
			// nguoianphu
			$search->sort = 'no_views';
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

            $links=$this->generate_links($search->search());

			$asset = new \CODOF\Asset\Stream();
			$col = new \CODOF\Asset\Collection('head_col');
			$col->addCSS(PLUGIN_DIR . "similar_topics/view.less");
			$asset->addCollection($col);
			
			//args: plugin_name,view_file,data
            Block::renderView('codo_similar_topics','view.tpl',['topics'=>$links]);


	}


	function generate_links($topics){

		$links=[];
			foreach($topics as $topic){

				$links[]=['link'=>\CODOF\Forum\Forum::getPostURL($topic['topic_id'],$topic['title']),
						  'title'=>$topic['title']];

			}

			return $links;


	}


}

Hook::add('on_topic_view',[ new plg_similar_topics,'get_similar_topics']);



