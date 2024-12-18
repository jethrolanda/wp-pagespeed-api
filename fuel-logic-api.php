<?php

/**
 * Plugin Name: Fuel logic API
 * Description: FL API.
 * Version: 1.0
 * Author: Xammis
 * Author URI: https://xammis.com/
 * Text Domain: fuel-logic-api
 * Domain Path: /languages/
 * Requires at least: 5.7
 * Requires PHP: 7.2
 */

defined('ABSPATH') || exit;

add_action('rest_api_init', 'register_testimonial_rest_route');

function register_testimonial_rest_route()
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

function get_items()
{
  $post_types = get_post_types(array('public' => true));
  $delist = array('page', 'post', 'attachment');

  // remove post types
  $arr_final = array_diff($post_types, $delist);

  $testimonials = array();
  $args = array(
    'post_type' => $arr_final,
    // 'nopaging' => true,
  );
  $query = new WP_Query($args);
  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $testimonial_data = array(
        'link' => get_permalink(),
        // Add other fields as needed
      );
      $testimonials[] = $testimonial_data;
    }
    wp_reset_postdata();
  }
  $response =  rest_ensure_response($testimonials);
  $response->header('X-WP-Total', (int) $query->found_posts);
  $response->header('X-WP-TotalPages', (int) $query->max_num_pages);
  return $response;
}
