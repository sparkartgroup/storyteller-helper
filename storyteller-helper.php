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

include 'admin/storyteller-settings.php';
// WordPress Action Reference: https://codex.wordpress.org/Plugin_API/Action_Reference
function clear_storyteller_post_cache($post_id, $post_after, $post_before) {
  // Clear /posts
  // Clear /pages
}
add_action( 'post_updated', 'clear_storyteller_post_cache');

function clear_storyteller_attachment_cache($attachment_id) {
  // Clear /media
}
add_action('add_attachment', 'clear_storyteller_attachment_cache');
add_action('edit_attachment', 'clear_storyteller_attachment_cache');
add_action('delete_attachment', 'clear_storyteller_attachment_cache');