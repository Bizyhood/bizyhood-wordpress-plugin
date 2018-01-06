<?php

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/**
 * This class contains all Bizyhood Shortcodes.
 */
 
class Bizyhood_Sitemap
{
  
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
    
    $WPSEO_Sitemaps_Renderer = new WPSEO_Sitemaps_Renderer();
    
    $wpseo_sitemaps->set_sitemap( $this->bizyhood_create_sitemap() );
    $WPSEO_Sitemaps_Renderer->set_stylesheet( '<?xml-stylesheet type="text/xsl" href="' . preg_replace( '/(^http[s]?:)/', '', esc_url( home_url( 'main-sitemap.xsl' ) ) ) . ' "?>' );
  }
  
  
  public function bizyhood_create_sitemap() {
      
    // get only verified businesses
    $urls = $this->bizyhood_create_all_urls(true);
    
    if (empty($urls)) {
      return false;
    }
    
    $WPSEO_Sitemaps = new WPSEO_Sitemaps_Renderer();
    
    
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
  
  
  public function bizyhood_create_all_urls($verified = false) {
      
      $yoastoptions = WPSEO_Options::get_all();
      $max_entries  = $yoastoptions['entries-per-page']; // get the limit of urls per sitemap page
      $sitemapnum   = (get_query_var( 'sitemap_n' ) ? get_query_var( 'sitemap_n' ) : 1); // get the sitemap number / page
      $urlbase      = get_permalink( Bizyhood_Utility::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID) );
      $date         = date("Y-m-d H:i");

      $urls         = array(); // initialize URLs array
      $apimax       = Bizyhood_Core::API_MAX_LIMIT; // set the max we can get from the API in one fetch
      $urlindex     = 0; // help index the urls array
	  
           
      // if yoast is set to grab per sitemap more than $apimax (Bizyhood_Core::API_MAX_LIMIT) results
      if ($max_entries > $apimax) {
        $ps = $apimax;
        $query_params = array('paged' => 1, 'verified' => $verified, 'ps' => $ps);
        $queryapi = Bizyhood_Api::businesses_search($query_params);
        
		$count = $queryapi['total_count']; // get the number of results
        
        // max number of pages
        $maxsitemapnum = (int) ceil( $count / $max_entries );
        // get bizyhod page to start
        $start  = (($sitemapnum - 1) * $max_entries / $apimax == 0 ? 1 : ceil(($sitemapnum - 1) * $max_entries / $apimax));
        // get bizyhod page to end
        $end  = ceil($sitemapnum * $max_entries / $apimax);
                
        // we only have LESS than $apimax (Bizyhood_Core::API_MAX_LIMIT) then get only the first query results
        if ($queryapi['total_count'] <= $apimax && $sitemapnum == 1) {
          
          if (!empty($queryapi['businesses'])) {
            foreach($queryapi['businesses'] as $business) {
              $urls[$urlindex]['url'] = $urlbase.$business->slug.'/'.$business->bizyhood_id.'/';
              $urls[$urlindex]['date'] = $date;
              $urlindex++;
            }
          
            return $urls;
          } else {
            
            // nothing to return, no urls found
            return;
          }
          
        }
        
        // we have MORE than $apimax (Bizyhood_Core::API_MAX_LIMIT) results than we can get at once from the API
        if ($queryapi['total_count'] > $apimax) {
          
          $bizyresults = 0; // results that we have already fetch
          $bizypaged = $start;
          while ($bizyresults < $max_entries && $queryapi['total_count'] > $bizyresults && $bizypaged <= $end) {
                        
            $query_params = array('paged' => $bizypaged, 'verified' => $verified, 'ps' => $ps);
            $queryapi = Bizyhood_Api::businesses_search($query_params);

            
            if (!empty($queryapi['businesses'])) {
              
              // remove the first n from the array if we have it already on the previous xml page
              // only on the first loop and not on the first page
              $remove_from_start = ($max_entries % $apimax) * ($sitemapnum - 1);
              
              
              if ($remove_from_start >= 0 && $bizyresults == 0 && $sitemapnum > 1) {
                
                $queryapi['businesses'] = array_slice($queryapi['businesses'], $remove_from_start);
                
              }
              
              foreach($queryapi['businesses'] as $business) {
                
                if ($urlindex == $max_entries) { break 2; }
                
                $urls[$urlindex]['url'] = $urlbase.$business->slug.'/'.$business->bizyhood_id.'/';
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
        
      // if yoast is set to grab per sitemap less than $apimax (Bizyhood_Core::API_MAX_LIMIT) results, then we just follow the yoast pagination
      } else {
        
        $ps = $max_entries;
        $query_params = array('paged' => $sitemapnum, 'verified' => $verified, 'ps' => $ps);
        $queryapi = Bizyhood_Api::businesses_search($query_params);
        
        if (!empty($queryapi['businesses'])) {
          foreach($queryapi['businesses'] as $business) {

			$urls[$urlindex]['url'] = $urlbase.$business->slug.'/'.$business->bizyhood_id.'/';
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
    
    
    
    // add sitemap to index
    function bizyhood_addtoindex_sitemap() {
      
      $getfirstpage = Bizyhood_Api::businesses_search(array('paged' => 1, 'verified' => TRUE, 'ps' => Bizyhood_Core::API_MAX_LIMIT));
      $count  = $getfirstpage['total_count'];
      $yoastoptions = WPSEO_Options::get_all();
      $max_entries  = $yoastoptions['entries-per-page'];
      $sitemap = '';
      
      // if we need to split the sitemaps
      if ($count > $max_entries) {
        
        $n = (int) ceil( $count / $max_entries );
        for ( $i = 1; $i <= $n; $i ++ ) {
          
          $sitemap  .= '<sitemap>' . "\n";
          $sitemap .= '<loc>' . WPSEO_Sitemaps_Router::get_base_url( 'bizyhood-sitemap' . $i . '.xml' ) . '</loc>' . "\n";
          $sitemap .= '<lastmod>' . htmlspecialchars( date("c") ) . '</lastmod>' . "\n";
          $sitemap .= '</sitemap>' . "\n";
          
        }
        
      } else { // create just one
      
        $sitemap  = '<sitemap>' . "\n";
        $sitemap .= '<loc>' . WPSEO_Sitemaps_Router::get_base_url( 'bizyhood-sitemap.xml' ) . '</loc>' . "\n";
        $sitemap .= '<lastmod>' . htmlspecialchars( date("c") ) . '</lastmod>' . "\n";
        $sitemap .= '</sitemap>' . "\n";
        
      }
      return $sitemap;
    }
    
    
    
    function aiosp_sitemap_init($extra) {
            
      $extra[] = 'bizyhood';
      
      return $extra;
    }
    
    
    function bizy_add_aioseo_pages( $pages ) {
      
      // initialize array
      if ( empty( $pages ) ) $pages = Array();
      
      $queryapi = Bizyhood_Api::businesses_search(array('paged' => 1, 'verified' => TRUE, 'ps' => Bizyhood_Core::API_MAX_LIMIT));
      $numofpages = floor($queryapi['total_count'] / $queryapi['page_size']);
      $urlbase = get_permalink( Bizyhood_Utility::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID) );
      $date = date("Y-m-d H:i");
      $count  = $queryapi['total_count']; // get the number of results // 492
      
      $start = 1;
      
      // get first 12 urls to save an API request
      if ($start == 1) {
        foreach($queryapi['businesses'] as $business) {		  
          $pages[] = Array( "loc" => $urlbase.$business->slug.'/'.$business->bizyhood_id.'/', "lastmod" => $date, "changefreq" => "weekly", "priority" => "0.6" );
        }
      }
      
      // get the rest of the urls if they exist
      $i = $start + 1; // start  to query the API from the second batch
      while($i <= $numofpages) {
        $queryapi = Bizyhood_Api::businesses_search(array('paged' => $i, 'verified' => TRUE, 'ps' => Bizyhood_Core::API_MAX_LIMIT));
        foreach($queryapi['businesses'] as $business) {
          $pages[] = Array( "loc" => $urlbase.$business->slug.'/'.$business->bizyhood_id.'/', "lastmod" => $date, "changefreq" => "weekly", "priority" => "0.6" );
        }
        $i++;
      }
      
      return $pages;
    }
    
} 
 
?>