<?php

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/**
 * This class contains all calls methods to be made to the Bizyhood API.
 */
  class Bizyhood_Meta
  {
    
    function buffer_start() { 
      ob_start(array(&$this, "buffer_callback")); 
    }
    function buffer_end() { 
      if (ob_get_contents()) {
        ob_end_flush(); 
      }
    }
    
    function buffer_callback($buffer) {
      
      global $wp_query;
      
      $overview_page = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID);
      $events_page = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_EVENTS_PAGE_ID);
      $promotions_page = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_PROMOTIONS_PAGE_ID);
      
      if (!is_page($overview_page) && !is_page($events_page) && !is_page($promotions_page) ) {
        return $buffer;
      }
      
      $bizyhood_name = urldecode($wp_query->query_vars['bizyhood_name']);
      $bizyhood_id = urldecode($wp_query->query_vars['bizyhood_id']);
              
      if ($bizyhood_name == Bizyhood_Core::RSS_SUFFIX || $bizyhood_id == Bizyhood_Core::RSS_SUFFIX) {
        return $buffer;
      }
      
      
      $metadata = array();
      
      // overview page metadata
      if (is_page($overview_page)) {
        
        $metadata = self::get_single_page_meta($overview_page);
       
      }
      
      // overview page metadata
      if (is_page($events_page) && isset($wp_query->query_vars['bizyhood_id']) && $wp_query->query_vars['bizyhood_id'] != '') {
        
        $metadata = self::get_event_page_meta($events_page, $wp_query);

      }
      
      // promotions page metadata
      if (is_page($promotions_page) && isset($wp_query->query_vars['bizyhood_id']) && $wp_query->query_vars['bizyhood_id'] != '') {
        
        $metadata = self::get_promotions_page_meta($promotions_page, $wp_query);
        
      }
      
      
      if (empty($metadata)) {
        return $buffer;
      }

      
      // remove meta
      $buffer = preg_replace( '/<meta property="og.*?\/>\n/i', '', $buffer );
      $buffer = preg_replace( '/<meta name="twitter.*?\/>\n/i', '', $buffer );
      $buffer = preg_replace( '/<link rel="canonical.*?\/>\n/i', '', $buffer );
      
      
      
      $meta = '
        <link rel="canonical" href="'. $metadata['canonical'] .'" />
        <meta property="og:locale" content="'. get_locale() .'" />
        <meta property="og:type" content="article" />
        <meta property="og:title" content="'. $metadata['title'] .'" />
        <meta property="og:url" content="'. $metadata['canonical'] .'" />
        <meta property="og:site_name" content="'. get_bloginfo('name') .'" />
        
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" content="'. $metadata['title'] .'" />
      ';

      $meta .= '
        <meta property="og:description" content="'. $metadata['description']  .'" />
        <meta name="twitter:description" content="'. $metadata['description'] .'" />
        <meta name="description" content="'. $metadata['description'] .'" />
        ';

      
      if(isset($metadata['logo'])) {
        
        $meta .= '
          <meta property="og:image" content="'. $metadata['logo'] .'" />
          <meta property="og:image:width" content="'. $metadata['logo_width'] .'" />
          <meta property="og:image:height" content="'. $metadata['logo_height'] .'" />
        ';
        if ($metadata['logo_description'] != '') {
          $meta .= '<meta property="og:image:alt" content="'. $metadata['logo_description'] .'" />';
        } else {
          $meta .= '<meta property="og:image:alt" content="'. $metadata['title'] .'" />';
        }
      }
      
      
      $buffer = preg_replace( '/<title.*?\/title>/si', '<title>'. $metadata['title'] .'</title>'."\n".$meta, $buffer );

  
    
      return $buffer;
    }
    
    
    public static function get_single_page_meta($overview_page) {
      
      $single_business_information = Bizyhood_Api::get_business_details();
        
      $business = '';
      
      if($single_business_information === false || !isset($single_business_information->name)) {
        return $buffer;
      } else {
       $business = $single_business_information; 
      }
      
      $metadata['title'] = htmlentities($business->name .', '. $business->locality .', '. $business->region .' '. $business->postal_code .' - '.get_bloginfo('name'));
      $metadata['canonical'] = get_permalink($overview_page)  . $business->slug .'/'.$business->bizyhood_id .'/';

      if ($business->claimed == 1 && $business->description != '') {
        $metadata['description'] = wp_trim_words(htmlentities($business->description), Bizyhood_Core::META_DESCRIPTION_LENGTH, '');
      } else {
        $metadata['description'] = htmlentities($business->name.' is a hyper-local, small business, located in and/or serving the '. $business->locality .', '. $business->region .' area.');
      }
      
      
      if($business->business_logo) {
        $metadata['logo'] = $business->business_logo->url;
        $metadata['logo_width'] = $business->business_logo->width;
        $metadata['logo_height'] = $business->business_logo->height;
        $metadata['logo_description'] = $business->business_logo->description;
      }
      
      return $metadata;
      
    }
    
    
    public static function get_event_page_meta($events_page, $wp_query) {
      
      $bizyhood_name = '';
        
      $bizyhood_name = urldecode($wp_query->query_vars['bizyhood_name']);
      $bizyhood_id = urldecode($wp_query->query_vars['bizyhood_id']);
      
      $single_event_information = Bizyhood_Api::get_business_related_content('events', $bizyhood_name, $bizyhood_id);
      
      $business = '';
      
      if($single_event_information === false || !isset($single_event_information->name)) {
        return false;
      }
      
      $metadata['title'] = htmlentities($single_event_information->name .', '. $single_event_information->business_name .' - '.get_bloginfo('name'));
      $metadata['canonical'] = get_permalink($events_page) . $wp_query->query_vars['bizyhood_name'] .'/'.$wp_query->query_vars['bizyhood_id'].'/';
      $metadata['description'] = wp_trim_words(htmlentities($single_event_information->description), Bizyhood_Core::META_DESCRIPTION_LENGTH, '');
      
      if($single_event_information->image) {
        $metadata['logo'] = $single_event_information->image->url;
        $metadata['logo_width'] = $single_event_information->image->width;
        $metadata['logo_height'] = $single_event_information->image->height;
        $metadata['logo_description'] = $single_event_information->image->title;
      }
      
      return $metadata;
      
    }
    
    
    public static function get_promotions_page_meta($promotions_page, $wp_query) {
      
      $bizyhood_name = '';
        
      $bizyhood_name = urldecode($wp_query->query_vars['bizyhood_name']);
      $bizyhood_id = urldecode($wp_query->query_vars['bizyhood_id']);

      
      $single_promotion_information = Bizyhood_Api::get_business_related_content('promotions', $bizyhood_name, $bizyhood_id);
      
      $business = '';
      
      if($single_promotion_information === false || !isset($single_promotion_information->name)) {
        return false;
      }
      
      $metadata['title'] = htmlentities($single_promotion_information->name .', '. $single_promotion_information->business_name .' - '.get_bloginfo('name'));
      $metadata['canonical'] = get_permalink($promotions_page) . $wp_query->query_vars['bizyhood_name'] .'/'.$wp_query->query_vars['bizyhood_id'].'/';
      $metadata['description'] = wp_trim_words(htmlentities($single_promotion_information->details), Bizyhood_Core::META_DESCRIPTION_LENGTH, '');
      
      if($single_promotion_information->image) {
        $metadata['logo'] = $single_promotion_information->image->url;
        $metadata['logo_width'] = $single_promotion_information->image->width;
        $metadata['logo_height'] = $single_promotion_information->image->height;
        $metadata['logo_description'] = $single_promotion_information->image->title;
      }
    
      return $metadata;
    }
  }