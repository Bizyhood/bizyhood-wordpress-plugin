<div id="main">
      <?php Bizyhood_View::load('admin/global/header') ?>
      <div class="left_column">
         <?php if($errors): ?>
             <div class="box">
                    <div class="shadow_column">
                        <div class="title" style="padding-left: 27px; background: #F1F1F1 url('<?php echo Bizyhood_Utility::getImageBaseURL(); ?>info.png') no-repeat scroll 7px center;">
                            Alerts
                        </div>
                        <div class="content">
                            <p>
                                Nice to have you! We've noticed some things you may want to take
                                care of:
                            </p>
                            <ol>
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    </div>
                    <div class="shadow_bottom"></div>
             </div>
         <?php endif; ?>
          <div id="controls">
            <div class="box">
                <div class="title">Setup</div>
                <div class="content">

                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Production Mode</div>
                            <div class="desc nomargin">
                                Uncheck this only for development purposes.<br />
                                <small><?php echo $api_url; ?>
                            </div>
                        </div>
                        <div class="control-container">
                            <input type="checkbox" id="api_production" value="TRUE" <?php if ($api_production) { echo 'checked="checked"'; } ?> />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">API Client ID</div>
                            <div class="desc nomargin">
                              Provided by Bizyhood
                            </div>
                        </div>
                        <div class="control-container">
                            <input type="text" id="api_id" name="api_id" value="<?php echo $api_id; ?>" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">API Client Secret Key</div>
                            <div class="desc nomargin">
                              Provided by Bizyhood
                            </div>
                        </div>
                        <div class="control-container">
                            <input type="text" id="api_secret" name="api_secret" value="<?php echo $api_secret; ?>" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>

                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Business Directory Page</div>
                            <div class="desc nomargin">
                                The page that will be used to show the categories and businesses. Must include the [bh-businesses] shortcode.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <?php wp_dropdown_pages( array('name' => 'main_page_id', 'selected' => $main_page_id) ) ?>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>

                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Single Business Overview Page</div>
                            <div class="desc nomargin">
                                The page that will be used to show the single business details.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <?php wp_dropdown_pages( array('name' => 'overview_page_id', 'selected' => $overview_page_id) ) ?>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Business Signup Page</div>
                            <div class="desc nomargin">
                                The landing/marketing page that will be used to allow businesses to signup for a Bizyhood account.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <?php wp_dropdown_pages( array('name' => 'signup_page_id', 'selected' => $signup_page_id) ) ?>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    
                    <div class="break"></div>
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Business Promotions Page</div>
                            <div class="desc nomargin">
                                The page that will be used to display all businesses promotions. Must include the [bh-promotions] shortcode.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <?php wp_dropdown_pages( array('name' => 'promotions_page_id', 'selected' => $promotions_page_id) ) ?>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    
                    <div class="break"></div>
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Business Events Page</div>
                            <div class="desc nomargin">
                                The page that will be used to display all businesses events. Must include the [bh-events] shortcode.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <?php wp_dropdown_pages( array('name' => 'events_page_id', 'selected' => $events_page_id) ) ?>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    
                    <div class="break"></div>
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Business Guide Page</div>
                            <div class="desc nomargin">
                                The page that will be used to display all paying businesses. Must include the [bh-guide] shortcode.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <?php wp_dropdown_pages( array('name' => 'guide_page_id', 'selected' => $guide_page_id) ) ?>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    
                    <div class="break"></div>
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Business Detail Page button background color</div>
                            <div class="desc nomargin">
                                
                            </div>
                        </div>
                        <div class="control-container">
                          <input class="widefat color-picker colorfield colorfield_btn_bg jscolor {width:101, padding:0, shadow:false, borderWidth:0, backgroundColor:'transparent', insetColor:'#000'}" id="<?php echo Bizyhood_Core::BTN_BG_COLOR; ?>" name="btn_bg_color" type="text" value="<?php echo esc_attr( $btn_bg_color ); ?>">
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    
                    <div class="break"></div>
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Business Detail Page button font color</div>
                            <div class="desc nomargin">
                                
                            </div>
                        </div>
                        <div class="control-container">
                          <input class="widefat color-picker colorfield colorfield_btn_font jscolor {width:101, padding:0, shadow:false, borderWidth:0, backgroundColor:'transparent', insetColor:'#000'}" id="<?php echo Bizyhood_Core::BTN_FONT_COLOR; ?>" name="btn_font_color" type="text" value="<?php echo esc_attr( $btn_font_color ); ?>">
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    
                    <div class="break"></div>
                    
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Social sharing</div>
                            <div class="desc nomargin">
                              Choose social networks sharing for the business overview page
                            </div>
                        </div>
                        <div class="control-container">
                          <label for="bh_facebook">
                            <input type="checkbox" id="bh_facebook" name="bh_facebook" value="TRUE" <?php if ($bh_facebook) { echo 'checked="checked"'; } ?> />
                            Facebook
                          </label>
                          
                          <br />
                          <label for="bh_twitter">
                            <input type="checkbox" id="bh_twitter" name="bh_twitter" value="TRUE" <?php if ($bh_twitter) { echo 'checked="checked"'; } ?> />
                            Twitter
                          </label>
                          
                          <br />
                          <label for="bh_google">
                            <input type="checkbox" id="bh_google" name="bh_google" value="TRUE" <?php if ($bh_google) { echo 'checked="checked"'; } ?> />
                            Google
                          </label>
                          
                          <br />
                          <label for="bh_linkedin">
                            <input type="checkbox" id="bh_linkedin" name="bh_linkedin" value="TRUE" <?php if ($bh_linkedin) { echo 'checked="checked"'; } ?> />
                            Linkedin
                          </label>
                                                    
                          <br />
                          <label for="bh_mail">
                            <input type="checkbox" id="bh_mail" name="bh_mail" value="TRUE" <?php if ($bh_mail) { echo 'checked="checked"'; } ?> />
                            Email
                          </label>

                        </div>
                        <div style="clear:both;"></div>
                        
                        <div class="option">
                            <div class="control-label">
                                <div class="name nomargin">Social Icons Placement</div>
                                <div class="desc nomargin">
                                    The icons will be placed in single businesses, events and promotions pages<br />
                                </div>
                            </div>
                            <div class="control-container">
                                <select name="bh_icon_placement" id="bh_icon_placement">
                                  <option value="">Do not display</option>
                                  <option value="before" <?php echo ($bh_icon_placement == 'before' ? 'selected' : ''); ?>>Before content</option>
                                  <option value="after" <?php echo ($bh_icon_placement == 'after' ? 'selected' : ''); ?>>After content</option>
                                  <option value="both" <?php echo ($bh_icon_placement == 'both' ? 'selected' : ''); ?>>Before and After content</option>
                                </select>
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                        
                        <div style="clear:both;"></div>
                    </div>
                    
                    <div class="break"></div>

                    <div class="option">
                        <div class="save-container">
                            <span class="success" id="save-success">Saved!</span>
                            <input id="save-bizyhood" type="button" value="Save" name="" />
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
      </div>
    </div>
