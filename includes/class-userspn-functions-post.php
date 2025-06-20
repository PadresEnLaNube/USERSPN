<?php
/**
 * Define the posts management functionality.
 *
 * Loads and defines the posts management files for this plugin so that it is ready for post creation, edition or removal.
 *  
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Functions_Post {
	/**
	 * Insert a new post into the database
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
	public static function userspn_insert_post($title, $content, $excerpt, $name, $type, $status, $author = 1, $parent = 0, $cats = [], $tags = [], $postmeta = [], $overwrite_id = true) {
    $post_values = [
      'post_title' => trim($title),
      'post_content' => $content,
      'post_excerpt' => $excerpt,
      'post_name' => $name,
      'post_type' => $type,
      'post_status' => $status,
      'post_author' => $author,
      'post_parent' => $parent,
      'comment_status' => 'closed',
      'ping_status' => 'closed',
    ];

    if (!is_admin()) {
      require_once(ABSPATH . 'wp-admin/includes/post.php');
    }

    if (!post_exists($title, '', '', $type) || !$overwrite_id) {
      $post_id = wp_insert_post($post_values);
    }else{
      $posts = get_posts(['fields' => 'ids', 'post_type' => $type, 'title' => $title, 'post_status' => 'any', ]);
      $post_id = !empty($posts) ? $posts[0] : 0;

      if (!empty($post_id)) {
        wp_update_post(['ID' => $post_id, 'post_title' => $title, 'post_content' => $content, 'post_excerpt' => $excerpt, 'post_name' => $name, 'post_type' => $type, 'post_status' => $status, ]);
      }else{
        return false;
      }
    }

    if (!empty($cats)) {
      wp_set_post_categories($post_id, $cats);
      if ($type == 'product') {
        wp_set_post_terms($post_id, $cats, 'product_cat', true);
      }
    }

    if (!empty($tags)) {
      wp_set_post_tags($post_id, $tags);
      if ($type == 'product') {
        wp_set_post_terms($post_id, $tags, 'product_tag', true);
      }
    }
 
    if (!empty($postmeta)) {
      foreach ($postmeta as $meta_key => $meta_value) {
        if ((is_array($meta_value) && count($meta_value)) || (!is_array($meta_value) && (!empty($meta_value) || (string)($meta_value) == '0'))) {
          update_post_meta($post_id, $meta_key, $meta_value);
        }
      }
    }

    return $post_id;
  }
}