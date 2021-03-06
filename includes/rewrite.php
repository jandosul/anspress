<?php
/**
 * Plugin rewrite rules and query variables
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

/** 
 * This class handle all rewrite rules and define quesry varibale of anspress
 * @since 2.0.0-beta
 */
class AnsPress_Rewrite
{
	/**
	 * Initialize the class
	 */
	public function __construct()
	{
		add_filter('query_vars', array($this, 'query_var'));
		add_action('generate_rewrite_rules', array( $this, 'rewrites'), 1);
		add_filter( 'paginate_links', array($this, 'bp_com_paged') );
		add_filter('parse_request', array( $this, 'add_query_var' ));
	}

	/**
	 * Register query vars
	 * @param  array $query_vars 
	 * @return string[]             
	 */
	public function query_var( $query_vars) {

		$query_vars[] = 'edit_post_id';
		$query_vars[] = 'ap_nonce';
		$query_vars[] = 'question_id';
		$query_vars[] = 'question';
		$query_vars[] = 'question_name';
		$query_vars[] = 'answer_id';
		$query_vars[] = 'answer';
		$query_vars[] = 'ask';
		$query_vars[] = 'ap_page';
		$query_vars[] = 'qcat_id';
		$query_vars[] = 'qcat';
		$query_vars[] = 'qtag_id';
		$query_vars[] = 'q_tag';
		$query_vars[] = 'q_cat';
		$query_vars[] = 'ap_s';
		$query_vars[] = 'message_id';
		$query_vars[] = 'parent';
		$query_vars[] = 'ap_user';
		$query_vars[] = 'user_page';
		
		return $query_vars;
	}

	/**
	 * Rewrite rules
	 * @return array
	 */
	public function rewrites() 
	{  
		global $wp_rewrite;  
		global $ap_rules;
		
		unset($wp_rewrite->extra_permastructs['question']); 
        unset($wp_rewrite->extra_permastructs['answer']); 
		
		$base_page_id 		= ap_opt('base_page');
		
		$slug = ap_base_page_slug().'/';

		

		$new_rules = array(  
			
			$slug. "parent/([^/]+)/?" => "index.php?page_id=".$base_page_id."&parent=".$wp_rewrite->preg_index(1),		
 
			$slug. "category/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&ap_page=category&q_cat=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2),   
			
			$slug. "tag/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&ap_page=tag&q_tag=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2), 
			
			$slug. "category/([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=category&q_cat=".$wp_rewrite->preg_index(1),
			
			$slug. "tag/([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=tag&q_tag=".$wp_rewrite->preg_index(1),

			$slug. "page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&paged=".$wp_rewrite->preg_index(1), 
			
			$slug. "([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&ap_page=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2),

		);

		if(!ap_opt('question_permalink_follow')){
			
			$new_rules["question/([^/]+)/page/?([0-9]{1,})/?$"] =  "index.php?page_id=".$base_page_id."&question_name=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2);
			
			$new_rules["question/([^/]+)/?$"]  = "index.php?page_id=".$base_page_id."&question_name=".$wp_rewrite->preg_index(1);

		}else{

			$new_rules[$slug."question/([^/]+)/page/?([0-9]{1,})/?$"] = "index.php?page_id=".$base_page_id."&question_name=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2);
			
			$new_rules[$slug."question/([^/]+)/?$"] = "index.php?page_id=".$base_page_id."&question_name=".$wp_rewrite->preg_index(1);
		}

		$new_rules[$slug. "user/([^/]+)/([^/]+)/page/?([0-9]{1,})/?$"] = "index.php?page_id=".$base_page_id."&ap_page=user&ap_user=". $wp_rewrite->preg_index(1)."&user_page=". $wp_rewrite->preg_index(2)."&paged=".$wp_rewrite->preg_index(3);

		$new_rules[$slug. "user/([^/]+)/([^/]+)/?"] = "index.php?page_id=".$base_page_id."&ap_page=user&ap_user=". $wp_rewrite->preg_index(1)."&user_page=". $wp_rewrite->preg_index(2);

		$new_rules[$slug. "user/([^/]+)/?"] = "index.php?page_id=".$base_page_id."&ap_page=user&ap_user=".$wp_rewrite->preg_index(1);

		$new_rules[$slug. "search/([^/]+)/?"] = "index.php?page_id=".$base_page_id."&ap_page=search&ap_s=". $wp_rewrite->preg_index(1);

		$new_rules[$slug. "ask/([^/]+)/?"] = "index.php?page_id=".$base_page_id."&ap_page=ask&parent=".$wp_rewrite->preg_index(1);

		$new_rules[$slug. "([^/]+)/?"] = "index.php?page_id=".$base_page_id."&ap_page=".$wp_rewrite->preg_index(1);
		
		

		$ap_rules = $new_rules;

		return $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;  
	}

	public function bp_com_paged($args)
	{
		if(function_exists('bp_current_component')){
			$bp_com = bp_current_component();
			
			if('questions' == $bp_com || 'answers' == $bp_com)
				return preg_replace('/page.([0-9]+)./', '?paged=$1', $args);
		}

		return $args;
	}

	public function add_query_var($wp) {
	    if(!empty($wp->query_vars['question_name'])){
	        $question =  get_page_by_path( $wp->query_vars['question_name'], 'OBJECT', 'question' );

	        if($question)
	        	$wp->set_query_var('question_id', $question->ID);
	    }

	    if(!empty($wp->query_vars['ap_user'])){
	       	$user = get_user_by( 'login', sanitize_text_field(urldecode ($wp->query_vars['ap_user']) ) );
	       	
	       	if($user)
        		$wp->set_query_var('ap_user_id', $user->ID);
	    }
	}
}