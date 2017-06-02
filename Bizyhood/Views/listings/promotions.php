<div class="row bh_business-header">
    <div class="col-md-8 bh_business-header-title">	
        <h3><?php echo ($business_name != '' ? $business_name.' ' : ''); ?>Promotions</h3>
    </div>
</div>
<?php

  if (!empty($promotions)) {
    
    $view_business_page_id    = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID);
    $view_promotions_page_id  = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_PROMOTIONS_PAGE_ID);
        
    ?>
    <?php
      
      foreach($promotions as $promotion) {
        
        $single_promotion_link    = get_permalink( $view_promotions_page_id ).$promotion['business_identifier'].'/'.$promotion['identifier'].'/';
        $business_promotions_link = get_permalink( $view_promotions_page_id ).$promotion['business_identifier'].'/';
        $business_link            = get_permalink( $view_business_page_id ).$promotion['business_slug'].'/'.$promotion['business_identifier'].'/';
                
        
        // set the default logo
        //$promotion['business_logo'] = Bizyhood_Utility::getDefaultLogo();
        
        // get date text
        $dates = Bizyhood_Utility::buildDateText($promotion['start'], $promotion['end'], 'Promotion', 'promotions');
        
        // trim the description if needed
        if (str_word_count($promotion['details']) > Bizyhood_Core::EXCERPT_MAX_LENGTH) {
          $promotion['details'] = wp_trim_words($promotion['details'], Bizyhood_Core::EXCERPT_MAX_LENGTH, ' <a href="'. $single_promotion_link .'/" title="'. $promotion['business_name'] .' '. __('promotions', 'bizyhood').'">more&hellip;</a>');
        }
        
     ?>
    <div class="row">	
        <div class="col-md-8">
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
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><a title="<?php echo htmlentities($promotion['business_name']); ?>" href="<?php echo get_permalink( $view_business_page_id ); ?><?php echo $promotion['business_slug'].'/'.$promotion['business_identifier']; ?>/"><span class="business_name"><?php echo $promotion['business_name']; ?></span></a></h3>
                </div>
                <div class="panel-body">
                    <h4><span class="promotion_name"><a href="<?php echo $single_promotion_link; ?>" title="<?php echo 'More about '. $promotion['name']; ?>"><?php echo $promotion['name']; ?></a></span></h4>
                    <?php if (isset($promotion['image']) && !empty($promotion['image'])) { ?>
                    <div class="col-md-4">
                        <img src="<?php echo $promotion['image']['url'] ?>"/>
                    </div>
                    <?php } ?>
                    <div<?php if (isset($promotion['image']) && !empty($promotion['image'])) { ?> class="col-md-8"<?php } ?>>
                        <span class="promotion_description"><?php echo $promotion['details']; ?></span>
                    </div>
                </div>
                <div class="panel-footer"><?php echo $dates; ?></div>
            </div>            
        </div>
    </div>
<?php
      }
    ?>
    </div>
    <?php
  }
?>