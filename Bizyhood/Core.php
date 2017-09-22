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
require_once dirname(__FILE__) . '/Api.php';
require_once dirname(__FILE__) . '/Shortcodes.php';
require_once dirname(__FILE__) . '/Sitemap.php';
require_once dirname(__FILE__) . '/Meta.php';
require_once dirname(__FILE__) . '/Bizybox.php';
require_once dirname(__FILE__) . '/Cta.php';


if (! class_exists('Bizyhood_Core')):

/**
 * This class contains the core code and callback for the behavior of Wordpress.
 *  It is instantiated and executed directly by the Bizyhood plugin loader file
 *  (which is most likely at the root of the Bizyhood installation).
 */
class Bizyhood_Core
{
    CONST KEY_VERSION             = 'bh_version';
    CONST KEY_API_URL             = 'Bizyhood_API_URL';
    CONST KEY_API_PRODUCTION      = 'Bizyhood_API_Production';
    CONST KEY_API_ID              = 'Bizyhood_API_ID';
    CONST KEY_API_SECRET          = 'Bizyhood_API_Secret';
    CONST KEY_OAUTH_DATA          = 'bizyhood_oauth_data';
    CONST KEY_MAIN_PAGE_ID        = 'Bizyhood_Main_page_ID';
    CONST KEY_OVERVIEW_PAGE_ID    = 'Bizyhood_Overview_page_ID';
    CONST KEY_SIGNUP_PAGE_ID      = 'Bizyhood_Signup_page_ID';
    CONST KEY_PROMOTIONS_PAGE_ID  = 'Bizyhood_Promotions_page_ID';
    CONST KEY_EVENTS_PAGE_ID      = 'Bizyhood_Events_page_ID';
    CONST KEY_GUIDE_PAGE_ID       = 'Bizyhood_Guide_page_ID';
    CONST KEY_INSTALL_REPORT      = 'Bizyhood_Installed';
    CONST API_MAX_LIMIT           = 250;
    CONST BIZYBOX_MAX_LIMIT       = 25;
    CONST BUSINESS_LOGO_WIDTH     = 307;
    CONST BUSINESS_LOGO_HEIGHT    = 304;
    CONST EXCERPT_MAX_LENGTH      = 20;
    CONST META_DESCRIPTION_LENGTH = 80;
    CONST CATEGORIES_LENGTH       = 35;
    CONST BOOTSTRAP_VERSION       = '3.3.7';
    CONST GOOGLEMAPS_API_KEY      = 'AIzaSyBJvxrmMgNs6vQAJ9BUgzr7nG0KHKK9cns';
    CONST BTN_BG_COLOR            = 'bh_btn_bg_color';
    CONST BTN_FONT_COLOR          = 'bh_btn_font_color';
    CONST API_CACHE_TIME          = 30; // 30 seconds
    CONST ICON_FACEBOOK           = 'bh_facebook';
    CONST ICON_TWITTER            = 'bh_twitter';
    CONST ICON_GOOGLE             = 'bh_google';
    CONST ICON_LINKEDIN           = 'bh_linkedin';
    CONST ICON_MAIL               = 'bh_mail';
    CONST ICON_PLACEMENT          = 'bh_icon_placement';
    CONST RSS_SUFFIX              = 'rssfeed';
    
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
        
        $business_list_page         = Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID);
        $business_list_page_exists  = false;
        
        // check if the id exists and if the page has not been deleted
        if (intval($business_list_page) > 0 && get_post_field('post_status', $business_list_page) == 'publish') {
          $business_list_page_exists = true;
        }
        
        if ( intval($business_list_page) == 0 || $business_list_page_exists === false )
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
            $business_list_page_id = wp_insert_post( $business_list_page );
            if ($business_list_page_id) {
              Bizyhood_Utility::setOption(self::KEY_MAIN_PAGE_ID, $business_list_page_id);
            }
        }

        // create the business overview DB entry to avoid duplicate page
        if( !Bizyhood_Utility::getOption(self::KEY_OVERVIEW_PAGE_ID) ) {
          $business_view_page = get_page_by_path( "business-overview" );
          if ( $business_view_page ) {
            Bizyhood_Utility::setOption(self::KEY_OVERVIEW_PAGE_ID, $business_view_page->ID);
          }
        }
        
        // Create the view business page
        $business_view_page         = Bizyhood_Utility::getOption(self::KEY_OVERVIEW_PAGE_ID);
        $business_view_page_exists  = false;
        
        // check if the id exists and if the page has not been deleted
        if (intval($business_view_page) > 0 && get_post_field('post_status', $business_view_page) == 'publish') {
          $business_view_page_exists = true;
        }
        
        if ( intval($business_view_page) == 0 ||  $business_view_page_exists === false )
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
            $business_view_page_id = wp_insert_post( $business_view_page );
            if ($business_view_page_id) {
              Bizyhood_Utility::setOption(self::KEY_OVERVIEW_PAGE_ID, $business_view_page_id);
            }
        }
        
        // Create the promotions page
        $business_promotions_page         = Bizyhood_Utility::getOption(self::KEY_PROMOTIONS_PAGE_ID);
        $business_promotions_page_exists  = false;
        
        // check if the id exists and if the page has not been deleted
        if (intval($business_promotions_page) > 0 && get_post_field('post_status', $business_promotions_page) == 'publish') {
          $business_promotions_page_exists = true;
        }
        
        if ( intval($business_promotions_page) == 0 || $business_promotions_page_exists  === false )
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
            $business_promotions_page_id = wp_insert_post( $business_promotions_page );
            if ($business_promotions_page_id) {
              Bizyhood_Utility::setOption(self::KEY_PROMOTIONS_PAGE_ID, $business_promotions_page_id);
            }
        }
        
        // Create the events page
        $business_events_page         = Bizyhood_Utility::getOption(self::KEY_EVENTS_PAGE_ID);
        $business_events_page_exists  = false;
        
        // check if the id exists and if the page has not been deleted
        if (intval($business_events_page) > 0 && get_post_field('post_status', $business_events_page) == 'publish') {
          $business_events_page_exists = true;
        }
        
        if ( intval($business_events_page) == 0 || $business_events_page_exists === false )
        {
            $business_events_page = array(
                'post_title'     => 'Business Events',
                'post_type'      => 'page',
                'post_name'      => 'business-events',
                'post_content'   => '[bh-events]',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_author'    => 1,
                'menu_order'     => 0,
            );
            $business_events_page_id = wp_insert_post( $business_events_page );
            if ($business_events_page_id) {
              Bizyhood_Utility::setOption(self::KEY_EVENTS_PAGE_ID, $business_events_page_id);
            }
        }
        
        // Create the guide page
        $business_guide_page         = Bizyhood_Utility::getOption(self::KEY_GUIDE_PAGE_ID);
        $business_guide_page_exists  = false;
        
        // check if the id exists and if the page has not been deleted
        if (intval($business_guide_page) > 0 && get_post_field('post_status', $business_guide_page) == 'publish') {
          $business_guide_page_exists = true;
        }
        
        if ( intval($business_guide_page) == 0 || $business_guide_page_exists === false )
        {
          $main_page_url = get_permalink(Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID));
          
          $default_categories_lists = '
            <h2>Popular Categories</h2>
            <ul class="bh-catslist">
              <li><a href="'.$main_page_url.'?keywords=Automobile">Auto Care</a></li>
              <li><a href="'.$main_page_url.'?keywords=Child Care">Childcare</a></li>
              <li><a href="'.$main_page_url.'?keywords=Entertainment">Entertainment</a></li>
              <li><a href="'.$main_page_url.'?keywords=Restaurants">Food &amp; Restaurants</a></li>
              <li><a href="'.$main_page_url.'?keywords=Home,Real Estate">Home</a></li>
              <li><a href="'.$main_page_url.'?keywords=Fitness,Recreation">Recreation</a></li>
            </ul>
          ';
          
            $business_guide_page = array(
                'post_title'     => 'Business Guide',
                'post_type'      => 'page',
                'post_name'      => 'business-guide',
                'post_content'   => '[bh-search]<h2>Featured Businesses</h2>[bh-guide]'.$default_categories_lists,
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_author'    => 1,
                'menu_order'     => 0,
            );
            $business_guide_page_id = wp_insert_post( $business_guide_page );
            if ($business_guide_page_id) {
              Bizyhood_Utility::setOption(self::KEY_GUIDE_PAGE_ID, $business_guide_page_id);
            }
        }
        
        
        // move logs to uploads if they are still on the old location
        
        // Get array of all source files
        $delete = array();
        $files = array();
        $old_path = dirname(__FILE__) . '/Logs/';
        if (file_exists($old_path)) {
          $files = scandir($old_path);
        }
        $copy_problem = 0;
        
        if ($files && !empty($files)) {
          
          $wp_upload_dir = wp_upload_dir();
          $source = $old_path;
          $destination = $wp_upload_dir['basedir'] . '/bizyhood/Logs/';

          foreach ($files as $file) {
            if (in_array($file, array(".",".."))) continue;

            if (copy($source.$file, $destination.$file)) {
              $delete[] = $source.$file;
            } else {
              $copy_problem = 1;
            }
          }
          // Delete all successfully-copied files
          if (!empty($delete)) {
            foreach ($delete as $file) {
              unlink($file);
            }
          }
          if ($copy_problem == 0) {
            rmdir($source);
          }
        }

    }

    public function uninstall()
    {
        Bizyhood_Log::add('debug', "Bizyhood uninstalling");
        
        // DO NOT DELETE PAGES // LET PUBLISHERS DO THIS MANUALLY
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
        
        # -- Below is core functionality --        
        
        // check if we need to reinitialize
        if (is_admin()) {
          add_action( 'init', array($this, 'reinitialize') );
        }
        
        add_action('admin_menu', 	array($this, 'adminCallback'));
        add_action('admin_init', 	array($this, 'adminInitCallback'));
        add_action('wp_enqueue_scripts', 	array($this, 'load_plugin_styles'));
        add_action('wp_enqueue_scripts', 	array($this, 'load_plugin_gallery'));
        add_action('wp_enqueue_scripts', 	array($this, 'load_plugin_analytics'));
        add_filter('the_content', array($this, 'postTemplate'), 100);
        add_action('wp_ajax_bizyhood_save_settings', array('Bizyhood_Ajax', 'Bizyhood_saveSettings'));
        
        // add shortcodes        
        $Bizyhood_Shortcodes = new Bizyhood_Shortcodes;
        
        add_shortcode('bh-businesses', array($Bizyhood_Shortcodes, 'businesses_shortcode'));
        add_shortcode('bh-promotions', array($Bizyhood_Shortcodes, 'promotions_shortcode'));
        add_shortcode('bh-events', array($Bizyhood_Shortcodes, 'events_shortcode'));
        add_shortcode('bh-guide', array($Bizyhood_Shortcodes, 'guide_shortcode'));
        add_shortcode('bh-search', array($Bizyhood_Shortcodes, 'search_shortcode'));
        add_shortcode('bh-group', array($Bizyhood_Shortcodes, 'businesses_group'));
        
        
        // create rewrite rule for single business
        add_filter('rewrite_rules_array', array($this, 'bizyhood_add_rewrite_rules'));
        // hook add_query_vars function into query_vars
        add_filter('query_vars', array($this, 'bizyhood_add_query_vars'));
        // check if a flush is needed
        add_action( 'wp_loaded', array($this, 'bizyhood_flush_rules') );
        
        // add SEO plugins support
        $Bizyhood_Sitemap = new Bizyhood_Sitemap;
        
        // Yoast SEO additions START
        
        add_action( 'init', array( $Bizyhood_Sitemap, 'sitemap_init' ), 10 );
        add_action('wpseo_do_sitemap_bizyhood-sitemap', array($Bizyhood_Sitemap, 'bizyhood_create_sitemap') );
        add_filter( 'wpseo_sitemap_index', array($Bizyhood_Sitemap, 'bizyhood_addtoindex_sitemap') );
        
        // Yoast SEO additions END      

        // AIOSP START
        
        add_filter( 'aiosp_sitemap_extra', array( $Bizyhood_Sitemap, 'aiosp_sitemap_init' ), 10 );
        add_filter( 'aiosp_sitemap_custom_bizyhood', array( $Bizyhood_Sitemap, 'bizy_add_aioseo_pages' ), 10, 3 );
        add_filter( 'aiosp_sitemap_addl_pages', array( $Bizyhood_Sitemap, 'bizy_add_aioseo_pages' ), 10, 1 );
        
        // AIOSP END
        
        
        // editor bizybutton START
        
        $Bizyhood_Bizybox = new Bizyhood_Bizybox();
        
        add_action('admin_head', array( $Bizyhood_Bizybox, 'bizy_add_bizylink_button'));
        
        add_action( 'wp_ajax_bizylink_insert_dialog', array( $Bizyhood_Bizybox, 'bizylink_insert_dialog' ));
        add_action( 'wp_ajax_bizylink_business_results', array( $Bizyhood_Bizybox, 'bizylink_business_results' ));

        // editor bizybutton END

        // add settings link on plugins list
        add_filter( 'plugin_action_links_' . str_replace('Bizyhood/Core.php', 'bizyhood.php', plugin_basename(__FILE__)), array( $this, 'bizyhood_plugin_action_links') );
        
        // load widgets START
        
        Bizyhood_View::load( 'widgets/search', array(), false, true);
        Bizyhood_View::load( 'widgets/meet_the_merchant', array(), false, true);
        Bizyhood_View::load( 'widgets/promotions', array(), false, true);
        Bizyhood_View::load( 'widgets/events', array(), false, true);
        add_action( 'widgets_init', array( $this, 'register_search_widget' ));
        add_action( 'widgets_init', array( $this, 'register_mtm_widget' ));
        add_action( 'widgets_init', array( $this, 'register_promotions_widget' ));
        add_action( 'widgets_init', array( $this, 'register_events_widget' ));
      
        // load widgets END

        
        // add oAuth Data START
        
        add_action( 'template_redirect', array('Bizyhood_oAuth', 'set_oauth_temp_data') );
        
        // add oAuth Data END
        
        
        // admin notices START
        
        add_action( 'admin_notices', array( $this, 'set_bizyhood_admin_notices' ));
        
        // admin notices END
        
        
        // remove empty paragraphs START

        add_action( 'template_redirect', array( $this, 'remove_empty_paragraphs' ));
        
        // remove empty paragraphs END
        
        
        // meta START

        $Bizyhood_Meta = new Bizyhood_Meta();

        add_action('wp_loaded', array( $Bizyhood_Meta, 'buffer_start'), 100000);    
        add_action('shutdown', array( $Bizyhood_Meta, 'buffer_end'), 100000);       
        
        // meta END
        
        // rss feed START
        
        add_action('wp', array( $this, 'load_rss'));
        
        // rss feed END
        
        
        // question cta form START
        
        $Bizyhood_CTA = new Bizyhood_CTA();
        
        add_action( 'wp_ajax_question_cta', array( $Bizyhood_CTA, 'question_cta') );
        add_action( 'wp_ajax_nopriv_question_cta', array( $Bizyhood_CTA, 'question_cta') );
        
        // question cta form END

    }
    
    function reinitialize() {
      
      // nothing to do if this is not admin
      if (!is_admin()) { 
        return; 
      }
      
      // check if the pages already exist and if not add them       
      // check version
      if( !Bizyhood_Utility::getOption(self::KEY_VERSION) ||  Bizyhood_Utility::getOption(self::KEY_VERSION) != BIZYHOOD_VERSION ) {
        Bizyhood_Utility::setOption(self::KEY_VERSION, BIZYHOOD_VERSION);
        self::install('initializing');
      }
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
    function register_events_widget() {
      register_widget( 'bizy_events_widget' );
    }

    
    
    function remove_empty_paragraphs() {
      
      // if it is not a bizyhood page there is nothign to do
      if ( is_admin() || !( Bizyhood_Utility::is_bizyhood_page() ) ) {
        return;
      }
      
      // get all filters
      global $wp_filter;
  
      // loop through filters
      foreach ($wp_filter['the_content'] as $priority => $filter_array) {
        
        foreach  ($filter_array as $filter_function => $filter) {
          
          if ($filter_function == 'wpautop' || $filter_function == 'aw_formatter') {
            
            remove_filter('the_content', $filter_function, $priority);
            
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
        if (is_wp_error($authetication) || $authetication === false) {
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
    
    
    
    
    
    function bizyhood_add_query_vars($aVars) 
    {
      $aVars[] = "bizyhood_id"; // represents the id of the business
      $aVars[] = "bizyhood_name"; // represents the name of the business
      return $aVars;
    }
     
    function bizyhood_add_rewrite_rules($wr_rules)
    {
      
      
      $bizy_rules = array(get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_OVERVIEW_PAGE_ID)).'/([^/]+)/([^/]+)/?$' => 'index.php?pagename='. get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_OVERVIEW_PAGE_ID)) .'&bizyhood_name=$matches[1]&bizyhood_id=$matches[2]');
      $wr_rules = $bizy_rules + $wr_rules;
      
      $promo_rules = array(get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_PROMOTIONS_PAGE_ID)).'/([^/]+)/?$' => 'index.php?pagename='. get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_PROMOTIONS_PAGE_ID)) .'&bizyhood_name=$matches[1]');
      $wr_rules = $promo_rules + $wr_rules;
      
      $promo_rules_single = array(get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_PROMOTIONS_PAGE_ID)).'/([^/]+)/([^/]+)/?$' => 'index.php?pagename='. get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_PROMOTIONS_PAGE_ID)) .'&bizyhood_name=$matches[1]&bizyhood_id=$matches[2]');
      $wr_rules = $promo_rules_single + $wr_rules;
      
      $events_rules = array(get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_EVENTS_PAGE_ID)).'/([^/]+)/?$' => 'index.php?pagename='. get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_EVENTS_PAGE_ID)) .'&bizyhood_name=$matches[1]');
      $wr_rules = $events_rules + $wr_rules;
      
      $events_rules_single = array(get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_EVENTS_PAGE_ID)).'/([^/]+)/([^/]+)/?$' => 'index.php?pagename='. get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_EVENTS_PAGE_ID)) .'&bizyhood_name=$matches[1]&bizyhood_id=$matches[2]');
      $wr_rules = $events_rules_single + $wr_rules;
      
      return $wr_rules;
    }

    // flush_rules() if our rules are not yet included
    function bizyhood_flush_rules(){
      
      $wr_rules = get_option( 'rewrite_rules' );
      
      // check if the rule already exits and if not then flush the rewrite rules
      if (  ! isset( $wr_rules[get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_OVERVIEW_PAGE_ID)).'/([^/]+)/([^/]+)/?$'] ) || 
            ! isset( $wr_rules[get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_PROMOTIONS_PAGE_ID)).'/([^/]+)/?$'] ) || 
            ! isset( $wr_rules[get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_PROMOTIONS_PAGE_ID)).'/([^/]+)/([^/]+)/?$'] ) || 
            ! isset( $wr_rules[get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_EVENTS_PAGE_ID)).'/([^/]+)/?$'] ) || 
            ! isset( $wr_rules[get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_EVENTS_PAGE_ID)).'/([^/]+)/([^/]+)/?$'] )
          ) {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
      }
    }
    
        
    
    function load_plugin_styles()
    {
        // check if shortcode exists
        global $post, $wpdb;
        
        // determine whether this page contains "bh-group" shortcode
        $shortcode_found = false;
        if ($post && isset($post->post_content) && has_shortcode($post->post_content, 'bh-group')) {         
          $shortcode_found = true;
        }
      
        if (Bizyhood_Utility::is_bizyhood_page() || $shortcode_found == true) {
          wp_enqueue_style ('bizyhood-bootstrap-styles', Bizyhood_Utility::getCSSBaseURL() . 'bootstrap.min.css', array(), self::BOOTSTRAP_VERSION);
          wp_enqueue_style ('bizyhood-plugin-styles',  Bizyhood_Utility::getCSSBaseURL() . 'plugin.css', array(), BIZYHOOD_VERSION);
          wp_enqueue_style ('socicon-styles',  Bizyhood_Utility::getCSSBaseURL() . 'socicon.css', array(), BIZYHOOD_VERSION);
        }
        wp_enqueue_style ('bizyhood-icons-styles',  'https://d17bale0hcbyzh.cloudfront.net/bizyhood/styles/entypo/entypo-icon-fonts.css?family=entypoplugin.css', array(), BIZYHOOD_VERSION);
        wp_enqueue_style ('bizyhood-plugin-global-styles',  Bizyhood_Utility::getCSSBaseURL() . 'plugin-global.css', array(), BIZYHOOD_VERSION);
    }
    
    function load_plugin_gallery()
    {
        wp_enqueue_style ('photoswipe-css',  Bizyhood_Utility::getVendorBaseURL() . 'photoswipe/css/photoswipe.css', array(), BIZYHOOD_VERSION);
        wp_enqueue_style ('photoswipe-css-default-skin',  Bizyhood_Utility::getVendorBaseURL() . 'photoswipe/css/default-skin/default-skin.css', array('photoswipe-css'), BIZYHOOD_VERSION);
        wp_enqueue_script('photoswipe-js', Bizyhood_Utility::getVendorBaseURL() . 'photoswipe/js/photoswipe.min.js', array(), BIZYHOOD_VERSION, true);
        wp_enqueue_script('photoswipe-ui-js', Bizyhood_Utility::getVendorBaseURL() . 'photoswipe/js/photoswipe-ui-default.js', array('photoswipe-js'), BIZYHOOD_VERSION, true);
        wp_enqueue_script('bizyhood-gallery-js', Bizyhood_Utility::getJSBaseURL() . 'bizyhood-plugin-gallery.js', array(), BIZYHOOD_VERSION, true);
        wp_enqueue_script('bizyhood-matchHeight-js', Bizyhood_Utility::getJSBaseURL() . 'jquery.matchHeight-min.js', array(), BIZYHOOD_VERSION, true);
        wp_enqueue_script('bootstrap-min-js', Bizyhood_Utility::getJSBaseURL() . 'bootstrap.min.js', array(), BIZYHOOD_VERSION, true);
        wp_enqueue_script('bizyhood-custom-js', Bizyhood_Utility::getJSBaseURL() . 'bizyhood-custom.js', array(), BIZYHOOD_VERSION, true);
        
        // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
        wp_localize_script( 'bizyhood-custom-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    }
    
    function load_plugin_analytics()
    {
        wp_enqueue_script('bizyhood-segment-js', Bizyhood_Utility::getJSBaseURL() . 'bizyhood-segment-load.js', array(), BIZYHOOD_VERSION);
        if (Bizyhood_Utility::getApiProduction() == true) {
            $segment_api_key = 'a8yEWAUktJ3QtLruFMGqXIWnjGO1qoys';
        }else {
            $segment_api_key = '749WYOtudlvSDMYvverj7tObiaRI71ua';
        }
        
        $analytics_settings_array = array(
            'segment_api_key' => $segment_api_key
        );
        
        wp_localize_script( 'bizyhood-segment-js', 'analytics_settings', $analytics_settings_array );
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
            wp_enqueue_script('Bizyhood-main'  ,  Bizyhood_Utility::getJSBaseURL().'bizyhood.js?v='. BIZYHOOD_VERSION, array( 'wp-color-picker' ));
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
        
        if (isset($_GET['page']) && $_GET['page'] == 'Bizyhood') {
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
        $data['overview_page_id']   = Bizyhood_Utility::getOption(self::KEY_OVERVIEW_PAGE_ID);
        $data['signup_page_id']     = Bizyhood_Utility::getOption(self::KEY_SIGNUP_PAGE_ID);
        $data['promotions_page_id'] = Bizyhood_Utility::getOption(self::KEY_PROMOTIONS_PAGE_ID);
        $data['events_page_id']     = Bizyhood_Utility::getOption(self::KEY_EVENTS_PAGE_ID);
        $data['guide_page_id']      = Bizyhood_Utility::getOption(self::KEY_GUIDE_PAGE_ID);
        $data['btn_bg_color']       = Bizyhood_Utility::getOption(self::BTN_BG_COLOR);
        $data['btn_font_color']     = Bizyhood_Utility::getOption(self::BTN_FONT_COLOR);
        $data['bh_facebook']        = Bizyhood_Utility::getOption(self::ICON_FACEBOOK);
        $data['bh_twitter']         = Bizyhood_Utility::getOption(self::ICON_TWITTER);
        $data['bh_google']          = Bizyhood_Utility::getOption(self::ICON_GOOGLE);
        $data['bh_linkedin']        = Bizyhood_Utility::getOption(self::ICON_LINKEDIN);
        $data['bh_mail']            = Bizyhood_Utility::getOption(self::ICON_MAIL);
        $data['bh_icon_placement']  = Bizyhood_Utility::getOption(self::ICON_PLACEMENT);
        $data['errors']             = array();

        if(!function_exists('curl_exec'))
        {
            $data['errors'][] = 'Bizyhood requires the PHP cURL module to be enabled. You may need to ask your web host or developer to enable this.';
        }
        
        if(get_category_by_slug(get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_OVERVIEW_PAGE_ID))))
        {
            $data['errors'][] = 'You have a category named "'. get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_OVERVIEW_PAGE_ID)) .'", which will interfere with the business directory if you plan to use it. You must rename the slug of this category.';
        }
        
        if(get_category_by_slug(get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID))))
        {
            $data['errors'][] = 'You have a category named "'. get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID)) .'", which will interfere with the business directory if you plan to use it. You must rename the slug of this category.';
        }
        if(get_category_by_slug(get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_GUIDE_PAGE_ID))))
        {
            $data['errors'][] = 'You have a category named "'. get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_GUIDE_PAGE_ID)) .'", which will interfere with the business directory if you plan to use it. You must rename the slug of this category.';
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
    
    
    /**
     * Sets and returns cached API results as a transient
     * @param string $transient_name The name of the transient to get or set
     * @param string $transient_key The name of the api result key that will set the value of the key
     * @param string $method_name The name of the function to call to get the API data
     * @param array $attrs Attributes for the $method_name
     * @param string $method_command The name of the command to be execute by the API
     * @param boolean $random Either to return on random result or all of them
     * @return array transient result(s) or false
     */
    public static function get_cache_value($transient_name, $transient_key = 'response_json', $method_name, $attrs, $method_command = null, $random = false) {
      
      // cache the results
      if (get_transient($transient_name) === false || get_transient($transient_name) == '') {
        
        // get businesses

        $transient = Bizyhood_Api::$method_name($attrs, $method_command);
        
        set_transient($transient_name, $transient[$transient_key], self::API_CACHE_TIME);
      }
      
      $cached_transient = get_transient($transient_name);
      
      if (!is_array($cached_transient)) {
        return false;
      }
      
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
          if (is_wp_error($authetication) || $authetication === false) {
            return Bizyhood_View::load( 'listings/error', array( 'error' => 'Can not authenticate to the Bizyhood API'), true );
          }
        }

        # Override content for the view business page        
        $post_name = $post->post_name;
        if ($post_name === get_post_field('post_name', Bizyhood_Utility::getOption(self::KEY_OVERVIEW_PAGE_ID)))
        {
            $signup_page_id = Bizyhood_Utility::getOption(self::KEY_SIGNUP_PAGE_ID);
            $list_page_id = Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID); 
            
            $single_business_information = Bizyhood_Api::get_business_details();
                                    
            if ($single_business_information === NULL) {
              $business_view_page = get_page_by_path( 'business-directory' );
              wp_safe_redirect( get_permalink($business_view_page) );
              die;
            }
            if ($single_business_information === false) {
              
              return Bizyhood_View::load( 'listings/error', array( 'error' => __( 'Service is currently unavailable! Request timed out.', 'bizyhood' )), true );
              
            } elseif($single_business_information->bizyhood_id == '') {
              
              $wp_query->set_404();
              status_header( 404 );
              nocache_headers();
              return Bizyhood_View::load( 'listings/error', array( 'error' => __( 'The business you requested can not be found.', 'bizyhood' ), 'noheader' => true), true );
              
            }else {
              $business = $single_business_information;
            }
            
            // get buttons color settings
            $colors = array(
              'bg'        => Bizyhood_Utility::getOption(self::BTN_BG_COLOR),
              'font'      => Bizyhood_Utility::getOption(self::BTN_FONT_COLOR),
              'style'     => '',
              'stylefont' => '',
              'stylebg'   => '',
            );
            
            if ($colors['bg'] != '' || $colors['font'] != '') {
              $colors['style'] = 'style="'. ($colors['bg'] != '' ? 'background-color: '.$colors['bg'].' !important; border-color: '.$colors['bg'].' !important; ' : '') .''. ($colors['font'] != '' ? 'color: '.$colors['font'].' !important;' : '') .'"';
            }
            
            if ($colors['font'] != '') {
              $colors['stylefont'] = 'style="color: '.$colors['font'].' !important;"';
            }
            
            if ($colors['bg'] != '') {
              $colors['stylebg'] = 'style="background-color: '.$colors['bg'].' !important;"';
            }
            
            // get promotions and events only for claimed businesses

            $events     = Bizyhood_Api::get_business_related_content('events', $business->bizyhood_id);
            $promotions = Bizyhood_Api::get_business_related_content('promotions', $business->bizyhood_id);

            if ($events !== false && !empty($events)) {
              $latest_event = $events[0];
            } else {
              $latest_event = '';
            }

            if ($promotions !== false && !empty($promotions)) {
              $latest_promotion = $promotions[0];
            } else {
              $latest_promotion = '';
            }
            
            
            $top_columns            = 12;
            $top_columns_count      = 0;
            $location_column_width  = 6;
            $show_contact_details   = false;
            $show_first_row          = false;
            
            if ($business->claimed == 1) {
              if (isset($latest_event) && !empty($latest_event)) {
                $top_columns_count++;
              }
              if (isset($business->news) && !empty($business->news)) {
                $top_columns_count++;
              }
              if (isset($latest_promotion) && !empty($latest_promotion)) {
                $top_columns_count++;
              }

              if ($top_columns_count > 0) {
                $show_first_row = true;
                $top_columns = $top_columns / $top_columns_count; // avoid devision by zero
              }
              
              
            }
            
            if(
              (!isset($business->hours) || empty($business->hours)) && 
              (!isset($business->telephone) || empty($business->telephone)) && 
              (!isset($business->website) || empty($business->website)) && 
              (!isset($business->social_networks) || empty($business->social_networks))
              ) 
            {
              $location_column_width = 12;
            }
            
            if( $business->telephone || $business->website || $business->social_networks) {
              $show_contact_details = true;
            }
              
            $defaut_args = array(
              'content' => $content, 
              'business' => $business, 
              'signup_page_id' => $signup_page_id, 
              'list_page_id' => $list_page_id, 
              'colors' => $colors, 
              'top_columns' => $top_columns, 
              'show_first_row' => $show_first_row, 
              'location_column_width' => $location_column_width, 
              'show_contact_details' => $show_contact_details,
              'latest_event' => $latest_event,
              'latest_promotion' => $latest_promotion,
              'url' => get_permalink( Bizyhood_Utility::getOption(self::KEY_OVERVIEW_PAGE_ID) ).$business->slug.'/'.$business->bizyhood_id.'/'
            );
            
            return Bizyhood_View::load('listings/single/default', $defaut_args, true);
            
        }

        return $content;
    }

    
    function bizyhood_plugin_action_links( $links ) {
       $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=Bizyhood') ) .'">'. __('Settings', 'bizyhood') .'</a>';
       return $links;
    }
    
    
    function load_rss() {
      
      global $wp_query;
      
      $events_page = Bizyhood_Utility::getOption(self::KEY_EVENTS_PAGE_ID);
      $promotions_page = Bizyhood_Utility::getOption(self::KEY_PROMOTIONS_PAGE_ID);
      if (is_page($events_page)) {
        $current_page = 'events';
      } elseif (is_page($promotions_page)) {
        $current_page = 'promotions';
      } else {
        return;
      }

      $bizyhood_name = (isset($wp_query->query_vars['bizyhood_name']) ? urldecode($wp_query->query_vars['bizyhood_name']) : false);
      $bizyhood_id = (isset($wp_query->query_vars['bizyhood_id']) ? urldecode($wp_query->query_vars['bizyhood_id']) : false);
              
      if ($bizyhood_name == self::RSS_SUFFIX || $bizyhood_id == self::RSS_SUFFIX) {
        
        
        
        
        
        // init variable
        $business_name = '';
        
        $authetication = Bizyhood_oAuth::set_oauth_temp_data();
        if (is_wp_error($authetication) || $authetication === false) {
          Bizyhood_View::load( 'rss/error', array( 'error' => 'Can not authenticate to the Bizyhood API'), false );
          die;
        }

        $atts = array();
        // cache the results
        $cached_results = self::get_cache_value('bizyhood_'. $current_page .'_widget', 'response_json', 'get_all_content_by_type', $atts, $current_page);
              
        if ($cached_results === false) {
          $signup_page_id = Bizyhood_Utility::getOption(self::KEY_SIGNUP_PAGE_ID);
          $errormessage = 'sign up or login to Bizyhood';
          
          Bizyhood_View::load( 'rss/error', array( 'error' => $errormessage), false );
          die;
        }
        
        
        $list_page_id = Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID);
        
        
        if (isset($wp_query->query_vars['bizyhood_name']) && $wp_query->query_vars['bizyhood_name'] != self::RSS_SUFFIX ) {
          $results = Bizyhood_Api::get_business_related_content($current_page, $wp_query->query_vars['bizyhood_name']);
          if ($results !== false && !empty($results)) {
            $cached_results = json_decode(json_encode($results), true); // convert to array and replace results
            $business_name = $cached_results[0]['business_name'];
          }
        }

        $results_args = array( 
          'data' => $cached_results, 
          'list_page_id' => $list_page_id, 
          'business_name' => $business_name
        );
      
        Bizyhood_View::load('rss/'.$current_page, $results_args);
        die;
      }
        
      
    }
    
}

endif;