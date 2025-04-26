<?php
/**
 * Load the plugin mailing functions.
 *
 * Loads the plugin mailing functions getting email types and auxiliar features.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_CSV {
	public function userspn_csv_template_creator($userspn_file) {
    $row = 0;
    $array = [];

    global $wp_filesystem;

    if (!is_a($wp_filesystem, 'WP_Filesystem_Base')) {
      include_once(ABSPATH . 'wp-admin/includes/file.php');
      $credentials = request_filesystem_credentials( site_url() );
      wp_filesystem($credentials);
    }

    if (($handle = $wp_filesystem->put_contents($userspn_file)) !== false) {
      while (($data = fgetcsv($handle, 0, ',')) !== false) {
        $num = count($data);
        for ($c = 0; $c < $num; $c++) {
          $array[$row][] = $data[$c];
        }

        $row++;
      }
    }

    if (!empty($array)) {
      foreach ($array as $index_row => $row) {
        if ($index_row != 0) {
          if (!empty($row[0])) {
            $userspn_email = $row[0];
            $userspn_role = !empty($row[1]) ? $row[1] : 'subscriber';
            $userspn_login = sanitize_title(substr($userspn_email, 0, strpos($userspn_email, '@')) . '-' . bin2hex(openssl_random_pseudo_bytes(4)));
            $userspn_password = bin2hex(openssl_random_pseudo_bytes(12));

            $user_id = $this->userspn_insert_user($userspn_login, $userspn_password, $userspn_email, '', '', $userspn_login, $userspn_login, $userspn_login, '', [$userspn_role]);
            $userspn_user_register_fields = $this->userspn_user_register_get_fields([]);

            if (!empty($userspn_user_register_fields)) {
              $field_counter = 2;

              foreach ($userspn_user_register_fields as $field) {
                if (!empty($field)) {
                  update_user_meta($user_id, $field['id'], USERSPN_Forms::sanitizer(wp_strip_all_tags($row[$field_counter]), $field['input'], $field['type']));
                }

                $field_counter++;
              }
            }
          }
        }
      }
    }
  }
}