<?php
/**
 * Define the attachments management functionality.
 *
 * Loads and defines the attachments management files for this plugin so that it is ready for attachment creation, edition or removal.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Functions_Attachment {
	/**
	 * Insert a new attachment into the library
	 * 
	 * @param string $title
	 * @param string $content
	 * @param string $excerpt
	 * @param string $name
	 * @param string $type
	 * @param string $status
	 * @param int $author
	 * @param int $parent
	 * @param array $cats
	 * @param array $tags
	 * @param array $postmeta
	 * @param bool $overwrite_id Overwrites the post if it already exists checking existing post by post name
	 * 
	 * @since    1.0.0
	 */
	public function insert_attachment_from_url($url, $parent_post_id = null) {
    if(!class_exists('WP_Http')){
      include_once(ABSPATH . WPINC . '/class-http.php');
    }

    $http = new WP_Http();
    $response = $http->request($url);
    $file_extension = pathinfo($url, PATHINFO_EXTENSION);

    if (is_wp_error($response)) {
      return false;
    }

    $upload = wp_upload_bits(basename($url . '.' . $file_extension), null, $response['body']);

    if(!empty($upload['error'])) {
      return false;
    }

    $file_path = $upload['file'];
    $file_name = basename($file_path);
    $file_type = wp_check_filetype($file_name, null);
    $attachment_title = sanitize_file_name(pathinfo($file_name, PATHINFO_FILENAME));
    $wp_upload_dir = wp_upload_dir();

    $post_info = [
      'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
      'post_mime_type' => $file_type['type'],
      'post_title'     => $attachment_title,
      'post_content'   => '',
      'post_status'    => 'inherit',
    ];

    $attach_id = wp_insert_attachment($post_info, $file_path, $parent_post_id);
    require_once(ABSPATH . 'wp-admin/includes/file.php');

    return $attach_id;
  }

  public function userspn_user_files() {
    /* echo do_shortcode('[userspn-user-files]'); */
    $post_id = get_the_ID();
    $user_id = get_current_user_id();
    $plugin_settings = new USERSPN_Settings();

    ob_start();
    ?>
      <div class="userspn-private-file-upload userspn-mt-30 userspn-mb-50">
        <?php if (!wp_script_is('userspn-upload-private-files-btn', 'enqueued')): ?>
          <?php wp_enqueue_script('userspn-upload-private-files-btn', USERSPN_URL . 'assets/js/userspn-upload-private-files-btn.js', ['jquery'], USERSPN_VERSION, false, ['in_footer' => true, 'strategy' => 'defer']); ?>
        <?php endif ?>
        
        <ul class="userspn-file-private-upload-list userspn-mb-30">
          <?php $userspn_user_files = get_posts(['fields' => 'ids', 'numberposts' => -1, 'post_type' => 'attachment', 'post_status' => ['any'], 'meta_key' => 'userspn_user_files', 'orderby' => 'ID', 'order' => 'ASC', ]); ?>

          <?php if (!empty($userspn_user_files)): ?>
            <?php foreach ($userspn_user_files as $file_id): ?>
              <?php if (current_user_can('administrator') || get_post($file_id)->post_author == get_current_user_id()): ?>
                <?php echo wp_kses(self::userspn_get_private_file_uploaded($file_id), USERSPN_KSES); ?>
              <?php endif ?>
            <?php endforeach ?>
          <?php endif ?>
        </ul>

        <div class="userspn-text-align-center">
          <p class="userspn-mb-10"><?php esc_html_e('Upload your private file', 'userspn'); ?>  <i class="material-icons-outlined userspn-color-main-0 userspn-tooltip" title="<?php esc_html_e('You can upload files to the system and access them from your profile.', 'userspn'); ?> <?php esc_html_e('Current file max size allowed:', 'userspn'); ?> <?php echo esc_html($plugin_settings->userspn_bytes_format(wp_max_upload_size())); ?>.">help</i></p>

          <form action="" method="post">
            <div class="userspn-mb-20">
              <input type="file" id="userspn-user-file-private" class="userspn-cursor-pointer"/>
            </div>

            <div>
              <input type="submit" value="<?php esc_html_e('Upload', 'userspn'); ?>" class="userspn-btn userspn-btn-mini userspn-upload-private-files-btn" data-userspn-user-id="<?php echo esc_attr($user_id); ?>" data-userspn-post-id="<?php echo esc_attr($post_id); ?>"/><?php echo esc_html(USERSPN_Data::loader()); ?>
            </div>
          </form>
        </div>
      </div>
    <?php
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }

  public function userspn_user_files_allowed($user_id) {
    $userspn_user_files = get_option('userspn_user_files');
    $userspn_user_files_roles = get_option('userspn_user_files_roles');
    
    if ($userspn_user_files == 'on') {
      if (user_can($user_id, 'administrator') || empty($userspn_user_files_roles)) {
        return true;
      }else{
        foreach ($userspn_user_files_roles as $role) {
          if (user_can($user_id, $role)) {
            return true;
          }
        }
      }
    }
      
    return false;
  }

  public function userspn_get_private_file_uploaded($file_id) {
    $user_id = get_current_user_id();
    
    ob_start();
    ?>
      <li class="userspn-file-private" data-userspn-file-id="<?php echo esc_attr($file_id); ?>" data-userspn-user-id="<?php echo esc_attr($user_id); ?>">
        <div class="userspn-display-table userspn-width-100-percent">
          <div class="userspn-display-inline-table userspn-width-70-percent">
            <span class="userspn-pl-10"> <?php echo esc_html(get_the_title($file_id)); ?></span>
          </div>

          <div class="userspn-display-inline-table userspn-width-30-percent userspn-text-align-right">
            <a href="#" class="userspn-file-private-remove-btn userspn-tooltip" title="<?php esc_html_e('Remove file', 'userspn'); ?>"><i class="material-icons-outlined userspn-font-size-30 userspn-vertical-align-middle userspn-color-main-0">delete_outline</i></a><?php echo esc_html(USERSPN_Data::loader()); ?>
            <a href="<?php echo esc_url(wp_get_attachment_url($file_id)); ?>" download class="file-uploaded-download-btn userspn-ml-20 userspn-tooltip" title="<?php esc_html_e('Download file', 'userspn'); ?>"><i class="material-icons-outlined userspn-font-size-30 userspn-vertical-align-middle userspn-color-main-0">cloud_download</i></a>
          </div>
        </div>
      </li>
    <?php
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }
}