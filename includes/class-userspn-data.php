<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin so that it is ready for translation.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Data {
	/**
	 * The main data array.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      USERSPN_Data    $data    Empty array.
	 */
	protected $data = [];

	/**
	 * Load the plugin most usefull data.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_data() {
		$this->data['user_id'] = get_current_user_id();

		if (is_admin()) {
			$this->data['post_id'] = !empty($GLOBALS['_REQUEST']['post']) ? $GLOBALS['_REQUEST']['post'] : 0;
		}else{
			$this->data['post_id'] = get_the_ID();
		}

		$GLOBALS['userspn_data'] = $this->data;
	}

	/**
	 * Flush wp rewrite rules.
	 *
	 * @since    1.0.0
	 */
	public function flush_rewrite_rules() {
    if (get_option('userspn_options_changed')) {
      flush_rewrite_rules();
      update_option('userspn_options_changed', false);
    }
  }

  /**
	 * Get buttons mini loader.
	 *
	 * @since    1.0.0
	 */
	public static function loader() {
		?>
			<div class="userspn-waiting userspn-display-inline userspn-display-none">
				<div class="userspn-loader-circle-waiting"><div></div><div></div><div></div><div></div></div>
			</div>
		<?php
  }
}