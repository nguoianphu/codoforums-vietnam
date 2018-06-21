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
			$title=$info[0]['title'];
			
			echo $info[1];

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



