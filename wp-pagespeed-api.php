<?php

/**
 * Plugin Name: WP Pagespeed API
 * Description: Add custom REST API endpoint to get all custom post types. The end point is /wp/v2/cpts.
 * Version: 1.0
 * Author: Xammis
 * Author URI: https://xammis.com/
 * Text Domain: wp-pagespeed-api
 * Domain Path: /languages/
 * Requires at least: 5.7
 * Requires PHP: 7.2
 */

defined('ABSPATH') || exit;

add_action('rest_api_init', 'cpts_register_rest_route');

function cpts_register_rest_route()
{
  register_rest_route(
    'wp/v2',
    '/cpts',
    array(
      'methods' => 'GET',
      'callback' => 'get_items',
    )
  );
}

function get_items($request)
{
  $post_types = get_post_types(array('public' => true));
  $delist = array('page', 'post', 'attachment');

  // remove post types
  $arr_final = array_diff($post_types, $delist);

  $posts = array();
  $args = array(
    'post_type' => $arr_final,
    'page' => $request['page'],
    'paged' => $request['page']
    // 'nopaging' => true,
  );
  $query = new WP_Query($args);
  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $data = array(
        'id' => get_the_ID(),
        'link' => get_permalink(),
        // Add other fields as needed
      );
      $posts[] = $data;
    }
    wp_reset_postdata();
  }
  $response =  rest_ensure_response($posts);
  $response->header('X-WP-Total', (int) $query->found_posts);
  $response->header('X-WP-TotalPages', (int) $query->max_num_pages);
  return $response;
}
