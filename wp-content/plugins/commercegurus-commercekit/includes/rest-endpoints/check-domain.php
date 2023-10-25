<?php

/**
 * Business Hours: Localized week
 *
 * @since 7.1
 */
class CGSSUBS_REST_API_V1_Endpoint_CheckDomain extends WP_REST_Controller {
	function __construct() {

		$this->namespace = 'cgsubs/v2';
		$this->rest_base = 'checkdomain';
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace, '/' . $this->rest_base, array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( $this, 'check_domain' ),
				),
			)
		);
	}

	/**
	 * Retreives localized business hours
	 *
	 * @return array data object containing information about business hours
	 */
	public function check_domain( $request ) {
		$domain_result = '';
			// check for a search term
		if ( isset( $request['d'] ) ) {
			$domain_param = $request['d'];

			// lookup domain function call.
			$domain_result = $this->cg_subs_search_domain( $domain_param );

			if ( ! empty( $domain_result ) ) {
				// check for active sub.
				return rest_ensure_response( $domain_result );
			} else {
				return new WP_Error( 'domain_search', 'No results' );
			}
		} else {
			return new WP_Error( 'domain_search', 'No domain provided' );
		}
	}

	public function cg_subs_search_domain( $domain ) {

		global $wpdb;
		$domain_check_result = array();
		$query               = $wpdb->get_results( "SELECT * FROM {$wpdb->wc_software_activations} WHERE instance = '{$domain}' LIMIT 1" );

		if ( ! empty( $query ) ) {
			// we've got a result.
			// return activation status (active/inactive)
			//error_log( $query[0]->activation_active );
			$domain_check_result = array(
				'domain'            => $query[0]->instance,
				'activation_status' => $query[0]->activation_active,
				'activation_time'   => $query[0]->activation_time,
				'ref'               => $query[0]->key_id,
			);
		} else {
			//error_log( 'no results' );
		}
		return $domain_check_result;
	}

}

$CGSSUBS_REST_API_V1_Endpoint_CheckDomain = new CGSSUBS_REST_API_V1_Endpoint_CheckDomain();

