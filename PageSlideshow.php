<?php
/*
Plugin Name: Page slideshow
Author: <a href="mailto:a.kr3mer@gmail.com">Adrian Kremer</a> - <a href="http://www.proseed.de">proseed GmbH</a>
Description: Page based slideshow
*/


// Backend Image size

add_image_size( 'proseed_teaserThumb', 290, 150,true);

// Output Image size @todo: create a options page to change output image size

add_image_size( 'proseed_slideThumb', 1680, 600,true);



function proseed_meta_image_fields($cnt, $p = null) {

    if ($p === null){

        $a = $b = $c = $d = '';

    }else{

        $a = $p['id'];

        $headline = $p['headline'];

    }

    $image_src = wp_get_attachment_image_src( $a, "proseed_teaserThumb");

    echo '<li>';

    echo '<img src="'.$image_src[0].'" width="'.$image_src[1].'" height="'.$image_src[2].'" />';

    echo '<fieldset>';

    echo '<legend>Text:</legend>';

    echo '<input type="text" name="image_data['.$cnt.'][headline]" id="meta-image'.$cnt.'" value="'.$headline.'" /><br />';

    echo '</fieldset>';

    echo '<input type="hidden" name="image_data['.$cnt.'][id]" id="meta-image" value="'.$a.'" />';

    echo '<button type="button" class="button button-primary button-small remove">-</button>';

    echo '</li>';

}





add_action("add_meta_boxes", "proseed_object_init");



function proseed_object_init(){

    add_meta_box("proseed_meta_image_id", "Slideshow","proseed_meta_image", "page", "normal", "low");



}



function proseed_slideshow_image_enqueue() {

    global $typenow;

    if( $typenow == 'page' ) {

        wp_enqueue_media();


        wp_register_script( 'proseed-meta-image', plugins_url( 'js/meta-image.js', __FILE__ ), array( 'jquery' ) );

        wp_localize_script( 'proseed-meta-image', 'proseed_meta_image',

            array(

                'title' => 'Bild wählen oder hochladen',

                'button' => 'Bild verwenden',

            )

        );

        wp_enqueue_script( 'jquery-ui' );

        wp_enqueue_script( 'proseed-meta-image' );

        wp_enqueue_style('proseed-meta-image-style',plugins_url( 'css/meta-image.css', __FILE__ ));

    }

}

add_action( 'admin_enqueue_scripts', 'proseed_slideshow_image_enqueue' );

function proseed_meta_image(){

    global $post;



    $data = get_post_meta($post->ID,"image_data",true);

    echo '<div>';

    echo '<ul id="proseed-image-items">';

    $c = 0;

    if (count($data) > 0){

        foreach((array)$data as $p ){

            if (isset($p['id'])){

                proseed_meta_image_fields($c,$p);

                $c = $c +1;

            }

        }



    }

    echo '</ul>';



    ?>

    <span id="here"></span>

    <button id="meta-image-button" type="button" class="button button-primary button-large proseed-add-image"><?php echo __('+'); ?></button>

    <script>

        var $ = jQuery.noConflict();

        $(document).ready(function() {

            var count = <?php echo $c; ?>;

            $(".proseed-add-image").click(function() {

                count = count + 1;

                $('#proseed-image-items').append('<?php echo implode('',explode("\n",proseed_meta_image_fields('count'))); ?>'.replace(/count/g, count));

                return false;

            });

            $(".remove").live('click', function() {

                $(this).parent().remove();

            });

        });

    </script>

    <style>#proseed-image-items {list-style: none;}</style>

    <?php

    echo '</div>';

}





add_action('save_post', 'proseed_save_details');



function proseed_save_details($post_id){

    global $post;





    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )

        return $post_id;



    if (isset($_POST['image_data'])){

        $data = $_POST['image_data'];

        update_post_meta($post_id,'image_data',$data);

    }else{

        delete_post_meta($post_id,'image_data');

    }

}

function proseed_insert_custom_image_sizes( $sizes ) {

    global $_wp_additional_image_sizes;

    if ( empty($_wp_additional_image_sizes) )

        return $sizes;



    foreach ( $_wp_additional_image_sizes as $id => $data ) {

        if ( !isset($sizes[$id]) )

            $sizes[$id] = ucfirst( str_replace( '-', ' ', $id ) );

    }



    return $sizes;

}

add_filter( 'image_size_names_choose', 'proseed_insert_custom_image_sizes' );

function proseed_slideshow(){

    global $post;
    wp_enqueue_media();
    wp_register_script( 'flexslider', plugins_url( 'js/jquery.flexslider.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script( 'flexslider' );

    $images = get_post_meta($post->ID,"image_data",true);
    if ($images) {
        if (count($images)>1){
            echo '<div class="flexslider">';
            echo '<ul class="slides">';
            foreach ( $images as $image ){
                echo '<li>';
                echo wp_get_attachment_image( $image[id] , 'proseed_slideThumb' );
                echo '<p class="flex-caption">'.$image[headline].'</p>';
                echo '</li>';
            }
            echo '</ul>';
            echo '</div>';
        } else {
            echo wp_get_attachment_image( $images[1][id] , 'proseed_slideThumb' );
        }
    } else {
        echo '<img src="'.get_template_directory_uri().'/css/screen/images/dres-gruppe_kl.jpg" alt="" />';
    }

}