<?php if (!empty($promotion)) { 
  
  $view_business_page_id      = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID);
  $view_promotions_page_id    = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_PROMOTIONS_PAGE_ID);
  
  $single_promotion_link    = get_permalink( $view_promotions_page_id ).$promotion['business_identifier'].'/'.$promotion['identifier'].'/';
  $business_promotions_link = get_permalink( $view_promotions_page_id ).$promotion['business_identifier'].'/';
  $business_link            = get_permalink( $view_business_page_id ).$promotion['business_slug'].'/'.$promotion['business_identifier'].'/';

?>
<div class="row bh_promotion-content single_promotion_page">	

  <?php if (Bizyhood_Utility::getOption(Bizyhood_Core::ICON_PLACEMENT) == 'before' || Bizyhood_Utility::getOption(Bizyhood_Core::ICON_PLACEMENT) == 'both') { ?>
  <div class="col-md-12">
    <?php echo Bizyhood_Utility::buildShareIcons($single_promotion_link, $promotion['name'], $promotion['description'],''); ?>
  </div>
  <?php } ?>

  <div class="col-md-12">
    <div class="row bh_business-header">
        <div class="col-md-12 bh_business-header-title">	
            <h3>
              <a title="<?php echo htmlentities($promotion['business_name']); ?>" href="<?php echo $business_link; ?>">
                <span  class="business_name"><?php echo $promotion['business_name']; ?></span>
              </a>
            </h3>
            <h4><span class="promotion_name"><?php echo $promotion['name']; ?></span></h4>
        </div>
    </div>
    <?php
        
        // set the default logo
        // $promotion['business_logo'] = Bizyhood_Utility::getDefaultLogo();
        
        // get date text
        $dates = Bizyhood_Utility::buildDateText($promotion['start'], $promotion['end'], 'Promotion', 'promotions');
        
     ?>

          
          
        <div class="row no-gutter">
      
      <?php
      // removing until we have the data
      /*
        <div class="col-sm-12">
          <a href="<?php echo get_permalink( $view_business_page_id ); ?><?php echo $promotion['business_slug'].'/'.$promotion['business_identifier']; ?>/" title="<?php echo $promotion['business_name'] .' '. __('promotions', 'bizyhood'); ?>">
            <img alt="<?php echo $promotion['name']; ?>" src="<?php echo $promotion['business_logo']['image']['url']; ?>" width="<?php echo $promotion['business_logo']['image_width']; ?>" height="<?php echo $promotion['business_logo']['image_height']; ?>" />
          </a>
        </div>
      */
      ?>
            <?php if (isset($promotion['image']) && !empty($promotion['image'])): ?>
            <div class="col-sm-3">
                <img src="<?php echo $promotion['image']['url'] ?>"/>
            </div>
            <?php endif; ?>
        <div class="<?php if (isset($promotion['image']) && !empty($promotion['image'])): ?>col-sm-9<?php else: ?>col-sm-12<?php endif; ?>">
          <span class="promotion_description"><?php echo $promotion['details']; ?></span>
          <br /><br />
          <dl class="bh_dl-horizontal">
            <dt>Offer Valid</dt>&nbsp; 
            <dd><?php echo $dates; ?></dd>
            <dt>More Information</dt>&nbsp; 
            <dd><a class="details_url" href="<?php echo $promotion['details_url']; ?>" target="_blank" title="<?php echo $promotion['name']; ?>"><?php echo $promotion['details_url']; ?></a></dd>
          </dl>

        </div>
      </div>
          

      
  </div>
  
  <?php if (Bizyhood_Utility::getOption(Bizyhood_Core::ICON_PLACEMENT) == 'after' || Bizyhood_Utility::getOption(Bizyhood_Core::ICON_PLACEMENT) == 'both') { ?>
  <div class="col-md-12">
    <?php echo Bizyhood_Utility::buildShareIcons($single_promotion_link, $promotion['name'], $promotion['description'],''); ?>
  </div>
  <?php } ?>
  
</div>
    <?php
  }
?>
<script type="text/javascript">
    analytics.page("Business Promotion", {
        path: location.pathname,
        business_id: "<?php echo $promotion['business_identifier']; ?>",
        business_name: "<?php echo $promotion['business_name']; ?>",
        promotion_identifier: "<?php echo $promotion['identifier']; ?>"
    });
</script>
