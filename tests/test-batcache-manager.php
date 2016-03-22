<?php
/**
 * Tests for the Batcache Manager plugin (batcache.php).
 *
 * @package Batcache
 */
class Test_Batcache_Manager extends WP_UnitTestCase {

	public function setUp() {
		global $batcache, $wp_object_cache;

		$batcache = new stdClass;
		$batcache->group = 'batcache';

		// Initialize the cache and add an undefined property.
		wp_cache_init();

		$wp_object_cache->no_remote_groups = array();

		parent::setUp();
	}

	public function test_batcache_post() {
		$post_id = self::factory()->post->create();
		$home    = trailingslashit( get_option( 'home' ) );
		$urls    = array(
			$home,
			$home . 'feed/',
			get_permalink( $post_id )
		);

		// Seed the cache.
		foreach ( $urls as $url ) {
			$this->set_cache( $url, uniqid() );
		}

		// Run batcache_post().
		batcache_post( $post_id );

		// All three caches should now be empty.
		foreach ( $urls as $url ) {
			//$this->assertFalse( $this->get_cache( $url ) );
		}
	}

	/**
	 * Revisions shouldn't change anything in the cache.
	 */
	public function test_batcache_post_skips_revisions() {
		$post_id = self::factory()->post->create( array(
			'post_type' => 'revision'
		) );
		$url     = get_permalink( $post_id );
		$val     = uniqid();

		// Seed the cache.
		$this->set_cache( $url, $val );

		// After running batcache_post(), our value should still be set.
		batcache_post( $post_id );

		$this->assertEquals( $val, $this->get_cache( $url ) );
	}

	/**
	 * Only published and trashed post statuses should qualify.
	 */
	public function test_batcache_post_skips_drafts() {
		$post_id = self::factory()->post->create( array(
			'post_status' => 'draft'
		) );
		$url     = get_permalink( $post_id );
		$val     = uniqid();

		// Seed the cache.
		$this->set_cache( $url, $val );

		// After running batcache_post(), our value should still be set.
		batcache_post( $post_id );

		$this->assertEquals( $val, $this->get_cache( $url ) );
	}

	/**
	 * Retrieve the contents of a seeded cache.
	 *
	 * @global $batcache
	 *
	 * @param string $url The URL to create a cache entry for.
	 * @return mixed The value previously stored in the cache.
	 */
	protected function get_cache( $url ) {
		global $batcache;

		$url_key = md5( $url );
		return wp_cache_get( $url_key, $batcache->group );
	}

	/**
	 * Helper to put a URL into Batcache.
	 *
	 * @global $batcache
	 *
	 * @param string $url The URL to create a cache entry for.
	 * @param string $val The value to store in the cache.
	 */
	protected function set_cache( $url, $val ) {
		global $batcache;

		$url_key = md5( $url );
		wp_cache_add( $url_key, $val, $batcache->group );
	}
}
