<?php
/**
 * Plugin Name: Simple WP Crossposting – Page Builder by SiteOrigin
 * Plugin URL: https://rudrastyh.com/support/page-builder-by-siteorigin
 * Description: Adds better compatibility with Page Builder by SiteOrigin.
 * Author: Misha Rudrastyh
 * Author URI: https://rudrastyh.com
 * Version: 1.0
 */

class Rudr_SWC_SiteOrigin_Panels {

	function __construct() {
		// in SiteOrigin we are working with one specific meta key
		add_filter( 'rudr_swc_pre_crosspost_meta', array( $this, 'process' ), 25, 4 );

	}


	private function loop_elements( $panels_data, $blog ) {

		if( $panels_data[ 'widgets' ] && is_array( $panels_data[ 'widgets' ] ) ) {
			foreach( $panels_data[ 'widgets' ] as &$widget ) {
				// process our specific elements
				if( 'SiteOrigin_Widget_Image_Widget' === $widget[ 'panels_info' ][ 'class' ] ) {
//echo '<pre>';print_r( $widget );exit;
					$widget = $this->process_image_element( $widget, $blog );
					continue;
				}
				// loop child elements if any
				if( 'SiteOrigin_Panels_Widgets_Layout' === $widget[ 'panels_info' ][ 'class' ] && ! empty( $widget[ 'panels_data' ] ) ) {
//echo '<pre>';print_r( $widget );exit;
					$widget[ 'panels_data' ] = $this->loop_elements( $widget[ 'panels_data' ], $blog );
					continue;
				}

			}
		}

		return $panels_data;

	}


	public function process( $meta_value, $meta_key, $object_id, $blog ) {

		// serialized settings of a page created with pagebuilder
		if( 'panels_data' !== $meta_key ) {
			return $meta_value;
		}
		// now we convert the meta key json into an array of elements
		$panels_data = maybe_unserialize( $meta_value, true );
		// process the elements
//echo '<pre>';print_r( $panels_data );exit;
		$panels_data = $this->loop_elements( $panels_data, $blog );
//echo '<pre>';print_r( $panels_data );exit;
		return $panels_data;

	}

	private function process_image_element( $widget, $blog ) {

		$upload = Rudr_Simple_WP_Crosspost::maybe_crosspost_image( $widget[ 'image' ], $blog );
		if( $upload ) {
			$widget[ 'image' ] = $upload[ 'id' ];
		}
		return $widget;

	}


}

new Rudr_SWC_SiteOrigin_Panels();
