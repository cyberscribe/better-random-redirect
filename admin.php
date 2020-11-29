<?php
if (!is_admin()) {
    die();
}
?><div class="wrap">
<h2><?php _e('Better Random Redirect','better_random_redirect'); ?></h2>
<div class="highlight" id="brr_default_slug_help_text"><?php _e('Configure the URL you want to use.', 'better_random_redirect'); ?></div>
<div class="highlight" id="brr_default_category_help_text"><?php _e('Configure the category of posts you want to use as the basis for the randomiser. Leave set at "All Categories" to include posts in all categories. This value can be overridden in the shortcode and by using cat= in the url.','better_random_redirect') ?></div>
<div class="highlight" id="brr_default_posttype_help_text"><?php _e('Configure the post types you want to use as the basis for the randomiser. Leave set to the default of "Posts" to include blog posts. This value can be overridden in the shortcode and by using posttype= in the url.','better_random_redirect') ?></div>
<div class="highlight" id="brr_default_timeout_help_text"><?php _e('Cache time represents how long to cache the list of valid posts (in seconds), and therefore affects how long it takes for new posts become eligible to be part of the randomiser. Leave this setting at the default unless you have a specific reason to change it.','better_random_redirect') ?></div>
<form method="post" action="options.php">
<?php
echo settings_fields( 'better_random_redirect' );
?>
<table class="form-table">
	<tr valign="top">
            <th scope="row"><label for="id_brr_default_slug"><?php _e('Main Randomiser URL','better_random_redirect'); ?>: <span id="brr_default_slug_help" class="dashicons dashicons-editor-help"></span>
            </label></th>
	    <td><?php echo site_url(); ?>/<input type="text" id="id_brr_default_slug" name="brr_default_slug" value="<?php echo get_option('brr_default_slug'); ?>" size="12" />/</td>
	</tr>
    <tr valign="top">
            <th scope="row"><label for="id_brr_default_category"><?php _e('Category to use','better_random_redirect'); ?>: <span id="brr_default_category_help" class="dashicons dashicons-editor-help"></span></label></th>
	    <td>
	        <select name="brr_default_category" id="id_brr_default_category">
                <option value=""><?php _e('All Categories','better_random_redirect'); ?></option>
                <?php 
                $categories = get_categories();  
                foreach ($categories as $category): ?>
                    <option value="<?php echo $category->slug; ?>" <?php if ($category->slug == get_option('brr_default_category')) echo 'selected="selected"'; ?> ><?php echo $category->name; ?></option>
                <?php endforeach; ?>
	        </select>
	    </td>
	</tr>
    <tr valign="top">
            <th scope="row"><label for="id_brr_default_posttype"><?php _e('Post Type to use','better_random_redirect'); ?>: <span id="brr_default_posttype_help" class="dashicons dashicons-editor-help"></span></label></th>
	    <td>
	        <select name="brr_default_posttype" id="id_brr_default_posttype">
                <?php 
                $posttypes = get_post_types();  
                foreach ($posttypes as $posttype): ?>
                    <option value="<?php echo $posttype; ?>" <?php if ($posttype == get_option('brr_default_posttype')) echo 'selected="selected"'; ?> ><?php echo $posttype; ?></option>
                <?php endforeach; ?>
	        </select>
	    </td>
	</tr>
	<tr valign="top">
            <th scope="row"><label for="id_brr_default_timeout"><?php _e('Cache Time','better_random_redirect'); ?>: <span id="brr_default_timeout_help" class="dashicons dashicons-editor-help"></span></label></th>
	    <td><input type="number" id="id_brr_default_timeout" name="brr_default_timeout" value="<?php echo get_option('brr_default_timeout'); ?>" /> seconds</td>
	</tr>
    </table>
    <h3><?php _e('Shortcodes and URLs','better_random_redirect'); ?></h3>
    <p><?php _e('Examples based on current saved settings:'); ?></p>
    <?php 
    if (get_option('brr_default_category') == '') {
        $current_cat = strtolower(__('All Categories','better_random_redirect'));
    } else {
        $current_cat = '"'.get_option('brr_default_category').'"';
    }
    ?>
    <table>
        <tr>
            <th scope="col"><?php _e('Shortcode','better_random_redirect'); ?></th>
            <th scope="col"><?php _e('URL','better_random_redirect'); ?></th>
            <th scope="col"><?php _e('Result','better_random_redirect'); ?></th>
        </tr>
        <tr>
            <td><code>[random-url]</code></td>
            <td><a href="<?php echo site_url().'/'.get_option('brr_default_slug').'/'; ?>" target="_blank">
                <?php echo site_url().'/'.get_option('brr_default_slug').'/'; ?>
            </a></td>
            <td><?php echo sprintf(__('Random post from %s','better_random_redirect'), $current_cat); ?></a></td>
        </tr>
        <tr>
            <td><code>[random-url cat="foo"]</code></td>
            <td><a href="<?php echo site_url().'/'.get_option('brr_default_slug').'/?cat=foo'; ?>" target="_blank">
                <?php echo site_url().'/'.get_option('brr_default_slug').'/?cat=foo'; ?>
            </a></td>
            <td><?php _e('Random post from "foo"','better_random_redirect'); ?></td>
        </tr>
        <tr>
            <td><code>[random-url posttype="page"]</code></td>
            <td><a href="<?php echo site_url().'/'.get_option('brr_default_slug').'/?posttype=page'; ?>" target="_blank">
                <?php echo site_url().'/'.get_option('brr_default_slug').'/?posttype=page'; ?>
            </a></td>
            <td><?php _e('Random post of type "page"','better_random_redirect'); ?></td>
        </tr>
        <?php echo apply_filters('brr_admin_table_filter', '', $lang); ?>
    </table>
    <?php submit_button(); ?>
</form>
<script type="text/javascript">
( function($) {
    var mouseX;
    var mouseY;
    $(document).mousemove( function(e) {
       mouseX = e.pageX; 
       mouseY = e.pageY;
    });
    $('#brr_default_slug_help_text').css({ 'position' : 'absolute', 'max-width' : '320px' }).hide();
    $('#brr_default_slug_help').hover( function() {
        $('#brr_default_slug_help_text').css({ 'top' : mouseY - $('#wpadminbar').height() , 'left': mouseX - $('#adminmenuwrap').width() }).fadeIn();
        console.log(mouseX + ',' + mouseY);
        console.log($('#wpadminbar').height() + ',' + $('#adminmenuwrap').width() );
    }, function() {
        $('#brr_default_slug_help_text').fadeOut();
    });
    $('#brr_default_category_help_text').css({ 'position' : 'absolute', 'max-width' : '320px' }).hide();
    $('#brr_default_category_help').hover( function() {
        $('#brr_default_category_help_text').css({ 'top' : mouseY - $('#wpadminbar').height() , 'left': mouseX - $('#adminmenuwrap').width() }).fadeIn();
        console.log(mouseX + ',' + mouseY);
        console.log($('#wpadminbar').height() + ',' + $('#adminmenuwrap').width() );
    }, function() {
        $('#brr_default_category_help_text').fadeOut();
    });
    $('#brr_default_posttype_help_text').css({ 'position' : 'absolute', 'max-width' : '320px' }).hide();
    $('#brr_default_posttype_help').hover( function() {
        $('#brr_default_posttype_help_text').css({ 'top' : mouseY - $('#wpadminbar').height() , 'left': mouseX - $('#adminmenuwrap').width() }).fadeIn();
        console.log(mouseX + ',' + mouseY);
        console.log($('#wpadminbar').height() + ',' + $('#adminmenuwrap').width() );
    }, function() {
        $('#brr_default_posttype_help_text').fadeOut();
    });
    $('#brr_default_timeout_help_text').css({ 'position' : 'absolute', 'max-width' : '320px' }).hide();
    $('#brr_default_timeout_help').hover( function() {
        $('#brr_default_timeout_help_text').css({ 'top' : mouseY - $('#wpadminbar').height() , 'left': mouseX - $('#adminmenuwrap').width() }).fadeIn();
        console.log(mouseX + ',' + mouseY);
        console.log($('#wpadminbar').height() + ',' + $('#adminmenuwrap').width() );
    }, function() {
        $('#brr_default_timeout_help_text').fadeOut();
    });
})(jQuery);
</script>
</div>
