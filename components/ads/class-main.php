<?php
/**
 * Ads Main Class
 *
 * @package AdThrive Ads
 */

namespace AdThrive_Ads\Components\Ads;

/**
 * Main class
 */
class Main {

	/**
	 * Add hooks
	 */
	public function setup() {
		add_filter( 'adthrive_ads_options', array( $this, 'add_options' ), 10, 1 );

		add_action( 'cmb2_admin_init', array( $this, 'all_objects' ) );

		add_action( 'wp_head', array( $this, 'ad_head' ), 1 );

		add_filter( 'body_class', array( $this, 'body_class' ) );

		add_action( 'wp_ajax_adthrive_terms', array( $this, 'ajax_terms' ) );
	}

	/**
	 * AJAX method to get terms with the matched search query for the specified taxonomy
	 */
	public function ajax_terms() {
		if ( isset( $_GET['query'] ) ) {
			$query = sanitize_text_field( wp_unslash( $_GET['query'] ) );
		}

		if ( isset( $_GET['taxonomy'] ) ) {
			$taxonomy = sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) );
		}

		wp_send_json( $this->get_term_selectize( $taxonomy, array( 'search' => $query ) ) );
	}

	/**
	 * Adds classes to disable ads based on the plugin settings
	 *
	 * @param string|array $classes One or more classes to add to the class list.
	 */
	public function body_class( $classes ) {
		if ( is_singular() ) {
			global $post;

			$disable = get_post_meta( get_the_ID(), 'adthrive_ads_disable' );
			$disable_in_image = get_post_meta( get_the_ID(), 'adthrive_ads_disable_in_image' );
			$disable_content_ads = get_post_meta( get_the_ID(), 'adthrive_ads_disable_content_ads' );
			$disable_auto_insert_videos = get_post_meta( get_the_ID(), 'adthrive_ads_disable_auto_insert_videos' );
			$re_enable_ads_on = get_post_meta( get_the_ID(), 'adthrive_ads_re_enable_ads_on' );

			$disabled_categories = \AdThrive_Ads\Options::get( 'disabled_categories' );
			$disabled_tags = \AdThrive_Ads\Options::get( 'disabled_tags' );

			$categories = get_the_category( $post->ID );
			$tags = get_the_tags( $post->ID );

			$category_names = is_array( $categories ) ? array_map( array( $this, 'pluck_name' ), $categories ) : array();
			$tag_names = is_array( $tags ) ? array_map( array( $this, 'pluck_name' ), $tags ) : array();

			if ( ! isset( $re_enable_ads_on[0] ) || false === trim( $re_enable_ads_on[0] ) || $re_enable_ads_on[0] > time() ) {
				if ( isset( $disable[0] ) ) {
					$classes[] = 'adthrive-disable-all';
				}

				if ( isset( $disable_in_image[0] ) || in_array( 'noads', $tag_names, true ) ) {
					$classes[] = 'adthrive-disable-in-image';
				}

				if ( isset( $disable_content_ads[0] ) || in_array( 'noads', $tag_names, true ) ) {
					$classes[] = 'adthrive-disable-content';
				}

				if ( isset( $disable_auto_insert_videos[0] ) || in_array( 'noads', $tag_names, true ) ) {
					$classes[] = 'adthrive-disable-video';
				}
			}

			if ( is_array( $disabled_categories ) && array_intersect( $disabled_categories, $category_names ) ) {
				$classes[] = 'adthrive-disable-all';
			} elseif ( is_array( $disabled_tags ) && array_intersect( $disabled_tags, $tag_names ) ) {
				$classes[] = 'adthrive-disable-all';
			}
		} elseif ( is_404() ) {
			$classes[] = 'adthrive-disable-all';
		}

		return $classes;
	}

	/**
	 * Gets the object name
	 *
	 * @param object $obj    An object with a name property
	 *
	 * @return string The object name
	 */
	private function pluck_name( $obj ) {
		return $obj->name;
	}

	/**
	 * Gets just the object name property
	 *
	 * @param object $obj    An object with a name property
	 *
	 * @return string An object with just a name property
	 */
	private function get_selectize( $obj ) {
		return array(
			'text' => $obj->name,
			'value' => $obj->name,
		);
	}

	/**
	 * Add fields to the options metabox
	 */
	public function all_objects() {
		$post_meta = new_cmb2_box( array(
			'id' => 'adthrive_ads_object_metabox',
			'title' => __( 'AdThrive Ads', 'adthrive_ads' ),
			'object_types' => array( 'page', 'post' ),
		) );

		$post_meta->add_field( array(
			'name' => __( 'Disable all ads', 'adthrive_ads' ),
			'id' => 'adthrive_ads_disable',
			'type' => 'checkbox',
		) );

		$post_meta->add_field( array(
			'name' => __( 'Disable in image ads', 'adthrive_ads' ),
			'id' => 'adthrive_ads_disable_in_image',
			'type' => 'checkbox',
		) );

		$post_meta->add_field( array(
			'name' => __( 'Disable content ads', 'adthrive_ads' ),
			'id' => 'adthrive_ads_disable_content_ads',
			'type' => 'checkbox',
		) );

		$post_meta->add_field( array(
			'name' => __( 'Disable auto-insert video players', 'adthrive_ads' ),
			'id' => 'adthrive_ads_disable_auto_insert_videos',
			'type' => 'checkbox',
		) );

		$post_meta->add_field( array(
			'name' => __( 'Re-enable ads on', 'adthrive_ads' ),
			'desc' => __( 'All ads on this post will be enabled on the specified date', 'adthrive_ads' ),
			'id'   => 'adthrive_ads_re_enable_ads_on',
			'type' => 'text_date_timestamp',
		) );

		if ( \AdThrive_Ads\Options::get( 'disable_video_metadata' ) === 'on' ) {
			$post_meta->add_field( array(
				'name' => __( 'Enable Video Metadata', 'adthrive_ads' ),
				'desc' => __( 'Enable adding metadata to video player on this post', 'adthrive_ads' ),
				'id'   => 'adthrive_ads_enable_metadata',
				'type' => 'checkbox',
			) );
		} else {
			$post_meta->add_field( array(
				'name' => __( 'Disable Video Metadata', 'adthrive_ads' ),
				'desc' => __( 'Disable adding metadata to video player on this post', 'adthrive_ads' ),
				'id'   => 'adthrive_ads_disable_metadata',
				'type' => 'checkbox',
			) );
		}

	}

	/**
	 * Add fields to the options metabox
	 *
	 * @param CMB $cmb A CMB metabox instance
	 */
	public function add_options( $cmb ) {
		$cmb->add_field( array(
			'name' => __( 'Site Id', 'adthrive_ads' ),
			'desc' => __( 'Add your AdThrive Site ID', 'adthrive_ads' ),
			'id' => 'site_id',
			'type' => 'text',
			'attributes' => array(
				'required' => 'required',
				'pattern' => '[0-9a-f]{24}',
				'title' => 'The site id needs to match the one provided by AdThrive exactly',
			),
		) );

		$cmb->add_field( array(
			'name' => 'Disabled for Categories',
			'desc' => 'Disable ads for the selected categories.',
			'id' => 'disabled_categories',
			'type' => 'text',
			'escape_cb' => array( $this, 'selectize_escape' ),
			'sanitization_cb' => array( $this, 'selectize_sanitize' ),
		) );

		$cmb->add_field( array(
			'name' => 'Disabled for Tags',
			'desc' => 'Disable ads for the selected tags.',
			'id' => 'disabled_tags',
			'type' => 'text',
			'escape_cb' => array( $this, 'selectize_escape' ),
			'sanitization_cb' => array( $this, 'selectize_sanitize' ),
		) );

		$cmb->add_field( array(
			'name' => 'Disable Video Metadata',
			'desc' => 'Disable adding metadata to video players. Caution: This is a site-wide change. Only choose if metadata is being loaded another way.',
			'id' => 'disable_video_metadata',
			'type' => 'checkbox',
		) );

		return $cmb;
	}

	/**
	 * Convert a selectize field array value to string
	 *
	 * @param  mixed $value The actual field value.
	 * @return String Field value converted to a string
	 */
	public function selectize_escape( $value ) {
		if ( is_string( $value ) ) {
			return $value;
		}

		return ! empty( $value ) ? implode( ',', $value ) : null;
	}

	/**
	 * Convert a selectize field value to array
	 *
	 * @param  mixed $value The actual field value.
	 * @return array Field value converted to an array
	 */
	public function selectize_sanitize( $value ) {
		if ( is_array( $value ) ) {
			return $value;
		}

		return ! empty( $value ) ? explode( ',', $value ) : null;
	}

	/**
	 * Add the AdThrive ads script
	 */
	public function ad_head() {
		$data['site_id'] = \AdThrive_Ads\Options::get( 'site_id' );
		$thrive_architect_enabled = isset( $_GET['tve'] ) && sanitize_key( $_GET['tve'] ) === 'true';

		if ( isset( $data['site_id'] ) && preg_match( '/[0-9a-f]{24}/i', $data['site_id'] ) && ! $thrive_architect_enabled ) {
			require 'partials/ads.php';
		}
	}

	/**
	 * Gets terms and displays them as options
	 *
	 * @param  String $taxonomy Taxonomy terms to retrieve. Default is category.
	 * @param  String|array $args Optional. get_terms optional arguments
	 * @return array An array of options that matches the CMB2 options array
	 */
	public function get_term_selectize( $taxonomy = 'category', $args = array() ) {
		$args['taxonomy'] = $taxonomy;
		$args = wp_parse_args( $args, array(
			'taxonomy' => 'category',
			'number' => 100,
		) );

		$taxonomy = $args['taxonomy'];

		$terms = (array) get_terms( $taxonomy, $args );

		return is_array( $terms ) ? array_map( array( $this, 'get_selectize' ), $terms ) : array();
	}
}
