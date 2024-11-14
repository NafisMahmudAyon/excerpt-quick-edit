<?php
/*
Plugin Name: Excerpt Quick Edit
Plugin URI: http://example.com
Description: Allows quick editing of excerpts in the Quick Edit menu for posts, pages, and custom post types.
Version: 1.0
Author: Your Name
Author URI: http://example.com
*/

if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

// function eqe_enqueue_scripts($hook)
// {
//   if ('edit.php' !== $hook) {
//     return;
//   }
//   wp_enqueue_script('eqe-scripts', plugins_url('/js/eqe-scripts.js', __FILE__), array(), null, true);
//   wp_localize_script('eqe-scripts', 'EQEData', array('nonce' => wp_create_nonce('eqe_nonce')));
// }
// add_action('admin_enqueue_scripts', 'eqe_enqueue_scripts');


function eqe_save_excerpt()
{
  check_ajax_referer('eqe_nonce', 'nonce');

  if (isset($_POST['post_id']) && isset($_POST['excerpt'])) {
    $post_id = intval($_POST['post_id']);
    $excerpt = sanitize_text_field($_POST['excerpt']);

    // Update the post
    wp_update_post(array(
      'ID' => $post_id,
      'post_excerpt' => $excerpt
    ));
    echo 'success';
  } else {
    echo 'error';
  }
  die();
}
add_action('wp_ajax_eqe_save_excerpt', 'eqe_save_excerpt');