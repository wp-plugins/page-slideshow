jQuery(document).ready(function($){

	jQuery('#proseed-image-items').sortable();

    var proseed_meta_image_frame;
    var proseedTargetInput;
    
    $('.proseed-add-image').on('click',function(e){

        e.preventDefault();
 		proseedTargetInput = $(this);

        if ( proseed_meta_image_frame ) {
            proseed_meta_image_frame.open();
            return;	
        }
 		

        proseed_meta_image_frame = wp.media.frames.proseed_meta_image_frame = wp.media({
            title: proseed_meta_image.title,
            button: { text: proseed_meta_image.button },
            library: { type: 'image' }
        });

        proseed_meta_image_frame.on('select', function(){
 
  
            var proseed_media_attachment = proseed_meta_image_frame.state().get('selection').first().toJSON();

            $('#proseed-image-items li').last().children('input[type="hidden"]').val(proseed_media_attachment.id);
            if(proseed_media_attachment.sizes.proseed_teaserThumb){
                $('#proseed-image-items li').last().children('img').attr('src',proseed_media_attachment.sizes.proseed_teaserThumb.url);
            }else{
                $('#proseed-image-items li').last().children('img').attr('src',proseed_media_attachment.sizes.thumbnail.url);
            }
        });

        proseed_meta_image_frame.open();
    });
   
});