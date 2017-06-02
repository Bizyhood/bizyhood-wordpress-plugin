<div class="row bh_business-header">
    <div class="col-md-8 bh_business-header-title">	
        <h3><?php echo ($business_name != '' ? $business_name.' ' : ''); ?>Events</h3>
    </div>
</div>
<?php

  if (!empty($events)) {
    
    $view_business_page_id  = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID);
    $view_events_page_id    = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_EVENTS_PAGE_ID);

    ?>
    <?php
      foreach($events as $event) {
        
        $single_event_link    = get_permalink( $view_events_page_id ).$event['business_identifier'].'/'.$event['identifier'].'/';
        $business_events_link = get_permalink( $view_events_page_id ).$event['business_identifier'].'/';
        $business_link        = get_permalink( $view_business_page_id ).$event['business_slug'].'/'.$event['business_identifier'].'/';
        
        $event['admission_info'] = (strtolower(trim($event['admission_info'])) == 'free' ? 0 : $event['admission_info']);
        
        // set the default logo
        // $event['business_logo'] = Bizyhood_Utility::getDefaultLogo();
        
        // get date text
        $dates = Bizyhood_Utility::buildDateTextMicrodata($event['start'], $event['end'], 'Event', 'events');
        
        // trim the description if needed
        if (str_word_count($event['description']) > Bizyhood_Core::EXCERPT_MAX_LENGTH) {
          $event['description'] = wp_trim_words($event['description'], Bizyhood_Core::EXCERPT_MAX_LENGTH, ' <a itemprop="url" href="'. $single_event_link .'" title="More about '. $event['name'] .'">more&hellip;</a>');
        }
        
        // create date objects
        $eventstart = new DateTime($event['start']);
        $eventend = new DateTime($event['end']);
        
     ?>
    <div class="row">
        <div class="col-md-8" itemscope itemtype="http://schema.org/Event">
            <span class="hidden">
                <span itemprop="location" itemscope itemtype="http://schema.org/Place">
                    <span itemprop="name"><?php echo $event['name']; ?></span>
                    <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                        <span itemprop="streetAddress"><?php echo $event['address1'].', '. $event['address2']; ?></span><br />
                        <span itemprop="addressLocality"><?php echo $event['locality']; ?></span>, 
                        <span itemprop="addressRegion"><?php echo $event['region']; ?></span> 
                        <span itemprop="postalCode"><?php echo $event['postal_code']; ?></span>
                    </span>
                </span>
                <span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                    <a itemprop="url" href="<?php echo $single_event_link; ?>">
                    <span itemprop="validFrom" content="<?php echo $eventstart->format('c'); ?>"><?php echo $eventstart->format('c'); ?></span> – 
                    <span itemprop="validThrough" content="<?php echo $eventend->format('c'); ?>"><?php echo $eventend->format('c'); ?></span> – 
                    <?php if (isset($event['admission_info']) && !empty($event['admission_info'])) { ?>
                    <span itemprop="price" content="<?php echo number_format(str_replace('$', '', $event['admission_info']), 2, '.', ' '); ?>"><span itemprop="priceCurrency" content="USD"><?php echo $event['admission_info']; ?></span></span>
                    <?php } ?>
                    </a>
                </span>
            </span>
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><a title="<?php echo htmlentities($event['business_name']); ?>" href="<?php echo $business_link; ?>"><span itemprop="name" class="business_name"><?php echo $event['business_name']; ?></span></a></h3>
                </div>
                <div class="panel-body">
                    <h4><span class="event_name"><a href="<?php echo $single_event_link; ?>" title="<?php echo 'More about '. $event['name']; ?>"><?php echo $event['name']; ?></a></span></h4>
                    <?php if (isset($event['image']) && !empty($event['image'])) { ?>
                    <div class="col-md-4">
                        <img src="<?php echo $event['image']['url'] ?>"/>
                    </div>
                    <?php } ?>
                    <div<?php if (isset($event['image']) && !empty($event['image'])) { ?> class="col-md-8"<?php } ?>>
                        <span class="event_description" itemprop="description"><?php echo $event['description']; ?></span>
                    </div>
                </div>
                <div class="panel-footer"><?php echo $dates; ?></div>
            </div>            
            <?php
            // removing until we have the data
            /*
              <div class="col-sm-12">
                <a itemprop="url" href="<?php echo $single_event_link; ?>" title="<?php echo 'More about '.$event['name']; ?>">
                  <img itemprop="image" alt="<?php echo $event['name']; ?>" src="<?php echo $event['business_logo']['image']['url']; ?>" width="<?php echo $event['business_logo']['image_width']; ?>" height="<?php echo $event['business_logo']['image_height']; ?>" />
                </a>
              </div>
            */
            ?>
            
        </div>
    </div>
<?php
      }
    ?>
    <?php
  }
?>