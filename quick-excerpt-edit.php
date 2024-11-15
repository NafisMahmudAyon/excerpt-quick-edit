<?php
/*
Plugin Name: Excerpt Quick Edit
Description: Adds excerpt editing capability to Quick Edit for posts, pages, and custom post types
Version: 1.0
Author: Your Name
License: GPL2
*/

if (!defined('ABSPATH')) {
  exit;
}

class ExcerptQuickEdit
{

  public function __construct()
  {
    error_log('Excerpt Quick Edit plugin initialized');

    add_action('quick_edit_custom_box', array($this, 'add_quick_edit_field'), 10, 2);
    add_action('admin_enqueue_scripts', array($this, 'add_admin_scripts'));
    // add_action('save_post', 'save_quick_edit_excerpt');
    // add_action('wp_ajax_save_quick_edit_excerpt', array($this, 'save_quick_edit_excerpt'));
    add_filter('manage_posts_columns', array($this, 'add_excerpt_column'));
    add_filter('manage_pages_columns', array($this, 'add_excerpt_column'));
    add_action('manage_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
    add_action('manage_pages_custom_column', array($this, 'custom_column_content'), 10, 2);
    // add_action('wp_ajax_save_quick_edit_excerpt', 'save_quick_edit_excerpt');
    add_action('wp_ajax_save_quick_edit_excerpt', array($this, 'save_quick_edit_excerpt'));

  }

  public function add_excerpt_column($columns)
  {
    $columns['excerpt'] = __('Excerpt', 'excerpt-quick-edit');
    error_log('Excerpt column added');
    return $columns;
  }

  public function custom_column_content($column_name, $post_id)
  {
    if ($column_name == 'excerpt') {
      $excerpt = get_post_field('post_excerpt', $post_id);
      error_log("Displaying excerpt for post ID {$post_id}: " . $excerpt);
      echo '<div id="excerpt-' . $post_id . '">' . esc_html($excerpt) . '</div>';
    }
  }

  public function add_quick_edit_field($column_name, $post_type)
  {
    if ($column_name != 'excerpt') return;
    error_log('Quick Edit field added for Excerpt');
?>
<fieldset class="inline-edit-col-right">
  <div class="inline-edit-col">
    <label>
      <span class="title">Excerpt</span>
      <span class="input-text-wrap">
        <textarea name="excerpt" class="excerpt" id="excerpt" rows="3"></textarea>
      </span>
    </label>
  </div>
  <span class="update-excerpt">Update Excerpt</span>
</fieldset>
<?php
  }

  public function add_admin_scripts($hook)
  {
    if ('edit.php' != $hook) return;
    wp_enqueue_script(
      'excerpt-quick-edit',
      plugins_url('js/excerpt-quick-edit.js', __FILE__)
      // ,array('inline-edit-post'),
      // ''
      ,
      true
    );
    wp_enqueue_style('excerpt-quick-edit-style', plugins_url('css/style.css', __FILE__),true);

    // wp_enqueue_script('jquery');

    // Localize script to add nonce
    wp_localize_script('excerpt-quick-edit', 'excerptQuickEdit', array(
      'nonce' => wp_create_nonce('excerpt_quick_edit_nonce')
    ));

    error_log('Admin script enqueued for Excerpt Quick Edit');
  }

  

function save_quick_edit_excerpt() {
  error_log(('Hello'));
    if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'excerpt_quick_edit_nonce')) {
      wp_send_json_error(array('data' => 'Invalid nonce.'));
      return;
    }

    // Check if the post ID and excerpt are available
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $excerpt = isset($_POST['excerpt']) ? sanitize_textarea_field($_POST['excerpt']) : '';
    var_export($_POST, true);

    if ($post_id > 0 && !empty($excerpt)) {
        $post_update = wp_update_post(array(
            'ID' => $post_id,
            'post_excerpt' => $excerpt
        ));

        if ($post_update) {
            wp_send_json_success();
        } else {
            wp_send_json_error(array('data' => 'Failed to update excerpt.'));
        }
    } else {
        wp_send_json_error(array('data' => 'Invalid post ID or excerpt.'));
    }
}


  // public function save_quick_edit_excerpt()
  // {
  //   error_log('Received AJAX data: ' . var_export($_POST, true));
  //   // Verify nonce for security
  //   if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'save_quick_edit_excerpt_nonce')) {
  //     error_log('Nonce verification failed.');
  //     wp_send_json_error('Nonce verification failed.');
  //   }

  //   // Validate post data
  //   if (isset($_POST['post_id'])) {
  //     $post_id = intval($_POST['post_id']);
  //     error_log('Raw excerpt from POST: ' . var_export($_POST['excerpt'], true));
  //     $excerpt = sanitize_textarea_field($_POST['excerpt']);
  //     error_log('Sanitized excerpt: ' . var_export($excerpt, true));

  //     error_log("AJAX request received for post_id: {$post_id}, excerpt: {$excerpt}");

  //     $updated = wp_update_post(array(
  //       'ID' => $post_id,
  //       'post_excerpt' => $excerpt
  //     ));

  //     if ($updated) {
  //       error_log("Excerpt successfully updated for post ID {$post_id}");
  //       wp_send_json_success('Excerpt saved successfully');
  //     } else {
  //       error_log("Excerpt update failed for post ID {$post_id}");
  //       wp_send_json_error('Failed to update excerpt');
  //     }
  //   } else {
  //     error_log('Error: Required data not found in AJAX request');
  //     wp_send_json_error('Required data not found');
  //   }
  // }
}

// Initialize the plugin
new ExcerptQuickEdit();