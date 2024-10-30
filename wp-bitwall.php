<?php 
/* 
* Plugin Name: BitWall 
* 
* Description: Monetize More. 
* 
* Author: BitWall 
* Author URI: https://www.bitwall.io 
* Plugin URI: https://www.bitwall.io 
* Version: 0.3 
*/



/*******************************************************************************
** bitwallPageMenu()
**
** Setup the plugin options menu
**
** @since 0.1
*******************************************************************************/
function bitwallMenu() {
	if (is_admin()) {
		register_setting('bitwall_options', 'bitwall_options');
		add_options_page('BitWall Settings', 'BitWall Settings Page', 'administrator', __FILE__, 'bitwall_options_page');
	}
}


/*******************************************************************************
** checkbox_init()
**
** Adds a metabox to post, page, and event
**
** @since 0.1
********************************************************************************/
function checkbox_init(){
	$post_types = array ( 'post', 'page', 'event' );

	foreach($post_types as $post_type)
	{
		add_meta_box("bitwall", "Bitwall", "bitwall", $post_type, "normal", "high");
	}
}

function bitwall(){
	global $post;
	$custom = get_post_custom($post->ID);
	$field_id = $custom["field_id"][0];
	?>

	<label>Add BitWall Paywall</label>
	<?php 
		$field_id_value = get_post_meta($post->ID, 'field_id', true);
		$field_title = get_post_meta($post->ID, 'field_title', true);
		if($field_id_value == "yes") $field_id_checked = 'checked="checked"'; 
	?>
	<input type="checkbox" name="field_id" value="yes" <?php echo $field_id_checked; ?> />
	<br>
	<br>
	<label>Widget Title</label>
	<input type="text" name="field_title" value="<?php echo $field_title; ?>" />

	<?php

}

// Save Meta Details


function save_details(){
	global $post;

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post->ID;
	}

	update_post_meta($post->ID, "field_title", $_POST["field_title"]);
	update_post_meta($post->ID, "field_id", $_POST["field_id"]);
}



/*******************************************************************************
** bitwall_options_page()
**
** Present the options page
**
** @since 0.1
*******************************************************************************/
function bitwall_options_page() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have suffifient permissions to access this page.') );
	}
	
	echo '<div class="wrap">' . screen_icon() . '<h2>BitWall Merchant Settings</h2>';
	
	$bitwall = get_option('bitwall_options');
	
	echo '<form method="post" action="options.php">';
	
	settings_fields( 'bitwall_options' );

	echo '
	<h3>Setup</h3>
	<p>Start your monetization in minutes</p>
	<ol>
		<li>Signup at <a href="http://bit.ly/1aWdIZY" target="_blank">bitwall.io/signup</a>.</li>
		<li>Configure your widget for you specific needs.</li>
		<li>After you have created a widget, copy and paste the Widget ID into the admin page of BitWall settings.</li>
		<li>While creating a new post check to enable a widget in the BitWall metabox.</li>
	</ol>
	<br>
	<h3>Configure</h3>
	<table class="form-table">
	
	<tr valign="top">
	<th scope="row" style="white-space: nowrap;">BitWall publisher Id:</th>
	<td>
	<input type="text" name="bitwall_options[bitwall_pub_id]" id="bitwall_pub_id" value="'.$bitwall['bitwall_pub_id'].'" />

	
	</table>
		
	<p class="submit">
	<input type="submit" class="button-primary" value="Save Changes" />
	</p>
	
	</form>
	</div>';

}


/*******************************************************************************
** the_posts()
**
** Add bitwall to footer of tagged pages
**
** @since 0.1
*******************************************************************************/

function the_posts($posts) {
    add_action('wp_footer', 'add_to_footer'); 
    return $posts; 
}


function add_to_footer() {
    global $posts; 
    
    
    $ray = array(); 
    foreach ($posts as $p) {
        $ray[] = $p->ID;
    }
    
    if (count($ray) < 1) {
        return; 
    }
    $ids = implode(',',$ray);
    
    $myPosts = get_posts(array(
        'post_type' => 'any',
        'post__in' => $ray,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'field_id',
                'value' => '',
                'compare' => '!='
            )
        )
    ));
    
    if (empty($myPosts)) {
        return; 
    }

    if(is_home() || is_front_page()){
    	return;
    }
   	
   	$bitwall = get_option('bitwall_options');

   	if(!$bitwall || ($bitwall['bitwall_pub_id'] == '')){
   		return;
   	}

    foreach ($posts as $p) {
    	$field_title = get_post_meta($p->ID, 'field_title', true);
    	if($field_title){
    		echo '<script src="//bitwall.io/javascripts/widget.js" id="bitwallWidgetScript" data-title="'.$field_title.'" data-key="'.$bitwall['bitwall_pub_id'].'"></script>';
    	} else {
    		echo '<script src="//bitwall.io/javascripts/widget.js" id="bitwallWidgetScript" data-key="'.$bitwall['bitwall_pub_id'].'"></script>';

    	}
    }
}



/*******************************************************************************
** initBitWall()
**
** Constructor
**
** @since 0.1
*******************************************************************************/
function initBitWall() {
	add_action('admin_menu', 'bitwallMenu');
	add_action("admin_init", "checkbox_init");
	add_action('save_post', 'save_details');
	add_action('the_posts', 'the_posts'); 	
}

add_action('init', 'initBitWall', 1);



?>
