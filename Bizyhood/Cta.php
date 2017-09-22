<?php

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/**
 * This class contains all CTA question methods to be made to the Bizyhood API.
 */
  class Bizyhood_CTA
  {

  
    // do not rename the function
    public static function question_cta() {
      
      $data = array();
      
      $bizyhood_id                    = $_POST['bizyhood_id'];
      $params['text']                 = $_POST['text'];
      $params['author']['email']      = $_POST['email'];
      $params['author']['first_name'] = $_POST['first_name'];
      $params['author']['last_name']  = $_POST['last_name'];
      
      $error = array();
      
      if (strlen($bizyhood_id) < 30) {
        $error[] = __('There was an error submitting your request (no business id).', 'bizyhood');
        
        $message = implode("<br />", $error);
        wp_send_json(array('error' => true, 'message' => $message));
        die();
      }
      
      if (!filter_var($params['author']['email'], FILTER_VALIDATE_EMAIL)) {
        $error[] = __('Your email seems to be invalid.', 'bizyhood');
      }
      
      if (strlen($params['author']['first_name']) < 1) {
        $error[] = __('The provided first name seems to be invalid.', 'bizyhood');
      }
      
      
      if (strlen($params['author']['last_name']) < 1) {
        $error[] = __('The provided last name seems to be invalid.', 'bizyhood');
      }
      
      // no reason to make the request if the data is invalid
      if (count($error) > 0) {
         
        $message = implode("<br />", $error);
        wp_send_json(array('error' => true, 'message' => $message));
        die();
        
      }
      
      $headers['Content-type'] = "application/json";
      
      $request = Bizyhood_Api::submit_cta_form($bizyhood_id, json_encode($params), $headers);
      
      wp_send_json($request);
      
    }
    
    
  }