<?php
class QtranslatexFilter extends BrrFilter {

    public static function filters_required() {
        global $q_config;
        if (isset($q_config)) {
            return true;
        } else {
            return false;
        }
    }

    public static function load_filters() {
        global $wpdb;
        add_option('brr_query_qtranslate_pattern', ' and '.$wpdb->posts.'.post_content like %s ');
        add_filter('brr_transient_id_filter', array( 'QtranslatexFilter', 'brr_transient_id_filter' ));
        add_filter('brr_additional_where_filter', array( 'QtranslatexFilter', 'brr_additional_where_filter' ));
        add_filter('brr_url_base_filter', array( 'QtranslatexFilter', 'brr_url_base_filter' ));
        add_filter('brr_admin_table_filter', array( 'QtranslatexFilter', 'brr_admin_table_filter' ));
    }

    public static function brr_transient_id_filter($transient_id, $lang = '') {
        global $q_config;
        if (isset($q_config['default_language']) && $lang != $q_config['default_language']) {
            $transient_id = $transient_id . '_qtranslate_'.$lang; 
        }
        return $transient_id;
    }

    public static function brr_additional_where_filter( $additional_where ) {
        global $wpdb, $q_config;
        if(isset($q_config['language']) && $q_config['language'] != $q_config['default_language']) {
                $tmp_additional_where = $wpdb->prepare(get_option('brr_query_qtranslate_pattern'), '%[:'.$q_config['language'].']%');
        }
        if (strlen($additional_where) > 0) {
            return $additional_where . ' AND ' . $tmp_additional_where;
        } else {
            return $tmp_additional_where;
        }
    }
    public static function brr_url_base_filter($url_base, $lang = '') {
        global $q_config;
        if (isset($q_config['language'])) {
            $url_base .= '/' . $lang;
        }
        return $url_bse;
    }

    public static function brr_admin_table_filter($html, $lang = '') {
        global $q_config;
        if(isset($q_config['enabled_languages']) && sizeof($q_config['enabled_languages']) > 0) {
            foreach($q_config['enabled_languages'] as $lang) {
                $output .= '<tr>'."\n";
                $output .= '    <td><code>[random-url lang="'.$lang.'"]</code></td>'."\n";
                $output .= '    <td><a href="'.site_url().'/'.$lang.'/'.get_option('brr_default_slug').'/'.'" target="_blank">'."\n";
                $output .= '    '.site_url().'/'.$lang.'/'.get_option('brr_default_slug').'/'."\n";
                $output .= '    </a></td>'."\n";
                $output .= '    <td>'.sprintf(__('Random post in the %s language','better_random_redirect'), $q_config['language_name'][$lang]).'</td>'."\n";
                $output .= '</tr>'."\n";
            }
        }
        return $html . "\n" . $output;
    }
}
