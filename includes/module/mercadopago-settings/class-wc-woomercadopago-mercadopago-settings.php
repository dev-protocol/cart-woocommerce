<?php

class WC_WooMercadoPago_MercadoPago_Settings {



	const PRIORITY_ON_MENU = 90;

	protected $options;

	public function __construct( WC_WooMercadoPago_Options $options ) {
		$this->options = $options;
	}

	/**
	 * Action to insert Mercado Pago in WooCommerce Menu and Load JavaScript and CSS
	 */
	public function init() {
		$this->load_menu();
		$this->register_endpoints();
		$this->load_scripts_and_styles();
	}

	/**
	 * Load menu
	 */
	public function load_menu() {
		add_action('admin_menu', array($this, 'register_mercadopago_in_woocommerce_menu'), self::PRIORITY_ON_MENU);
	}

	/**
	 * Load Scripts
	 *
	 * @return void
	 */
	public function load_scripts_and_styles() {
		add_action('admin_enqueue_scripts', array($this, 'load_admin_scripts'));
		add_action('admin_enqueue_scripts', array($this, 'load_admin_style'));
	}

	/**
	 * Load CSS
	 */
	public function load_admin_style() {
		wp_register_style(
			'mercadopago_settings_admin_css',
			$this->get_url('../../../assets/css/mercadopago-settings/mercadopago_settings', '.css'),
			false,
			WC_WooMercadoPago_Constants::VERSION
		);
		wp_enqueue_style('mercadopago_settings_admin_css');
	}

	/**
	 * Load JavaScripts
	 */
	public function load_admin_scripts() {
		wp_enqueue_script(
			'mercadopago_settings_javascript',
			$this->get_url('../../../assets/js/mercadopago-settings/mercadopago_settings', '.js'),
			array(),
			WC_WooMercadoPago_Constants::VERSION,
			true
		);
	}

	/**
	 * Register Mercado Pago Option in WooCommerce Menu
	 */
	public function register_mercadopago_in_woocommerce_menu() {
		add_submenu_page(
			'woocommerce',
			__('Mercado Pago Settings', 'woocommerce-mercadopago'),
			'Mercado Pago',
			'manage_options',
			'mercadopago-settings',
			array($this, 'mercadopago_submenu_page_callback')
		);
	}

	/**
	 * Mercado Pago Template Call
	 */
	public function mercadopago_submenu_page_callback() {
		$categories_store    = WC_WooMercadoPago_Module::$categories;
		$category_selected   = $this->options->get_store_category();
		$category_id         = $this->options->get_store_activity_identifier();
		$store_identificator = $this->options->get_store_name_on_invoice();
		$integrator_id       = $this->options->get_integrator_id();
		$devsite_links       = $this->options->get_mp_devsite_links();
		$debug_mode          = $this->options->get_debug_mode();
		$url_ipn             = $this->options->get_url_ipn();
		$links               = WC_WooMercadoPago_Helper_Links::woomercadopago_settings_links();
		$checkbox_test_mode  = $this->options->get_checkbox_test_mode();
		$options_credentials = $this->options->get_access_token_and_public_key();
		$admin_header        = self::field_admin_header();
		$admin_credential    = self::field_admin_credential();
		$admin_store         = self::field_admin_store();
		$admin_payment       = self::field_admin_payment();
		$admin_test_mode     = self::field_admin_test_mode();
		include __DIR__ . '/../../../templates/mercadopago-settings/mercadopago-settings.php';
	}

	/**
	 * Register Mercado Pago Endpoints
	 */
	public function register_endpoints() {
		add_action('wp_ajax_mp_get_requirements', array($this, 'mercadopago_get_requirements'));
		add_action('wp_ajax_mp_validate_credentials', array($this, 'mp_validate_credentials'));
		add_action('wp_ajax_mp_update_store_information', array($this, 'mp_update_store_info'));
		add_action('wp_ajax_mp_store_mode', array($this, 'mp_set_mode'));
		add_action('wp_ajax_mp_get_payment_properties', array($this, 'mp_get_payment_class_properties'));
		add_action('wp_ajax_mp_validate_store_tips', array($this, 'mp_validate_store_tips'));
		add_action('wp_ajax_mp_validate_credentials_tips', array($this, 'mp_validate_credentials_tips'));
		add_action('wp_ajax_mp_validate_payment_tips', array($this, 'mp_validate_field_payment_tips'));
	}

	/**
	 * Admin field header
	 *
	 * @return array
	 */
	public function field_admin_header() {
		$field_header = array(
			'title_head_part_one'         => __('Accept ', 'woocommerce-mercadopago'),
			'title_head_part_two'           => __('payments on the spot ', 'woocommerce-mercadopago'),
			'title_head_part_three'         => __('with', 'woocommerce-mercadopago'),
			'title_head_part_four'          => __('the ', 'woocommerce-mercadopago'),
			'title_head_part_six'           => __('security ', 'woocommerce-mercadopago'),
			'title_head_part_seven'         => __('from Mercado Pago', 'woocommerce-mercadopago'),
			'title_requirements'            => __('Technical requirements', 'woocommerce-mercadopago'),
			'ssl'                                     => __('SSL', 'woocommerce-mercadopago'),
			'gd_extensions'                     => __('GD Extensions', 'woocommerce-mercadopago'),
			'curl'                                          => __('Curl', 'woocommerce-mercadopago'),
			'description_ssl'                                     => __('Implementation responsible for transmitting data to Mercado Pago in a secure and encrypted way.', 'woocommerce-mercadopago'),
			'description_gd_extensions'                     => __('These extensions are responsible for the implementation and operation of Pix in your store.', 'woocommerce-mercadopago'),
			'description_curl'                                          => __('It is an extension responsible for making payments via requests from the plugin to Mercado Pago.', 'woocommerce-mercadopago'),
			'title_installments'            => __('Collections and installments', 'woocommerce-mercadopago'),
			'descripition_installments'   => __('Choose ', 'woocommerce-mercadopago'),
			'descripition_installments_one'   => __('when you want to receive the money ', 'woocommerce-mercadopago'),
			'descripition_installments_two'   => __('from your sales and if you want to offer ', 'woocommerce-mercadopago'),
			'descripition_installments_three'   => __('interest-free installments ', 'woocommerce-mercadopago'),
			'descripition_installments_four'   => __('to your clients.', 'woocommerce-mercadopago'),
			'button_installments'           => __('Set deadlines and fees', 'woocommerce-mercadopago'),
			'title_questions'                   => __('Questions? ', 'woocommerce-mercadopago'),
			'descripition_questions_one'      => __('Review the step-by-step of ', 'woocommerce-mercadopago'),
			'descripition_questions_two'      => __('how to integrate the Mercado Pago Plugin ', 'woocommerce-mercadopago'),
			'descripition_questions_three'      => __('on our webiste for developers.', 'woocommerce-mercadopago'),
			'button_questions'                  => __('Plugin manual', 'woocommerce-mercadopago'),
		);

		return $field_header;
	}

	/**
	 * Admin field credential
	 *
	 * @return array
	 */
	public function field_admin_credential() {

		$field_credential = array(

			'title_credentials'             => __('1. Integrate your store with Mercado Pago  ', 'woocommerce-mercadopago'),
			'subtitle_credentials_one'          => __('To enable and test sales, you must copy and paste your ', 'woocommerce-mercadopago'),
			'subtitle_credentials_two'          => __('credentials below.', 'woocommerce-mercadopago'),
			'button_link_credentials'     => __('Check credentials', 'woocommerce-mercadopago'),
			'title_credential_test'     => __('Test credentials ', 'woocommerce-mercadopago'),
			'subtitle_credential_test'     => __('Enable Mercado Pago checkouts for test purchases in the store.', 'woocommerce-mercadopago'),
			'public_key'     => __('Public key', 'woocommerce-mercadopago'),
			'access_token'     => __('Access Token', 'woocommerce-mercadopago'),
			'title_credential_prod'     => __('Production credentials', 'woocommerce-mercadopago'),
			'subtitle_credential_prod'     => __('Enable Mercado Pago checkouts to receive real payments in the store.', 'woocommerce-mercadopago'),
			'placeholder_public_key'     => __('Paste your Public Key here', 'woocommerce-mercadopago'),
			'placeholder_access_token'     => __('Paste your Access Token here', 'woocommerce-mercadopago'),
			'button_credentials' => __('Save and continue', 'woocommerce-mercadopago'),

		);
		return $field_credential;
	}

	/**
	 * Admin field store
	 *
	 * @return array
	 */
	public function field_admin_store() {

		$field_store = array(

			'title_store'     => __('2. Customize your business', 'woocommerce-mercadopago'),
			'subtitle_store'     => __('Fill out the following information to have a better experience and offer more information to your clients', 'woocommerce-mercadopago'),
			'title_info_store'  => __('Your store information', 'woocommerce-mercadopago'),
			'subtitle_name_store'  => __("Name of your store in your client's invoice", 'woocommerce-mercadopago'),
			'placeholder_name_store'  => __("Eg: Mary's store", 'woocommerce-mercadopago'),
			'helper_name_store'  => __('If this field is empty, the purchase will be identified as Mercado Pago.', 'woocommerce-mercadopago'),
			'subtitle_activities_store'  => __('Identification in Activities of Mercad Pago', 'woocommerce-mercadopago'),
			'placeholder_activities_store'  => __('Eg: Marystore', 'woocommerce-mercadopago'),
			'helper_activities_store'  => __('In Activities, you will view this term before the order number', 'woocommerce-mercadopago'),
			'subtitle_category_store'  => __('Store category', 'woocommerce-mercadopago'),
			'placeholder_category_store'  => __('Select', 'woocommerce-mercadopago'),
			'helper_category_store'  => __('Select ”Other” if you do not find the appropriate category.', 'woocommerce-mercadopago'),
			'title_advanced_store'  => __('Advanced integration options (optional)', 'woocommerce-mercadopago'),
			'subtitle_advanced_store'  => __('For further integration of your store with Mercado Pago (IPN, Certified Partners, Debug Mode)', 'woocommerce-mercadopago'),
			'accordion_advanced_store'  => __('View advanced options', 'woocommerce-mercadopago'),
			'subtitle_url'  => __('URL for IPN ', 'woocommerce-mercadopago'),
			'placeholder_url'  => __('Eg: https://examples.com/my-custom-ipn-url', 'woocommerce-mercadopago'),
			'helper_url'  => __('Add the URL to receive payments notifications. Find out more information in the ', 'woocommerce-mercadopago'),
			'helper_url_link'  => __('guides.', 'woocommerce-mercadopago'),
			'subtitle_integrator'  => __('integrator_id', 'woocommerce-mercadopago'),
			'placeholder_integrator'  => __('Eg: 14987126498', 'woocommerce-mercadopago'),
			'helper_integrator'  => __('If you are a Mercado Pago Certified Partner, make sure to add your integrator_id. If you do not have the code, please ', 'woocommerce-mercadopago'),
			'helper_integrator_link'  => __('request it now. ', 'woocommerce-mercadopago'),
			'title_debug'  => __('Debug and Log Mode', 'woocommerce-mercadopago'),
			'subtitle_debug'  => __("We record your store's actions in order to provide a better assistance.", 'woocommerce-mercadopago'),
			'button_store' => __('Save and continue', 'woocommerce-mercadopago'),
		);
		return $field_store;

	}

	/**
	 * Admin field payment
	 *
	 * @return array
	 */
	public function field_admin_payment() {
		$field_payment = array(
			'title_payments'  => __('3. Set payment methods', 'woocommerce-mercadopago'),
			'subtitle_payments'  => __('To view more options, please select a payment method below', 'woocommerce-mercadopago'),
			'settings_payment' => __('Settings', 'woocommerce-mercadopago'),
			'button_payment' => __('Continue', 'woocommerce-mercadopago'),
		);
		return $field_payment;

	}

	/**
	 * Admin field test mode
	 *
	 * @return array
	 */
	public function field_admin_test_mode() {
		$field_test_mode = array(
			'title_test_mode'  => __('4. Test your store before you sell', 'woocommerce-mercadopago'),
			'subtitle_test_mode'  => __('Test the experience in Test Mode and then enable the Sale Mode (Production) to sell.', 'woocommerce-mercadopago'),
			'title_mode'  => __('Choose how you want to operate your store:', 'woocommerce-mercadopago'),
			'title_test'  => __('Test Mode', 'woocommerce-mercadopago'),
			'subtitle_test'  => __('Mercado Pago Checkouts disabled for real collections. ', 'woocommerce-mercadopago'),
			'subtitle_test_link'  => __('Test Mode rules.', 'woocommerce-mercadopago'),
			'title_prod'  => __('Sale Mode (Production)', 'woocommerce-mercadopago'),
			'subtitle_prod'  => __('Mercado Pago Checkouts enabled for real collections.', 'woocommerce-mercadopago'),
			'title_message_prod'  => __('Mercado Pago payment methods in Production Mode', 'woocommerce-mercadopago'),
			'subtitle_message_prod'  => __('The clients can make real purchases in your store.', 'woocommerce-mercadopago'),
			'title_message_test'  => __('Mercado Pago payment methods in Test Mode', 'woocommerce-mercadopago'),
			'subtitle_link_test'  => __('Visit your store ', 'woocommerce-mercadopago'),
			'subtitle_message_test'  => __('to test purchases', 'woocommerce-mercadopago'),
			'button_mode' => __('Save changes', 'woocommerce-mercadopago'),
			'badge_test' => __('Store under test', 'woocommerce-mercadopago'),
			'badge_mode' => __('Store in sale mode (Production)', 'woocommerce-mercadopago'),
		);
		return $field_test_mode;
	}


	/**
	 * Requirements
	 */
	public function mercadopago_get_requirements() {
		$hasCurl = in_array('curl', get_loaded_extensions(), true);
		$hasGD   = in_array('gd', get_loaded_extensions(), true);
		$hasSSL  = is_ssl();

		wp_send_json_success([
			'ssl' => $hasSSL,
			'gd_ext' => $hasGD,
			'curl_ext' => $hasCurl
		]);
	}

	/**
	 * Validate credentials Ajax
	 */
	public function mp_validate_credentials() {
		try {
			$access_token = WC_WooMercadoPago_Credentials::get_sanitize_text_from_post('access_token');
			$public_key   = WC_WooMercadoPago_Credentials::get_sanitize_text_from_post('public_key');
			$is_test      = ( WC_WooMercadoPago_Credentials::get_sanitize_text_from_post('is_test') === 'true' );

			$mp = WC_WooMercadoPago_Module::get_mp_instance_singleton();

			if ( $access_token ) {
				$validate_access_token = $mp->get_credentials_wrapper($access_token);
				if ( ! $validate_access_token || $validate_access_token['is_test'] !== $is_test ) {
					wp_send_json_error( __( 'Invalid Access Token', 'woocommerce-mercadopago') );
				}
				wp_send_json_success( __( 'Valid Access Token', 'woocommerce-mercadopago') );
			}

			if ( $public_key ) {
				$validate_public_key = $mp->get_credentials_wrapper(null, $public_key);
				if ( ! $validate_public_key || $validate_public_key['is_test'] !== $is_test ) {
					wp_send_json_error( __( 'Invalid Public Key', 'woocommerce-mercadopago') );
				}
				wp_send_json_success( __( 'Valid Public Key', 'woocommerce-mercadopago') );
			}

			throw new Exception( __( 'Credentials must be valid', 'woocommerce-mercadopago') );
		} catch ( Exception $e ) {
			$response = [
			'message' => $e->getMessage()
			];

			wp_send_json_error($response);
		}
	}

	/**
	 * Get URL with path
	 *
	 * @param $path
	 * @param $extension
	 *
	 * @return string
	 */
	public function get_url( $path, $extension ) {
		return sprintf(
			'%s%s%s%s',
			plugin_dir_url(__FILE__),
			$path,
			$this->get_suffix(),
			$extension
		);
	}

	/**
	 * Get suffix to static files
	 *
	 * @return string
	 */
	public function get_suffix() {
		return defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
	}

	/**
	 * Validate store info Ajax
	 */
	public function mp_update_store_info() {
		try {
			$store_info = array(
				'mp_statement_descriptor'           => WC_WooMercadoPago_Credentials::get_sanitize_text_from_post('store_identificator'),
				'_mp_category_id'   => WC_WooMercadoPago_Credentials::get_sanitize_text_from_post('store_categories'),
				'_mp_store_identificator'       => WC_WooMercadoPago_Credentials::get_sanitize_text_from_post('store_category_id'),
				'_mp_custom_domain'         => WC_WooMercadoPago_Credentials::get_sanitize_text_from_post('store_url_ipn'),
				'_mp_integrator_id' => WC_WooMercadoPago_Credentials::get_sanitize_text_from_post('store_integrator_id'),
				'_mp_debug_mode'        => WC_WooMercadoPago_Credentials::get_sanitize_text_from_post('store_debug_mode'),
			);

			foreach ( $store_info as $key => $value ) {
				update_option($key, $value, true);
			}

			wp_send_json_success( __( 'Store information is valid', 'woocommerce-mercadopago') );

		} catch ( Exception $e ) {
			$response = [
			'message' => $e->getMessage()
			];

			wp_send_json_error($response);
		}
	}

	/**
	 * Switch store mode
	 */
	public function mp_set_mode() {
		try {
			$checkout_test_mode = WC_WooMercadoPago_Credentials::get_sanitize_text_from_post('input_mode_value');
			update_option('checkbox_checkout_test_mode', $checkout_test_mode, true);
			wp_send_json_success( __( 'Store mode was updated', 'woocommerce-mercadopago') );

		} catch ( Exception $e ) {
			$response = [
			'message' => $e->getMessage()
			];

			wp_send_json_error($response);
		}
	}

	/**
	 * Get payment class properties
	 */
	public function mp_get_payment_class_properties() {
		try {
			$payments_gateways          = WC_WooMercadoPago_Constants::PAYMENT_GATEWAYS;
			$payment_gateway_properties = array();
			$wc_country                 = WC_WooMercadoPago_Module::get_woocommerce_default_country();

			foreach ( $payments_gateways as $payment_gateway ) {
				if ( 'WC_WooMercadoPago_Pix_Gateway' === $payment_gateway && 'BR' !== $wc_country ) {
					continue;
				}
				$gateway = new $payment_gateway();

				$additional_info = [
					'woo-mercado-pago-basic' => ['icon' => 'mp-settings-icon-mp'],
					'woo-mercado-pago-custom' => ['icon' => 'mp-settings-icon-card'],
					'woo-mercado-pago-ticket' => ['icon' => 'mp-settings-icon-code'],
					'woo-mercado-pago-pix' => ['icon' => 'mp-settings-icon-pix']
				];

				$payment_gateway_properties[] = array(

					'id'     => $gateway->id,
					'description'   => $gateway->description,
					'title'   => $gateway->title,
					'enabled' => $gateway->settings['enabled'],
					'icon' => $additional_info[$gateway->id]['icon'],
					'link' => admin_url('admin.php?page=wc-settings&tab=checkout&section=') . $gateway->id,
					'badge_translator' => [ 'yes' => __('Enabled', 'woocommerce-mercadopago'), 'no' => __('Disabled', 'woocommerce-mercadopago')],
				);
			}
			wp_send_json_success($payment_gateway_properties);
		} catch ( Exception $e ) {
			$response = [
			'message' => $e->getMessage()
			];

			wp_send_json_error($response);
		}
	}

	/**
	 * Validate credentials tips
	 */
	public function mp_validate_credentials_tips() {
		try {
			$public_key_test   = get_option(WC_WooMercadoPago_Options::CREDENTIALS_PUBLIC_KEY_TEST, '');
			$access_token_test = get_option(WC_WooMercadoPago_Options::CREDENTIALS_ACCESS_TOKEN_TEST, '');
			$public_key_prod   = get_option(WC_WooMercadoPago_Options::CREDENTIALS_PUBLIC_KEY_PROD, '');
			$access_token_prod = get_option(WC_WooMercadoPago_Options::CREDENTIALS_ACCESS_TOKEN_PROD, '');

			if ( $public_key_test && $access_token_test && $public_key_prod && $access_token_prod ) {
				wp_send_json_success( __( 'Valid Credentials', 'woocommerce-mercadopago') );
			}

			throw new Exception( __( 'Credentials couldn\'t be validated', 'woocommerce-mercadopago') );

		} catch ( Exception $e ) {
		$response = [
			'message' => $e->getMessage()
		];

		wp_send_json_error($response);
		}
	}


		/**
	 * Validate store tips
	 */
	public function mp_validate_store_tips() {
		try {
			$statement_descriptor = get_option('mp_statement_descriptor');
			$category_id          = get_option('_mp_category_id');
			$identificator        = get_option('_mp_store_identificator');

			if ( $statement_descriptor && $category_id && $identificator ) {
				wp_send_json_success( __( 'Store business fields are valid', 'woocommerce-mercadopago') );
			}

			throw new Exception( __( 'Store business fields couldn\'t be validated', 'woocommerce-mercadopago') );

		} catch ( Exception $e ) {
		$response = [
			'message' => $e->getMessage()
		];

		wp_send_json_error($response);
		}
	}

	/**
	 * Validate field payment
	 */
	public function mp_validate_payment_tips() {
		try {
			$payments_gateways = WC_WooMercadoPago_Constants::PAYMENT_GATEWAYS;

			foreach ( $payments_gateways as $payment_gateway ) {
				$gateway = new $payment_gateway();

				if ( 'yes' === $gateway->settings['enabled'] ) {
					wp_send_json_success( __( 'At least one paymet method is enabled', 'woocommerce-mercadopago') );
				}
			}
			throw new Exception( __( 'No payment method enabled', 'woocommerce-mercadopago') );
		} catch ( Exception $e ) {
			$response = [
			'message' => $e->getMessage()
			];

			wp_send_json_error($response);
		}
	}
}
