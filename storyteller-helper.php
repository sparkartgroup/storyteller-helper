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
  global $site_subdomain;
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

function clear_storyteller_post_cache($post_id) {
  $routes_to_clear = array();
  $updated_post = get_post($post_id);
  if ($updated_post->post_type == 'page') {
    $parent_post = $updated_post->post_parent? get_post($updated_post->post_parent) : null;
    $parent_slug = $parent_post->post_name?$parent_post->post_name.'/':null;
    array_push($routes_to_clear,
      '/pages',
      '/pages/'.$post_id,
      '/pages/'.$parent_slug.$updated_post->post_name
    );
  } else {
    array_push($routes_to_clear,
      '/posts',
      '/posts/'.$post_id
    );
  }
  clear_storyteller_cache($routes_to_clear);
}
add_action( 'post_updated', 'clear_storyteller_post_cache');

function clear_storyteller_attachment_cache($attachment_id) {
  $routes_to_clear = array();
  array_push($routes_to_clear,
    '/media',
    '/media/'.$attachment_id
  );
  clear_storyteller_cache($routes_to_clear);
}
add_action('add_attachment', 'clear_storyteller_attachment_cache');
add_action('edit_attachment', 'clear_storyteller_attachment_cache');
add_action('delete_attachment', 'clear_storyteller_attachment_cache');

function clear_storyteller_category_cache($category_id) {
  $routes_to_clear = array();
  array_push($routes_to_clear,
    '/posts/types/post/taxonomies/category/terms',
    '/posts/types/post/taxonomies/category/terms/'.$category_id
  );
  clear_storyteller_cache($routes_to_clear);
}
add_action('add_category', 'clear_storyteller_category_cache');
add_action('edit_category', 'clear_storyteller_category_cache');
add_action('delete_category', 'clear_storyteller_category_cache');