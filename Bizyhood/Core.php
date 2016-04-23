<?php
/**
 * This file acts as the 'Controller' of the application. It contains a class
 *  that will load the required hooks, and the callback functions that those
 *  hooks execute.
 */

require_once dirname(__FILE__) . '/Ajax.php';
require_once dirname(__FILE__) . '/Cache.php';
require_once dirname(__FILE__) . '/Config.php';
require_once dirname(__FILE__) . '/Benchmark.php';
require_once dirname(__FILE__) . '/Log.php';
require_once dirname(__FILE__) . '/Model.php';
require_once dirname(__FILE__) . '/Utility.php';
require_once dirname(__FILE__) . '/View.php';
require_once dirname(__FILE__) . '/Exception.php';
require_once dirname(__FILE__) . '/Public/vendor/OAuth2/Client.php';
require_once dirname(__FILE__) . '/Public/vendor/OAuth2/GrantType/IGrantType.php';
require_once dirname(__FILE__) . '/Public/vendor/OAuth2/GrantType/ClientCredentials.php';
require_once dirname(__FILE__) . '/oAuth.php';


if (! class_exists('Bizyhood_Core')):

/**
 * This class contains the core code and callback for the behavior of Wordpress.
 *  It is instantiated and executed directly by the Bizyhood plugin loader file
 *  (which is most likely at the root of the Bizyhood installation).
 */
class Bizyhood_Core
{
    CONST KEY_API_URL             = 'Bizyhood_API_URL';
    CONST KEY_API_PRODUCTION      = 'Bizyhood_API_Production';
    CONST KEY_API_ID              = 'Bizyhood_API_ID';
    CONST KEY_API_SECRET          = 'Bizyhood_API_Secret';
    CONST KEY_OAUTH_DATA          = 'bizyhood_oauth_data';
    CONST KEY_MAIN_PAGE_ID        = 'Bizyhood_Main_page_ID';
    CONST KEY_SIGNUP_PAGE_ID      = 'Bizyhood_Signup_page_ID';
    CONST KEY_PROMOTIONS_PAGE_ID  = 'Bizyhood_Promotions_page_ID';
    CONST KEY_INSTALL_REPORT      = 'Bizyhood_Installed';
    CONST API_MAX_LIMIT           = 250;
    CONST BUSINESS_LOGO_WIDTH     = 307;
    CONST BUSINESS_LOGO_HEIGHT    = 304;
    CONST EXCERPT_MAX_LENGTH      = 20;
    
    public static $globals = null;

    /**
     * The constructor
     */
    public function __construct()
    {
        Bizyhood_Log::add('debug', "Bizyhood initializing");
    }

    static function install()
    {
        Bizyhood_Log::add('debug', "Bizyhood installing");
        
        // Create the business list page
        $business_list_page = get_page_by_path( "business-directory" );
        if ( !$business_list_page )
        {
            $business_list_page = array(
                'post_title'     => 'Business Directory',
                'post_type'      => 'page',
                'post_name'      => 'business-directory',
                'post_content'   => '[bh-businesses]',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_author'    => 1,
                'menu_order'     => 0,
            );
            wp_insert_post( $business_list_page );
        }

        // Create the view business page
        $business_view_page = get_page_by_path( "business-overview" );
        if ( !$business_view_page )
        {
            $business_view_page = array(
                'post_title'     => 'Business Overview',
                'post_type'      => 'page',
                'post_name'      => 'business-overview',
                'post_content'   => '',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_author'    => 1,
                'menu_order'     => 0,
            );
            wp_insert_post( $business_view_page );
        }
        
        // Create the promotions page
        $business_promotions_page = get_page_by_path( "business-promotions" );
        if ( !$business_promotions_page )
        {
            $business_promotions_page = array(
                'post_title'     => 'Business Promotions',
                'post_type'      => 'page',
                'post_name'      => 'business-promotions',
                'post_content'   => '[bh-promotions]',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_author'    => 1,
                'menu_order'     => 0,
            );
            wp_insert_post( $business_promotions_page );
        }

    }

    public function uninstall()
    {
        Bizyhood_Log::add('debug', "Bizyhood uninstalling");

        // Remove business list page
        $business_list_page = get_page_by_path( "business-directory" );
        if ($business_list_page)
        {
            Bizyhood_Log::add('info', "Removing business list page (post ID " . $business_list_page->ID . ")");
            wp_delete_post($business_list_page->ID);
        }

        // Remove business list page
        $business_view_page = get_page_by_path( "business-overview" );
        if ($business_view_page)
        {
            Bizyhood_Log::add('info', "Removing view business page (post ID " . $business_view_page->ID . ")");
            wp_delete_post($business_view_page->ID);
        }
        
        // Remove business promotions page
        $business_promotions_page = get_page_by_path( "business-promotions" );
        if ($business_promotions_page)
        {
            Bizyhood_Log::add('info', "Removing promotions page (post ID " . $business_promotions_page->ID . ")");
            wp_delete_post($business_promotions_page->ID);
        }

    }

    /**
     * Get the Bizyhood environment loaded and register Wordpress hooks
     */
    public function execute()
    {
        $this->_registerHooks();
    }
    
    /**
     * Register Wordpress hooks required for Bizyhood
     */
    private function _registerHooks()
    {
        Bizyhood_Log::add('debug', "Registering hooks..");
        
        // check for the pages everytime // upgrades will not execute the install
        add_action( 'init', array( $this, 'install' ), 10 );

        # -- Below is core functionality --
        add_action('admin_menu', 	array($this, 'adminCallback'));
        add_action('admin_init', 	array($this, 'adminInitCallback'));
        add_action('wp_enqueue_scripts', 	array($this, 'load_plugin_styles'));
        add_action('wp_enqueue_scripts', 	array($this, 'load_plugin_gallery'));
        add_shortcode('bh-businesses', array($this, 'businesses_shortcode'));
        add_shortcode('bh-promotions', array($this, 'promotions_shortcode'));
        add_filter('the_content', array($this, 'postTemplate'), 100);
        add_action('wp_ajax_bizyhood_save_settings', array('Bizyhood_Ajax', 'Bizyhood_saveSettings'));
        
        
        // create rewrite rule for single business
        add_filter('rewrite_rules_array', array($this, 'bizyhood_add_rewrite_rules'));
        // hook add_query_vars function into query_vars
        add_filter('query_vars', array($this, 'bizyhood_add_query_vars'));
        // check if a flush is needed
        add_action( 'wp_loaded', array($this, 'bizyhood_flush_rules') );
        
        // Yoast SEO additions START
        
        add_action( 'init', array( $this, 'sitemap_init' ), 10 );
        add_action('wpseo_do_sitemap_bizyhood-sitemap', array($this, 'bizyhood_create_sitemap') );
        add_filter( 'wpseo_sitemap_index', array($this, 'bizyhood_addtoindex_sitemap') );
        
        // Yoast SEO additions END      

        // AIOSP START
        
        add_filter( 'aiosp_sitemap_extra', array( $this, 'aiosp_sitemap_init' ), 10 );
        add_filter( 'aiosp_sitemap_custom_bizyhood', array( $this, 'bizy_add_aioseo_pages' ), 10, 3 );
        add_filter( 'aiosp_sitemap_addl_pages', array( $this, 'bizy_add_aioseo_pages' ), 10, 1 );
        
        // AIOSP END
        
        
        // editor bizybutton START
        
        add_action('admin_head', array( $this, 'bizy_add_bizylink_button'));
        
        add_action( 'wp_ajax_bizylink_insert_dialog', array( $this, 'bizylink_insert_dialog' ));
        add_action( 'wp_ajax_bizylink_business_results', array( $this, 'bizylink_business_results' ));

        // editor bizybutton END
        
        // load widgets START
        
        Bizyhood_View::load( 'widgets/search', array(), false, true);
        Bizyhood_View::load( 'widgets/meet_the_merchant', array(), false, true);
        Bizyhood_View::load( 'widgets/promotions', array(), false, true);
        add_action( 'widgets_init', array( $this, 'register_search_widget' ));
        add_action( 'widgets_init', array( $this, 'register_mtm_widget' ));
        add_action( 'widgets_init', array( $this, 'register_promotions_widget' ));
      
        // load widgets END

        
        // add oAuth Data START

        add_action( 'init', array('Bizyhood_oAuth', 'set_oauth_temp_data') );
        
        // add oAuth Data END
        
        
        // admin notices START
        
        add_action( 'admin_notices', array( $this, 'set_bizyhood_admin_notices' ));
        
        // admin notices END
        
        
        // remove empty paragraphs START

        add_action( 'template_redirect', array( $this, 'remove_empty_paragraphs' ));
        
        // remove empty paragraphs END
        
        
    }
    
    
    function register_search_widget() {
      register_widget( 'bizy_search_widget' );
    }
    function register_mtm_widget() {
      register_widget( 'bizy_mtm_widget' );
    }
    function register_promotions_widget() {
      register_widget( 'bizy_promotions_widget' );
    }


    function remove_empty_paragraphs() {
      
      // if it is not a bizyhood page there is nothign to do
      if ( !( is_page(Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID)) || is_page(Bizyhood_Utility::getOption(self::KEY_SIGNUP_PAGE_ID)) ) ) {
        return;
      }
      
      // get all filters
      global $wp_filter;
  
      // loop through filters
      foreach ($wp_filter['the_content'] as $priority => $filter_array) {
        
        foreach  ($filter_array as $filter_function => $filter) {
          
          if ($filter_function == 'wpautop') {
            
            remove_filter('the_content', 'wpautop', $priority);
            
            // no need to keep looping
            return;
          }
          
        }
        
      }
      
    }
    
    
    function set_bizyhood_admin_notices() {
      
      $errors = array();
      if (Bizyhood_Utility::getApiID() == '') {
        $errors[] = __('Your Bizyhood API Client ID is missing. %s', 'bizyhood');
      }
      if (Bizyhood_Utility::getApiSecret() == '') {
        $errors[] = __('Your Bizyhood API Client Secret Key is missing. %s', 'bizyhood');
      }
      if (Bizyhood_Utility::getApiProduction() != true) {
        $errors[] = __('WARNING: you are using the Bizyhood TEST server. Are you sure you want to do that?. %s', 'bizyhood');
      }
      
      if (Bizyhood_Utility::getApiID() != '' && Bizyhood_Utility::getApiSecret() != '') {
        $authetication = Bizyhood_oAuth::set_oauth_temp_data();
        if (is_wp_error($authetication) || Bizyhood_oAuth::checkoAuthData() == false) {
          $errors[] = __('Can not authenticate to the Bizyhood API. Check your Client ID and Secret Key. %s', 'bizyhood');
        }
      }
      
      if (!empty($errors)) {
        foreach ($errors as $error) {
          echo '
            <div class="notice notice-error">
              <p>'. sprintf($error, '<a href="admin.php?page=Bizyhood">'.__('Click here to fix', 'bizyhiid').'</a>') .'</p>
            </div>';
        }
      }
      
    }
    
    
    function bizylink_business_results() {

      $_GET['keywords']  = $_REQUEST['keywords'];
    
      
      $queryapi = $this->businesses_information(array('paged' => 1));
      $numofpages = floor($queryapi['total_count'] / $queryapi['page_size']);
      $urlbase = get_permalink( get_page_by_path( 'business-overview' ) );
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
              <input type="radio" id="bizylink_type_box" name="bizylink_type" value="bizybox" class="bizyradio" checked /> 
              Call to Action Link<br>

              <input type="radio" id="bizylink_type_normal" name="bizylink_type" value="bizylink" class="bizyradio" /> 
              Normal Link
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

    
    
    function bizy_add_aioseo_pages( $pages ) {
      
      // initialize array
      if ( empty( $pages ) ) $pages = Array();
      
      $queryapi = $this->businesses_information(array('paged' => 1, 'verified' => 'y', 'ps' => self::API_MAX_LIMIT));
      $numofpages = floor($queryapi['total_count'] / $queryapi['page_size']);
      $urlbase = get_permalink( get_page_by_path( 'business-overview' ) );
      $date = date("Y-m-d H:i");
      $count  = $queryapi['total_count']; // get the number of results // 492
      
      $start = 1;
      
      // get first 12 urls to save an API request
      if ($start == 1) {
        foreach($queryapi['businesses'] as $business) {
          $urlarr = array_slice(explode('/', $business->bizyhood_url), -3);
          $pages[] = Array( "loc" => $urlbase.$urlarr[0].'/'.$urlarr[1].'/', "lastmod" => $date, "changefreq" => "weekly", "priority" => "0.6" );
        }
      }
      
      // get the rest of the urls if they exist
      $i = $start + 1; // start  to query the API from the second batch
      while($i <= $numofpages) {
        $queryapi = $this->businesses_information(array('paged' => $i, 'verified' => 'y', 'ps' => self::API_MAX_LIMIT));
        foreach($queryapi['businesses'] as $business) {
          $urlarr = array_slice(explode('/', $business->bizyhood_url), -3);
          $pages[] = Array( "loc" => $urlbase.$urlarr[0].'/'.$urlarr[1].'/', "lastmod" => $date, "changefreq" => "weekly", "priority" => "0.6" );
        }
        $i++;
      }
      
      

      
      return $pages;
    }
    
    
    function aiosp_sitemap_init($extra) {
            
      $extra[] = 'bizyhood';
      
      return $extra;
    }
    
        
    
    function bizyhood_add_query_vars($aVars) 
    {
      $aVars[] = "bizyhood_id"; // represents the id of the business
      $aVars[] = "bizyhood_name"; // represents the name of the business
      return $aVars;
    }
     
    function bizyhood_add_rewrite_rules($wr_rules)
    {
      
      
      $bizy_rules = array('business-overview/([^/]+)/([^/]+)/?$' => 'index.php?pagename=business-overview&bizyhood_name=$matches[1]&bizyhood_id=$matches[2]');
      $wr_rules = $bizy_rules + $wr_rules;
      
      return $wr_rules;
    }

    // flush_rules() if our rules are not yet included
    function bizyhood_flush_rules(){
      
      $wr_rules = get_option( 'rewrite_rules' );
      
      // check if the rule already exits and if not then flush the rewrite rules
      if ( ! isset( $wr_rules['business-overview/([^/]+)/([^/]+)/?$'] ) ) {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
      }
    }
    
    
    // create Yoast sitemap
    
    public function sitemap_init() {
      if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
        $GLOBALS['wpseo_sitemaps']->register_sitemap( 'bizyhood', array( $this, 'sitemap_build' ) );
      }
    }
    
    
    public function sitemap_build() {
      global $wpseo_sitemaps;
      
      if (!$this->bizyhood_create_sitemap()) {
        return false;
      }
      
      $wpseo_sitemaps->set_sitemap( $this->bizyhood_create_sitemap() );
      $wpseo_sitemaps->set_stylesheet( '<?xml-stylesheet type="text/xsl" href="' . preg_replace( '/(^http[s]?:)/', '', esc_url( home_url( 'main-sitemap.xsl' ) ) ) . ' "?>' );
    }
    
    
    public function bizyhood_create_all_urls($verified = false) {
      
      $yoastoptions = WPSEO_Options::get_all();
      $max_entries  = $yoastoptions['entries-per-page']; // get the limit of urls per sitemap page
      $sitemapnum   = (get_query_var( 'sitemap_n' ) ? get_query_var( 'sitemap_n' ) : 1); // get the sitemap number / page
      $urlbase      = get_permalink( get_page_by_path( 'business-overview' ) );
      $date         = date("Y-m-d H:i");

      $urls         = array(); // initialize URLs array
      $apimax       = self::API_MAX_LIMIT; // set the max we can get from the API in one fetch
      $urlindex     = 0; // help index the urls array
           
      // if yoast is set to grab per sitemap more than $apimax (self::API_MAX_LIMIT) results
      if ($max_entries > $apimax) {
        $ps = $apimax;
        $query_params = array('paged' => 1, 'verified' => $verified, 'ps' => $ps);
        $queryapi = $this->businesses_information($query_params);
        
        
        // max number of pages
        $maxsitemapnum = (int) ceil( $count / $max_entries );
        // get bizyhod page to start
        $start  = (($sitemapnum - 1) * $max_entries / $apimax == 0 ? 1 : ceil(($sitemapnum - 1) * $max_entries / $apimax));
        // get bizyhod page to end
        $end  = ceil($sitemapnum * $max_entries / $apimax);
                
        // we only have LESS than $apimax (self::API_MAX_LIMIT) then get only the first query results
        if ($queryapi['total_count'] <= $apimax && $sitemapnum == 1) {
          
          if (!empty($queryapi['businesses'])) {
            foreach($queryapi['businesses'] as $business) {
              $urlarr = array_slice(explode('/', $business->bizyhood_url), -3);
              $urls[$urlindex]['url'] = $urlbase.$urlarr[0].'/'.$urlarr[1].'/';
              $urls[$urlindex]['date'] = $date;
              $urlindex++;
            }
          
            return $urls;
          } else {
            
            // nothing to return, no urls found
            return;
          }
          
        }
        
        // we have MORE than $apimax (self::API_MAX_LIMIT) results than we can get at once from the API
        if ($queryapi['total_count'] > $apimax) {
          
          $bizyresults = 0; // results that we have already fetch
          $bizypaged = $start;
          while ($bizyresults < $max_entries && $queryapi['total_count'] > $bizyresults && $bizypaged <= $end) {
                        
            $query_params = array('paged' => $bizypaged, 'verified' => $verified, 'ps' => $ps);
            $queryapi = $this->businesses_information($query_params);

            
            if (!empty($queryapi['businesses'])) {
              
              // remove the first n from the array if we have it already on the previous xml page
              // only on the first loop and not on the first page
              $remove_from_start = ($max_entries % $apimax) * ($sitemapnum - 1);
              
              
              if ($remove_from_start >= 0 && $bizyresults == 0 && $sitemapnum > 1) {
                
                $queryapi['businesses'] = array_slice($queryapi['businesses'], $remove_from_start);
                
              }
              
              foreach($queryapi['businesses'] as $business) {
                
                if ($urlindex == $max_entries) { break 2; }
                
                $urlarr = array_slice(explode('/', $business->bizyhood_url), -3);
                $urls[$urlindex]['url'] = $urlbase.$urlarr[0].'/'.$urlarr[1].'/';
                $urls[$urlindex]['date'] = $date;
                $urlindex++;
              }
            } else {
              // there are no more results, so break the while
              break;
            }
            
            $bizyresults = $bizyresults + $apimax; // count the number of results we have added
            $bizypaged++; // increase the page number by one
            
          }
          
          return $urls;
          
        }
        
      // if yoast is set to grab per sitemap less than $apimax (self::API_MAX_LIMIT) results, then we just follow the yoast pagination
      } else {
        
        $ps = $max_entries;
        $query_params = array('paged' => $sitemapnum, 'verified' => $verified, 'ps' => $ps);
        $queryapi = $this->businesses_information($query_params);        
        
        if (!empty($queryapi['businesses'])) {
          foreach($queryapi['businesses'] as $business) {
            $urlarr = array_slice(explode('/', $business->bizyhood_url), -3);
            $urls[$urlindex]['url'] = $urlbase.$urlarr[0].'/'.$urlarr[1].'/';
            $urls[$urlindex]['date'] = $date; // this needs to be changed to the last modified when added to the API // TODO
            $urlindex++;
          }
        } else {
            
          // nothing to return, no urls found
          return;
        }
        
        return $urls;
        
      }
            
            
      return;
    }
    
    
    public function bizyhood_create_sitemap() {
      
      // get only verified businesses
      $urls = $this->bizyhood_create_all_urls(true);
      
      if (empty($urls)) {
        return false;
      }
      
      $WPSEO_Sitemaps = new WPSEO_Sitemaps();
      
      
      $sitemap  = '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ';
      $sitemap .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" ';
      $sitemap .= 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
      
      foreach($urls as $u) {      
        $sitemap .= $WPSEO_Sitemaps->sitemap_url(
            array(
              'loc' => $u['url'],
              'pri' => 0.6,
              'chf' => 'monthly',
              'mod' => $u['date']
            )
        );
      }
        
			$sitemap .= '</urlset>';
      
      return $sitemap;
      
    }
    
    
    // add sitemap to index
    function bizyhood_addtoindex_sitemap() {
      
      $getfirstpage = $this->businesses_information(array('paged' => 1, 'verified' => 'y', 'ps' => self::API_MAX_LIMIT));
      $count  = $getfirstpage['total_count'];
      $yoastoptions = WPSEO_Options::get_all();
      $max_entries  = $yoastoptions['entries-per-page'];
      $sitemap = '';
      
      // if we need to split the sitemaps
      if ($count > $max_entries) {
        
        $n = (int) ceil( $count / $max_entries );
        for ( $i = 1; $i <= $n; $i ++ ) {
          
          $sitemap  .= '<sitemap>' . "\n";
          $sitemap .= '<loc>' . wpseo_xml_sitemaps_base_url( 'bizyhood-sitemap' . $i . '.xml' ) . '</loc>' . "\n";
          $sitemap .= '<lastmod>' . htmlspecialchars( date("c") ) . '</lastmod>' . "\n";
          $sitemap .= '</sitemap>' . "\n";
          
        }
        
      } else { // create just one
      
        $sitemap  = '<sitemap>' . "\n";
        $sitemap .= '<loc>' . wpseo_xml_sitemaps_base_url( 'bizyhood-sitemap.xml' ) . '</loc>' . "\n";
        $sitemap .= '<lastmod>' . htmlspecialchars( date("c") ) . '</lastmod>' . "\n";
        $sitemap .= '</sitemap>' . "\n";
        
      }
      return $sitemap;
    }
    
    
    function load_plugin_styles()
    {
        wp_enqueue_style ('bizyhood-icons-styles',  'https://d17bale0hcbyzh.cloudfront.net/bizyhood/styles/entypo/entypo-icon-fonts.css?family=entypoplugin.css', array(), BIZYHOOD_VERSION);
        wp_enqueue_style ('bizyhood-plugin-styles',  Bizyhood_Utility::getCSSBaseURL() . 'plugin.css', array(), BIZYHOOD_VERSION);
    }
    
    function load_plugin_gallery()
    {
        wp_enqueue_style ('photoswipe-css',  Bizyhood_Utility::getVendorBaseURL() . 'photoswipe/css/photoswipe.css', array(), BIZYHOOD_VERSION);
        wp_enqueue_style ('photoswipe-css-default-skin',  Bizyhood_Utility::getVendorBaseURL() . 'photoswipe/css/default-skin/default-skin.css', array('photoswipe-css'), BIZYHOOD_VERSION);
        wp_enqueue_script('photoswipe-js', Bizyhood_Utility::getVendorBaseURL() . 'photoswipe/js/photoswipe.min.js', array(), BIZYHOOD_VERSION, true);
        wp_enqueue_script('photoswipe-ui-js', Bizyhood_Utility::getVendorBaseURL() . 'photoswipe/js/photoswipe-ui-default.js', array('photoswipe-js'), BIZYHOOD_VERSION, true);
        wp_enqueue_script('bizyhood-gallery-js', Bizyhood_Utility::getJSBaseURL() . 'bizyhood-plugin-gallery.js', array(), BIZYHOOD_VERSION, true);
        wp_enqueue_script('bizyhood-custom-js', Bizyhood_Utility::getJSBaseURL() . 'bizyhood-custom.js', array(), BIZYHOOD_VERSION, true);
    }
    
    /**
     * A callback executed whenever the user tried to access the Bizyhood admin page
     */
    public function adminCallback()
    {
        $icon_url = null;
                
        add_menu_page('Bizyhood', 'Bizyhood', 'edit_pages', 'Bizyhood', array($this, 'adminMenuCallback'), $icon_url);
        add_submenu_page('Bizyhood', 'Settings', 'Account Setup', 'edit_pages', 'Bizyhood', array($this, 'adminMenuCallback'));
    }

    /**
     * Emit a warning that the search index hasn't been built (if it hasn't)
     */
    public function adminWarningCallback()
    {
        if(in_array($GLOBALS['pagenow'], array('edit.php', 'post.php', 'post-new.php')))
        {
            $info = Bizyhood_Utility::getNetwork();
        }
    }

    /**
     * A callback executed when the admin page callback is a about to be called.
     *  Use this for loading stylesheets/css.
     */
    public function adminInitCallback()
    {
        add_image_size('bs-biz-size', 600, 450, true);
        
        # Only register javascript and css if the Bizyhood admin page is loading
        if(strstr($_SERVER['QUERY_STRING'], 'Bizyhood'))
        {
            wp_enqueue_style ('Bizyhood-styles',  Bizyhood_Utility::getCSSBaseURL() . 'bizyhood.css?v='. BIZYHOOD_VERSION);
            wp_enqueue_script('Bizyhood-main'  ,  Bizyhood_Utility::getJSBaseURL().'bizyhood.js?v='. BIZYHOOD_VERSION);
        }
        
        # Only register on the post editing page
        if($GLOBALS['pagenow'] == 'post.php'
                || $GLOBALS['pagenow'] == 'post-new.php')
        {
            wp_enqueue_style ('Bizyhood-custom-post-css', Bizyhood_Utility::getCSSBaseURL() . 'bizyhood-admin.css');
            wp_enqueue_style ('Bizyhood-vendorcss-time', Bizyhood_Utility::getVendorBaseURL() . 'timepicker/css/timePicker.css');
            wp_enqueue_script('Bizyhood-main'  ,  Bizyhood_Utility::getJSBaseURL().'bizyhood.js?v='. BIZYHOOD_VERSION);
            wp_enqueue_script('Bizyhood-vendorjs-time'  ,  Bizyhood_Utility::getVendorBaseURL().'timepicker/js/jquery.timePicker.min.js');
        }
        
        // include color picker for widgets
        if($GLOBALS['pagenow'] == 'widgets.php'
                || strstr($_SERVER['QUERY_STRING'], 'Bizyhood-Business'))
        {
          wp_enqueue_style ('Bizyhood-custom-post-css', Bizyhood_Utility::getCSSBaseURL() . 'bizyhood-admin-widgets.css');
          wp_enqueue_style( 'wp-color-picker' );        
          wp_enqueue_script( 'wp-color-picker' );
        }
        
        # Include thickbox on widgets page
        if($GLOBALS['pagenow'] == 'widgets.php'
                || strstr($_SERVER['QUERY_STRING'], 'Bizyhood-Business'))
        {
            wp_enqueue_script('thickbox');
            wp_enqueue_style( 'thickbox' );
        }
    }

    /**
     * The callback that is executed when the user is loading the admin page.
     *  Basically, output the page content for the admin page. The function
     *  acts just like a controller method for and MVC app. That is, it loads
     *  a view.
     */
    public function adminMenuCallback()
    {
        Bizyhood_Log::add('debug', "Admin page callback executed");
        Bizyhood_Utility::sendInstallReportIfNew();
        
        $data = array();

        $data['api_url']            = Bizyhood_Utility::getApiUrl();
        $data['api_production']     = Bizyhood_Utility::getApiProduction();
        $data['api_id']             = Bizyhood_Utility::getApiID();
        $data['api_secret']         = Bizyhood_Utility::getApiSecret();
        $data['main_page_id']       = Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID);
        $data['signup_page_id']     = Bizyhood_Utility::getOption(self::KEY_SIGNUP_PAGE_ID);
        $data['promotions_page_id'] = Bizyhood_Utility::getOption(self::KEY_PROMOTIONS_PAGE_ID);
        $data['errors']             = array();

        if(!function_exists('curl_exec'))
        {
            $data['errors'][] = 'Bizyhood requires the PHP cURL module to be enabled. You may need to ask your web host or developer to enable this.';
        }
        
        if(get_category_by_slug('businesses-overview'))
        {
            $data['errors'][] = 'You have a category named "businesses-overview", which will interfere with the business directory if you plan to use it. You must delete that page.';
        }
        
        if(get_category_by_slug('business-directory'))
        {
            $data['errors'][] = 'You have a category named "business-directory", which will interfere with the business directory if you plan to use it. You must delete that category.';
        }

        Bizyhood_View::load('admin/admin', $data);
    }
    
    public function adminMenuBusinessCallback() {        
        
        if (isset($_POST['featured_business_image'])) {
            $featured_image = Bizyhood_Utility::featuredBusinessImage($_POST['featured_business_image']);
        } else {
            $featured_image = Bizyhood_Utility::featuredBusinessImage();
        }
        
        Bizyhood_View::load('admin/businesses', array('featured_image' => $featured_image));
    }
    
    public function adminMenuEditableCallback()
    {
        Bizyhood_View::load('admin/editable');
    }
    
    
    public function adminMenuHelpCallback()
    {
        Bizyhood_View::load('admin/help');
    }
    
    public function adminMenuLayoutCallback()
    {
        Bizyhood_View::load('admin/layout');
    }
    
    
    /***************************/
    /***** API Calls START *****/
    
    public function businesses_information($atts)
    {
      
      // no reason to continue if we do not have oAuth token
      if (get_transient('bizyhood_oauth_data') === false) {
        return;
      }
      
      $a = shortcode_atts( array(
        'paged'     => null,
        'verified'  => null,
        'ps'        => null
      ), $atts );

      
      $remote_settings = Bizyhood_Utility::getRemoteSettings();
      $api_url = Bizyhood_Utility::getApiUrl();
      $list_page_id = Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID);
      $params = array();


      // get current page
      if (isset($a['paged']))
          $page = $a['paged'];
      elseif (get_query_var('paged'))
          $page = get_query_var('paged');
      elseif (isset($_GET['paged']))
          $page = $_GET['paged'];
      else
          $page = 1;

      // get current ps
      if (isset($a['ps'])) {
        $ps = $a['ps'];
      } elseif (get_query_var('ps')) {
        $ps = get_query_var('ps');
      } elseif (isset($_GET['ps'])) {
        $ps = $_GET['ps'];
      } else {
        $ps = self::API_MAX_LIMIT;
      }
        
      // get category filter
      $category = false;
      if (get_query_var('cf')) {
        $category = urldecode( get_query_var('cf') );
      } elseif (isset($_GET['cf'])) {
        $category = urldecode( $_GET['cf'] );
      }
      
      $keywords = false;
      if(isset($_GET['keywords'])) {
        $keywords = esc_attr(strip_tags($_GET['keywords']));
      }
      
      // get verified

      $verified = $a['verified'];
      if (get_query_var('verified')) {
          $verified = get_query_var('verified');
      } elseif (isset($_GET['verified'])) {
          $verified = $_GET['verified'];
      }
      
      // check if $verified has a valid value
      if ($verified == true || $verified == True || $verified == 1 || $verified == 'y' || $verified == 'Y') {
        $verified = 'y';
      } else {
        $verified = false;
        $ps = 12;
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
      
      if ($verified == 'y') {
        $params['verified'] = $verified;
      }
      
      try {
        $response = $client->fetch($api_url.'/business/', $params);
      } catch (Exception $e) {
        $error = new WP_Error( 'bizyhood_error', __( 'Service is currently unavailable! Request timed out.', 'bizyhood' ) );
        return array('error' => $error);
      }  
      
      // avoid throwing an error
      if (!is_array($response) || empty($response)) { return; }
      
      $response_json = $response['result'];
      
      // avoid throwing an error
      if ($response_json === null) { return; }
      
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
        'facets'            => $facets
      );
      
      return $return;
    }
    
    
    
    /***** additional busineess info *****/    
    
    /**
     * API call for additional business info
     * @param array $atts Attributes to be passesd on the API
     * @param string $command data to get promotions (default) and events
     * @return mixed results (array) or error (array) or empty
     */
    public function business_details_information($atts, $command = 'promotions')
    {
      
      
      $a = shortcode_atts( array(
        'bid'         => null,
        'identifier'  => null
      ), $atts );
      
      $remote_settings = Bizyhood_Utility::getRemoteSettings();
      $api_url = Bizyhood_Utility::getApiUrl();
      $list_page_id = Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID);      
      $client = Bizyhood_oAuth::oAuthClient();
      
      $params = array(
        'format'      => 'json',
        'bid'         => $a['bid'],
        'identifier'  => $a['identifier']
      );

      
      try {
        $response = $client->fetch($api_url.'/'. $command .'/', $params);
      } catch (Exception $e) {
        $error = new WP_Error( 'bizyhood_error', __( 'Service is currently unavailable! Request timed out.', 'bizyhood' ) );
        return array('error' => $error);
      }
      
      // avoid throwing an error
      if (!is_array($response) || empty($response)) { return; }
      
      $response_json = $response['result'];
            
      $return = array(
        'remote_settings'   => $remote_settings,
        'api_url'           => $api_url,
        'list_page_id'      => $list_page_id,
        'bid'               => (isset($a['bid']) ? $a['bid'] : ''),
        'identifier'        => (isset($a['identifier']) ? $a['identifier'] : ''),
        'response'          => json_encode($response_json),
        'response_json'     => $response_json
      );
      
      return $return;
      
    }
    
    /***** API Calls END *****/
    /***************************/
    
    
    public function promotions_shortcode($attrs) {
      
      $authetication = Bizyhood_oAuth::set_oauth_temp_data();
      if (is_wp_error($authetication) || Bizyhood_oAuth::checkoAuthData() == false) {
        return Bizyhood_View::load( 'listings/error', array( 'error' => $authetication->get_error_message()), true );
      }
      
      
      // cache the results
      $cached_promotions = self::try_transient('bizyhood_promotions_widget', 'response_json', 'business_details_information', $attrs);
      
      if ($cached_promotions === false) {
        return Bizyhood_View::load( 'listings/error', array( 'error' => $authetication->get_error_message()), true );
      }
      
      $list_page_id = Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID);

      return Bizyhood_View::load( 'listings/promotions', array( 'promotions' => $cached_promotions, 'list_page_id' => $list_page_id ), true );
      
    }
    
    
    public function businesses_shortcode($attrs)
    {
      
        
        $authetication = Bizyhood_oAuth::set_oauth_temp_data();
        if (is_wp_error($authetication) || Bizyhood_oAuth::checkoAuthData() == false) {
          return Bizyhood_View::load( 'listings/error', array( 'error' => $authetication->get_error_message()), true );
        }
        
        
        $q = $this->businesses_information($attrs);
        
        if (isset($q['error'])) {
          $error = $q['error'];
          return Bizyhood_View::load( 'listings/error', array( 'error' => $error->get_error_message()), true );
        }
        
        $list_page_id = $q['list_page_id'];
        $page = $q['page'];
       
        $businesses     = $q['businesses'];
        $keywords       = $q['keywords'];
        $facets         = $q['categories'];
        $categories     = $q['categories'];
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
        $view_business_page_id = get_page_by_path( "business-overview" )->ID;
        
        return Bizyhood_View::load( 'listings/index', array( 'facets' => (isset($facets) ? $facets : ''), 'keywords' => (isset($keywords) ? $keywords : ''), 'categories' => (isset($categories) ? $categories : ''), 'cf' => (isset($cf) ? $cf : ''), 'list_page_id' => $list_page_id, 'pagination_args' => $pagination_args, 'businesses' => $businesses, 'view_business_page_id' => $view_business_page_id ), true );
    }
    
    
    
    /**
     * Sets and returns cached API results as a transient
     * @param string $transient_name The name of the transient to get or set
     * @param string $method_name The name of the function to call to get the API data
     * @param array $attrs Attributes for the $method_name
     * @param boolean $random Either to return on random result or all of them
     * @return array transient result(s) or false
     */
    public function try_transient($transient_name, $transient_value = 'response_json', $method_name, $attrs, $random = false) {
      
      // cache the results    
      if (get_transient($transient_name) === false || get_transient($transient_name) == '') {
        
        // get businesses

        $transient = Bizyhood_Core::$method_name($attrs);
        
        set_transient($transient_name, $transient[$transient_value], 12 * HOUR_IN_SECONDS);
      }
      
      $cached_transient = get_transient($transient_name);
      
      // pick one random business
      if (!empty($cached_transient) && $random === true) {
        $randomize_transient = array_rand($cached_transient, 1);
        $random_transient = $cached_transient[$randomize_transient];
        
        return $random_transient;
      }
      
      return $cached_transient;
      
    }
    

    /**
     * Handler used for modifying the way business listings are displayed
     * @param string $content The post content
     * @return string Content
     */
    public function postTemplate($content)
    {   
        global $post, $wp_query;
                
        // no reason to continue if we do not have oAuth token
        if (get_transient('bizyhood_oauth_data') === false) {
          $authetication = Bizyhood_oAuth::set_oauth_temp_data();
          if (is_wp_error($authetication)) {
            return Bizyhood_View::load( 'listings/error', array( 'error' => $authetication->get_error_message()), true );
          }
        }
        
        
        $api_url = Bizyhood_Utility::getApiUrl();
        $params = array();

        # Override content for the view business page        
        $post_name = $post->post_name;
        if ($post_name === 'business-overview')
        {
            $signup_page_id = Bizyhood_Utility::getOption(self::KEY_SIGNUP_PAGE_ID);
            
            // get the bizyhood_id
            if(isset($wp_query->query_vars['bizyhood_id'])) {
              
              $bizyhood_id = urldecode($wp_query->query_vars['bizyhood_id']);
            } else {
              $bizyhood_id = (isset($_REQUEST['bizyhood_id']) ? $_REQUEST['bizyhood_id'] : '');
            }
            
            
            $client = Bizyhood_oAuth::oAuthClient();
            
            if (is_wp_error($client)) {
              return Bizyhood_View::load( 'listings/error', array( 'error' => $client->get_error_message()), true );
            }

            try {
              $response = $client->fetch($api_url . "/business/" . $bizyhood_id.'/', $params);
            } catch (Exception $e) {
              return Bizyhood_View::load( 'listings/error', array( 'error' => __( 'Service is currently unavailable! Request timed out.', 'bizyhood' )), true );
            }  
            $business = json_decode(json_encode($response['result']), FALSE);
                        
            return Bizyhood_View::load('listings/single/default', array('content' => $content, 'business' => $business, 'signup_page_id' => $signup_page_id), true);
        }

        return $content;
    }
    
}

endif;