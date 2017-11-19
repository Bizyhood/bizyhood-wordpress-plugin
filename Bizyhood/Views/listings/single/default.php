<?php
  
  // Get lat and long by address         
  $address = urlencode($business->address1).'+'.urlencode($business->locality).'+'.urlencode($business->region).'+'.urlencode($business->postal_code);
  
  // check and create the backlink
  $backlink = wp_get_referer();
  if (wp_get_referer() == get_site_url() . $_SERVER["REQUEST_URI"] || wp_get_referer() == false) {
    $backlink = get_permalink(Bizyhood_Utility::getOption(Bizyhood_Core::KEY_MAIN_PAGE_ID));
  }
  
?>
<div class="bizyhood_wrap <?php if ($business->claimed == 1) { ?> claimed <?php } else { ?> unclaimed <?php } ?>" itemscope itemtype="http://schema.org/LocalBusiness">
  <div class="row zero-gutter bh_headline_row">
    <?php if (Bizyhood_Utility::getOption(Bizyhood_Core::ICON_PLACEMENT) == 'before' || Bizyhood_Utility::getOption(Bizyhood_Core::ICON_PLACEMENT) == 'both') { ?>
    <div class="col-md-12">
      <?php echo Bizyhood_Utility::buildShareIcons($url, $business->name, $business->description,$business->business_logo->url); ?>
    </div>
    <?php } ?>
    <div class="col-md-9">
      <h2 itemprop="name"><?php echo $business->name ?></h2>
    </div>
    <div class="col-md-3">
      <a href="<?php echo $backlink; ?>" class="btn-inline pull-right hidden-xs hidden-sm"><span class="entypo-left" aria-hidden="true"></span> Back</a>
    </div>
  </div>
      
  <div class="row zero-gutter">
    <div class="col-md-12">
      <?php 
        if (!empty($business->category)) {
        ?>         
          <h3 class="bh_category"><span class="bh_category_title"><?php echo $business->category; ?></span></h3>
        <?php
          }
      ?>
    </div>
  </div>
  
  
<div class="rowgrid_wrap">

  <?php if ($business->claimed != 1) { ?>
  
  <div class="row rowgrid zero-gutter main_business_info sameheight">
    <div class="col-md-12 feedback_cta">
      <div class="bh_table">
        <div class="bh_tablerow">
          <div class="bh_tablecell">
          
            <div class="bh_alert text-center">
              <h4 class="h2">IS THIS YOUR BUSINESS?</h4>
              <a href="<?php echo $business->signup_url ?>" class="btn btn-info" <?php echo $colors['style']; ?> target="_blank">Claim it now!</a>
            </div>
          </div>  
        </div>
      </div>      
    </div><!-- /.col-md-12 // unclaimed business-->
  </div><!-- /.row -->
  <?php } ?>
  
  <?php if ($business->verified == 1) { ?>
  <div class="row rowgrid zero-gutter bh_infoboxes sameheight">
    <div class="col-md-12 question_cta bh_infobox">
      <div class="column-inner">
        <div class="bh_alert text-center">
          <?php echo __('Have a question?', 'bizyhood'); ?>
          <a id="id_question_cta_btn" class="btn btn-info question_cta_btn" <?php echo $colors['style']; ?> href="#" title="<?php echo __('Ask now!', 'bizyhood'); ?>"><?php echo __('Ask now!', 'bizyhood'); ?></a>
        </div>
      </div>
    </div>
  </div>
  
  <div id="question_cta_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Ask the business</h4>
        </div>
        <div class="modal-body">
          <form id="question_cta_form" data-business="<?php echo $business->bizyhood_id; ?>">
            <fieldset>
              <div class="form-group">
                <label for="question_cta_fname">First Name</label>
                <input id="question_cta_fname" name="question_cta_fname" class="form-control" type="text" />
              </div>
              <div class="form-group">
                <label for="question_cta_lname">Last Name</label>
                <input id="question_cta_lname" name="question_cta_lname" class="form-control" type="text" />
              </div>
              <div class="form-group">
                <label for="question_cta_email">Email</label>
                <input id="question_cta_email" name="question_cta_email" class="form-control" type="email" />
              </div>
              <div class="form-group">
                <label for="question_cta_message">Question</label>
                <textarea id="question_cta_message" name="question_cta_message" class="form-control"></textarea>
              </div>
              
              <button type="submit" class="btn btn-info" <?php echo $colors['style']; ?>>Submit</button>
            </fieldset>
          </form>
          
          <div class="response_area">
          
          </div>
        </div>
      </div>

    </div>
  </div>
  <?php } ?>
  
  <?php if ($business->claimed == 1 && $show_first_row === true) { ?>
  
  
  
  
  <div class="row rowgrid zero-gutter bh_infoboxes sameheight">
  
    <?php if (isset($latest_promotion) && !empty($latest_promotion)) { ?>
      <div class="col-md-<?php echo $top_columns; ?> latest_promotion bh_infobox">
        <h3>Promotions</h3>
        <div class="column-inner">
            
            <div class="bh_alert">
              <h4><?php echo $latest_promotion->name; ?></h4>
              <dl class="bh_dl-horizontal">
                <dt>Date</dt><br />
                <dd><?php echo Bizyhood_Utility::buildDateTextMicrodata($latest_promotion->start, $latest_promotion->end, 'promotion', 'promotions'); ?></dd>
              </dl>
              <a href="<?php echo get_permalink(Bizyhood_Utility::getOption(Bizyhood_Core::KEY_PROMOTIONS_PAGE_ID)).$business->bizyhood_id.'/'.$latest_promotion->identifier; ?>" title="<?php echo $latest_promotion->name; ?> details">View Details &rarr;</a>
            </div>
            
            <a itemprop="url" class="btn btn-info" <?php echo $colors['style']; ?> href="<?php echo get_permalink(Bizyhood_Utility::getOption(Bizyhood_Core::KEY_PROMOTIONS_PAGE_ID)).$business->bizyhood_id.'/'; ?>" title="All <?php echo $business->name; ?> promotions">All Promotions</a>
        </div>
      </div>
    <?php } ?>
  
    <?php if (isset($latest_event) && !empty($latest_event)) { ?>
      <div class="col-md-<?php echo $top_columns; ?> latest_events bh_infobox event-wrapper" itemscope itemtype="http://schema.org/Event">
            
        <h3>Upcoming Events</h3>
        <div class="column-inner">
        
            <span class="hidden" itemprop="location" itemscope itemtype="http://schema.org/Place">
              <span itemprop="name"><?php echo $business->name ?></span>
              <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                <span itemprop="streetAddress"><?php echo $latest_event->address1.', '. $latest_event->address2 ?></span><br />
                <span itemprop="addressLocality"><?php echo $latest_event->locality ?></span>,
                <span itemprop="addressRegion"><?php echo $latest_event->region ?></span>
                <span itemprop="postalCode"><?php echo $latest_event->postal_code ?></span>
              </span>
            </span>

            <span class="hidden">
              <img itemprop="image" src="<?php echo $business->business_logo->url ?>"/>
              <span itemprop="description"><?php echo $latest_event->description; ?></span>
              <time itemprop="endDate" datetime="<?php echo date('c', strtotime($latest_event->end));?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $latest_event->end ) ); ?></time>
            </span>

            <div class="bh_alert">
              <h4 itemprop="name"><?php echo $latest_event->name; ?></h4>
              <dl class="bh_dl-horizontal">
                <dt>Date</dt><br />
                <dd><time itemprop="startDate" datetime="<?php echo date('c', strtotime($latest_event->start));?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $latest_event->start ) ); ?></time></dd>
              </dl>
              <a itemprop="url" href="<?php echo get_permalink(Bizyhood_Utility::getOption(Bizyhood_Core::KEY_EVENTS_PAGE_ID)).$business->bizyhood_id.'/'.$latest_event->identifier; ?>" title="<?php echo $latest_event->name; ?> details">View Details &rarr;</a>
            </div>
            
            <a itemprop="url" class="btn btn-info" <?php echo $colors['style']; ?> href="<?php echo get_permalink(Bizyhood_Utility::getOption(Bizyhood_Core::KEY_EVENTS_PAGE_ID)).$business->bizyhood_id.'/'; ?>" title="All <?php echo $business->name; ?> events">All Events</a>
        </div>
      </div>
    <?php } ?>

    <?php if (isset($business->news) && !empty($business->news)) { ?>
      <div class="col-md-<?php echo $top_columns; ?> latest_news bh_infobox">
        <h3>In the news</h3>
        <div class="column-inner">
            
            <div class="bh_alert">
              <h4></h4>
              <a href="#" title="read more" target="_blank">Read More &rarr;</a>
            </div>
            
            <a class="btn btn-info" <?php echo $colors['style']; ?> href="#" title="All news">All News</a>
        </div>
      </div>
    <?php } ?>

  </div><!-- /.row -->
  <?php } ?>
  
  
    
    
  <div class="row rowgrid zero-gutter bh_infoboxes sameheight">
      <?php if($show_contact_details) { ?>
        <div class="col-md-<?php echo ($business->hours ? '3' : '6'); ?> business_contact">
          <div class="column-inner">
            <?php if($business->cta) { ?>
              <p><a id="id_<?php echo $business->cta->name; ?>" class="btn btn-info" <?php echo $colors['style']; ?> href="<?php echo $business->cta->url; ?>"><?php echo $business->cta->label; ?></a></p>
            <?php } ?>  
            <?php if($business->telephone) { ?>
              <p>Call us: <a href="tel:<?php echo $business->telephone; ?>" itemprop="telephone"><?php echo $business->telephone; ?></a></p>
            <?php } ?>
            <?php if($business->website) { ?>
              <p class="truncate long">Visit: <a class="bh_site_link" itemprop="url" href="<?php echo $business->website; ?>" target="_blank"><?php echo str_replace(array('http://', 'https://', 'www.'), array('','',''), $business->website); ?></a></p>
            <?php } ?>
            <?php if($business->social_networks) { ?>
              <p class="social_icons_wrap">
              <?php foreach($business->social_networks as $social_network) { 
                  if (strtolower($social_network->name) == 'google') {
                    $social_network->name = 'gplus';
                  }
                ?>
                <a class="bh_social_link" <?php echo $colors['style']; ?> itemprop="sameAs" href="<?php echo $social_network->url; ?>" title="<?php echo $social_network->name; ?>" target="_blank">
                  <span class="entypo-<?php echo strtolower($social_network->name); ?>"></span>
                </a>
              <?php } ?>
              </p>
            <?php } ?>
          </div>
        </div>
      <?php } ?>
      
        <?php if($business->hours) { ?>
        <div class="col-md-<?php echo ($show_contact_details == true ? '3' : '6'); ?> business_hours">
          <div class="column-inner">
            <div class="bh_section" itemprop="openingHoursSpecification" itemscope itemtype="http://schema.org/OpeningHoursSpecification">
                <h5>Hours</h5>
                <dl class="bh_dl-horizontal">
                    <?php foreach($business->hours as $hour): ?>
                    <dt><link itemprop="dayOfWeek" href="http://purl.org/goodrelations/v1#<?php echo $hour->day_name; ?>"><?php echo substr($hour->day_name,0,3); ?>:</dt>
                    <dd>
                      <?php 
                        if($hour->hours_type == 1) { 
                          foreach($hour->hours as $hoursindex => $hour_display) {
                            
                            // get the next pair of hours to the next line
                            if ($hoursindex > 0) { ?>
                              </dd><dt>&nbsp;</dt><dd>
                            <?php } ?>
                            <span itemprop="opens" content="<?php echo date('c',strtotime($hour_display->opens)); ?>">
                              <?php echo date('g:i a',strtotime($hour_display->opens)); ?>
                            </span>&ndash;
                            <span itemprop="closes" content="<?php echo date('c',strtotime($hour_display->closes)); ?>">
                              <?php echo date('g:i a',strtotime($hour_display->closes)); ?></span>
                          <?php }
                        } else { 
                          echo $hour->hours_type_name; 
                        } 
                      ?></dd>
                    <?php endforeach; ?>
                </dl>
            </div>
          </div>
        </div>
        <?php } ?>
        
        
        <div class="col-md-<?php echo $location_column_width; ?>">      
          <div class="column-inner">
            <div class="bh_section" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                <h5>Location</h5>
                <p class="bh_addresstext">
                  <span itemprop="streetAddress"><?php echo $business->address1 ?></span><br />
                  <span itemprop="addressLocality"><?php echo $business->locality ?></span>, 
                  <span itemprop="addressRegion"><?php echo $business->region ?></span> 
                  <span itemprop="postalCode"><?php echo $business->postal_code ?></span>
                </p>
            </div>
            <div class="bh_section bh_map_wrap" itemprop="hasMap" itemtype="http://schema.org/Map">
              <p class="bh_staticmap text-center" <?php echo $colors['style']; ?>>
                <a class="clearfix" <?php echo $colors['stylefont']; ?> itemprop="url" href="https://maps.google.com?daddr=<?php echo $address; ?>" target="_blank">
                  <span itemprop="image">
                    <img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $address; ?>&amp;zoom=14&amp;scale=2&amp;size=400x200&amp;maptype=roadmap&amp;markers=color:red%7C<?php echo $address; ?>&amp;key=<?php echo Bizyhood_Core::GOOGLEMAPS_API_KEY; ?>" />
                  </span>
                  <span class="bh_directions">Get Directions</span>
                </a>
              </p>
            </div>
          </div>
          
        </div>
        
        
  </div><!-- /.row -->
      
      
  <?php if ($business->claimed == 1) { ?>
  <div class="row rowgrid zero-gutter bh_infoboxes sameheight">
    
    <div class="col-md-12">
      <div class="column-inner">
        <?php if($business->business_logo) {?>
          <div itemprop="logo" class="bh_business-avatar pull-left">
              <img src="<?php echo $business->business_logo->url ?>"/>
          </div>
        <?php } ?>
        <?php if ( $business->description ) { ?>
          <div itemprop="description"><?php echo wpautop($business->description); ?></div>
        <?php } else { ?>
          <div itemprop="description" class="text-muted"><?php echo wpautop('This business has not yet added a description.'); ?></div>
        <?php } ?>
      </div>
      
      <?php if ( $business->business_images ) { ?>
          <div class="column-inner">
            <div class="bh_image-gallery">
                <div itemscope itemtype="http://schema.org/ImageGallery" class="bh_gallery-list clearfix">
                    <?php foreach($business->business_images as $business_image) { ?>
                    <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject" class="bh_gallery-thumb">
                        <a href="<?php echo $business_image->url; ?>" itemprop="contentUrl" data-size="<?php echo $business_image->width; ?>x<?php echo $business_image->height; ?>">
                            <img src="<?php echo $business_image->url; ?>" itemprop="thumbnail" alt="<?php echo $business_image->title; ?>" />
                        </a>
                        <figcaption><?php echo $business_image->title; ?></figcaption>
                    </figure>
                    <?php } ?>
                </div>
            </div>
          </div>
            <?php } ?>
        </div><!-- /.col-md-12 -->
      
      
  </div><!-- /.row -->
  <?php } ?>
  
</div><!-- /.rowgrid_wrap -->
  
  
  
  <div class="row zero-gutter">
    <div class="col-md-9">
      <?php 
        if (Bizyhood_Utility::getOption(Bizyhood_Core::ICON_PLACEMENT) == 'after' || Bizyhood_Utility::getOption(Bizyhood_Core::ICON_PLACEMENT) == 'both') {
        
          echo Bizyhood_Utility::buildShareIcons($url, $business->name, $business->description,$business->business_logo->image->url);
    
        } 
      ?>
    </div>
    <div class="col-md-3">
      <a href="<?php echo $backlink; ?>" class="btn-inline pull-right"><span class="entypo-left" aria-hidden="true"></span> Back</a>
    </div>
  </div>
  

<?php if ($business->claimed == 1) { ?>

<!-- Root element of PhotoSwipe. Must have class pswp. -->
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <!-- Background of PhotoSwipe. It's a separate element as animating opacity is faster than rgba(). -->
    <div class="pswp__bg"></div>
    <!-- Slides wrapper with overflow:hidden. -->
    <div class="pswp__scroll-wrap">
        <!-- Container that holds slides. 
            PhotoSwipe keeps only 3 of them in the DOM to save memory.
            Don't modify these 3 pswp__item elements, data is added later on. -->
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
                <!--  Controls are self-explanatory. Order can be changed. -->
                <div class="pswp__counter"></div>
                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                <button class="pswp__button pswp__button--share" title="Share"></button>
                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                <!-- Preloader demo http://codepen.io/dimsemenov/pen/yyBWoR -->
                <!-- element will get class pswppreloaderactive when preloader is running -->
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                      <div class="pswp__preloader__cut">
                        <div class="pswp__preloader__donut"></div>
                      </div>
                    </div>
                </div>
            </div>
            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div> 
            </div>
            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
            </button>
            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
            </button>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<script type="text/javascript">
    analytics.page("Business Overview", {
        business_id: "<?php echo $business->bizyhood_id; ?>",
        business_name: "<?php echo $business->name; ?>"
    });
    <?php if($business->cta) { ?>
    var cta_link = document.getElementById("id_<?php echo $business->cta->name; ?>");
    analytics.trackLink(cta_link, "Clicked Business CTA", {
        cta: "<?php echo $business->cta->name; ?>",
        business_id: "<?php echo $business->bizyhood_id; ?>",
        business_name: "<?php echo $business->name; ?>"
    });
    <?php } ?>  


    <?php if ($business->claimed == 1) { ?>
    var aaq_link = document.getElementById("id_question_cta_btn");
    analytics.trackLink(aaq_link, "Clicked AAQ CTA", {
        business_id: "<?php echo $business->bizyhood_id; ?>",
        business_name: "<?php echo $business->name; ?>"
    });
    
    var aaq_form = document.getElementById("question_cta_form");
    analytics.trackForm(aaq_form, "AAQ Submitted");
    <?php } ?>  

</script>