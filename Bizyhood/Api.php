<?php

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/**
 * This class contains all calls methods to be made to the Bizyhood API.
 */
  class Bizyhood_Api
  {
    
    public static function get_business_details($bizyhood_id = '')
    {
      
      global $wp_query;
      
      $api_url = Bizyhood_Utility::getApiUrl();
      $params = array();
      
      if ($bizyhood_id == '') {
        if(isset($wp_query->query_vars['bizyhood_id'])) {
          $bizyhood_id = urldecode($wp_query->query_vars['bizyhood_id']);
        } else {
          $bizyhood_id = (isset($_REQUEST['bizyhood_id']) ? $_REQUEST['bizyhood_id'] : '');
        }
      }
      
      if (!$bizyhood_id) {
        return NULL;
      }

      $client = Bizyhood_oAuth::oAuthClient();
      
      if (is_wp_error($client)) {
        return false;
      }

      try {
        $response = $client->fetch($api_url . "/v2/business/" . $bizyhood_id.'/', $params);
      } catch (Exception $e) {
        Bizyhood_Log::add('warn', "Business details fetch failed: $e");
        return false;
      }  
      $business = json_decode(json_encode($response['result']), FALSE);
    
      return $business;
    }
    
    
    public static function businesses_search($atts)
    {
      
      // no reason to continue if we do not have oAuth token
      if (get_transient('bizyhood_oauth_data') === false) {
        return;
      }
      
      $filtered_attributes = shortcode_atts( array(
        'paged'     => null,
        'verified'  => null,
        'paid'      => null,
        'ps'        => null
      ), $atts );

      
      $remote_settings = Bizyhood_Utility::getRemoteSettings();
      $api_url = Bizyhood_Utility::getApiUrl();
      $list_page_id = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_MAIN_PAGE_ID);
      $params = array();

      $show_category_facets = FALSE;
      
      // get current page
      if (isset($filtered_attributes['paged']))
          $page = $filtered_attributes['paged'];
      elseif (get_query_var('paged'))
          $page = get_query_var('paged');
      elseif (isset($_GET['paged']))
          $page = $_GET['paged'];
      else
          $page = 1;

      // get current ps
      if (isset($filtered_attributes['ps'])) {
        $ps = $filtered_attributes['ps'];
      } elseif (get_query_var('ps')) {
        $ps = get_query_var('ps');
      } elseif (isset($_GET['ps'])) {
        $ps = $_GET['ps'];
      } else {
        $ps = 12;
      }

      if (isset($_GET['c'])) {
          $categories_query = $_GET['c'];
          $show_category_facets = TRUE;
      }
      
      // get category filter
      $category = false;
      if (get_query_var('cf')) {
        $category = urldecode( stripslashes(get_query_var('cf')) );
        $show_category_facets = TRUE;
      } elseif (isset($_GET['cf'])) {
        $category = urldecode( stripslashes($_GET['cf']) );
        $show_category_facets = TRUE;
      }
      
      $keywords = false;
      if(!empty($_GET['keywords'])) {
        $keywords = stripslashes(strip_tags($_GET['keywords']));
        $show_category_facets = TRUE;
      }
      
      // get verified

      $verified = $filtered_attributes['verified'];
      if (get_query_var('verified')) {
          $verified = get_query_var('verified');
      } elseif (isset($_GET['verified'])) {
          $verified = $_GET['verified'];
      }
      
      // check if $verified has a valid value
      if ($verified === true || $verified === TRUE || $verified == 1 || $verified == 'y' || $verified == 'Y') {
        $verified = TRUE;
      } else {
        $verified = false;
      }
      
      
      // get paid
      $paid = $filtered_attributes['paid'];
      if (get_query_var('paid')) {
          $paid = get_query_var('paid');
      } elseif (isset($_GET['paid'])) {
          $paid = $_GET['paid'];
      }
      
      // check if $paid has a valid value
      if ($paid === true || $paid === TRUE || $paid == 1 || $paid == 'y' || $paid == 'Y') {
        $paid = TRUE;
      } else {
        $paid = false;
      }
      

      $client = Bizyhood_oAuth::oAuthClient();
           
      if (is_wp_error($client)) {
        $error = new WP_Error( 'bizyhood_error', $client->get_error_message() );
        return array('error' => $error);
      }      
      
      $params = array(
        'format' =>'json',
        'ps'  => $ps,
        'pn'  => $page
      );
      
      if ($keywords !== false) {
        $params['k'] = $keywords;
      }
      
      if ($category != false) {
        $params['cf'] = $category;
      }
      
      if ($verified === TRUE) {
        $params['verified'] = $verified;
      }
      
      if ($paid === TRUE) {
        $params['paid'] = $paid;
      }
      
      if (!empty($categories_query)) {
         $params['cat'] = $categories_query; 
      }
      
      try {
        $response = $client->fetch($api_url.'/v2/search/', $params);
      } catch (Exception $e) {
        $error = new WP_Error( 'bizyhood_error', __( 'Service is currently unavailable! Request timed out.', 'bizyhood' ) );
        return array('error' => $error);
      }  
      
      // avoid throwing an error
      if (!is_array($response) || (is_array($response) && isset($response['code']) && $response['code'] != 200)) { return; }
      
      $response_json = $response['result'];
      
      // avoid throwing an error
      if ($response_json === null || empty($response_json)) { return; }
      
      $businesses = json_decode(json_encode($response_json['businesses']), FALSE);
      $total_count = $response_json['total_count'];
      $page_size = $response_json['page_size'];
      $facets = $response_json['search_facets'];
      $categories = $response_json['search_facets']['categories_facet'];
            
      $return = array(
        'remote_settings'   => $remote_settings,
        'api_url'           => $api_url,
        'list_page_id'      => $list_page_id,
        'keywords'          => (isset($keywords) && $keywords != '' ? urldecode($keywords) : ''),
        'categories'        => (isset($categories) ? $categories : ''),
        'category'          => (isset($category) ? $category : ''),
        'page'              => $page,
        'businesses'        => $businesses,
        'total_count'       => $total_count,
        'page_size'         => $page_size,
        'response'          => json_encode($response['result']),
        'response_json'     => $response_json,
        'facets'            => $facets,
        'show_category_facets' => $show_category_facets     
      );
      
      return $return;
    }
    
     public static function get_business_related_content($info_request = '', $business_identifier = '', $bizyhood_id = '')
    {
      
      global $wp_query;
      
      if ($info_request == '') {
        return false;
      }
      
      $api_url = Bizyhood_Utility::getApiUrl();
      $params = array();
      
      if ($business_identifier == '') {
        if(isset($wp_query->query_vars['bizyhood_name'])) {
          $business_identifier = urldecode($wp_query->query_vars['bizyhood_name']);
        } else {
          $business_identifier = (isset($_REQUEST['bizyhood_name']) ? $_REQUEST['bizyhood_name'] : '');
        }
      }
      
      // false if there is no business name
      if ($business_identifier == '') {
        return false;
      } else {
        $params['bid'] = $business_identifier;
      }

      $client = Bizyhood_oAuth::oAuthClient();
      
      if (is_wp_error($client)) {
        return false;
      }
      

      try {
        $response = $client->fetch($api_url . '/v2/'. $info_request .($bizyhood_id != '' ? '/' . $bizyhood_id : '').'/', $params);
      } catch (Exception $e) {
        Bizyhood_Log::add('warn', "API business related content fetch failed: $e");
        return false;
      }  
      $info = json_decode(json_encode($response['result']), FALSE);
    
      return $info;
    }
    
    
    
    /***** additional business info *****/    
    
    /**
     * API call for additional business info
     * @param array $atts Attributes to be passesd on the API
     * @param string $command data to get promotions (default) and events
     * @return mixed results (array) or error (array) or empty
     */
    public static function get_all_content_by_type($atts, $command = 'promotions')
    {
      
      
      $filtered_attributes = shortcode_atts( array(
        'bid'         => null,
        'identifier'  => null
      ), $atts );
      
      $remote_settings = Bizyhood_Utility::getRemoteSettings();
      $api_url = Bizyhood_Utility::getApiUrl();
      $list_page_id = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_MAIN_PAGE_ID);      
      $client = Bizyhood_oAuth::oAuthClient();
      
      if (is_wp_error($client)) {
        return false;
      }
      
      $params = array(
        'format'      => 'json',
        'bid'         => $filtered_attributes['bid'],
        'identifier'  => $filtered_attributes['identifier']
      );

      try {
        $response = $client->fetch($api_url.'/v2/'. $command .'/', $params);
      } catch (Exception $e) {
        $error = new WP_Error( 'bizyhood_error', __( 'Service is currently unavailable! Request timed out.', 'bizyhood' ) );
        return array('error' => $error);
      }
      
      // avoid throwing an error
      if (!is_array($response) || empty($response['result'])) { return; }
      
      $response_json = $response['result'];
            
      $return = array(
        'remote_settings'   => $remote_settings,
        'api_url'           => $api_url,
        'list_page_id'      => $list_page_id,
        'bid'               => (isset($filtered_attributes['bid']) ? $filtered_attributes['bid'] : ''),
        'identifier'        => (isset($filtered_attributes['identifier']) ? $filtered_attributes['identifier'] : ''),
        'response'          => json_encode($response_json),
        'response_json'     => $response_json
      );
      
      return $return;
      
    }
    
    
    public static function businesses_group_request($groupd_id)
    {
      
      $client = Bizyhood_oAuth::oAuthClient();
           
      if (is_wp_error($client)) {
        $error = new WP_Error( 'bizyhood_error', $client->get_error_message() );
        return array('error' => $error);
      }      
      
      
      $api_url = Bizyhood_Utility::getApiUrl();
      
      try {
        $response = $client->fetch($api_url.'/v2/business/group/'. $groupd_id .'/');
      } catch (Exception $e) {
        $error = new WP_Error( 'bizyhood_error', __( 'Service is currently unavailable! Request timed out.', 'bizyhood' ) );
        return array('error' => $error);
      }  
      
      // avoid throwing an error
      if (!is_array($response) || (is_array($response) && isset($response['code']) && $response['code'] != 200)) { return false; }
      
      return $response;
      
    }
    
    public static function submit_cta_form($bizyhood_id, $params, $headers)
    {
      
      global $wp_query;
      
      $api_url = Bizyhood_Utility::getApiUrl();
      $client = Bizyhood_oAuth::oAuthClient();
      
      if (is_wp_error($client)) {
        return false;
      }

      try {
        $response = $client->fetch($api_url . "/v2/business/" . $bizyhood_id.'/topic/', $params, $client::HTTP_METHOD_POST, $headers);        
      } catch (Exception $e) {
        Bizyhood_Log::add('warn', "CTA form submit failed: $e");
        return false;
      }  
      $result = json_decode(json_encode($response['result']), FALSE);
    
      return $response;
    }
    
    
    
  }
?>