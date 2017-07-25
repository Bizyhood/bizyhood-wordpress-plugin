<?php

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/**
 * This class contains all Bizyhood Shortcodes.
 */
 
class Bizyhood_Shortcodes
{

    public function businesses_shortcode($attrs)
    {
      
        $attributes = shortcode_atts( array(
          'search_widget' => 'on', // set to off to disable the search widget before the directory content
        ), $attrs );
      
        
        $authetication = Bizyhood_oAuth::set_oauth_temp_data();
        if (is_wp_error($authetication) || $authetication === false) {
          return Bizyhood_View::load( 'listings/error', array( 'error' => $authetication->get_error_message()), true );
        }
        
        
        $q = Bizyhood_Api::businesses_search($attrs);
        
        if (isset($q['error'])) {
          $error = $q['error'];
          return Bizyhood_View::load( 'listings/error', array( 'error' => $error->get_error_message()), true );
        }
        
        $list_page_id = $q['list_page_id'];
        $page = $q['page'];
       
        $businesses     = $q['businesses'];
        $keywords       = $q['keywords'];
        $categories     = $q['categories'];
        $show_category_facets = $q['show_category_facets'];
        $cf             = $q['category'];
        $total_count    = $q['total_count'];
        $page_size      = $q['page_size'];
        $page_count     = 0;
        
        if ($page_size > 0) {
            $page_count = ( $total_count / $page_size ) + ( ( $total_count % $page_size == 0 ) ? 0 : 1 );
        }
        $pagination_args = array(
            'total'              => $page_count,
            'current'            => $page,
            'type'               => 'list',
        );
        $view_business_page_id = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID);
        
        return Bizyhood_View::load( 'listings/index', array( 'keywords' => (isset($keywords) ? $keywords : ''), 'categories' => (isset($categories) ? $categories : ''), 'show_category_facets' => $show_category_facets,'cf' => (isset($cf) ? $cf : ''), 'list_page_id' => $list_page_id, 'pagination_args' => $pagination_args, 'businesses' => $businesses, 'view_business_page_id' => $view_business_page_id, 'search_widget' => $attributes['search_widget'] ), true );
    }
    
    
    
    public function promotions_shortcode($attrs) {
      
      global $wp_query;
      
      // init variable
      $business_name = '';
      
      $authetication = Bizyhood_oAuth::set_oauth_temp_data();
      if (is_wp_error($authetication) || $authetication === false) {
        return Bizyhood_View::load( 'listings/error', array( 'error' => 'Can not authenticate to the Bizyhood API'), true );
      }
      
      // cache the results
      $cached_promotions = Bizyhood_Core::get_cache_value('bizyhood_promotions_widget', 'response_json', 'get_all_content_by_type', $attrs, 'promotions');
            
      if ($cached_promotions === false) {
        $signup_page_id = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_SIGNUP_PAGE_ID);
        $errormessage = 'Are you a business owner? Would you like to see your promotion(s) on this page? Click <a href="'. get_permalink($signup_page_id) .'" title="sign up or login to Bizyhood">here</a> to sign up or login!';
        
        return Bizyhood_View::load( 'listings/noresults', array( 'error' => $errormessage), true );
      }
      
      $list_page_id = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_MAIN_PAGE_ID);

      if (isset($wp_query->query_vars['bizyhood_name']) && isset($wp_query->query_vars['bizyhood_id'])) {
        
        $promotions = Bizyhood_Api::get_business_related_content('promotions', $wp_query->query_vars['bizyhood_name'], $wp_query->query_vars['bizyhood_id']);
        
        if ($promotions !== false && !empty($promotions) && !empty($promotions->identifier)) {
          
          $cached_promotions = json_decode(json_encode($promotions), true); // convert to array and replace results
          $business_name = $cached_promotions['business_name'];
          
          $promotions_args = array( 
            'promotion' => $cached_promotions, 
            'list_page_id' => $list_page_id, 
            'business_name' => $business_name
          );
          
          // no need to continue // we can return a template page with the result
          return Bizyhood_View::load( 'listings/single/promotion', $promotions_args, true );
          
        }
      }
      
      if (isset($wp_query->query_vars['bizyhood_name']) && !isset($wp_query->query_vars['bizyhood_id'])) {
        $promotions = Bizyhood_Api::get_business_related_content('promotions', $wp_query->query_vars['bizyhood_name']);
        
        if ($promotions !== false && !empty($promotions)) {
          $cached_promotions = json_decode(json_encode($promotions), true); // convert to array and replace results
          $business_name = $cached_promotions[0]['business_name'];
        }
      }
      
      $promotions_args = array( 
        'promotions' => $cached_promotions, 
        'list_page_id' => $list_page_id, 
        'business_name' => $business_name
      );

      return Bizyhood_View::load( 'listings/promotions', $promotions_args, true );
      
    }
    
    
    public function events_shortcode($attrs) {
      
      global $wp_query;
      
      // init variable
      $business_name = '';
      
      $authetication = Bizyhood_oAuth::set_oauth_temp_data();
      if (is_wp_error($authetication) || $authetication === false) {
        return Bizyhood_View::load( 'listings/error', array( 'error' => 'Can not authenticate to the Bizyhood API'), true );
      }
      
      // cache the results
      $cached_events = Bizyhood_Core::get_cache_value('bizyhood_events_widget', 'response_json', 'get_all_content_by_type', $attrs, 'events');
            
      if ($cached_events === false) {
        $signup_page_id = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_SIGNUP_PAGE_ID);
        $errormessage = 'Are you a business owner? Would you like to see your event(s) on this page? Click <a href="'. get_permalink($signup_page_id) .'" title="sign up or login to Bizyhood">here</a> to sign up or login!';
        
        return Bizyhood_View::load( 'listings/noresults', array( 'error' => $errormessage), true );
      }
      
      $list_page_id = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_MAIN_PAGE_ID);
      
      if (isset($wp_query->query_vars['bizyhood_name']) && isset($wp_query->query_vars['bizyhood_id'])) {
        $events = Bizyhood_Api::get_business_related_content('events', $wp_query->query_vars['bizyhood_name'], $wp_query->query_vars['bizyhood_id']);
        
        if ($events !== false && !empty($events) && !empty($events->identifier)) {
          $cached_events = array();
          $cached_events = json_decode(json_encode($events), true); // convert to array and replace results
          $business_name = $cached_events['business_name'];
          $event_identifier = $cached_events['identifier'];
          
          $events_args = array( 
            'event' => $cached_events, 
            'list_page_id' => $list_page_id, 
            'business_name' => $business_name,
            'event_identifier' => $event_identifier
          );
          
          // no need to continue // we can return a template page with the result
          return Bizyhood_View::load( 'listings/single/event', $events_args, true );
          
        }
      }
      
      if (isset($wp_query->query_vars['bizyhood_name']) && !isset($wp_query->query_vars['bizyhood_id'])) {
        $events = Bizyhood_Api::get_business_related_content('events', $wp_query->query_vars['bizyhood_name']);
        if ($events !== false && !empty($events)) {
          $cached_events = json_decode(json_encode($events), true); // convert to array and replace results
          $business_name = $cached_events[0]['business_name'];
        }
      }
      
      $events_args = array( 
        'events' => $cached_events, 
        'list_page_id' => $list_page_id, 
        'business_name' => $business_name
      );
      
      return Bizyhood_View::load( 'listings/events', $events_args, true );

    }
    
    
    public function guide_shortcode($attrs) {
      
      global $wp_query;
      
      $filtered_attributes = shortcode_atts( array(
        'paged'     => 1,
        'verified'  => true,
        'paid'      => true,
        'ps'        => Bizyhood_Core::API_MAX_LIMIT
      ), $attrs );
      
      // init variable
      $business_name = '';
      
      $authetication = Bizyhood_oAuth::set_oauth_temp_data();
      if (is_wp_error($authetication) || $authetication === false) {
        return Bizyhood_View::load( 'listings/error', array( 'error' => 'Can not authenticate to the Bizyhood API'), true );
      }
      
      $q = Bizyhood_Api::businesses_search($filtered_attributes);
        
      if (isset($q['error'])) {
        $error = $q['error'];
        return Bizyhood_View::load( 'listings/error', array( 'error' => $error->get_error_message()), true );
      }
      
      $page = $q['page'];
     
      $businesses     = $q['businesses'];
      $total_count    = $q['total_count'];
      $page_size      = $q['page_size'];
      $page_count     = 0;
      
      if ($page_size > 0) {
          $page_count = ( $total_count / $page_size ) + ( ( $total_count % $page_size == 0 ) ? 0 : 1 );
      }
      $pagination_args = array(
          'total'              => $page_count,
          'current'            => $page,
          'type'               => 'list',
      );
      $view_business_page_id = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID);
      
      return Bizyhood_View::load( 'listings/guide', array( 'pagination_args' => $pagination_args, 'businesses' => $businesses, 'view_business_page_id' => $view_business_page_id ), true );
      
    }
    
    
    
    public function search_shortcode($attrs) {
      
      $attributes = shortcode_atts( array(
        'title' => '',
        'color_widget_back' => '',
        'color_cta_back' => '',
        'color_cta_font' => '',
        'color_button_back' => '',
        'color_button_font' => '',
        'color_label_font' => '',
        'color_input_back' => '',
        'color_input_border' => '',
        'color_input_font' => '',
        'layout' => 'full',
        'row1' => 'List your business',
        'row2' => 'Add now, it\'s free',
        'widget_id' => uniqid ()
      ), $attrs );
      
      
      $button_style = $input_style = array();
      if ($attributes['color_button_back'] != '') {
        $button_style[] = 'background-color: '. $attributes['color_button_back'] .';';
      }
      if ($attributes['color_button_font'] != '') {
        $button_style[] = 'color: '. $attributes['color_button_font'] .';';
      }
      
      if ($attributes['color_input_back'] != '') {
        $input_style[] = 'background-color: '. $attributes['color_input_back'] .';';
      }
      if ($attributes['color_input_font'] != '') {
        $input_style[] = 'color: '. $attributes['color_input_font'] .';';
      }
      if ($attributes['color_input_border'] != '') {
        $input_style[] = 'border-color: '. $attributes['color_input_border'] .';';
      }
      
      $attributes['button_style'] = $button_style;
      $attributes['input_style']  = $input_style;

      return Bizyhood_View::load( 'widgets/search-shortcode', $attributes, true );
    }
    
    
    public function businesses_group($attrs)
    {
      $attributes = shortcode_atts( array(
        'group' => false, // group id 
      ), $attrs );
    
      
      $error = false;
      
      $authetication = Bizyhood_oAuth::set_oauth_temp_data();
      if (is_wp_error($authetication)) {
        $error = $authetication->get_error_message();
      }
      
      if ($authetication === false ) {
        $error = 'oAuth Failed';
      }
      if ($attrs['group'] === false) {
        $error = 'The Group ID is required';
      }
      
      if ($error !== false) {
        return Bizyhood_View::load( 'listings/error', array( 'error' => $error), true );
      }
      
      $response = Bizyhood_Api::businesses_group_request($attrs['group']);
      
      // avoid throwing an error
      if ($response === false || $response['result'] === null || empty($response['result'])) { return; }
      
      $businesses = $response['result']['results'];
      $total_count = $response['result']['count'];

      $view_business_page_id = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID);
            
      return Bizyhood_View::load( 'listings/group', array( 'businesses' => $businesses, 'view_business_page_id' => $view_business_page_id ), true );
      
    }
    
    
} 
 
?>