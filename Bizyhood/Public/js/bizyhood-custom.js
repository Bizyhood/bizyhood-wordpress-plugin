jQuery(function($){
  
  if (jQuery('#question_cta_form').length > 0) {
    
	jQuery('#id_question_cta_btn').on('click', function(e) {
		e.preventDefault();
		jQuery('#question_cta_modal').modal();
		return false;
	});
    
    var bizyhood_id = jQuery('#question_cta_form').attr('data-business');
    
    jQuery("#question_cta_form").submit(function(e){
      e.preventDefault();
      var fname   = jQuery('#question_cta_modal #question_cta_fname').val();
      var lname   = jQuery('#question_cta_modal #question_cta_lname').val();
      var email   = jQuery('#question_cta_modal #question_cta_email').val();
      var message = jQuery('#question_cta_modal #question_cta_message').val();
      
      var data = {
        'action': 'question_cta',
        'bizyhood_id': bizyhood_id,
        'text': message,
        'email': email,
        'first_name': fname,
        'last_name': lname,
      };
      // We can also pass the url value separately from ajaxurl for front end AJAX implementations
      jQuery.post(ajax_object.ajax_url, data, function(response) {
        console.log('Got this from the server: ');
        console.log(response);
        if(response.error == true) {
          jQuery('.response_area').addClass('bh_error').html(response.message);
          
          setTimeout(function () {
            jQuery('.response_area').removeClass('bh_error').html('');
          }, 10000);
          
        } else {
          if (response.code == 200 || response.code == 201) {
            jQuery('.response_area').text('Thank you for your question. We will get back to you as soon as possible.');
            
            setTimeout(function () {
              jQuery('#question_cta_modal').modal('hide');
              jQuery('#question_cta_fname').val('');
              jQuery('#question_cta_lname').val('');
              jQuery('#question_cta_email').val('');
              jQuery('#question_cta_message').val('');
              jQuery('.response_area').text('');
            }, 5000);
            
            
          }
        }
      });
      
    });
  
    
  }

  $('#more_categories').on('click', function(e) {
    e.preventDefault();
    
    var inner = $('.more_list_inner').height();
    $('.bh_list-group.more_list_wrap').animate({height: inner},400);

    $('#less_categories').show();
    $('#more_categories').hide();
    
    return false;
  });
  
  $('#less_categories').on('click', function(e) {
    e.preventDefault();
        
    $('.bh_list-group.more_list_wrap').animate({height: 0},400);
    
    $('#less_categories').hide();
    $('#more_categories').show();
    
    return false;
  });
  
  bizyload();
  jQuery(window).load(function() { bizyload(); });
  jQuery(window).resize(function() { bizyload(); });
  
  function bizyload() { 
    
    jQuery('.sameheight>div').matchHeight();

  }
  
  var windowSizeArray = [ "width=800,height=500,scrollbars=yes" ];
  
  jQuery('.bh_nw').click(function (event){
    event.preventDefault();
    
    var url = jQuery(this).attr("href");
    var windowName = "bh_popUp";
    var windowSize = windowSizeArray[0];

    window.open(url, windowName, windowSize);

    return false;

  });
  
  
});
    
    