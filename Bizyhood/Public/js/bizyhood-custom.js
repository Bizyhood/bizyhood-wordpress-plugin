jQuery(function($){

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
    
    