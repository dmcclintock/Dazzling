<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package dazzling
 */

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * @param array $args Configuration arguments.
 * @return array
 */
function dazzling_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'dazzling_page_menu_args' );

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function dazzling_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	return $classes;
}
add_filter( 'body_class', 'dazzling_body_classes' );

/**
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string The filtered title.
 */
function dazzling_wp_title( $title, $sep ) {
	global $page, $paged;

	if ( is_feed() ) {
		return $title;
	}

	// Add the blog name
	$title .= get_bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title .= " $sep $site_description";
	}

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 ) {
		$title .= " $sep " . sprintf( __( 'Page %s', 'dazzling' ), max( $paged, $page ) );
	}

	return $title;
}
add_filter( 'wp_title', 'dazzling_wp_title', 10, 2 );


// Mark Posts/Pages as Untiled when no title is used
add_filter( 'the_title', 'dazzling_title' );

function dazzling_title( $title ) {
  if ( $title == '' ) {
    return 'Untitled';
  } else {
    return $title;
  }
}

// Add Filters

add_filter('widget_text', 'do_shortcode'); // Allow shortcodes in Dynamic Sidebar

/**
 * Sets the authordata global when viewing an author archive.
 *
 * This provides backwards compatibility with
 * http://core.trac.wordpress.org/changeset/25574
 *
 * It removes the need to call the_post() and rewind_posts() in an author
 * template to print information about the author.
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @return void
 */
function dazzling_setup_author() {
	global $wp_query;

	if ( $wp_query->is_author() && isset( $wp_query->post ) ) {
		$GLOBALS['authordata'] = get_userdata( $wp_query->post->post_author );
	}
}
add_action( 'wp', 'dazzling_setup_author' );

/************* search form *****************/

// Search Form
function dazzling_wpsearch($form) {
    $form = '<form method="get" class="form-search" action="' . home_url( '/' ) . '">
  <div class="row">
    <div class="col-lg-12">
      <div class="input-group">
        <input type="text" class="form-control search-query" value="' . get_search_query() . '" name="s" id="s" placeholder="'. esc_attr__('Search...','dazzling') .'">
        <span class="input-group-btn">
          <button type="submit" class="btn btn-default" name="submit" id="searchsubmit" value="Go"><span class="glyphicon glyphicon-search"></span></button>
        </span>
      </div>
    </div>
  </div>
</form>';
    return $form;
} // don't remove this bracket!

/****************** password protected post form *****/

add_filter( 'the_password_form', 'custom_password_form' );

function custom_password_form() {
	global $post;
	$label = 'pwbox-'.( empty( $post->ID ) ? rand() : $post->ID );
	$o = '<form class="protected-post-form" action="' . get_option('siteurl') . '/wp-login.php?action=postpass" method="post">
  <div class="row">
    <div class="col-lg-10">
        ' . __( "<p>This post is password protected. To view it please enter your password below:</p>" ,'dazzling') . '
        <label for="' . $label . '">' . __( "Password:" ,'dazzling') . ' </label>
      <div class="input-group">
        <input class="form-control" value="' . get_search_query() . '" name="post_password" id="' . $label . '" type="password">
        <span class="input-group-btn"><button type="submit" class="btn btn-default" name="submit" id="searchsubmit" vvalue="' . esc_attr__( "Submit",'dazzling' ) . '">' . __( "Submit" ,'dazzling') . '</button>
        </span>
      </div>
    </div>
  </div>
</form>';
	return $o;
}

// Add Bootstrap classes for table
add_filter( 'the_content', 'dazzling_add_custom_table_class' );
function dazzling_add_custom_table_class( $content ) {
    return str_replace( '<table>', '<table class="table table-hover">', $content );
}

// //Display social links
function dazzling_social(){
    $services = array ('facebook','twitter','googleplus','youtube','linkedin','pinterest','rss','tumblr','flickr','instagram','dribbble','skype');

    echo '<div id="social" class="social"><ul>';

    foreach ( $services as $service ) :

        $active[$service] = of_get_option ('social_'.$service);
        if ($active[$service]) { echo '<li><a href="'.$active[$service].'" class="social-icon '. $service .'" title="'. __('Follow us on ','dazzling').$service.'"><i class="social_icon fa fa-'.$service.'"></i></a></li>';}

    endforeach;
    echo '</ul></div>';

}

// header menu (should you choose to use one)
function dazzling_header_menu() {
        // display the WordPress Custom Menu if available
        wp_nav_menu(array(
                    'menu'              => 'primary',
                    'theme_location'    => 'primary',
                    'depth'             => 2,
                    'container'         => 'div',
                    'container_class'   => 'collapse navbar-collapse navbar-ex1-collapse',
                    'menu_class'        => 'nav navbar-nav',
                    'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
                    'walker'            => new wp_bootstrap_navwalker()
        ));
} /* end header menu */

// footer menu (should you choose to use one)
function dazzling_footer_links() {
        // display the WordPress Custom Menu if available
        wp_nav_menu(array(
                'container' => '',                              // remove nav container
                'container_class' => 'footer-links clearfix',   // class of container (should you choose to use it)
                'menu' => __( 'Footer Links', 'dazzling' ),   // nav name
                'menu_class' => 'nav footer-nav clearfix',      // adding custom nav class
                'theme_location' => 'footer-links',             // where it's located in the theme
                'before' => '',                                 // before the menu
                'after' => '',                                  // after the menu
                'link_before' => '',                            // before each link
                'link_after' => '',                             // after each link
                'depth' => 0,                                   // limit the depth of the nav
                'fallback_cb' => 'dazzling_footer_links_fallback'  // fallback function
        ));
} /* end dazzling footer link */

// Get Post Views - for Popular Posts widget
function dazzling_getPostViews($postID){
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return "0 View";
    }
    return $count.' Views';
}
function dazzling_setPostViews($postID) {
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

// Call for action text area
function dazzling_call_for_action() {
  if ( is_front_page() && of_get_option('w2f_cfa_text')!=''){
    echo '<div class="cfa">';
      echo '<div class="container">';
        echo '<div class="col-md-8">';
          echo '<span class="cfa-text">'. of_get_option('w2f_cfa_text').'</span>';
          echo '</div>';
          echo '<div class="col-md-4">';
          echo '<a class="btn btn-lg cfa-button" href="'. of_get_option('w2f_cfa_link'). '">'. of_get_option('w2f_cfa_button'). '</a>';
          echo '</div>';
      echo '</div>';
    echo '</div>';
  } else; {
  //Do nothing
  }
}

// Featured image slider
function dazzling_featured_slider() {
    if ( is_front_page() && of_get_option('dazzling_slider_checkbox') == 1 ) {
      echo '<div class="flexslider">';
        echo '<ul class="slides">';

          $count = of_get_option('dazzling_slide_number');
          $slidecat =of_get_option('dazzling_slide_categories');

          $query = new WP_Query( array( 'cat' =>$slidecat,'posts_per_page' =>$count ) );
          if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();

            echo '<li>';
              if ( (function_exists('has_post_thumbnail')) && (has_post_thumbnail()) ) :
                echo get_the_post_thumbnail();
              endif;

                echo '<div class="flex-caption">';
                  echo '<a href="'. get_permalink() .'">';
                    if ( get_the_title() != '' ) echo '<h2 class="entry-title">'. get_the_title().'</h2>';
                    if ( get_the_excerpt() != '' ) echo '<div class="excerpt">' . get_the_excerpt() .'</div>';
                  echo '</a>';
                echo '</div>';

                endwhile;
              endif;

            echo '</li>';
        echo '</ul>';
      echo ' </div>';
    } else; {
      // Do nothing
    }
}

/**
 * function to show the footer info, copyright information
 */
function dazzling_footer_info() {
global $dazzling_footer_info;
  printf( __( 'Theme by %1$s Powered by %2$s', 'dazzling' ) , '<a href="http://colorlib.com/wp/" target="_blank">Colorlib</a>', '<a href="http://wordpress.org/" target="_blank">WordPress</a>');
}

// Get theme options

if (!function_exists('get_dazzling_theme_options'))  {
    function get_dazzling_theme_options(){

      echo '<style type="text/css">';

      if ( of_get_option('link_color')) {
        echo 'a, #infinite-handle span {color:' . of_get_option('link_color') . '}';
      }
      if ( of_get_option('link_hover_color')) {
        echo 'a:hover {color: '.of_get_option('link_hover_color', '#000').';}';
      }
      if ( of_get_option('link_active_color')) {
        echo 'a:active {color: '.of_get_option('link_active_color', '#000').';}';
      }
      if ( of_get_option('element_color')) {
        echo '.btn-default, .label-default, .flex-caption h2, .navbar-default .navbar-nav > .active > a, .navbar-default .navbar-nav > .active > a:hover, .navbar-default .navbar-nav > .active > a:focus, .navbar-default .navbar-nav > li > a:hover, .navbar-default .navbar-nav > li > a:focus, .navbar-default .navbar-nav > .open > a, .navbar-default .navbar-nav > .open > a:hover, .navbar-default .navbar-nav > .open > a:focus, .dropdown-menu > li > a:hover, .dropdown-menu > li > a:focus, .navbar-default .navbar-nav .open .dropdown-menu > li > a:hover, .navbar-default .navbar-nav .open .dropdown-menu > li > a:focus {background-color: '.of_get_option('element_color', '#000').'; border-color: '.of_get_option('element_color', '#000').';} .btn.btn-default.read-more, .entry-meta .fa, .site-main [class*="navigation"] a, .more-link { color: '.of_get_option('element_color', '#000').'}';
      }
      if ( of_get_option('element_color_hover')) {
        echo '.btn-default:hover, .label-default[href]:hover, .label-default[href]:focus, #infinite-handle span:hover, .btn.btn-default.read-more:hover, .btn-default:hover, .scroll-to-top:hover, .btn-default:focus, .btn-default:active, .btn-default.active, .site-main [class*="navigation"] a:hover, .more-link:hover, #image-navigation .nav-previous a:hover, #image-navigation .nav-next a:hover  { background-color: '.of_get_option('element_color_hover', '#000').'; border-color: '.of_get_option('element_color_hover', '#000').'; }';
      }
      if ( of_get_option('cfa_bg_color')) {
        echo '.cfa { background-color: '.of_get_option('cfa_bg_color', '#000').'; } .cfa-button:hover {color: '.of_get_option('cfa_bg_color', '#000').';}';
      }
      if ( of_get_option('cfa_color')) {
        echo '.cfa-text { color: '.of_get_option('cfa_color', '#000').';}';
      }
      if ( of_get_option('cfa_btn_color')) {
        echo '.cfa-button {border-color: '.of_get_option('cfa_btn_color', '#000').';}';
      }
      if ( of_get_option('cfa_btn_txt_color')) {
        echo '.cfa-button {color: '.of_get_option('cfa_btn_txt_color', '#000').';}';
      }
      if ( of_get_option('heading_color')) {
        echo 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6, .entry-title {color: '.of_get_option('heading_color', '#000').';}';
      }
      if ( of_get_option('top_nav_bg_color')) {
        echo '.navbar.navbar-default {background-color: '.of_get_option('top_nav_bg_color', '#000').';}';
      }
      if ( of_get_option('top_nav_link_color')) {
        echo '.navbar-default .navbar-nav > li > a, .navbar-default .navbar-nav > .open > a, .navbar-default .navbar-nav > .open > a:hover, .navbar-default .navbar-nav > .open > a:focus, .navbar-default .navbar-nav > .active > a, .navbar-default .navbar-nav > .active > a:hover, .navbar-default .navbar-nav > .active > a:focus { color: '.of_get_option('top_nav_link_color', '#000').';}';
      }
      if ( of_get_option('top_nav_dropdown_bg')) {
        echo '.dropdown-menu, .dropdown-menu > .active > a, .dropdown-menu > .active > a:hover, .dropdown-menu > .active > a:focus {background-color: '.of_get_option('top_nav_dropdown_bg', '#000').';}';
      }
      if ( of_get_option('top_nav_dropdown_item')) {
        echo '.navbar-default .navbar-nav .open .dropdown-menu > li > a { color: '.of_get_option('top_nav_dropdown_item', '#000').';}';
      }
      if ( of_get_option('footer_bg_color')) {
        echo '#colophon {background-color: '.of_get_option('footer_bg_color', '#000').';}';
      }
      if ( of_get_option('footer_text_color')) {
        echo '#footer-area, .site-info {color: '.of_get_option('footer_text_color', '#000').';}';
      }
      if ( of_get_option('footer_widget_bg_color')) {
        echo '#footer-area {background-color: '.of_get_option('footer_widget_bg_color', '#000').';}';
      }
      if ( of_get_option('footer_link_color')) {
        echo '.site-info a, #footer-area a {color: '.of_get_option('footer_link_color', '#000').';}';
      }
      if ( of_get_option('social_color')) {
        echo '.social-icon {color: '.of_get_option('social_color', '#000').' !important ;}';
      }
      if ( of_get_option('social_hover_color')) {
        echo '.social-icon:hover {color: '.of_get_option('social_hover_color', '#000').'!important ;}';
      }
      $typography = of_get_option('main_body_typography');
      if ( $typography ) {
        echo '.entry-content {font-family: ' . $typography['face'] . '; font-size:' . $typography['size'] . '; font-weight: ' . $typography['style'] . '; color:'.$typography['color'] . ';}';
      }
      if ( of_get_option('custom_css')) {
        echo of_get_option( 'custom_css', 'no entry' );
      }
        echo '</style>';
    }
}
add_action('wp_head','get_dazzling_theme_options',10);

// Theme Options sidebar
add_action( 'optionsframework_after','dazzling_options_display_sidebar' );

function dazzling_options_display_sidebar() { ?>
  <!-- Twitter -->
  <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>

  <!-- Facebook -->
    <div id="fb-root"></div>
  <div id="fb-root"></div>
  <script>(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=328285627269392";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));</script>

  <div id="optionsframework-sidebar" class="metabox-holder">
    <div id="optionsframework" class="postbox">
        <h3><?php _e('Support and Documentation','dazzling') ?></h3>
          <div class="inside">
              <div id="social-share">
                <div class="fb-like" data-href="https://www.facebook.com/colorlib" data-send="false" data-layout="button_count" data-width="90" data-show-faces="true"></div>
                <div class="tw-follow" ><a href="https://twitter.com/colorlib" class="twitter-follow-button" data-show-count="false">Follow @colorlib</a></div>
              </div>
                <p><b><a href="http://colorlib.com/wp/support/dazzling"><?php _e('dazzling Documentation','dazzling'); ?></a></b></p>
                <p><?php _e('The best way to contact us with <b>support questions</b> and <b>bug reports</b> is via','dazzling') ?> <a href="http://colorlib.com/wp/forums"><?php _e('Colorlib support forum','dazzling') ?></a>.</p>
                <p><?php _e('If you like this theme, I\'d appreciate any of the following:','dazzling') ?></p>
                <ul>
                    <li><a class="button" href="http://wordpress.org/support/view/theme-reviews/dazzling?filter=5" title="<?php esc_attr_e('Rate this Theme', 'dazzling'); ?>" target="_blank"><?php printf(__('Rate this Theme','dazzling')); ?></a></li>
                    <li><a class="button" href="http://www.facebook.com/colorlib" title="Like Colorlib on Facebook" target="_blank"><?php printf(__('Like on Facebook','dazzling')); ?></a></li>
                    <li><a class="button" href="http://twitter.com/colorlib/" title="Follow Colrolib on Twitter" target="_blank"><?php printf(__('Follow on Twitter','dazzling')); ?></a></li>
                </ul>
          </div>
      </div>
    </div>
<?php }

/*
 * This one shows/hides the an option when a checkbox is clicked.
 *
 */

add_action( 'optionsframework_custom_scripts', 'optionsframework_custom_scripts' );

function optionsframework_custom_scripts() { ?>

<script type="text/javascript">
jQuery(document).ready(function() {

  jQuery('#dazzling_slider_checkbox').click(function() {
      jQuery('#section-dazzling_slide_categories').fadeToggle(400);
  });

  if (jQuery('#dazzling_slider_checkbox:checked').val() !== undefined) {
    jQuery('#section-dazzling_slide_categories').show();
  }

  jQuery('#dazzling_slider_checkbox').click(function() {
      jQuery('#section-dazzling_slide_number').fadeToggle(400);
  });

  if (jQuery('#dazzling_slider_checkbox:checked').val() !== undefined) {
    jQuery('#section-dazzling_slide_number').show();
  }

});
</script>


<?php
}