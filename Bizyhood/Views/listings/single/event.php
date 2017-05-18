<?php if (!empty($event)) { ?>

<?php
    
  $view_business_page_id  = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID);
  $view_events_page_id    = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_EVENTS_PAGE_ID);
      
  $single_event_link    = get_permalink( $view_events_page_id ).$event['business_identifier'].'/'.$event['identifier'].'/';
  $business_events_link = get_permalink( $view_events_page_id ).$event['business_identifier'].'/';
  $business_link        = get_permalink( $view_business_page_id ).$event['business_slug'].'/'.$event['business_identifier'].'/';
  
  $event['admission_info'] = (strtolower(trim($event['admission_info'])) == 'free' ? 0 : $event['admission_info']);
  
  // set the default logo
  // $event['business_logo'] = Bizyhood_Utility::getDefaultLogo();
  
  // get date text
  $dates = Bizyhood_Utility::buildDateTextMicrodata($event['start'], $event['end'], 'Event', 'events');
  
  // create date objects
  $eventstart = new DateTime($event['start']);
  $eventend = new DateTime($event['end']);
  
?>


<div class="row bh_event-content single_event_page">

  <?php if (Bizyhood_Utility::getOption(Bizyhood_Core::ICON_PLACEMENT) == 'before' || Bizyhood_Utility::getOption(Bizyhood_Core::ICON_PLACEMENT) == 'both') { ?>
  <div class="col-md-12">
    <?php echo Bizyhood_Utility::buildShareIcons($single_event_link, $event['name'], $event['description'],''); ?>
  </div>
  <?php } ?>

  <div class="col-md-12" itemscope itemtype="http://schema.org/Event">
    <div class="row bh_business-header">
        <div class="col-md-12 bh_business-header-title">	
            <h3>
              <a title="<?php echo htmlentities($event['business_name']); ?>" href="<?php echo $business_link; ?>">
                <span  class="business_name"><?php echo $event['business_name']; ?></span>
              </a>
            </h3>
            
            <h4>
              <span itemprop="name" class="event_name"><?php echo $event['name']; ?></span>
            </h4>
        </div>
    </div>
    
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
          <?php if (isset($event['end']) && !empty($event['end'])) { ?>
          <span itemprop="validThrough" content="<?php echo $eventend->format('c'); ?>"><?php echo $eventend->format('c'); ?></span> – 
          <?php } ?>
          <?php if (isset($event['admission_info']) && !empty($event['admission_info'])) { ?>
          <span itemprop="price" content="<?php echo number_format(str_replace('$', '', $event['admission_info']), 2, '.', ' '); ?>"><span itemprop="priceCurrency" content="USD"><?php echo $event['admission_info']; ?></span></span>
          <?php } ?>
        </a>
      </span>
    </span>
      
      

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
      <div class="row bh_event-content single_event_info">
        <div class="col-md-8">
            <?php if (isset($event['image']) && !empty($event['image'])) { ?>
            <div class="col-md-4">
                <img src="<?php echo $event['image']['url'] ?>"/>
            </div>
            <?php } ?>
            <div<?php if (isset($event['image']) && !empty($event['image'])) { ?> class="col-md-8"<?php } ?>>
                <span class="event_description" itemprop="description"><?php echo $event['description']; ?></span>
            </div>
        </div>
        <div class="col-md-4">
          <dl class="bh_dl-horizontal">
            <dt>Date</dt><br />
            <dd><?php echo $dates; ?></dd>
            <dt>Time</dt><br />
            <dd><?php echo $eventstart->format('g:i A'); ?><?php echo ($event['end'] ? ' - '.$eventend->format('g:i A') : ''); ?></dd>
            <dt>Location</dt><br />
            <dd><?php echo $event['address1']; ?><br /><?php echo $event['locality']; ?>, <?php echo $event['region']; ?> <?php echo $event['postal_code']; ?></dd>
            <dt>Cost</dt><br />
            <dd><?php echo $event['admission_info']; ?></dd>
            <dt>More Information</dt><br />
            <dd><a class="details_url truncate" href="<?php echo $event['details_url']; ?>" target="_blank" title="<?php echo $event['name']; ?>"><?php echo $event['details_url']; ?></a></dd>
            <dt>Contact</dt><br />
            <dd><span itemprop="url" content="<?php echo $event['details_url']; ?>"><a class="external_contact" href="mailto:<?php echo $event['external_contact']; ?>" title="Contact <?php echo $event['business_name']; ?>"><?php echo $event['external_contact']; ?></a></span></dd>
          </dl>
        </div>
      </div>


  </div>
  
  
  <?php if (Bizyhood_Utility::getOption(Bizyhood_Core::ICON_PLACEMENT) == 'after' || Bizyhood_Utility::getOption(Bizyhood_Core::ICON_PLACEMENT) == 'both') { ?>
  <div class="col-md-12">
    <?php echo Bizyhood_Utility::buildShareIcons($single_event_link, $event['name'], $event['description'],''); ?>
  </div>
  <?php } ?>

</div>
    <?php
  } else { 
  ?>
  <div class="bh_alert">Event not found</div>
  <?php
  }
?>
<script type="text/javascript">
    analytics.page('Business Event', {
        path: location.pathname,
        business_id: '<?php echo $event['business_identifier']; ?>',
        business_name: `<?php echo $event['business_name']; ?>`,
        event_identifier: '<?php echo $event['identifier']; ?>'
    });
</script>
