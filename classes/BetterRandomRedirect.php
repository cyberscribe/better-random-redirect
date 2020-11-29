<?php
class BetterRandomRedirect {

    public static function init() {
        add_action( 'plugins_loaded', array('BetterRandomRedirect', 'load_textdomain' ));
        add_action( 'admin_menu', array('BetterRandomRedirect', 'register_menu_page' ));
        add_action( 'admin_init', array('BetterRandomRedirect', 'register_settings' ));
        add_action( 'template_redirect', array('BetterRandomRedirect', 'do_redirect' ));
        add_shortcode('random-url',array('BetterRandomRedirect', 'random_url_shortcode'));
        BetterRandomRedirect::load_filters();
    }

    private static function load_filters() {
        foreach (glob(plugin_dir_path( __FILE__ ) . '*Filter.php') as $full_path) {
            $matches = array();
            preg_match('#([^/]*Filter)\.php$#', $full_path, $matches);
            if (isset($matches[1])) {
                $ClassName = $matches[1];
                call_user_func( array($ClassName, 'load_filters'));
            }
        }
    }

    public static function load_textdomain() {
        load_plugin_textdomain( 'better_random_redirect', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    public static function register_menu_page(){
        add_options_page( __('Better Random Redirect Options','better_random_redirect'), __('Better Random Redirect','better_random_redirect'), 'manage_options', str_replace('classes/','', plugin_dir_path(  __FILE__ )).'admin.php');
    }

    public static function register_settings() {
        global $wpdb;

        /* user-configurable values */
        add_option('brr_default_slug', __('random','better_random_redirect'));
        add_option('brr_default_timeout', 3600);
        add_option('brr_default_category', '');
        add_option('brr_default_posttype', 'post');
        
        /* global non-configurable values used across functions */
        add_option('brr_transient_id','better_random_redirect_post_ids');
        add_option('brr_query_posttype_pattern','SELECT %s FROM '.$wpdb->posts.' where post_type=\'%s\' and post_status=\'publish\' and post_password = \'\'');
        add_option('brr_query_posttype_category_pattern', 'SELECT %s FROM '.$wpdb->posts.' p '.
                     'LEFT OUTER JOIN '.$wpdb->term_relationships.' r ON r.object_id = p.ID '.
                     'LEFT OUTER JOIN '.$wpdb->term_taxonomy.' x ON x.term_taxonomy_id = r.term_taxonomy_id '.
                     'LEFT OUTER JOIN '.$wpdb->terms.' t ON t.term_id = x.term_id '.
                     ' where post_type=\'%s\' and post_status=\'publish\' and post_password = \'\' and t.slug=\'%s\'');
        
        /* user-configurable value checking functions */
        register_setting( 'better_random_redirect', 'brr_default_slug', array('BetterRandomRedirect', 'slug_check') );
        register_setting( 'better_random_redirect', 'brr_default_category', array('BetterRandomRedirect', 'cat_check') );
        register_setting( 'better_random_redirect', 'brr_default_posttype', array('BetterRandomRedirect', 'posttype_check' ));
        register_setting( 'better_random_redirect', 'brr_default_timeout', array('BetterRandomRedirect', 'integer_check' ));  
    }

    public static function slug_check( $string ) {
        return filter_var($string, FILTER_SANITIZE_URL); //must consist of valid URL characters
    }

    public static function cat_check( $string ) {
        if ($string == '') {
            return $string; //blank is valid for 'all categories'
        }
        $string = filter_var($string, FILTER_SANITIZE_STRING); //must be a valid string
        if (term_exists($string,'category')) { //must exist in category taxonomy
            return $string;
        } else {
            return '';
        }
    }

    public static function posttype_check( $string ) {
        if ($string == 'post') {
            return $string; //post is valid default
        }
        $string = filter_var($string, FILTER_SANITIZE_STRING); //must be a valid string
        if (post_type_exists($string)) { //must exist in category taxonomy
            return $string;
        } else {
            return 'post';
        }
    }

    public static function integer_check( $int ) {
        return filter_var($int, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function random_url_shortcode( $atts ) {
        global $wpdb;
        global $q_config;
        
        // get some options
        $url_slug = get_option('brr_default_slug'); //slug to use in URL
        $expiration = get_option('brr_default_timeout'); //how long to cache the list of valid posts (in seconds)
        $transient_id = get_option('brr_transient_id');
        
        // extract shortcode attribute
        extract( shortcode_atts( array(
                    'cat' => '',
                    'posttype' => 'post',
                    'lang' => strstr( get_locale(), '_', true),
                ), $atts, 'better_random_redirect' ) );
        
        if ($posttype == 'page') {
            $category = '';
        }

        // if category exists, use category-specific transient ID
        if ($cat && strlen($cat) > 0 && term_exists($cat,'category')) {
            $category = $cat;
            $transient_id = $transient_id . '_category_'.$category;
        }
        // if posttype exists, use posttype-specific transient ID
        if ($posttype && $posttype != 'post') {
            $transient_id = $transient_id . '_posttype_'.$posttype; 
        }
        $transient_id = apply_filters('brr_transient_id_filter', $transient_id, $lang);
        
        // check the transient cache first, if the post id index maximum is not found or expired, regenerate it
        if (false === ($max = get_transient( $transient_id . '_max'))) {
            
            //Use category-specific query
            if (strlen($category) > 0) {
                $query = $wpdb->prepare( sprintf( get_option('brr_query_posttype_category_pattern'), 'count(*)', '%s','%s') ,$posttype,$category);
            //Use posttype-specific query
            } else {
                $query = $wpdb->prepare(sprintf(get_option('brr_query_posttype_pattern'),'count(*)','%s'),$posttype);
            }
            $additional_where = '';
            $additional_where = apply_filters('brr_additional_where_filter',$additional_where);
            $query .= $additional_where;
            
            $total = $wpdb->get_var($query);
            $max = $total - 1; //use index range, not total count
            if ($max < 0) {
                $max = 0;
            }
            set_transient( $transient_id . '_max', $max, $expiration);
        }
        // build URL base
        $url_base = site_url();
        $url_base = apply_filters('brr_url_base_filter',$url_base, $lang);
        $url_base .= '/'.$url_slug.'/';
        
        // build query string
        $query_data = array();
        if (strlen($category) > 0) {
            $query_data['cat'] = $category;
        }
        if (strlen($posttype) > 0) {
            $query_data['posttype'] = $posttype;
        }
        $query_data['r'] = mt_rand(0,$max);
        $query_part = http_build_query($query_data);
        if ($query_part && strlen($query_part) > 0) {
            $url = $url_base . '?' . $query_part;
        } else {
            $url = $url_base;
        }
        return $url;
    }

    public static function do_redirect() {
        global $wpdb;
        global $q_config;

        //get URL slug for matching
        $url_slug = get_option('brr_default_slug'); //slug to use in URL
        
        // parse site URL for matching
        $url_base = parse_url(site_url(),PHP_URL_PATH);
        $url_current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (substr($url_base,-1) != '/') {
            $url_base = $url_base . '/';
        }
        if (substr($url_base,0,1) != '/') {
            $url_base = '/'.$url_base;
        }
        $match_url = $url_base;
        if (isset($q_config['language']) && $q_config['language'] != $q_config['default_language']) {
            $match_url .= $q_config['language'] . '/';
        }
        $match_url .= $url_slug;
        // if we are in a designated randomiser URL location, get to work
        if (  $match_url == $url_current || $match_url.'/' == $url_current ) {
            // get some options
            $expiration = get_option('brr_default_timeout');; //how long to cache the list of valid posts (in seconds)
            $transient_id = get_option('brr_transient_id');
            
            // use URL category if available on query line, otherwise default
            if (isset($_GET['cat']) && term_exists($_GET['cat'],'category')) {
                $category = $_GET['cat'];
            } else {
                $category = get_option('brr_default_category');
            }
            
            // use URL posttype if available on query line, otherwise default
            if (isset($_GET['posttype']) && post_type_exists($_GET['posttype'])) {
                $posttype = $_GET['posttype'];
            } else {
                $posttype = get_option('brr_default_posttype');
                if (strlen($posttype) == 0) {
                    $posttype = 'post';
                }
            }
            
            if ($posttype == 'page') {
                $category = '';
            }
            
            // if category exists, use category-specific transient ID
            if ($category && strlen($category) > 0) {
                $transient_id = $transient_id . '_category_'.$category; 
            }
            
            // if posttype exists, use posttype-specific transient ID
            if ($posttype && $posttype != 'post') {
                $transient_id = $transient_id . '_posttype_'.$posttype; 
            }
            
            // check the transient cache first, if either the post id list or count is not found or expired, regenerate both
            if ( false === ( $post_ids = get_transient( $transient_id ) ) || false === ($max = get_transient( $transient_id . '_max')) ) {
            
                
                //Use category-specific query
                if (strlen($category) > 0) {
                    $query = $wpdb->prepare( sprintf( get_option('brr_query_posttype_category_pattern'), 'ID', '%s','%s') , $posttype, $category);
                //Use posttype-specific query
                } else {
                    $query = $wpdb->prepare(sprintf(get_option('brr_query_posttype_pattern'),'ID','%s'),$posttype);
                }

                /* additional WHERE filters (to be used with AND) */
                $additional_where = '';
                $additional_where = apply_filters('brr_additional_where_filter',$additional_where);
                $transient_id = apply_filters('brr_transient_id_filter', $transient_id, $lang);
                $query .= $additional_where;

                // query for valid post IDs
                $post_ids = $wpdb->get_col( $query );
                
                // set the transient cache for post IDs and index maximum
                set_transient($transient_id, $post_ids, $expiration);
                $max = (sizeof($post_ids) - 1);
                set_transient( $transient_id . '_max', $max, $expiration);
            }
            
            // if we have at least one post to choose from
            if ($max >= 0) { //max is indexed from zero to count minus one
            
                // if the r-value on the get line is a valid integer-type string and in range, use that for the index, otherwise generate a random integer between 0 and max
                if (isset($_GET['r']) && is_numeric($_GET['r']) &&  ( ctype_digit($_GET['r']) || is_int($_GET['r']) ) && $_GET['r'] >= 0 && $_GET['r'] <= $max ) {
                    $index = filter_var($_GET['r'], FILTER_SANITIZE_NUMBER_INT);
                } else {
                    $index = mt_rand(0,$max); // get a random index in PHP
                }
                
                // if a valid post id exists at that index
                if (isset($post_ids[$index])) {
                    $id = $post_ids[$index];
                    
                    // query for posts multiple times if needed, in case the cache and actual posts are considerably out of sync, but don't get into an infiinte loop
                    $max_count = 10; //how many "lucky dip" requests to make before giving up, 
                    do {
                        $post = get_post( $id );
                        if ($post) {
                            // found a valid random post, redirect to it
                            BetterRandomRedirect::force_redirect_no_cache();
                            wp_redirect ( get_permalink ( $post->ID ) , 302 );
                            exit; //job done
                        } else {
                            // not a valid post, regenerate index and id and try again up to $max_count times
                            $index = mt_rand(0,$max);
                            $id = $post_ids[$index];
                        }
                        $count++;
                    } while (!$post && $count < $max_count); //continue as long as we haven't exceeded $max_count
                } // initial index was not in valid range
            } // no post IDs in the set to pick from
        } // we are not in the randomiser URL
    }

    public static function force_redirect_no_cache() {
        header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
        header('Pragma: no-cache'); // HTTP 1.0.
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT'); // Proxies.
        header('X-Robots-Tag: noindex'); // Web Spiders
    }
}
