<?php

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/**
 * This class contains all Bizybox methods.
 */
  class Bizyhood_Bizybox
  {
    
    function bizylink_business_results() {

      $_GET['keywords']  = $_REQUEST['keywords'];
    
      
      $queryapi = Bizyhood_Api::businesses_search(array('paged' => 1, 'verified' => false, 'ps' => Bizyhood_Core::BIZYBOX_MAX_LIMIT));
      $numofpages = floor($queryapi['total_count'] / $queryapi['page_size']);
      $urlbase = get_permalink( Bizyhood_Utility::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID) );
      $date = date("Y-m-d H:i");
      $count  = $queryapi['total_count']; // get the number of results // 492
      
      $out = '
        <div class="query-notice" id="query-notice-message">
          <em class="query-notice-default">Results for: <b>'. $_GET['keywords'].'</b> ('. count($queryapi['businesses']) .')</em>
        </div>';
      
      
      
      
      if (count($queryapi['businesses']) > 0) {
        $out .= '<ul class="bizyres">';
        $i = 0;
        foreach ($queryapi['businesses'] as $business) {
          
          $urlarr = array_slice(explode('/', $business->bizyhood_url), -3);
          
          $out .= '<li class="'. ($i%2 == false ? 'alternate' : '') .'"><a href="'. $urlbase.$urlarr[0].'/'.$urlarr[1].'/' .'" title="'. $business->name .'">'. $business->name .' - '. $business->address1 .', '. $business->locality .', '. $business->region.', '. $business->postal_code .'</li>';
          $i++;
        }
        $out .= '</ul>';
      } else {
        $out = '<span class="faded">No results found for <em>'.$_GET['keywords'] .'</em></span>';
      }
      
      
      
      
      die( $out );
    }
    
    function bizylink_insert_dialog() {
      
      $out ='
        <table class="wp-list-table widefat fixed striped table options_table">
          <tr>
            <td class="first_column">
              <label id="bizylink_type-l" class="mce-widget mce-label mce-first" for="bizylink_type" aria-disabled="false">Link Type</label>
            </td>
            <td>
              <input type="radio" id="bizylink_type_box" name="bizylink_type" value="bizybox" class="bizyradio" /> 
              Call to Action Link<br>

              <input type="radio" id="bizylink_type_normal" name="bizylink_type" value="bizylink" class="bizyradio" checked /> 
              Regular Hyperlink
            </td>
          </tr>
          <tr>
            <td>
              <label id="bizylink_title-l" class="mce-widget mce-label mce-first" for="bizylink_title" aria-disabled="false">Link Text</label>
            </td>
            <td>
              <input type="text" placeholder="your bizybox text" id="bizylink_title" class="mce-textbox mce-last" value="" hidefocus="1" aria-labelledby="bizylink_title-l">
            </td>
          </tr>
          <tr>
            <td>
              <label id="bizylink_search-l" class="mce-widget mce-label mce-first" for="bizylink_search" aria-disabled="false">Search Business</label>
            </td>
            <td>
              <input type="text" placeholder="type keyword" id="bizylink_search" class="mce-textbox mce-last form-initialized" value="" hidefocus="1" aria-labelledby="bizylink_search-l">
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <div id="bizylink_results"><span class="faded">Type a keyword on the field above to search the Bizyhood directory.</span></div>
            </td>
          </tr>
          <tr>
            <td>
              <label id="bizylink_link-l" class="mce-widget mce-label mce-first" for="bizylink_link" aria-disabled="false">Business Link</label>
            </td>
            <td>
              <input type="text" placeholder="business overview link" id="bizylink_link" class="mce-textbox mce-last form-initialized" value="" hidefocus="1" aria-labelledby="bizylink_link-l">
            </td>
          </tr>
          <tr>
            <td>
              <label id="bizylink_target-l" class="mce-widget mce-label mce-first" for="bizylink_target" aria-disabled="false">Open in new window</label>
            </td>
            <td>
              <input type="checkbox" placeholder="business overview link" id="bizylink_target" class="mce-last form-initialized" value="yes" hidefocus="1" aria-labelledby="bizylink_target-l">
            </td>
          </tr>
        </table>
      ';
    
    
      die( $out );

    }
    
    
    function bizy_add_bizylink_button() {
      if ( get_user_option('rich_editing') == 'true' && current_user_can('edit_posts')) {
        add_filter('mce_buttons', array( $this, 'bizy_register_buttons' ), 10);
        add_filter('mce_external_plugins', array( $this, 'bizy_register_tinymce_javascript' ), 10);
      }
      
      return;
      
    }
    
    
    function bizy_register_buttons($buttons) {
      array_push($buttons, 'separator', 'bizylink');
      return $buttons;
    }
    
    function bizy_register_tinymce_javascript($plugin_array) {
      $plugin_array['bizylink'] = plugins_url('/Public/js/bizybutton-plugin.js',__FILE__);
      return $plugin_array;
    }
    
  }