<?php

/****************************/
/* Meet the Merchant WIDGET */
/****************************/


/**
 * Adds bizy_mtm_widget widget.
 */
class bizy_mtm_widget extends WP_Widget {
  
  var $limitchars = 30;
  var $limitchars_header = 40;

  static private $default_colors = array(
    "color_widget_back"  => '#e2e2e2',
    "color_cta_back"     => '#45aae8',
    "color_cta_font"     => '#ffffff',
    "color_label_font"   => '#6e7273',
    "color_business_font"=> '#333333',
  );
  
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'bizy_mtm_widget', // Base ID
			__( 'Bizyhood Meet the Merchant', 'bizy' ), // Name
			array( 'description' => __( 'A Widget to display bizyhood merchants', 'bizyhood' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

    $widget_id = $args['widget_id'];
    
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
    
    // cache the results
    $atts = array(
      'paged'     => 1,
      'verified'  => TRUE,
      'paid'      => (isset($instance['paid']) ? $instance['paid'] : 'n'),
      'ps'        => 25
    );
    
    $response = false;
    if ($instance['group'] != '') {
      $response = Bizyhood_Api::businesses_group_request($instance['group']);
    }
    
    if (!($response === false || $response['result'] === null || empty($response['result']))) {
      // pick a random result
      $business_array = $response['result']['results'][array_rand($response['result']['results'])];      
      // convert to object
      $business = json_decode(json_encode($business_array), FALSE);
    } else {
      $business = Bizyhood_Core::get_cache_value('bizyhood_mtm_widget', 'businesses', 'businesses_information', $atts, null, true);
    }
        
    // if no businesses are found exit with an error message
    if ($business === false || empty($business)) {
      echo __('There are no businesses to display', 'bizyhood');
      echo $args['after_widget'];
      return;
    }
    
    $view_business_page_id = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID);
    
    if (empty($business->business_logo)) {
      $business_logo_url = Bizyhood_Utility::getImageBaseURL().'placeholder-logo.jpg';
      $business_logo_width = Bizyhood_Core::BUSINESS_LOGO_WIDTH;;
      $business_logo_height = Bizyhood_Core::BUSINESS_LOGO_HEIGHT;
    } else {
      $business_logo_url = $business->business_logo->image->url;
      $business_logo_width = (isset($business->business_logo->image_width) ? $business->business_logo->image_width : '');
      $business_logo_height = (isset($business->business_logo->image_height) ? $business->business_logo->image_height : '');
    }
    
    $intro = ! empty( $instance['intro'] ) ? $instance['intro'] : '';
    
    $color_widget_back = ! empty( $instance['color_widget_back'] ) ? $instance['color_widget_back'] : '';
		$color_cta_back = ! empty( $instance['color_cta_back'] ) ? $instance['color_cta_back'] : '';
		$color_cta_font = ! empty( $instance['color_cta_font'] ) ? $instance['color_cta_font'] : '';	
    $color_label_font = ! empty( $instance['color_label_font'] ) ? $instance['color_label_font'] : '';
    $color_business_font = ! empty( $instance['color_business_font'] ) ? $instance['color_business_font'] : '';
    $logo_size = ! empty( $instance['logo_size'] ) ? $instance['logo_size'] : 'large';
		
    $widget_backcolor = ($color_widget_back != '' ? 'style="background-color: '. $color_widget_back .'; border-color: '. $color_widget_back .';"' : '');
    
    echo '<div id="bizyhood_mtm_'. $widget_id .'" class="bizyhood_widget bizyhood_mtm  has_logo '. $instance['layout'] .'">';
    
    echo '
    <div class="wrap widget_layout_'. $instance['layout'] .' table_div">
      <div class="tr_div" '. $widget_backcolor .'>';
      
      if ($intro != '') {
      ?>
        <div class="mtm_fields mtm_intro td_div" <?php echo $widget_backcolor; ?>>
          <div <?php echo ($color_label_font != '' ? 'style="color: '. $color_label_font .'"' : ''); ?>>
            <?php echo substr($intro, 0, $this->limitchars); ?>
          </div>
        </div>
      <?php }
      
      if ($logo_size != 'hide' ) {
      ?>
      <div class="mtm_fields  mtm_logo td_div" <?php echo $widget_backcolor; ?>>
        <a href="<?php echo get_permalink( $view_business_page_id ); ?><?php echo sanitize_title($business->name).'-'.sanitize_title($business->locality).'-'.sanitize_title($business->region).'-'.sanitize_title($business->postal_code) .'/'.$business->bizyhood_id ?>/" title="<?php echo $business->name; ?>">
          <img alt="<?php echo $business->name; ?>" src="<?php echo $business_logo_url; ?>" width="<?php echo $business_logo_width; ?>" height="<?php echo $business_logo_height; ?>" class="<?php echo $logo_size;?>"/>
        </a>
      </div>
      <?php } ?>

      <!-- business info START -->
      <div class="mtm_fields  mtm_info td_div" <?php echo $widget_backcolor; ?>>
        <a <?php echo ($color_business_font != '' ? 'style="color: '. $color_business_font .'"' : ''); ?> title="<?php echo $business->name; ?>" href="<?php echo get_permalink( $view_business_page_id ); ?><?php echo sanitize_title($business->name).'-'.sanitize_title($business->locality).'-'.sanitize_title($business->region).'-'.sanitize_title($business->postal_code) .'/'.$business->bizyhood_id ?>/">
          <span class="merchant_name"><?php echo $business->name; ?></span>
        </a>
        <span class="merchant_address" <?php echo ($color_business_font != '' ? 'style="color: '. $color_business_font .'"' : ''); ?>><?php echo $business->locality; ?>, <?php echo $business->region; ?></span>
      </div>
      <!-- business info END -->
      <?php
      if ($instance['see_all_link'] != '') {
        $see_all_link = $instance['see_all_link'];
      } else {
        $see_all_link = get_permalink(get_option('Bizyhood_Guide_page_ID'));
      }
      echo '
        <div class="mtm_fields list_your_business arrow_box td_div" '. ($color_cta_back != '' ? 'style="background-color: '. $color_cta_back .'; border-color: '. $color_cta_back .';"' : '') .'>
          
            <a href="'. $see_all_link .'" title="'. __('All businesses', 'bizyhood') .'" '. ($color_cta_font != '' ? 'style="color: '. $color_cta_font .';"' : '') .' >
              <span class="link_row row1" '. ($color_cta_font != '' ? 'style="color: '. $color_cta_font .';"' : '') .'>
                '. __(esc_attr(substr($instance['row1'], 0, $this->limitchars_header)), 'bizyhood') .'
              </span>
            </a>
            <a href="'. $see_all_link .'" title="'. __('All businesses', 'bizyhood') .'" '. ($color_cta_font != '' ? 'style="color: '. $color_cta_font .';"' : '') .' >
              <span class="link_row row2" '. ($color_cta_font != '' ? 'style="color: '. $color_cta_font .';"' : '') .'>
                '. __(esc_attr(substr($instance['row2'], 0, $this->limitchars)), 'bizyhood') .'
              </span>
            </a>
        </div>
      </div>
    </div>
  ';

    
    echo '</div>';
    
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$layout = ! empty( $instance['layout'] ) ? $instance['layout'] : 'full';
		$intro = ! empty( $instance['intro'] ) ? $instance['intro'] : '';
		$row1 = ! empty( $instance['row1'] ) ? $instance['row1'] : 'Want to see all our business listings?';
		$row2 = ! empty( $instance['row2'] ) ? $instance['row2'] : 'CLICK HERE';
		$see_all_link = ! empty( $instance['see_all_link'] ) ? $instance['see_all_link'] : '';
		$color_widget_back = ! empty( $instance['color_widget_back'] ) ? $instance['color_widget_back'] : self::$default_colors['color_widget_back'];
		$color_cta_back = ! empty( $instance['color_cta_back'] ) ? $instance['color_cta_back'] : self::$default_colors['color_cta_back'];
		$color_cta_font = ! empty( $instance['color_cta_font'] ) ? $instance['color_cta_font'] : self::$default_colors['color_cta_font'];
		$color_label_font = ! empty( $instance['color_label_font'] ) ? $instance['color_label_font'] : self::$default_colors['color_label_font'];
		$color_business_font = ! empty( $instance['color_business_font'] ) ? $instance['color_business_font'] : self::$default_colors['color_business_font'];
    $logo_size = ! empty( $instance['logo_size'] ) ? $instance['logo_size'] : 'large';
    $paid = ! empty( $instance['paid'] ) ? $instance['paid'] : 'n';
    $group = ! empty( $instance['group'] ) ? $instance['group'] : '';
    
    $uid = uniqid ();
		?>
		<p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
    <p>
      <label for="<?php echo $this->get_field_id( 'paid' ); ?>"><?php _e( 'Paying only Businesses:' ); ?></label> 
      <select class="widefat" id="<?php echo $this->get_field_id( 'paid' ); ?>" name="<?php echo $this->get_field_name( 'paid' ); ?>">
        <option value="n" <?php echo ($paid == 'n' ? 'selected="selected"': ''); ?>><?php _e( 'No, include all verified businesses', 'bizyhood' ); ?></option>
        <option value="y" <?php echo ($paid == 'y' ? 'selected="selected"': ''); ?>><?php _e( 'Yes, include only paying businesses', 'bizyhood' ); ?></option>
      </select>
		</p>

    <p>
      <label for="<?php echo $this->get_field_id( 'group' ); ?>"><?php _e( 'Group ID:' ); ?></label> 
      <input placeholder="Group ID results only" class="widefat" id="<?php echo $this->get_field_id( 'group' ); ?>" name="<?php echo $this->get_field_name( 'group' ); ?>" type="text" value="<?php echo esc_attr( $group ); ?>">
      <small><?php echo  __('This will override the above "Paying only Business" selection', 'bizyhood' ); ?></small>
		</p>
    
		<p>
      <label for="<?php echo $this->get_field_id( 'layout' ); ?>"><?php _e( 'Layout:', 'bizyhood' ); ?></label> 
      <select class="widefat" id="<?php echo $this->get_field_id( 'layout' ); ?>" name="<?php echo $this->get_field_name( 'layout' ); ?>">
        <option value="full" <?php echo ($layout == 'full' ? 'selected="selected"': ''); ?>><?php _e( 'Full width', 'bizyhood' ); ?></option>
        <option value="side" <?php echo ($layout == 'side' ? 'selected="selected"': ''); ?>><?php _e( 'Sidebar', 'bizyhood' ); ?></option>
      </select>
		</p>

    <p>
      <label for="<?php echo $this->get_field_id( 'logo_size' ); ?>"><?php _e( 'Logo Width:', 'bizyhood' ); ?></label> 
      <select class="widefat" id="<?php echo $this->get_field_id( 'logo_size' ); ?>" name="<?php echo $this->get_field_name( 'logo_size' ); ?>">
        <option value="large" <?php echo ($logo_size == 'large' ? 'selected="selected"': ''); ?>><?php echo __('large', 'bizyhood'); ?></option>
        <option value="small" <?php echo ($logo_size == 'small' ? 'selected="selected"': ''); ?>><?php echo __('small', 'bizyhood'); ?></option>
        <option value="hide" <?php echo ($logo_size == 'hide' ? 'selected="selected"': ''); ?>><?php echo __('hide', 'bizyhood'); ?></option>
      </select>
		</p>
        
		<p>
      <label for="<?php echo $this->get_field_id( 'intro' ); ?>"><?php _e( 'Intro text:' ); ?></label> 
      <input placeholder="eg.Meet Our Merchants" class="widefat" maxlength="<?php echo $this->limitchars; ?>" id="<?php echo $this->get_field_id( 'intro' ); ?>" name="<?php echo $this->get_field_name( 'intro' ); ?>" type="text" value="<?php echo esc_attr( $intro ); ?>">
      <small><?php echo $this->limitchars .' '. __('characters max', 'bizyhood' ); ?></small>
		</p>
		<p>
      <label for="<?php echo $this->get_field_id( 'row1' ); ?>"><?php _e( 'Link text header:' ); ?></label> 
      <input class="widefat" maxlength="<?php echo $this->limitchars_header; ?>" id="<?php echo $this->get_field_id( 'row1' ); ?>" name="<?php echo $this->get_field_name( 'row1' ); ?>" type="text" value="<?php echo esc_attr( $row1 ); ?>">
      <small><?php echo $this->limitchars_header .' '. __('characters max', 'bizyhood' ); ?></small>
    </p>
		<p>
      <label for="<?php echo $this->get_field_id( 'row2' ); ?>"><?php _e( 'Link text subheader:' ); ?></label> 
      <input class="widefat" maxlength="<?php echo $this->limitchars; ?>" id="<?php echo $this->get_field_id( 'row2' ); ?>" name="<?php echo $this->get_field_name( 'row2' ); ?>" type="text" value="<?php echo esc_attr( $row2 ); ?>">
      <small><?php echo $this->limitchars .' '. __('characters max', 'bizyhood' ); ?></small>
    </p>
		<p>
      <label for="<?php echo $this->get_field_id( 'see_all_link' ); ?>"><?php _e( 'Link URL' ); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id( 'see_all_link' ); ?>" name="<?php echo $this->get_field_name( 'see_all_link' ); ?>" type="text" value="<?php echo esc_attr( $see_all_link ); ?>">
      <small><?php echo __('Leave empty to link to the business guide page', 'bizyhood' ); ?></small>
    </p>

    <h4>Colors</h4>
    <div class="color_wrap" id="color_wrap_<?php echo $uid; ?>">
      <p>
        <label for="<?php echo $this->get_field_id( 'color_widget_back' ); ?>"><?php _e( 'Widget Background:' ); ?></label> 
        <input data-default-color="<?php echo self::$default_colors['color_widget_back']; ?>" class="widefat color-picker colorfield " id="<?php echo $this->get_field_id( 'color_widget_back' ); ?>" name="<?php echo $this->get_field_name( 'color_widget_back' ); ?>" type="text" value="<?php echo esc_attr( $color_widget_back ); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'color_cta_back' ); ?>"><?php _e( 'Call to Action Background:' ); ?></label> 
        <input data-default-color="<?php echo self::$default_colors['color_cta_back']; ?>" class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> " id="<?php echo $this->get_field_id( 'color_cta_back' ); ?>" name="<?php echo $this->get_field_name( 'color_cta_back' ); ?>" type="text" value="<?php echo esc_attr( $color_cta_back ); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'color_cta_font' ); ?>"><?php _e( 'Call to Action Font:' ); ?></label> 
        <input data-default-color="<?php echo self::$default_colors['color_cta_font']; ?>" class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> " id="<?php echo $this->get_field_id( 'color_cta_font' ); ?>" name="<?php echo $this->get_field_name( 'color_cta_font' ); ?>" type="text" value="<?php echo esc_attr( $color_cta_font ); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'color_label_font' ); ?>"><?php _e( 'Label Font:' ); ?></label> 
        <input data-default-color="<?php echo self::$default_colors['color_label_font']; ?>" class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> " id="<?php echo $this->get_field_id( 'color_label_font' ); ?>" name="<?php echo $this->get_field_name( 'color_label_font' ); ?>" type="text" value="<?php echo esc_attr( $color_label_font ); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'color_business_font' ); ?>"><?php _e( 'Business Info Font:' ); ?></label> 
        <input data-default-color="<?php echo self::$default_colors['color_business_font']; ?>" class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> " id="<?php echo $this->get_field_id( 'color_business_font' ); ?>" name="<?php echo $this->get_field_name( 'color_business_font' ); ?>" type="text" value="<?php echo esc_attr( $color_business_font ); ?>">
      </p>
      <p>
        <a class="colorfield_reset" href="#">Reset Colors to Default</a>
      </p>
    </div>
    <script>
      ( function( $ ){
          function initColorPicker( widget ) {
                  widget.find( '.color-picker' ).wpColorPicker( {
                          change: _.throttle( function() { // For Customizer
                                  $(this).trigger( 'change' );
                          }, 3000 )
                  });
          }
              function onFormUpdate( event, widget ) {
                  initColorPicker( widget );
          }
          $( document ).on( 'widget-added widget-updated', onFormUpdate );

          $( document ).ready( function() {
                  $( '#widgets-right .widget:has(.color-picker)' ).each( function () {
                          initColorPicker( $( this ) );                                                   
                  } );
          } );
      }( jQuery ) );  
      
      jQuery(document).ready(function() {
        
        jQuery('#color_wrap_<?php echo $uid; ?> .colorfield_reset').on('click', function(e) {
          e.preventDefault();
          jQuery('#color_wrap_<?php echo $uid; ?> .color-picker').each(function() {
            jQuery(this).wpColorPicker('color', jQuery(this).data('default-color'));            
          });

          return false;
          
        });
      });
    </script>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['layout'] = ( ! empty( $new_instance['layout'] ) ) ? strip_tags( $new_instance['layout'] ) : '';
		$instance['paid'] = ( ! empty( $new_instance['paid'] ) ) ? strip_tags( $new_instance['paid'] ) : '';
		$instance['group'] = ( ! empty( $new_instance['group'] ) ) ? strip_tags( $new_instance['group'] ) : '';
		$instance['intro'] = ( ! empty( $new_instance['intro'] ) ) ? strip_tags( $new_instance['intro'] ) : '';
		$instance['row1'] = ( ! empty( $new_instance['row1'] ) ) ? strip_tags( $new_instance['row1'] ) : '';
		$instance['row2'] = ( ! empty( $new_instance['row2'] ) ) ? strip_tags( $new_instance['row2'] ) : '';
		$instance['see_all_link'] = ( ! empty( $new_instance['see_all_link'] ) ) ? strip_tags( $new_instance['see_all_link'] ) : '';
    
    // colors
    $instance['color_widget_back']    = ( ! empty( $new_instance['color_widget_back'] ) ) ? strip_tags( $new_instance['color_widget_back'] ) : '';
		$instance['color_cta_back']       = ( ! empty( $new_instance['color_cta_back'] ) ) ? strip_tags( $new_instance['color_cta_back'] ) : '';
		$instance['color_cta_font']       = ( ! empty( $new_instance['color_cta_font'] ) ) ? strip_tags( $new_instance['color_cta_font'] ) : '';
		$instance['color_label_font']     = ( ! empty( $new_instance['color_label_font'] ) ) ? strip_tags( $new_instance['color_label_font'] ) : '';   
		$instance['color_business_font']  = ( ! empty( $new_instance['color_business_font'] ) ) ? strip_tags( $new_instance['color_business_font'] ) : '';  

    $instance['logo_size']   = ( ! empty( $new_instance['logo_size'] ) ) ? strip_tags( $new_instance['logo_size'] ) : '';   

		return $instance;
	}

} // class bizy_mtm_widget

?>