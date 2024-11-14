<?php
/*
Plugin Name: Excerpt Quick Edit
Plugin URI: 
Description: Adds excerpt editing capability to Quick Edit for posts, pages, and custom post types
Version: 1.0
Author: Your Name
Author URI: 
License: GPL2
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

class ExcerptQuickEdit
{

  public function __construct()
  {
    // Add quick edit custom box
    add_action('quick_edit_custom_box', array($this, 'add_quick_edit_field'), 10, 2);

    // Add necessary scripts
    add_action('admin_enqueue_scripts', array($this, 'add_admin_scripts'));

    // Save quick edit data
    add_action('save_post', array($this, 'save_quick_edit_data'), 10, 2);

    // Add this to your main plugin file
    add_action('init', function () {
      // Add excerpt support to pages (if needed)
      add_post_type_support('page', 'excerpt');

      // Add excerpt support to any other post type
      // add_post_type_support('your_custom_post_type', 'excerpt');


    });
    // Add these new actions to register the column
    add_filter(
      'manage_posts_columns',
      array($this, 'add_excerpt_column')
    );
    add_filter(
      'manage_pages_columns',
      array($this, 'add_excerpt_column')
    );

    // Add column values
    add_action('manage_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
    add_action('manage_pages_custom_column', array($this, 'custom_column_content'), 10, 2);
  }

  public function add_excerpt_column($columns)
  {
    $columns['excerpt'] = __('Excerpt', 'excerpt-quick-edit');
    return $columns;
  }
  // Add Quick Edit field
  public function add_quick_edit_field($column_name, $post_type)
  {
    // Only add for excerpt column
    if ($column_name != 'excerpt') return;
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
</fieldset>
<?php
  }

  // Add necessary JavaScript
  public function add_admin_scripts($hook)
  {
    if ('edit.php' != $hook) return;

    wp_enqueue_script(
      'excerpt-quick-edit',
      plugins_url('js/excerpt-quick-edit.js', __FILE__),
      array('inline-edit-post'),
      '',
      true
    );
  }

  // Save the quick edit data
  // public function save_quick_edit_data($post_id, $post)
  // {
  //   // Verify nonce and permissions
  //   if (!current_user_can('edit_post', $post_id)) {
  //     return;
  //   }

  //   // If this is an autosave, don't update the excerpt
  //   if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
  //     return;
  //   }

  //   // Check if excerpt was set
  //   if (isset($_POST['excerpt'])) {
  //     update_post_meta($post_id, '_excerpt', sanitize_textarea_field($_POST['excerpt']));
  //     $my_post = array(
  //       'ID' => $post_id,
  //       'post_excerpt' => sanitize_textarea_field($_POST['excerpt'])
  //     );
  //     remove_action('save_post', array($this, 'save_quick_edit_data'));
  //     wp_update_post($my_post);
  //     add_action('save_post', array($this, 'save_quick_edit_data'), 10, 2);
  //   }
  // }


  // public function save_quick_edit_data($post_id, $post)
  // {
  //   // Verify nonce and permissions
  //   if (!current_user_can('edit_post', $post_id)) {
  //     return;
  //   }

  //   // If this is an autosave, don't update the excerpt
  //   if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
  //     return;
  //   }

  //   // Check if excerpt was set
  //   if (isset($_POST['excerpt'])) {
  //     $my_post = array(
  //       'ID' => $post_id,
  //       'post_excerpt' => sanitize_textarea_field($_POST['excerpt'])
  //     );
  //     remove_action('save_post', array($this, 'save_quick_edit_data'));
  //     wp_update_post($my_post);
  //     add_action('save_post', array($this, 'save_quick_edit_data'), 10, 2);
  //   }
  // }

  public function save_quick_edit_data($post_id, $post)
  {
    // Add basic debug logging
    error_log('Quick Edit Save Triggered for post: ' . $post_id);
    
    // Verify permissions
    if (!current_user_can('edit_post', $post_id)) {
      error_log('Permission check failed for post: ' . $post_id);
      return;
    }
    
    // If this is an autosave, don't update the excerpt
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      error_log('Autosave detected, skipping excerpt update');
      return;
    }
    
    // Check if excerpt was set
    if (isset($_POST['excerpt'])) {
      $message = $_POST['excerpt'];
      error_log('Quick Edit Save Triggered for post: '. $post_id.'with message: '. $message);
      error_log('Excerpt found in POST data: ' . $_POST['excerpt']);

      // Temporarily store old excerpt for verification
      $old_excerpt = get_post_field('post_excerpt', $post_id);

      $my_post = array(
        'ID' => $post_id,
        'post_excerpt' => wp_kses_post($_POST['excerpt'])
      );

      // Remove the action to prevent infinite loop
      remove_action('save_post', array($this, 'save_quick_edit_data'));

      // Update the post
      $result = wp_update_post($my_post);

      // Log the result and verify the update
      error_log('Update result: ' . ($result ? 'success' : 'failed'));
      $new_excerpt = get_post_field('post_excerpt', $post_id);
      error_log('Old excerpt: ' . $old_excerpt);
      error_log('New excerpt: ' . $new_excerpt);

      // Re-add the action
      add_action('save_post', array($this, 'save_quick_edit_data'), 10, 1);
    }
  }





  // Add content to custom column
  public function custom_column_content($column_name, $post_id)
  {
    if ($column_name == 'excerpt') {
      $excerpt = get_post_field('post_excerpt', $post_id);
      echo '<div id="excerpt-' . $post_id . '">' . esc_html($excerpt) . '</div>';
    }
  }
}

// Initialize the plugin
new ExcerptQuickEdit();