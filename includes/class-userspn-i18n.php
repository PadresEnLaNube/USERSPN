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
class USERSPN_i18n {
  /**
   * Load the plugin text domain for translation.
   *
   * @since    1.0.0
   */
  public function userspn_load_plugin_textdomain() {
    load_plugin_textdomain(
      'userspn',
      false,
      dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
    );
  }

	/**
	 * Load the plugin translation functions.
	 *
	 * @since    1.0.0
	 */
	public function userspn_pll_get_post_types($post_types, $is_settings) {
    if ($is_settings){
      unset($post_types['userspn_recipe']);
    }else{
      $post_types['userspn_recipe'] = 'userspn_recipe';
    }

    return $post_types;
  }

  public function userspn_timestamp_server_gap() {
    $time = new DateTime(gmdate('Y-m-d H:i:s', time()));
    $current_time = new DateTime(gmdate('Y-m-d H:i:s', current_time('timestamp')));

    $interval = $current_time->diff($time);
    return ((($interval->invert) ? '-' : '+') . $interval->d . ' days ') . ((($interval->invert) ? '-' : '+') . $interval->h . ' hours ') . ((($interval->invert) ? '-' : '+') . $interval->i . ' minutes ') . ((($interval->invert) ? '-' : '+') . $interval->s . ' seconds');
  }

  /**
   * Get Polylang translations for a page
   * 
   * @param int $page_id The page ID to get translations for
   * @return array Array of translations with language codes as keys
   */
  public function userspn_get_polylang_page_translations($page_id) {
    $translations = [];
    
    if (class_exists('Polylang') && function_exists('pll_get_post_translations')) {
      $translations = pll_get_post_translations($page_id);
    }
    
    return $translations;
  }

  /**
   * Get the best page link considering Polylang translations
   * 
   * @param int $page_id The page ID
   * @param string $current_lang Current language code
   * @return array Array with 'link', 'title', and 'available_languages'
   */
  public function userspn_get_polylang_page_info($page_id, $current_lang = null) {
    $page_info = [
      'link' => !empty($page_id) ? get_permalink($page_id) : '',
      'title' => !empty($page_id) ? get_the_title($page_id) : '',
      'available_languages' => []
    ];
    
    if (class_exists('Polylang') && function_exists('pll_get_post_translations')) {
      $translations = pll_get_post_translations($page_id);
      
      if (!empty($translations)) {
        // Get current language if not provided
        if ($current_lang === null) {
          $current_lang = pll_current_language('slug');
        }
        
        // If we have a translation for current language, use it
        if (!empty($translations[$current_lang])) {
          $translated_page_id = $translations[$current_lang];
          $page_info['link'] = get_permalink($translated_page_id);
          $page_info['title'] = get_the_title($translated_page_id);
        }
        
        // Store all available languages
        $page_info['available_languages'] = $translations;
      }
    }
    
    return $page_info;
  }

  /**
   * Get language name from language code
   * 
   * @param string $lang_code Language code
   * @return string Language name
   */
  public function userspn_get_language_name($lang_code) {
    if (class_exists('Polylang') && function_exists('pll_languages_list')) {
      $languages = pll_languages_list(['fields' => []]);
      foreach ($languages as $language) {
        if ($language->slug === $lang_code) {
          return $language->name;
        }
      }
    }
    
    return strtoupper($lang_code);
  }
}