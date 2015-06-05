
(function($){
    $(function(){
       $('.flexslider').flexslider({
           animation: pageSlideshow.animation,
           directionNav: (pageSlideshow.direction=='on'),
           controlNav: (pageSlideshow.control=='on')
       });
    });
})(jQuery);