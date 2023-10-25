<?php

/**
 * Business Hours: Localized week
 *
 * @since 7.1
 */
class CGSSUBS_REST_API_V1_Endpoint_CheckSubscription extends WP_REST_Controller {
	function __construct() {

		$this->namespace = 'cgsubs/v2';
		$this->rest_base = 'checksub';
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( $this, 'check_subscription' ),
				),
			)
		);
	}

	/**
	 * Retreives localized business hours
	 *
	 * @return array data object containing information about business hours
	 */
	public function check_subscription( $request ) {
		global $wpdb;

		$key_id               = '';
		$order_id             = '';
		$subscription_id      = '';
		$subscription_summary = array();

		$params = $request->get_params();
		//error_log( print_r( $params, true ) );
		if ( $params['id'] ) {
			$key_id = $params['id'];
		}

		$license = $wpdb->get_results(
			"
				SELECT * FROM {$wpdb->wc_software_licenses} WHERE key_id = '{$key_id}' LIMIT 1
			"
		);

		if ( ! empty( $license ) ) {
			//error_log( print_r( $license, true ) );
			$order_id      = $license['0']->order_id;
			$subscriptions = wcs_get_subscriptions_for_order( $order_id );
			$subscription  = reset( $subscriptions );

			//error_log( print_r( $subscription, true ) );
			if ( ! empty( $subscription ) ) {
				$subscription_id = $subscription->get_id();
				//error_log( $subscription_id );
				// check subscription overall status is active.
				$subscription_status = $subscription->get_status();
				//error_log( print_r( $subscription_status, true ) );

				// check expiry date for current subscription.
				$subscription_expiry_date = $subscription->get_date( 'next_payment' );
				//error_log( print_r( $subscription_expiry_date, true ) );

				$subscription_summary = array(
					'subscription_status'      => $subscription_status,
					'subscription_expiry_date' => $subscription_expiry_date,
				);
				return rest_ensure_response( $subscription_summary );

			} else {
				return new WP_Error( 'check_subscription', 'Subscription not found' );
			}
		} else {
			return new WP_Error( 'check_subscription', 'Ref not found' );
		}
	}

	public function cg_subs_search_domain( $domain ) {

		global $wpdb;
		$domain_check_result = array();
		$query               = $wpdb->get_results( "SELECT * FROM {$wpdb->wc_software_activations} WHERE instance = '{$domain}' LIMIT 1" );
		//error_log( print_r( $query, true ) );

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

$CGSSUBS_REST_API_V1_Endpoint_CheckSubscription = new CGSSUBS_REST_API_V1_Endpoint_CheckSubscription();

