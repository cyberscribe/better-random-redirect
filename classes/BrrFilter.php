<?php
class BrrFilter {

    public static function filters_required() {
        return false;
    }

    public static function load_filters() {
        if (BrrFilter::filters_required()) {
            add_filter('brr_transient_id_filter', array( get_called_class(), 'brr_transient_id_filter' ));
            add_filter('brr_additional_where_filter', array( get_called_class(), 'brr_additional_where_filter' ));
            add_filter('brr_url_base_filter', array( get_called_class(), 'brr_url_base_filter' ));
            add_filter('brr_admin_table_filter', array( get_called_class(), 'brr_admin_table_filter' ));
        }
    }

    public static function brr_transient_id_filter($transient_id, $lang = '') {
        return $transient_id;
    }

    public static function brr_additional_where_filter( $additional_where ) {
        return $additional_where;
    }
    public static function brr_url_base_filter($url_base, $lang = '') {
        return $url_base;
    }
    public static function brr_admin_table_filter($html,$lang) {
        return $html;
    }

}
