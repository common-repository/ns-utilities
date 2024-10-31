<?php
/*
Plugin Name: NS Utilities
Plugin URI: http://www.net-solutions.es
Description:This plugin extends the category. Allows us to store meta for these. You can associate images with various formats to categories in your admin section.
Version: 1.1
Author: Net Solutions
Author URI: http://www.net-solutions.es
License: GPLv3
*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/

#### LOAD TRANSLATIONS ####
load_plugin_textdomain('ns_utilities', 'wp-content/plugins/ns_utilities/lang/', 'ns_utilities/lang/');
####




#### INSTALL PROCESS ####
$nsu_dbVersion = "1.0";

#### LOAD UTILITIES CLASS ####
require_once dirname(__FILE__).'/class/ns_category_meta.php';
require_once dirname(__FILE__).'/class/ns_categories_extension.php';
require_once dirname(__FILE__).'/ns_assign_image_category.php';

####LOAD MODULES####
require_once 'ns_objects.php';
require_once 'ns_category_image.php';

function utilities_page_install() {

    global $wpdb;

    $the_page_title = __('My utilities', 'ns_utilities');
    
    $the_page_name = 'utilities';

    // the menu entry...
    delete_option("utilities_page_title");
    add_option("utilities_page_title", $the_page_title, '', 'yes');
    // the slug...
    delete_option("utilities_page_name");
    add_option("utilities_page_name", $the_page_name, '', 'yes');
    // the id...
    delete_option("utilities_page_id");
    add_option("utilities_page_id", '0', '', 'yes');

    $the_page = get_page_by_title( $the_page_title );

    if ( ! $the_page ) {

        // Create post object
        $_p = array();
        $_p['post_title'] = $the_page_title;
        $_p['post_content'] = "[ns_utilities /]";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'

        // Insert the post into the database
        $the_page_id = wp_insert_post( $_p );


    }
    else {
        // the plugin may have been previously active and the page may just be trashed...

        $the_page_id = $the_page->ID;

        //make sure the page is not trashed...
        $the_page->post_status = 'publish';
        $the_page_id = wp_update_post( $the_page );

    }

	add_post_meta($the_page_id, '_allow_all', true, true) or update_post_meta($the_page_id, '_allow_all', true);

    delete_option( 'utilities_page_id' );
    add_option( 'utilities_page_id', $the_page_id );

}

function utilities_page_remove() {

    global $wpdb;

    $the_page_title = get_option( "utilities_page_title" );
    $the_page_name = get_option( "utilities_page_name" );

    //  the id of our page...
    $the_page_id = get_option( 'utilities_page_id' );
    if( $the_page_id ) {

        wp_delete_post( $the_page_id ); // this will trash, not delete

    }

    delete_option("utilities_page_title");
    delete_option("utilities_page_name");
    delete_option("utilities_content_id");

}

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'utilies_page_install');

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'utilities_page_remove' );

function setOptionsNSU() {
	global $wpdb;
	global $nslt_dbVersion;

	$table_name = $wpdb->prefix . "ns_category_meta";
        
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
			id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                        term_id MEDIUMINT(9) NOT NULL ,
                        meta_key VARCHAR(255) NOT NULL,
                        meta_value LONGTEXT,
                        PRIMARY KEY id (id)
		);";
                
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		add_option("nsu_dbVersion", $nslt_dbVersion);
	}

	
}

register_activation_hook(__FILE__, 'setOptionsNSU');

//Add the Option Page
function ns_utilities_catimage_options()	{
    if (function_exists('add_options_page')) {
        add_options_page('NS Utilities Cat.Image','NS Utilities Cat.Image', 6, 'NSUtilitiesCatimage', 'ns_utilities_catimage');
    }
}
add_action('admin_menu', 'ns_utilities_catimage_options');


//function unsetOptionsNSU() {
	//global $wpdb;

	//$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."ns_category_meta");

	
//}
//register_uninstall_hook(__FILE__, 'unsetOptionsNSU');

?>
