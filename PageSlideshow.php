<?php
/*
Plugin Name: Page slideshow
Author: <a href="mailto:a.kr3mer@gmail.com">Adrian Kremer</a> - <a href="http://www.proseed.de">proseed GmbH</a>
Description: With Page Slideshow you can create individual, responsive and sortable slideshows. Uses performance-friendly CSS3 transitions.
Version: 0.4.4
Author URI: http://www.proseed.de/
License: GPL2+
Text Domain: page-slideshow
*/

// Backend Image size

add_image_size( 'pageSlideshow_teaserThumb', 290, 150,true);

add_image_size( 'pageSlideshow_slideThumb', get_option('proseed-slideshow-imagesize-width'), get_option('proseed-slideshow-imagesize-height'),true);

function pageSlideshow_load_plugin_textdomain() {
    load_plugin_textdomain( 'page-slideshow', FALSE, basename( dirname( __FILE__ ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'pageSlideshow_load_plugin_textdomain' );

function pageSlideshow_meta_image_fields($cnt, $p = null) {

    if ($p === null){

        $a = $b = $c = $d = '';

    }else{

        $a = $p['id'];

        $headline = $p['headline'];

    }

    $image_src = wp_get_attachment_image_src( $a, "pageSlideshow_teaserThumb");
    $attach_data = wp_generate_attachment_metadata( $a, get_attached_file($a) );
    wp_update_attachment_metadata( $a,  $attach_data );
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





add_action("add_meta_boxes", "pageSlideshow_object_init");



function pageSlideshow_object_init(){

    add_meta_box("pageSlideshow_meta_image_id", "Slideshow","pageSlideshow_meta_image", "page", "normal", "low");

}



function pageSlideshow_slideshow_image_enqueue() {

    global $typenow;

    if( $typenow == 'page' ) {

        wp_enqueue_media();

        wp_register_script( 'proseed-meta-image', plugins_url( 'js/meta-image.js', __FILE__ ), array( 'jquery' ) );

        wp_localize_script( 'proseed-meta-image', 'pageSlideshow_meta_image',

            array(
                'title' => __('Choose or Upload','page-slideshow'),
                'button' => __('Apply Image','page-slideshow')
            )

        );

        wp_enqueue_script( 'jquery-ui' );

        wp_enqueue_script( 'proseed-meta-image' );

        wp_enqueue_style('proseed-meta-image-style',plugins_url( 'css/meta-image.css', __FILE__ ));

    }

}

add_action( 'admin_enqueue_scripts', 'pageSlideshow_slideshow_image_enqueue' );

function pageSlideshow_meta_image(){

    global $post;



    $data = get_post_meta($post->ID,"image_data",true);

    echo '<div>';

    echo '<ul id="proseed-image-items">';

    $c = 0;

    if (count($data) > 0){

        foreach((array)$data as $p ){

            if (isset($p['id'])){

                pageSlideshow_meta_image_fields($c,$p);

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

                $('#proseed-image-items').append('<?php echo implode('',explode("\n",pageSlideshow_meta_image_fields('count'))); ?>'.replace(/count/g, count));

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





add_action('save_post', 'pageSlideshow_save_details');



function pageSlideshow_save_details($post_id){

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

function pageSlideshow_insert_custom_image_sizes( $sizes ) {

    global $_wp_additional_image_sizes;

    if ( empty($_wp_additional_image_sizes) )

        return $sizes;



    foreach ( $_wp_additional_image_sizes as $id => $data ) {

        if ( !isset($sizes[$id]) )

            $sizes[$id] = ucfirst( str_replace( '-', ' ', $id ) );

    }

    return $sizes;

}

add_filter( 'image_size_names_choose', 'pageSlideshow_insert_custom_image_sizes' );

function pageSlideshow_slideshow($content){
    if(get_post_type()=='page'){
        global $post;
        wp_enqueue_style('proseed-meta-flexslider', plugins_url('css/flexslider.css', __FILE__));


        wp_enqueue_media();
        wp_register_script('flexslider', plugins_url('js/jquery.flexslider-min.js', __FILE__), array('jquery'));
        wp_register_script('page-slideshow-js', plugins_url('js/page-slideshow.js', __FILE__), array('flexslider'));

        $script_params = array(
            'animation' => get_option('proseed-slideshow-animation'),
            'direction' => get_option('proseed-slideshow-direction'),
            'control' => get_option('proseed-slideshow-control')
        );

        wp_localize_script( 'page-slideshow-js', 'pageSlideshow', $script_params );
        wp_enqueue_script('flexslider');
        wp_enqueue_script('page-slideshow-js');

        $images = get_post_meta($post->ID, "image_data", true);
        if ($images) {
            $slideshow = '';
            if( get_option('proseed-slideshow-position')==1) $slideshow .= $content;
            if (count($images) > 1) {
                $slideshow .= '<div class="flexslider">';
                $slideshow .= '<ul class="slides">';
                foreach ($images as $image) {
                    $slideshow .= '<li>';
                    $slideshow .= wp_get_attachment_image($image[id], 'pageSlideshow_slideThumb');
                    $slideshow .= '<p class="flex-caption">' . $image[headline] . '</p>';
                    $slideshow .= '</li>';
                }

                $slideshow .= '</ul>';
                $slideshow .= '</div>';
            } else {
                $slideshow = wp_get_attachment_image($images[1][id], 'pageSlideshow_slideThumb');
            }
            if( get_option('proseed-slideshow-position')==0) $slideshow .= $content;
            return $slideshow;
        }else{
            return $content;
        }
    }else{
        return $content;
    }

}
add_filter('the_content','pageSlideshow_slideshow');

/* Options page */

if ( is_admin() ){ // admin actions

add_action( 'admin_menu', 'pageSlideshow_plugin_menu' );
  add_action( 'admin_init', 'pageSlideshow_register_settings' );
} else {
  // non-admin enqueues, actions, and filters
}

function pageSlideshow_register_settings() {
    register_setting( 'page-slideshow-options', 'proseed-slideshow-position' );
    register_setting( 'page-slideshow-options', 'proseed-slideshow-animation' );
    register_setting( 'page-slideshow-options', 'proseed-slideshow-control' );
    register_setting( 'page-slideshow-options', 'proseed-slideshow-direction' );
    register_setting( 'page-slideshow-options', 'proseed-slideshow-imagesize-width' );
    register_setting( 'page-slideshow-options', 'proseed-slideshow-imagesize-height' );
}

function pageSlideshow_plugin_menu() {
	add_options_page( 'Slideshow options', 'Page slideshow', 'manage_options', 'proseed-identifier', 'pageSlideshow_plugin_options' );
}


function pageSlideshow_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
    $position = array(
        array(
            'name' => __('above','page-slideshow'),
            'id' => 0
        ),
        array(
            'name' => __('below','page-slideshow'),
            'id' => 1
        ),
    );
    $animation = array(
        array(
            'name' => __('fade','page-slideshow'),
            'id' => 'fade'
        ),
        array(
            'name' => __('slide','page-slideshow'),
            'id' => 'slide'
        ),
    );
?>
    <div class="wrap">
        <h2><?php _e('Settings') ?> › <?php _e('Page slideshow options') ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'page-slideshow-options' ); ?>
            <?php do_settings_sections( 'page-slideshow-options' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="proseed-slideshow-position"><?php _e('Position','page-slideshow') ?></label>
                    </th>
                    <td>
                        <select name="proseed-slideshow-position" id="proseed-slideshow-position">
                            <?php foreach($position as $option): ?>
                                <option value="<?php echo $option['id'] ?>"<?php if(get_option('proseed-slideshow-position')==$option['id']) echo ' selected="selected"'; ?>><?php echo $option['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description" id="position-description">
                            <?php _e('choose the position of your slideshow whether to be "above" or "below".','page-slideshow'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="proseed-slideshow-position"><?php _e('Animation','page-slideshow') ?></label>
                    </th>
                    <td>
                        <select name="proseed-slideshow-animation" id="proseed-slideshow-animation">
                            <?php foreach($animation as $option): ?>
                                <option value="<?php echo $option['id'] ?>"<?php if(get_option('proseed-slideshow-animation')==$option['id']) echo ' selected="selected"'; ?>><?php echo $option['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description" id="position-description">
                            <?php _e('Select your animation type, "fade" or "slide"','page-slideshow'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="proseed-slideshow-control"><?php _e('Navigation dots','page-slideshow') ?></label>
                    </th>
                    <td>
                        <input type="checkbox"<?php if(get_option('proseed-slideshow-control')=='on') echo 'checked="checked"'; ?> name="proseed-slideshow-control" id="proseed-slideshow-control" />
                        <p class="description" id="position-description">
                            <?php _e('Create navigation for paging control of each slide?','page-slideshow'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="proseed-slideshow-control"><?php _e('Navigation arrows','page-slideshow') ?></label>
                    </th>
                    <td>
                        <input type="checkbox"<?php if(get_option('proseed-slideshow-direction')=='on') echo 'checked="checked"'; ?> name="proseed-slideshow-direction" id="proseed-slideshow-direction" />
                        <p class="description" id="position-description">
                            <?php _e('Create navigation for previous/next navigation?','page-slideshow'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="proseed-slideshow-imagesize-width"><?php _e('Image size','page-slideshow') ?></label>
                    </th>
                    <td>
                        <input name="proseed-slideshow-imagesize-width" type="text" id="proseed-slideshow-imagesize-width" value="<?php echo esc_attr( get_option('proseed-slideshow-imagesize-width') ); ?>" /> x
                        <input name="proseed-slideshow-imagesize-height" type="text" id="proseed-slideshow-imagesize-height" value="<?php echo esc_attr( get_option('proseed-slideshow-imagesize-height') ); ?>" />
                        <p class="description" id="position-description">
                            <?php _e('set width and height for your slideshow images.','page-slideshow'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php }