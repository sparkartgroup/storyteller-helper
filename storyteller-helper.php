<?php
/**
 * Plugin Name: Storyteller Helper
 * Plugin URI:  https://github.com/SparkartGroupInc/storyteller-helper
 * Description: Adds useful features for use with Storyteller.io such as triggering webhooks to clear the Storyteller cache on post updates.
 * Version:     0.0.0
 * Author:      Sparkart Group, Inc.
 * Author URI:  http://sparkart.com
 * License:     MIT
 */

// Blocks direct access to plugin PHP files
defined( 'ABSPATH' ) or die( 'Access denied!' );

$site_subdomain = str_replace(array('http://', 'https://'), '', site_url());

include 'admin/storyteller-settings.php';

function clear_storyteller_cache($routes_to_clear) {
  foreach ($routes_to_clear as $route) {
    $url = 'http://proxy.storyteller.io/wordpress-rest-api/' . $site_subdomain . $route;
    $args = array(
      'method' => 'PUT',
      'headers' => array(
        'Api-Key' => get_option('storyteller_apikey'),
        'Project' => get_option('storyteller_project')
      )
    );
    $response = wp_remote_request( $url, $args );
  }
}

function clear_storyteller_post_cache($post_id, $post_after, $post_before) {
  clear_storyteller_cache(array('/pages', '/posts'));
}
add_action( 'post_updated', 'clear_storyteller_post_cache');

function clear_storyteller_attachment_cache($attachment_id) {
  clear_storyteller_cache(array('/media'));
}
add_action('add_attachment', 'clear_storyteller_attachment_cache');
add_action('edit_attachment', 'clear_storyteller_attachment_cache');
add_action('delete_attachment', 'clear_storyteller_attachment_cache');