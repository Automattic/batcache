<?php
/*
Plugin name: Batcache Manager
Plugin URI: http://wordpress.org/extend/plugins/batcache/
Description: This optional plugin improves Batcache.
Author: Andy Skelton
Author URI: http://andyskelton.com/
Version: 1.2
*/

if ( ! isset( $batcache ) || ! is_object($batcache) || ! method_exists( $wp_object_cache, 'incr' ) )
	return;

function batcache_post_unpublished($new_status, $old_status, $post) {
    if ($old_status == 'publish'  &&  $new_status != 'publish' && get_post_type($post) == 'post') {

		batcache_clear_url(get_permalink($post->ID));
		batcache_clear_url( get_option('home') );
		batcache_clear_url( trailingslashit( get_option('home') ) );
			
    }
}
add_action('transition_post_status', 'batcache_post_unpublished', 10, 3);

function batcache_post_published($post_id) {
	
	$post = get_post($post_id);
	if (empty($post) || get_post_type($post) != 'post' || get_post_status($post_id) != "publish")
		return;

	batcache_clear_url(get_permalink($post->ID));
	batcache_clear_url( get_option('home') );
	batcache_clear_url( trailingslashit( get_option('home') ) );
	
	
}
add_action('clean_post_cache', 'batcache_post_published');

function batcache_clear_url($url) {
	
	if (empty($url))
		return;
	
	global $batcache;
	
	$url_key = md5($url);
	wp_cache_add("{$url_key}_version", 0, $batcache->group);
	return wp_cache_incr("{$url_key}_version", 1, $batcache->group);

}

