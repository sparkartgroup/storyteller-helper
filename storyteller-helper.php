<?php
/**
 * Plugin Name: Storyteller Helper
 * Plugin URI:  https://github.com/SparkartGroupInc/storyteller-helper
 * Description: Adds useful features for use with Storyteller.io such as triggering webhooks to clear the Storyteller cache on post updates.
 * Version:     1.0.1
 * Author:      Sparkart Group, Inc.
 * Author URI:  http://sparkart.com
 * License:     MIT
 */

// Blocks direct access to plugin PHP files
defined( 'ABSPATH' ) or die( 'Access denied!' );

$site_subdomain = str_replace(array('http://', 'https://'), '', site_url());

include 'admin/storyteller-settings.php';

$storyteller_project = get_option('storyteller_project');
$storyteller_apikey = get_option('storyteller_apikey');
$storyteller_proxyurl = get_option('storyteller_proxyurl');

if ($storyteller_project && $storyteller_apikey && $storyteller_proxyurl) {
  add_action( 'post_updated', 'clear_storyteller_post_cache');

  add_action('add_attachment', 'clear_storyteller_attachment_cache');
  add_action('edit_attachment', 'clear_storyteller_attachment_cache');
  add_action('delete_attachment', 'clear_storyteller_attachment_cache');

  add_action('add_category', 'clear_storyteller_category_cache');
  add_action('edit_category', 'clear_storyteller_category_cache');
  add_action('delete_category', 'clear_storyteller_category_cache');

  add_filter('redirect_post_location', 'pass_storyteller_confirmation');
  //add_filter('post_updated_messages', 'add_storyteller_confirmation');

  if($_GET['storyteller_routes_cleared'] || $_GET['storyteller_routes_errored']){
    add_action('admin_notices', 'display_storyteller_messages');
  }

}

function clear_storyteller_cache($routes_to_clear) {
  global $site_subdomain, $storyteller_project, $storyteller_apikey, $storyteller_proxyurl;
  $routes_cleared = array();
  $routes_errored = array();
  foreach ($routes_to_clear as $route) {
    $url = $storyteller_proxyurl . $site_subdomain . $route;
    $args = array(
      'method' => 'PUT',
      'headers' => array(
        'Project' => $storyteller_project,
        'Api-Key' => $storyteller_apikey
      )
    );
    $response = wp_remote_request( $url, $args );
    if( !is_wp_error( $response ) ) {
      $response_body = json_decode($response['body']);
      if ($response_body->status == 'ok') {
        array_push($routes_cleared, $route);
      } elseif ($response_body->status == 'error') {
        array_push($routes_errored, $route);
      }
    } else {
      array_push($routes_errored, $route);
    }
  }
  $_POST['storyteller_routes_cleared'] = $routes_cleared;
  $_POST['storyteller_routes_errored'] = $routes_errored;
}

function pass_storyteller_confirmation($location){
  if (isset($_POST['storyteller_routes_cleared'])) {
    $routes_param = array('storyteller_routes_cleared' => $_POST['storyteller_routes_cleared']);
    $location = esc_url_raw(add_query_arg($routes_param, $location));
  }
  if (isset($_POST['storyteller_routes_errored'])) {
    $routes_param_e = array('storyteller_routes_errored' => $_POST['storyteller_routes_errored']);
    $location = esc_url_raw(add_query_arg($routes_param_e, $location));
  }
  return $location;
}

function display_storyteller_messages(){
  if ($_GET['storyteller_routes_cleared']) {
    echo '<div id="message" class="notice notice-info is-dismissible">
            <p>Storyteller caches cleared: <code>' . implode('</code>, <code>', $_GET['storyteller_routes_cleared']) . '</code></p>
          </div>';
  }
  if ($_GET['storyteller_routes_errored']) {
    echo '<div id="message" class="notice notice-warning is-dismissible">
            <p>Failed to clear Storyteller caches: <code>' . implode('</code>, <code>', $_GET['storyteller_routes_errored']) . '</code></p>
          </div>';
  }
  remove_action('admin_notices', 'display_storyteller_messages');
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

function clear_storyteller_attachment_cache($attachment_id) {
  $routes_to_clear = array();
  array_push($routes_to_clear,
    '/media',
    '/media/'.$attachment_id
  );
  clear_storyteller_cache($routes_to_clear);
}

function clear_storyteller_category_cache($category_id) {
  $routes_to_clear = array();
  array_push($routes_to_clear,
    '/posts/types/post/taxonomies/category/terms',
    '/posts/types/post/taxonomies/category/terms/'.$category_id
  );
  clear_storyteller_cache($routes_to_clear);
}