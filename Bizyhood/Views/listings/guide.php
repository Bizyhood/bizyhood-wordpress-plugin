<div class="row">
    <div class="col-md-<?php echo ($show_category_facets ? 9 : 12); ?> bh_results">
        <ul class="bh_list">
            <?php if ( !empty($businesses) ) { ?>
            <?php $i = 0; foreach($businesses as $business): ?>
            <li class="bh_list_item">
              <div class="clear clearfix"></div>
              
              <div class="bh_info_wrap pull-left">
                <h4>
                  <a href="<?php echo get_permalink( $view_business_page_id ); ?><?php echo sanitize_title($business->name).'-'.sanitize_title($business->locality).'-'.sanitize_title($business->region).'-'.sanitize_title($business->postal_code) .'/'.$business->bizyhood_id ?>/" class="bh_block-link">
                    <?php echo $business->name ?>
                  </a>
                </h4>
                <div class="bh_address">
                    <p>
                      <?php echo $business->address1 ?>, 
                      <?php echo $business->locality ?>, <?php echo $business->region ?> <?php echo $business->postal_code ?>, 
                      <a href="tel:<?php echo $business->telephone ?>"><?php echo $business->telephone ?></a>
                    </p>
                </div>
              </div>
              <img itemprop="image" class="pull-right" src="<?php echo $business->business_logo->url ?>"/>
              
              <div class="clear clearfix"></div>
            </li><!-- /.col-md-4 -->
            <?php $i++; endforeach; ?>
            <?php } else { ?>
            <li>There were no results for your search.</li>
            <?php } ?>
        </ul><!-- /.bh_list -->
    </div>
</div>
