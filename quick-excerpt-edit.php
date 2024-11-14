<?php
/*
Plugin Name: Excerpt Quick Edit
Plugin URI: 
Description: Adds excerpt field in Quick Edit for posts, pages and custom post types
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
    try {
      // Add quick edit custom box
      add_action('quick_edit_custom_box', array($this, 'add_quick_edit_field'), 10, 2);

      // Add necessary scripts
      add_action('admin_enqueue_scripts', array($this, 'add_admin_scripts'));

      // Save quick edit data
      add_action('save_post', array($this, 'save_quick_edit_data'));

      // Add data to quick edit row
      add_filter('manage_posts_columns', array($this, 'add_hidden_column'));
      add_action('manage_posts_custom_column', array($this, 'add_hidden_column_content'), 10, 2);

      // Do the same for pages
      add_filter('manage_pages_columns', array($this, 'add_hidden_column'));
      add_action('manage_pages_custom_column', array($this, 'add_hidden_column_content'), 10, 2);
    } catch (Exception $e) {
      $this->log_error('Constructor error: ' . $e->getMessage());
    }
  }

  // Add quick edit field
  public function add_quick_edit_field($column_name, $post_type) {
    if ($column_name != 'excerpt_quick_edit') return;
    ?>
<fieldset class="inline-edit-col-right">
  <div class="inline-edit-col">
    <label>
      <span class="title">Excerpt</span>
      <span class="input-text-wrap">
        <textarea name="excerpt" id="excerpt" rows="3"></textarea>
      </span>
    </label>
    <?php wp_nonce_field('save_excerpt_quick_edit_nonce', 'excerpt_nonce'); ?>
  </div>
</fieldset>

<?php
    } 
  //   catch (Exception $e) {
  //     $this->log_error('Quick edit field error: ' . $e->getMessage());
  //   }
  // }


  public function add_admin_scripts($hook)
  {
    try {
      if ('edit.php' != $hook) return;

      $js_path = 'js/quick-edit.js';
      $full_path = plugin_dir_path(__FILE__) . $js_path;

      if (!file_exists($full_path)) {
        throw new Exception('JavaScript file not found: ' . $js_path);
      }

      wp_enqueue_script(
        'excerpt-quick-edit-script',
        plugins_url($js_path, __FILE__),
        array('inline-edit-post'), // Removed jQuery dependency
        filemtime($full_path),
        true
      );
    } catch (Exception $e) {
      $this->log_error('Admin scripts error: ' . $e->getMessage());
    }
  }



  // public function save_quick_edit_data($post_id)
  // {
  //   try {
  //     // Verify nonce if you're using one
  //     if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-post_' . $post_id)) {
  //       throw new Exception('Nonce verification failed.');
  //     }

  //     // Check if this is an autosave
  //     if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
  //       throw new Exception('Autosave in progress. Aborting to prevent overriding.');
  //     }

  //     // Check permissions
  //     if (!current_user_can('edit_post', $post_id)) {
  //       throw new Exception('Insufficient permissions to edit post.');
  //     }

  //     // Check if excerpt was sent
  //     if (!isset($_POST['excerpt'])) {
  //       throw new Exception('No excerpt data submitted.');
  //     }

  //     $excerpt = wp_kses_post(trim($_POST['excerpt']));

  //     // Update the post with the new excerpt
  //     $update_result = wp_update_post(array(
  //       'ID' => $post_id,
  //       'post_excerpt' => $excerpt
  //     ), true); // true to return WP_Error on failure

  //     if (is_wp_error($update_result)) {
  //       throw new Exception('Update failed: ' . $update_result->get_error_message());
  //     }

  //     // Verify that the update took place
  //     $updated_excerpt = get_post_field('post_excerpt', $post_id);
  //     if ($updated_excerpt !== $excerpt) {
  //       throw new Exception('Excerpt failed to update properly.');
  //     }

  //     // Log the successful update
  //     $this->log_error('Excerpt updated successfully for post ID ' . $post_id);
  //   } catch (Exception $e) {
  //     $this->log_error('Error saving excerpt: ' . $e->getMessage());
  //     wp_die('Error saving excerpt: ' . esc_html($e->getMessage())); // Optionally, you can handle this more gracefully depending on your application's needs
  //   }
  // }


  public function save_quick_edit_data($post_id)
  {
    try {
      // Verify nonce
      if (!isset($_POST['excerpt_nonce']) || !wp_verify_nonce($_POST['excerpt_nonce'], 'save_excerpt_quick_edit_nonce')) {
        throw new Exception('Nonce verification failed');
      }

      // Check if this is an autosave
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

      // Check permissions
      if (!current_user_can('edit_post', $post_id)) return;

      // Check if excerpt was sent
      if (isset($_POST['excerpt'])) {
        $excerpt = wp_kses_post(trim($_POST['excerpt']));

        $update_result = wp_update_post(array(
          'ID' => $post_id,
          'post_excerpt' => $excerpt
        ), true);

        if (is_wp_error($update_result)) {
          throw new Exception($update_result->get_error_message());
        }
      }
    } catch (Exception $e) {
      error_log('Excerpt Quick Edit Plugin Error: ' . $e->getMessage());
      wp_die('Error saving excerpt: ' . esc_html($e->getMessage()));
    }
  }

  
  public function add_hidden_column($columns)
  {
    try {
      $columns['excerpt_quick_edit'] = 'Excerpt Quick Edit';
      return $columns;
    } catch (Exception $e) {
      $this->log_error('Add hidden column error: ' . $e->getMessage());
      return $columns;
    }
  }

  // Add hidden column content
  public function add_hidden_column_content($column_name, $post_id)
  {
    try {
      if ($column_name == 'excerpt_quick_edit') {
        $excerpt = get_post_field('post_excerpt', $post_id);
        if (is_wp_error($excerpt)) {
          throw new Exception($excerpt->get_error_message());
        }
        echo '<div id="excerpt_quick_edit_' . intval($post_id) . '">' . esc_textarea($excerpt) . '</div>';
      }
    } catch (Exception $e) {
      $this->log_error('Hidden column content error: ' . $e->getMessage());
    }
  }

  // Error logging function
  private function log_error($message)
  {
    if (defined('WP_DEBUG') && WP_DEBUG) {
      error_log('Excerpt Quick Edit Plugin Error: ' . $message);
    }
  }
}

// Initialize the plugin with error handling
try {
  new ExcerptQuickEdit();
} catch (Exception $e) {
  error_log('Failed to initialize Excerpt Quick Edit Plugin: ' . $e->getMessage());
}