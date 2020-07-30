<?php
/**
 * Copyright © 2019 ZhenIT Software. All rights reserved.
 *
 * @since     1.0
 * @package   gravityforms_redsys
 */
use \Redsys\RedsysAPI;
use \Redsys\RedsysHelper;

GFForms::include_payment_addon_framework();

/**
 * Payment class
 */
class GFRedsys extends GFPaymentAddOn {

	protected $_version                  = GF_REDSYS_VERSION;
	protected $_min_gravityforms_version = '1.9.12';
	protected $_slug                     = 'redsys';
	protected $_path                     = 'gravityformsredsys/redsys.php';
	protected $_full_path                = __FILE__;
	protected $_url                      = 'http://modulosdepago.es';
	protected $_title                    = 'Gravity Forms Redsys Add-On';
	protected $_short_title              = 'Redsys';
	protected $_supports_callbacks       = true;
	protected $_requires_credit_card     = false;
	protected $_enable_rg_autoupgrade    = false;

	/**
	 * Redsys requires monetary amounts to be formatted as the smallest unit for the currency being used e.g. cents.
	 *
	 * @since  1.10.1
	 *
	 * @var bool $_requires_smallest_unit true
	 */
	protected $_requires_smallest_unit = true;

	private static $live_url = 'https://sis.redsys.es/sis/realizarPago';
	private static $test_url = 'https://sis-t.redsys.es:25443/sis/realizarPago';

	/**
	 * Members plugin integration
	 */
	protected $_capabilities = array(
		'gravityforms_redsys',
		'gravityforms_redsys_uninstall',
		'gravityforms_redsys_plugin_page',
	);

	/**
	 * Permissions
	 */
	protected $_capabilities_settings_page = 'gravityforms_redsys';
	protected $_capabilities_form_settings = 'gravityforms_redsys';
	protected $_capabilities_uninstall     = 'gravityforms_redsys_uninstall';
	protected $_capabilities_plugin_page   = 'gravityforms_redsys_plugin_page';

	// Automatic upgrade enabled
	// protected $_enable_rg_autoupgrade = true;
	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFRedsys
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFRedsys();
		}

		return self::$_instance;
	}

	private function __clone() {
	} /* do nothing */

	/**
	 * Load the Stripe credit card field.
	 *
	 * @since 2.6
	 */
	public function pre_init() {
		// For form confirmation redirection, this must be called in `wp`,
		// or confirmation redirect to a page would throw PHP fatal error.
		// Run before calling parent method. We don't want to run anything else before displaying thank you page.
		add_action( 'wp', array( 'GFRedsys', 'maybe_thankyou_page' ), 5 );
		parent::pre_init();
	}

	public function init_frontend() {
		parent::init_frontend();
		add_filter( 'gform_disable_post_creation', array( $this, 'delay_post' ), 10, 3 );
		add_filter( 'gform_disable_notification', array( $this, 'delay_notification' ), 10, 4 );
	}

		/**
		 * Return the scripts which should be enqueued.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @uses GFPaymentAddOn::scripts()
		 * @uses GFAddOn::get_base_url()
		 * @uses GFAddOn::get_short_title()
		 * @uses GFStripe::$_version
		 * @uses GFCommon::get_base_url()
		 * @uses GFStripe::frontend_script_callback()
		 *
		 * @return array The scripts to be enqueued.
		 */
	public function scripts() {
		$min     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
		$scripts = array(
			array(
				'handle'    => 'gf_redsys',
				'src'       => plugins_url( null, __FILE__ ) . '/js/gf_redsys.js',
				'version'   => GFCommon::$version,
				'deps'      => array(),
				'in_footer' => false,
				'enqueue'   => array(
					array( $this, 'gf_redsys' ),
				),
			),
		);
		return array_merge( parent::scripts(), apply_filters( 'gravityforms_redsys_scripts', $scripts ) );

	}

	/**
	 * Runs only when AJAX actions are being performed.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @uses GFFeedAddOn::init_ajax()
	 * @uses GFPaymentAddOn::ajax_cancel_subscription()
	 * @uses GFPaymentAddOn::before_delete_field()
	 *
	 * @return void
	 */
	public function init_ajax() {
		parent::init_ajax();
		do_action( 'gravityforms_redsys_init_ajax' );
	}

	// # ADMIN FUNCTIONS -----------------------------------------------------------------------------------------------
	// ------- Plugin settings -------

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		$description = '<p style="text-align: left;">' .
			sprintf(
				esc_html__( 'Redsys is a payment gateway. Use Gravity Forms to collect credit card payments' ),
				'gravityformsredsys'
			) . '</p>';

		return array(
			array(
				'description' => $description,
				'fields'      => array(
					array(
						'name'          => 'gf_redsys_mode',
						'label'         => esc_html__( 'Mode', 'gravityformsredsys' ),
						'type'          => 'radio',
						'default_value' => 'production',
						'choices'       => array(
							array(
								'label' => esc_html__( 'Live', 'gravityformsredsys' ),
								'value' => 'production',
							),
							array(
								'label' => esc_html__( 'Sandbox', 'gravityformsredsys' ),
								'value' => 'test',
							),
						),
						'horizontal'    => true,
					),
					array(
						'name'              => 'gf_redsys_merchant',
						'label'             => esc_html__( 'Merchant Id', 'gravityformsredsys' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( 'RedsysHelper', 'checkFuc' ),
					),
					array(
						'name'              => 'gf_redsys_terminal',
						'label'             => esc_html__( 'Terminal Id', 'gravityformsredsys' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( 'RedsysHelper', 'checkTerminal' ),
					),
					array(
						'name'  => 'gf_redsys_secret',
						'label' => esc_html__( 'Secret Key', 'gravityformsredsys' ),
						'type'  => 'password',
						'class' => 'medium',
					),
					array(
						'name'    => 'gf_redsys_force_http',
						'label'   => esc_html__( 'Force http notification', 'gravityformsredsys' ),
						'type'    => 'checkbox',
						'choices' => array(
							array(
								'label' => esc_html__(
									'Needed if your site uses SSL and are having problems with https notification',
									'gravityformsredsys'
								),
								'name'  => 'gf_redsys_force_http',
							),
						),
					),
				),
			),
		);
	}

	public function feed_list_no_item_message() {
		$settings = $this->get_plugin_settings();
		if ( ! rgar( $settings, 'gf_redsys_merchant' ) || ! rgar( $settings, 'gf_redsys_terminal' ) || ! rgar(
			$settings,
			'gf_redsys_secret'
		)
		) {
			return sprintf(
				esc_html__(
					'To get started, please configure your %1$sRedsys Settings%2$s!',
					'gravityformsredsys'
				),
				'<a href="' . admin_url( 'admin.php?page=gf_settings&subview=' . $this->_slug ) . '">',
				'</a>'
			);
		} else {
			return parent::feed_list_no_item_message();
		}
	}

	/**
	 * Define the markup for the password type field.
	 *
	 * @param array     $field The field properties.
	 * @param bool|true $echo Should the setting markup be echoed.
	 *
	 * @return string|void
	 */
	public function settings_password( $field, $echo = true ) {

		$field['type'] = 'text';

		$password_field = $this->settings_text( $field, false );

		// switch type="text" to type="password" so the password is not visible
		$password_field = str_replace( 'type="text"', 'type="password"', $password_field );

		if ( $echo ) {
			echo $password_field;
		}

		return $password_field;

	}

	// -------- Form Settings ---------

	/**
	 * Prevent feeds being listed or created if the api keys aren't valid.
	 *
	 * @return bool
	 */
	public function can_create_feed() {
		// return $this->is_valid_api_credentials();
		// @todo
		return true;
	}

	/**
	 * Configures the settings which should be rendered on the feed edit page.
	 *
	 * @return array The feed settings.
	 */
	public function feed_settings_fields() {
		$default_settings = parent::feed_settings_fields();
		// For different merchants per installation we could add fields here.
		$description = '<p style="text-align: left;">' .
			sprintf(
				esc_html__(
					'Redsys is a payment gateway. Use Gravity Forms to collect credit card payments'
				),
				'gravityformsredsys'
			) .
			'</p>';
		if ( apply_filters( 'gravityforms_redsys_remove_subscription', true, $this ) ) {
			$default_settings = parent::replace_field(
				'transactionType',
				array(
					'name'     => 'transactionType',
					'label'    => esc_html__( 'Transaction Type', 'gravityforms' ),
					'type'     => 'select',
					'onchange' => "jQuery(this).parents('form').submit();",
					'choices'  => array(
						array(
							'label' => esc_html__( 'Select a transaction type', 'gravityforms' ),
							'value' => '',
						),
						array(
							'label' => esc_html__( 'Products and Services', 'gravityforms' ),
							'value' => 'product',
						),
					),
					'tooltip'  => '<h6>' . esc_html__( 'Transaction Type', 'gravityforms' )
						. '</h6>' . esc_html__( 'Select a transaction type.', 'gravityforms' ),
				),
				$default_settings
			);
		}
		if ( apply_filters( 'gravityforms_redsys_remove_subscription_sub', true, $this ) ) {
			$default_settings = parent::remove_field( 'setupFee', $default_settings );
			$default_settings = parent::remove_field( 'trial', $default_settings );
		}
		// --add Page Style, Continue Button Label, Cancel URL
		$fields = array(
			array(
				'name'     => 'apiSettingsEnabled',
				'label'    => esc_html__( 'API Settings', 'gravityformsredsys' ),
				'type'     => 'checkbox',
				'tooltip'  => '<h6>' . esc_html__( 'API Settings', 'gravityformsredsys' ) . '</h6>' . esc_html__( 'Use specific setting for this form.', 'gravityformsredsys' ),
				'onchange' => 'gf_redsys_toggleApiSettings(this);',
				'choices'  => array(
					array(
						'label' => 'Override Default Settings',
						'name'  => 'apiSettingsEnabled',
					),
				),
			),
			array(
				'name'          => 'overrideMode',
				'label'         => esc_html__( 'Mode', 'gravityformsredsys' ),
				'type'          => 'radio',
				'default_value' => 'production',
				'choices'       => array(
					array(
						'label' => esc_html__( 'Live', 'gravityformsredsys' ),
						'value' => 'production',
					),
					array(
						'label' => esc_html__( 'Sandbox', 'gravityformsredsys' ),
						'value' => 'test',
					),
				),
				'horizontal'    => true,
			),
			array(
				'name'              => 'overrideMerchant',
				'label'             => esc_html__( 'Merchant Id', 'gravityformsredsys' ),
				'type'              => 'text',
				'class'             => 'medium',
				'feedback_callback' => array( 'RedsysHelper', 'checkFuc' ),
			),
			array(
				'name'              => 'overrideTerminal',
				'label'             => esc_html__( 'Terminal Id', 'gravityformsredsys' ),
				'type'              => 'text',
				'class'             => 'medium',
				'feedback_callback' => array( 'RedsysHelper', 'checkTerminal' ),
			),
			array(
				'name'  => 'overrideSecret',
				'label' => esc_html__( 'Secret Key', 'gravityformsredsys' ),
				'type'  => 'password',
				'class' => 'medium',
			),
			array(
				'name'     => 'cancelUrl',
				'label'    => esc_html__( 'Cancel URL', 'gravityformsredsys' ),
				'type'     => 'text',
				'class'    => 'medium',
				'required' => false,
				'tooltip'  => '<h6>' . esc_html__(
					'Cancel URL',
					'gravityformsredsys'
				) . '</h6>' . esc_html__(
					'Enter the URL the user should be sent to should they cancel before completing their Redsýs payment.',
					'gravityformsredsys'
				),
			),
		);

		if ( $this->get_setting( 'delayNotification' ) || ! $this->is_gravityforms_supported( '1.9.12' ) ) {
			$fields[] = array(
				'name'    => 'notifications',
				'label'   => esc_html__( 'Notifications', 'gravityformsredsys' ),
				'type'    => 'notifications',
				'tooltip' => '<h6>' . esc_html__(
					'Notifications',
					'gravityformsredsys'
				) . '</h6>' . esc_html__(
					"Enable this option if you would like to only send out this form's notifications for the 'Form is submitted' event after payment has been received. Leaving this option disabled will send these notifications immediately after the form is submitted. Notifications which are configured for other events will not be affected by this option.",
					'gravityformsredsys'
				),
			);
		}

		// Add post fields if form has a post
		$form = $this->get_current_form();
		if ( GFCommon::has_post_field( $form['fields'] ) ) {
			$post_settings = array(
				'name'    => 'post_checkboxes',
				'label'   => esc_html__( 'Posts', 'gravityformsredsys' ),
				'type'    => 'checkbox',
				'tooltip' => '<h6>' . esc_html__(
					'Posts',
					'gravityformsredsys'
				) . '</h6>' . esc_html__(
					'Enable this option if you would like to only create the post after payment has been received.',
					'gravityformsredsys'
				),
				'choices' => array(
					array(
						'label' => esc_html__( 'Create post only when payment is received.', 'gravityformsredsys' ),
						'name'  => 'delayPost',
					),
				),
			);

			if ( $this->get_setting( 'transactionType' ) == 'subscription' ) {
				$post_settings['choices'][] = array(
					'label'    => esc_html__( 'Change post status when subscription is canceled.', 'gravityformsredsys' ),
					'name'     => 'change_post_status',
					'onChange' => 'var action = this.checked ? "draft" : ""; jQuery("#update_post_action").val(action);',
				);
			}

			$fields[] = $post_settings;
		}

		$default_settings = $this->add_field_after( 'billingInformation', $fields, $default_settings );

		// -----------------------------------------------------------------------------------------
		return $default_settings;
	}

	public function supported_billing_intervals() {

		$billing_cycles = array(
			'day'   => array(
				'label' => esc_html__( 'day(s)', 'gravityformsredsys' ),
				'min'   => 1,
				'max'   => 90,
			),
			'week'  => array(
				'label' => esc_html__( 'week(s)', 'gravityformsredsys' ),
				'min'   => 1,
				'max'   => 52,
			),
			'month' => array(
				'label' => esc_html__( 'month(s)', 'gravityformsredsys' ),
				'min'   => 1,
				'max'   => 24,
			),
			'year'  => array(
				'label' => esc_html__( 'year(s)', 'gravityformsredsys' ),
				'min'   => 1,
				'max'   => 4,
			),
		);

		return $billing_cycles;
	}

	public function field_map_title() {
		return esc_html__( 'Redsýs Field', 'gravityformsredsys' );
	}

	public function set_trial_onchange( $field ) {
		// return the javascript for the onchange event
		return "
		if(jQuery(this).prop('checked')){
			jQuery('#{$field['name']}_product').show('slow');
			jQuery('#gaddon-setting-row-trialPeriod').show('slow');
			if (jQuery('#{$field['name']}_product').val() == 'enter_amount'){
				jQuery('#{$field['name']}_amount').show('slow');
			}
			else{
				jQuery('#{$field['name']}_amount').hide();
			}
		}
		else {
			jQuery('#{$field['name']}_product').hide('slow');
			jQuery('#{$field['name']}_amount').hide();
			jQuery('#gaddon-setting-row-trialPeriod').hide('slow');
		}";
	}

	public function settings_notifications( $field, $echo = true ) {
		$checkboxes = array(
			'name'    => 'delay_notification',
			'type'    => 'checkboxes',
			'onclick' => 'ToggleNotifications();',
			'choices' => array(
				array(
					'label' => esc_html__(
						"Send notifications for the 'Form is submitted' event only when payment is received.",
						'gravityformsredsys'
					),
					'name'  => 'delayNotification',
				),
			),
		);

		$html = $this->settings_checkbox( $checkboxes, false );

		$html .= $this->settings_hidden(
			array(
				'name' => 'selectedNotifications',
				'id'   => 'selectedNotifications',
			),
			false
		);

		$form                      = $this->get_current_form();
		$has_delayed_notifications = $this->get_setting( 'delayNotification' );
		ob_start();
		?>
		<ul id="gf_redsys_notification_container"
			style="padding-left:20px; margin-top:10px; <?php echo $has_delayed_notifications ? '' : 'display:none;'; ?>">
			<?php
			if ( ! empty( $form ) && is_array( $form['notifications'] ) ) {
				$selected_notifications = $this->get_setting( 'selectedNotifications' );
				if ( ! is_array( $selected_notifications ) ) {
					$selected_notifications = array();
				}

				// $selected_notifications = empty($selected_notifications) ? array() : json_decode($selected_notifications);
				$notifications = GFCommon::get_notifications( 'form_submission', $form );

				foreach ( $notifications as $notification ) {
					?>
					<li class="gf_redsys_notification">
						<input type="checkbox" class="notification_checkbox" value="<?php echo $notification['id']; ?>                            							   onclick="SaveNotifications();" 
																							   <?php
																								checked(
																									true,
																									in_array( $notification['id'], $selected_notifications )
																								)
																								?>
							 />
						<label class="inline"
							   for="gf_redsys_selected_notifications"><?php echo $notification['name']; ?></label>
					</li>
					<?php
				}
			}
			?>
		</ul>
		<script type='text/javascript'>
			function SaveNotifications() {
				var notifications = [];
				jQuery('.notification_checkbox').each(function () {
					if (jQuery(this).is(':checked')) {
						notifications.push(jQuery(this).val());
					}
				});
				jQuery('#selectedNotifications').val(jQuery.toJSON(notifications));
			}

			function ToggleNotifications() {

				var container = jQuery('#gf_redsys_notification_container');
				var isChecked = jQuery('#delaynotification').is(':checked');

				if (isChecked) {
					container.slideDown();
					jQuery('.gf_redsys_notification input').prop('checked', true);
				}
				else {
					container.slideUp();
					jQuery('.gf_redsys_notification input').prop('checked', false);
				}

				SaveNotifications();
			}
		</script>
		<?php

		$html .= ob_get_clean();

		if ( $echo ) {
			echo $html;
		}

		return $html;
	}

	/**
	 * Returns the markup for the change post status checkbox item.
	 *
	 * @param array  $choice The choice properties.
	 * @param string $attributes The attributes for the input tag.
	 * @param string $value Currently selection (1 if field has been checked. 0 or null otherwise).
	 * @param string $tooltip The tooltip for this checkbox item.
	 *
	 * @return string
	 */
	public function checkbox_input_change_post_status( $choice, $attributes, $value, $tooltip ) {
		$markup = $this->checkbox_input( $choice, $attributes, $value, $tooltip );

		$dropdown_field = array(
			'name'     => 'update_post_action',
			'choices'  => array(
				array( 'label' => '' ),
				array(
					'label' => esc_html__( 'Mark Post as Draft', 'gravityformsredsys' ),
					'value' => 'draft',
				),
				array(
					'label' => esc_html__( 'Delete Post', 'gravityformsredsys' ),
					'value' => 'delete',
				),

			),
			'onChange' => "var checked = jQuery(this).val() ? 'checked' : false; jQuery('#change_post_status').attr('checked', checked);",
		);
		$markup .= '&nbsp;&nbsp;' . $this->settings_select( $dropdown_field, false );

		return $markup;
	}

	public function billing_info_fields() {

		$fields = array(
			array(
				'name'     => 'titular',
				'label'    => esc_html__( 'Titular', 'gravityformsredsys' ),
				'required' => false,
			),
			array(
				'name'     => 'productdescription',
				'label'    => esc_html__( 'Product description', 'gravityformsredsys' ),
				'required' => false,
			),
		);

		return $fields;
	}

	/**
	 * Prevent the GFPaymentAddOn version of the options field being added to the feed settings.
	 *
	 * @return bool
	 */
	public function option_choices() {
		return false;
	}

	public function save_feed_settings( $feed_id, $form_id, $settings ) {

		// --------------------------------------------------------
		// For backwards compatibility
		$feed = $this->get_feed( $feed_id );

		// Saving new fields into old field names to maintain backwards compatibility for delayed payments
		$settings['type'] = $settings['transactionType'];

		if ( isset( $settings['recurringAmount'] ) ) {
			$settings['recurring_amount_field'] = $settings['recurringAmount'];
		}

		$feed['meta'] = $settings;
		$feed         = apply_filters( 'gform_redsys_save_config', $feed );

		// call hook to validate custom settings/meta added using gform_redsys_action_fields or gform_redsys_add_option_group action hooks
		$is_validation_error = apply_filters( 'gform_redsys_config_validation', false, $feed );
		if ( $is_validation_error ) {
			// fail save
			return false;
		}

		$settings = $feed['meta'];

		// --------------------------------------------------------
		return parent::save_feed_settings( $feed_id, $form_id, $settings );
	}

	// ------ SENDING TO REDSYS -----------//
	public function redirect_url( $feed, $submission_data, $form, $entry ) {

		// Don't process redirect url if request is a Redsýs return
		if ( ! rgempty( 'gf_redsys_return', $_GET ) ) {
			return false;
		}

		// updating lead's payment_status to Processing
		GFAPI::update_entry_property( $entry['id'], 'payment_status', 'Processing' );

		// Save form fields in session
		$settings                     = $this->get_api_settings( $feed );
		$ds_merchant_consumerlanguage = $this->_getLanguange();
		$ds_merchant_order            = str_pad( $entry['id'], 8, '0', STR_PAD_LEFT ) . date( 'is' );
		// $ds_merchant_amount           = round(self::get_product_total($form, $entry) * 100);
		$ds_merchant_amount = round( rgar( $submission_data, 'payment_amount' ) * 100 );
		$currency_codes     = array(
			'EUR' => 978,
			'USD' => 840,
			'GBP' => 826,
		);

		$ds_merchant_currency        = $currency_codes[ rgar( $entry, 'currency' ) ];
		$ds_merchant_code            = rgar( $settings, 'gf_redsys_merchant' );
		$ds_merchant_terminal        = rgar( $settings, 'gf_redsys_terminal' );
		$schema                      = rgar( $settings, 'gf_redsys_force_http' ) ? 'http' : null;
		$ds_merchant_merchanturl     = add_query_arg( array( 'callback' => $this->_slug ), home_url( '/', $schema ) );
		$ds_merchant_urlok           = $this->return_url( $form['id'], $entry['id'] );
		$ds_merchant_urlko           = $feed['meta']['cancelUrl'];
		$ds_merchant_transactiontype = 0;
		$redsys                      = self::get_redsys();
		$redsys->setParameter( 'DS_MERCHANT_AMOUNT', $ds_merchant_amount );
		$redsys->setParameter( 'DS_MERCHANT_ORDER', $ds_merchant_order );
		$redsys->setParameter( 'DS_MERCHANT_MERCHANTCODE', $ds_merchant_code );
		$redsys->setParameter( 'DS_MERCHANT_CURRENCY', $ds_merchant_currency );
		$redsys->setParameter( 'DS_MERCHANT_TRANSACTIONTYPE', $ds_merchant_transactiontype );
		$redsys->setParameter( 'DS_MERCHANT_TERMINAL', $ds_merchant_terminal );
		$redsys->setParameter( 'Ds_Merchant_ConsumerLanguage', $ds_merchant_consumerlanguage );
		$redsys->setParameter(
			'Ds_Merchant_ProductDescription',
			rgar( $submission_data, 'productdescription' )
		);
		$redsys->setParameter(
			'Ds_Merchant_Titular',
			rgar( $submission_data, 'titular' )
		);
		$redsys->setParameter( 'Ds_Merchant_MerchantData', $entry['id'] . '|' . wp_hash( $entry['id'] ) . '|' . $form['id'] );
		$redsys->setParameter( 'Ds_Merchant_MerchantName', get_option( 'blogname' ) );
		$redsys->setParameter( 'Ds_Merchant_PayMethods', 'T' );
		$redsys->setParameter( 'Ds_Merchant_Module', 'ZhenIT Software' );

		$redsys->setParameter( 'DS_MERCHANT_MERCHANTURL', $ds_merchant_merchanturl );
		$redsys->setParameter( 'DS_MERCHANT_URLOK', $ds_merchant_urlok );
		$redsys->setParameter( 'DS_MERCHANT_URLKO', $ds_merchant_urlko );

		$redsys = apply_filters( 'gravityforms_redsys_set_parameters', $redsys, $feed, $submission_data, $form, $entry );

		// Datos de configuración
		$version = 'HMAC_SHA256_V1';

		// Clave del comercio que se extrae de la configuración del comercio
		// Se generan los parámetros de la petición
		$request      = '';
		$paramsBase64 = $redsys->createMerchantParameters();
		$signatureMac = $redsys->createMerchantSignature( rgar( $settings, 'gf_redsys_secret' ) );
				$url  = add_query_arg(
					array(
						'Ds_SignatureVersion'   => urlencode( $version ),
						'Ds_MerchantParameters' => urlencode( $paramsBase64 ),
						'Ds_Signature'          => urlencode( $signatureMac ),
						'callback'              => urlencode( $this->_slug ),
						'gf_redsys_mode'        => rgar( $settings, 'gf_redsys_mode' ),
						'redirect'              => '1',
					),
					home_url( '/' )
				);

		$this->log_debug( __METHOD__ . "(): Sending to Redsýs: {$url}" );

		return apply_filters( 'gform_redsys_form_url', $url, $form['id'], $entry['id'] );
	}

	public function renderForm() {
		if ( rgget( 'redirect' ) == '1' && ! is_null( rgget( 'Ds_MerchantParameters' ) ) ) {
			$html  = '<html><body><h2>' .
				__( 'Connecting with Redsys, please wait...', 'gfp-redsys' ) . '</h2>';
			$html .= '<form action="' . ( rgget( 'gf_redsys_mode' ) == 'production' ? self::$live_url : self::$test_url ) . '" method="post" id="redsys_payment_form" target="_top">';
			$html .= '<input type="hidden" name="Ds_SignatureVersion" value="' . rgget( 'Ds_SignatureVersion' ) . '" />';
			$html .= '<input type="hidden" name="Ds_MerchantParameters" value="' . rgget( 'Ds_MerchantParameters' ) . '" />';
			$html .= '<input type="hidden" name="Ds_Signature" value="' . rgget( 'Ds_Signature' ) . '" />';
			$html .= '<input type="submit" class="button-alt" id="submit_servired_payment_form" value="' .
				__( 'Click if it takes too long', 'gfp-redsys' ) . '" />';
			$html .= '<button class="button cancel" onclick="history.go(-1)">' .
				__( 'Cancel', 'gfp-redsys' ) . '</button>';
			$html .= '</form>';
			$html .= '<script>document.getElementById("redsys_payment_form").submit();</script>';
			$html .= '</body></html>';
			die( $html );
		}
	}

	public function get_subscription_query_string( $feed, $submission_data, $entry_id ) {

		if ( empty( $submission_data ) ) {
			return false;
		}

		$query_string         = '';
		$payment_amount       = rgar( $submission_data, 'payment_amount' );
		$setup_fee            = rgar( $submission_data, 'setup_fee' );
		$trial_enabled        = rgar( $feed['meta'], 'trial_enabled' );
		$line_items           = rgar( $submission_data, 'line_items' );
		$discounts            = rgar( $submission_data, 'discounts' );
		$recurring_field      = rgar( $submission_data, 'payment_amount' ); // will be field id or the text 'form_total'
		$product_index        = 1;
		$shipping             = '';
		$discount_amt         = 0;
		$cmd                  = '_xclick-subscriptions';
		$extra_qs             = '';
		$name_without_options = '';
		$item_name            = '';

		// work on products
		if ( is_array( $line_items ) ) {
			foreach ( $line_items as $item ) {
				$product_id     = $item['id'];
				$product_name   = $item['name'];
				$quantity       = $item['quantity'];
				$quantity_label = $quantity > 1 ? $quantity . ' ' : '';

				$unit_price  = $item['unit_price'];
				$options     = rgar( $item, 'options' );
				$product_id  = $item['id'];
				$is_shipping = rgar( $item, 'is_shipping' );

				$product_options = '';
				if ( ! $is_shipping ) {
					// add options
					if ( ! empty( $options ) && is_array( $options ) ) {
						$product_options = ' (';
						foreach ( $options as $option ) {
							$product_options .= $option['option_name'] . ', ';
						}
						$product_options = substr( $product_options, 0, strlen( $product_options ) - 2 ) . ')';
					}

					$item_name            .= $quantity_label . $product_name . $product_options . ', ';
					$name_without_options .= $product_name . ', ';
				}
			}

			// look for discounts to pass in the item_name
			if ( is_array( $discounts ) ) {
				foreach ( $discounts as $discount ) {
					$product_name          = $discount['name'];
					$quantity              = $discount['quantity'];
					$quantity_label        = $quantity > 1 ? $quantity . ' ' : '';
					$item_name            .= $quantity_label . $product_name . ' (), ';
					$name_without_options .= $product_name . ', ';
				}
			}

			if ( ! empty( $item_name ) ) {
				$item_name = substr( $item_name, 0, strlen( $item_name ) - 2 );
			}

			// if name is larger than max, remove options from it.
			if ( strlen( $item_name ) > 127 ) {
				$item_name = substr( $name_without_options, 0, strlen( $name_without_options ) - 2 );

				// truncating name to maximum allowed size
				if ( strlen( $item_name ) > 127 ) {
					$item_name = substr( $item_name, 0, 124 ) . '...';
				}
			}
			$item_name = urlencode( $item_name );

		}

		$trial = '';
		// see if a trial exists
		if ( $trial_enabled ) {
			$trial_amount        = rgar( $submission_data, 'trial' ) ? rgar( $submission_data, 'trial' ) : 0;
			$trial_period_number = rgar( $feed['meta'], 'trialPeriod_length' );
			$trial_period_type   = $this->convert_interval( rgar( $feed['meta'], 'trialPeriod_unit' ), 'char' );
			$trial               = "&a1={$trial_amount}&p1={$trial_period_number}&t1={$trial_period_type}";
		}

		// check for recurring times
		$recurring_times = rgar( $feed['meta'], 'recurringTimes' ) ? '&srt=' . rgar( $feed['meta'], 'recurringTimes' ) : '';
		$recurring_retry = rgar( $feed['meta'], 'recurringRetry' ) ? '1' : '0';

		$billing_cycle_number = rgar( $feed['meta'], 'billingCycle_length' );
		$billing_cycle_type   = $this->convert_interval( rgar( $feed['meta'], 'billingCycle_unit' ), 'char' );

		$query_string = "&cmd={$cmd}&item_name={$item_name}{$trial}&a3={$payment_amount}&p3={$billing_cycle_number}&t3={$billing_cycle_type}&src=1&sra={$recurring_retry}{$recurring_times}";

		// save payment amount to lead meta
		gform_update_meta( $entry_id, 'payment_amount', $payment_amount );

		return $payment_amount > 0 ? $query_string : false;

	}

	public function return_url( $form_id, $lead_id ) {
		$pageURL = GFCommon::is_ssl() ? 'https://' : 'http://';

		$server_port = apply_filters( 'gform_redsys_return_url_port', $_SERVER['SERVER_PORT'] );

		if ( $server_port != '80' ) {
			$pageURL .= $_SERVER['SERVER_NAME'] . ':' . $server_port . $_SERVER['REQUEST_URI'];
		} else {
			$pageURL .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		}

		$ids_query  = "ids={$form_id}|{$lead_id}";
		$ids_query .= '&hash=' . wp_hash( $ids_query );

		$url = add_query_arg( 'gf_redsys_return', base64_encode( $ids_query ), $pageURL );

		$query = 'gf_redsys_return=' . base64_encode( $ids_query );

		/**
		 * Filters Redsýs's return URL, which is the URL that users will be sent to after completing the payment on Redsýs's site.
		 * Useful when URL isn't created correctly (could happen on some server configurations using PROXY servers).
		 *
		 * @since 2.4.5
		 *
		 * @param string $url The URL to be filtered.
		 * @param int $form_id The ID of the form being submitted.
		 * @param int $entry_id The ID of the entry that was just created.
		 * @param string $query The query string portion of the URL.
		 */
		return apply_filters( 'gform_redsys_return_url', $url, $form_id, $lead_id, $query );

	}

	public static function maybe_thankyou_page() {
		$instance = self::get_instance();

		if ( ! $instance->is_gravityforms_supported() ) {
			return;
		}

		if ( $str = rgget( 'gf_redsys_return' ) ) {
			$str = base64_decode( $str );

			parse_str( $str, $query );
			if ( wp_hash( 'ids=' . $query['ids'] ) == $query['hash'] ) {
				list($form_id, $lead_id) = explode( '|', $query['ids'] );

				$form = GFAPI::get_form( $form_id );
				$lead = GFAPI::get_entry( $lead_id );

				if ( ! class_exists( 'GFFormDisplay' ) ) {
					require_once GFCommon::get_base_path() . '/form_display.php';
				}

				$confirmation = GFFormDisplay::handle_confirmation( $form, $lead, false );

				if ( is_array( $confirmation ) && isset( $confirmation['redirect'] ) ) {
					header( "Location: {$confirmation['redirect']}" );
					exit;
				}

				GFFormDisplay::$submission[ $form_id ] = array(
					'is_confirmation'      => true,
					'confirmation_message' => $confirmation,
					'form'                 => $form,
					'lead'                 => $lead,
				);
			}
		}
	}

	public function delay_post( $is_disabled, $form, $entry ) {

		$feed            = $this->get_payment_feed( $entry );
		$submission_data = $this->get_submission_data( $feed, $form, $entry );

		if ( ! $feed || empty( $submission_data['payment_amount'] ) ) {
			return $is_disabled;
		}

		return ! rgempty( 'delayPost', $feed['meta'] );
	}

	public function delay_notification( $is_disabled, $notification, $form, $entry ) {
		if ( rgar( $notification, 'event' ) != 'form_submission' ) {
			return $is_disabled;
		}

		$feed            = $this->get_payment_feed( $entry );
		$submission_data = $this->get_submission_data( $feed, $form, $entry );

		if ( ! $feed || empty( $submission_data['payment_amount'] ) ) {
			return $is_disabled;
		}

		$selected_notifications = is_array( rgar( $feed['meta'], 'selectedNotifications' ) ) ? rgar(
			$feed['meta'],
			'selectedNotifications'
		) : array();
		return isset( $feed['meta']['delayNotification'] ) && in_array(
			$notification['id'],
			$selected_notifications
		) ? true : $is_disabled;
	}

	/**
	 * Process IPN Callback
	 *
	 * @return boolean|array
	 */
	public function callback() {
		$this->renderForm();
		if ( ! $this->is_gravityforms_supported() ) {
			return false;
		}

		$this->log_debug( __METHOD__ . '(): IPN request received. Starting to process ' );

		// Valid IPN requests must have a custom field
		$params = rgpost( 'Ds_MerchantParameters' );
		if ( empty( $params ) ) {
			$this->log_error( __METHOD__ . '(): IPN request does not have a Ds_MerchantParameters field, so it was not created by Gravity Forms. Aborting.' );

			return false;
		}

		// Verify request.
		$redsys = self::get_redsys();

		// Se decodifican los datos enviados y se carga el array de datos.
		$decoded = $redsys->decodeMerchantParameters( $params );
		$redsys->stringToArray( $decoded );

		// Extraer datos de la notificación.
		$ds_amount          = $redsys->getParameter( 'Ds_Amount' );
		$ds_order           = $redsys->getParameter( 'Ds_Order' );
		$ds_merchantcode    = $redsys->getParameter( 'Ds_MerchantCode' );
		$ds_currency        = $redsys->getParameter( 'Ds_Currency' );
		$ds_response        = substr( $redsys->getParameter( 'Ds_Response' ), 0, 4 );
		$txn_id             = $redsys->getParameter( 'Ds_AuthorisationCode' );
		$ds_merchantdata    = $redsys->getParameter( 'Ds_MerchantData' );
		$ds_transactiontype = $_REQUEST['Ds_TransactionType'];
		// Getting entry related to this IPN.
		$entry    = $this->get_entry( urldecode( $ds_merchantdata ) );
		$settings = $this->get_api_settings( $this->current_feed );
		// Clave.
		$kc = rgar( $settings, 'gf_redsys_secret' );

		// Se calcula la firma.
		$firma_local = $redsys->createMerchantSignatureNotif( $kc, $_REQUEST['Ds_MerchantParameters'] );

		$valid_signature = $firma_local === $_POST['Ds_Signature']
			&& RedsysHelper::checkRespuesta( $ds_response )
			&& RedsysHelper::checkMoneda( $ds_currency )
			&& RedsysHelper::checkFuc( $ds_merchantcode )
			&& RedsysHelper::checkPedidoNum( $ds_order )
			&& RedsysHelper::checkImporte( $ds_amount );

		$status = 'denied';

		if ( ! apply_filters( 'gravityforms_redsys_validate_signature', $valid_signature, $redsys, $this ) ) {
			$this->log_error( __METHOD__ . '(): IPN verification failed with an error. Aborting with a 500 error so that IPN is resent.' );

			return new WP_Error(
				'IPNVerificationError',
				'There was an error when verifying the IPN message with Redsýs',
				array( 'status_header' => 500 )
			);
		}
		$this->log_debug( __METHOD__ . '(): IPN message successfully verified by Redsýs' );

		// Ignore orphan IPN messages (ones without an entry).
		if ( ! $entry ) {
			$this->log_error( __METHOD__ . '(): Entry could not be found. Aborting.' );

			return false;
		}
		$this->log_debug( __METHOD__ . '(): Entry has been found.' );

		if ( $entry['status'] == 'spam' ) {
			$this->log_error( __METHOD__ . '(): Entry is marked as spam. Aborting.' );

			return false;
		}

		// ------ Getting feed related to this IPN.
		$feed = $this->get_payment_feed( $entry );

		// Ignore IPN messages from forms that are no longer configured with the Redsýs add-on.
		if ( ! $feed || ! rgar( $feed, 'is_active' ) ) {
			$this->log_error( __METHOD__ . "(): Form no longer is configured with Redsýs Addon. Form ID: {$entry['form_id']}. Aborting." );

			return false;
		}
		$this->log_debug( __METHOD__ . "(): Form {$entry['form_id']} is properly configured." );

		// ----- Making sure this IPN can be processed.
		if ( ! $this->can_process_ipn( $feed, $entry ) ) {
			$this->log_debug( __METHOD__ . '(): IPN cannot be processed.' );

			return false;
		}

		// ----- Processing IPN.
		$this->log_debug( __METHOD__ . '(): Processing IPN...' );
		$action = apply_filters(
			'gravityforms_redsys_filter_action_callback',
			$this->process_ipn(
				$feed,
				$entry,
				$ds_response,
				$ds_transactiontype == 0 ? 'payment' : 'unkwonw', // @todo: in case authorizations or refunds are added
				$txn_id,
				$ds_order,
				$redsys->getParameter( 'Ds_Merchant_Identifier' ),
				$ds_amount / 100,
				rgpost( 'pending_reason' ),
				rgpost( 'reason_code' ),
				rgpost( 'mc_amount3' )
			),
			$redsys
		);
		$this->log_debug( __METHOD__ . '(): IPN processing complete.' );

		if ( rgempty( 'entry_id', $action ) ) {
			return false;
		}
		return $action;
	}


	public function get_payment_feed( $entry, $form = false ) {

		$feed = parent::get_payment_feed( $entry, $form );

		if ( empty( $feed ) && ! empty( $entry['id'] ) ) {
			// looking for feed created by legacy versions.
			$feed = $this->get_redsys_feed_by_entry( $entry['id'] );
		}

		$feed = apply_filters(
			'gform_redsys_get_payment_feed',
			$feed,
			$entry,
			$form ? $form : GFAPI::get_form( $entry['form_id'] )
		);

		return $feed;
	}

	/**
	 * Undocumented function
	 *
	 * @param int $entry_id
	 * @return boolean|array
	 */
	private function get_redsys_feed_by_entry( $entry_id ) {

		$feed_id = gform_get_meta( $entry_id, 'redsys_feed_id' );
		$feed    = $this->get_feed( $feed_id );
		$ret     = ! empty( $feed ) ? $feed : false;
		return $ret;
	}

	public function post_callback( $callback_action, $callback_result ) {
		if ( is_wp_error( $callback_action ) || ! $callback_action ) {
			return false;
		}

		// run the necessary hooks
		$entry           = GFAPI::get_entry( $callback_action['entry_id'] );
		$feed            = $this->get_payment_feed( $entry );
		$transaction_id  = rgar( $callback_action, 'transaction_id' );
		$amount          = rgar( $callback_action, 'amount' );
		$subscription_id = rgar( $callback_action, 'subscription_id' );
		$pending_reason  = rgpost( 'pending_reason' );
		$reason          = rgpost( 'reason_code' );
		$status          = rgpost( 'payment_status' );
		$txn_type        = rgpost( 'txn_type' );
		$parent_txn_id   = rgpost( 'parent_txn_id' );

		// run gform_redsys_fulfillment only in certain conditions
		if ( rgar( $callback_action, 'ready_to_fulfill' ) && ! rgar( $callback_action, 'abort_callback' ) ) {
			$this->fulfill_order( $entry, $transaction_id, $amount, $feed );
		} else {
			if ( rgar( $callback_action, 'abort_callback' ) ) {
				$this->log_debug( __METHOD__ . '(): Callback processing was aborted. Not fulfilling entry.' );
			} else {
				$this->log_debug( __METHOD__ . '(): Entry is already fulfilled or not ready to be fulfilled, not running gform_redsys_fulfillment hook.' );
			}
		}

		do_action(
			'gform_post_payment_status',
			$feed,
			$entry,
			$status,
			$transaction_id,
			$subscription_id,
			$amount,
			$pending_reason,
			$reason
		);
		if ( has_filter( 'gform_post_payment_status' ) ) {
			$this->log_debug( __METHOD__ . '(): Executing functions hooked to gform_post_payment_status.' );
		}

		do_action(
			'gform_redsys_ipn_' . $txn_type,
			$entry,
			$feed,
			$status,
			$txn_type,
			$transaction_id,
			$parent_txn_id,
			$subscription_id,
			$amount,
			$pending_reason,
			$reason
		);
		if ( has_filter( 'gform_redsys_ipn_' . $txn_type ) ) {
			$this->log_debug( __METHOD__ . "(): Executing functions hooked to gform_redsys_ipn_{$txn_type}." );
		}

		do_action( 'gform_redsys_post_ipn', $_POST, $entry, $feed, false );
		if ( has_filter( 'gform_redsys_post_ipn' ) ) {
			$this->log_debug( __METHOD__ . '(): Executing functions hooked to gform_redsys_post_ipn.' );
		}
	}

	private function process_ipn(
		$config,
		$entry,
		$status,
		$transaction_type,
		$transaction_id,
		$parent_transaction_id,
		$subscription_id,
		$amount,
		$pending_reason,
		$reason,
		$recurring_amount
	) {
		$this->log_debug( __METHOD__ . "(): Payment status: {$status} - Transaction Type: {$transaction_type} - Transaction ID: {$transaction_id} - Parent Transaction: {$parent_transaction_id} - Subscriber ID: {$subscription_id} - Amount: {$amount} - Pending reason: {$pending_reason} - Reason: {$reason}" );

		$action = array();
		switch ( strtolower( $transaction_type ) ) {
			default:// case 0
				// handles products and donation
				if ( preg_match( '/\d*/', $status ) && (int) $status < 100 && (int) $status >= 0 ) {
					// creates transaction
					$action['id']               = $transaction_id . '_' . $status;
					$action['type']             = 'complete_payment';
					$action['transaction_id']   = $parent_transaction_id;
					$action['transaction_type'] = $transaction_type;
					$action['subscription_id']  = $subscription_id;
					$action['amount']           = $amount;
					$action['entry_id']         = $entry['id'];
					$action['payment_date']     = gmdate( 'y-m-d H:i:s' );
					$action['payment_method']   = $this->_slug;
					$action['ready_to_fulfill'] = ! $entry['is_fulfilled'] ? true : false;

					if ( ! $this->is_valid_initial_payment_amount( $entry['id'], $amount ) ) {
						// create note and transaction
						$this->log_debug( __METHOD__ . '(): Payment amount does not match product price. Entry will not be marked as Approved.' );
						GFPaymentAddOn::add_note(
							$entry['id'],
							sprintf(
								__(
									'Payment amount (%1$s) does not match product price. Entry will not be marked as Approved. Transaction ID: %2$s',
									'gravityformsredsys'
								),
								GFCommon::to_money( $amount, $entry['currency'] ),
								$transaction_id
							)
						);
						GFPaymentAddOn::insert_transaction( $entry['id'], 'payment', $parent_transaction_id, $amount );
						$action['abort_callback'] = true;
					}

					return $action;
				} else {
					$action['id']             = $transaction_id . '_' . $status;
					$action['type']           = 'fail_payment';
					$action['transaction_id'] = $transaction_id;
					$action['entry_id']       = $entry['id'];
					$action['amount']         = $amount;

					return $action;
				}

				break;
		}
	}

	public function get_entry( $custom_field ) {

		// Getting entry associated with this IPN message (entry id is sent in the 'custom' field)
		list($entry_id, $hash, $form_id) = explode( '|', $custom_field );
		$hash_matches                    = wp_hash( $entry_id ) == $hash;

		// allow the user to do some other kind of validation of the hash
		$hash_matches = apply_filters( 'gform_redsys_hash_matches', $hash_matches, $entry_id, $hash, $custom_field );

		// Validates that Entry Id wasn't tampered with
		if ( ! $hash_matches ) {
			$this->log_error( __METHOD__ . "(): Entry ID verification failed. Hash does not match. Custom field: {$custom_field}. Aborting." );

			return false;
		}

		$this->log_debug( __METHOD__ . "(): IPN message has a valid custom field: {$custom_field}" );

		$entry = GFAPI::get_entry( $entry_id );

		if ( is_wp_error( $entry ) ) {
			$this->log_error( __METHOD__ . '(): ' . $entry->get_error_message() );

			return false;
		}
		$form               = GFAPI::get_form( $form_id );
		$this->current_feed = $this->get_single_submission_feed( $entry, $form );
		return $entry;
	}

	public function can_process_ipn( $feed, $entry ) {

		$this->log_debug( __METHOD__ . '(): Checking that IPN can be processed.' );

		// Pre IPN processing filter. Allows users to cancel IPN processing
		$cancel = apply_filters( 'gform_redsys_pre_ipn', false, $_POST, $entry, $feed );

		if ( $cancel ) {
			$this->log_debug( __METHOD__ . '(): IPN processing cancelled by the gform_redsys_pre_ipn filter. Aborting.' );
			do_action( 'gform_redsys_post_ipn', $_POST, $entry, $feed, true );
			if ( has_filter( 'gform_redsys_post_ipn' ) ) {
				$this->log_debug( __METHOD__ . '(): Executing functions hooked to gform_redsys_post_ipn.' );
			}

			return false;
		}

		return true;
	}

	/**
	 * Handle cancelling the subscription from the entry detail page.
	 *
	 * @since Unknown
	 * @since 2.8     Updated to use the subscription object instead of the customer object. Added $feed param when including Stripe API.
	 *
	 * @param array $entry The entry object currently being processed.
	 * @param array $feed  The feed object currently being processed.
	 *
	 * @return bool True if successful. False if failed.
	 */
	public function cancel( $entry, $feed ) {
		$ret = apply_filters( 'gravityforms_redsys_cancel_subscription', false, $entry, $feed );
		return $ret;
	}

	public function get_entry_meta( $entry_meta, $form_id ) {
		return apply_filters( 'gravityforms_redsys_get_entry_meta', $entry_meta, $form_id );

	}

	public function modify_post( $post_id, $action ) {

		$result = false;

		if ( ! $post_id ) {
			return $result;
		}

		switch ( $action ) {
			case 'draft':
				$post              = get_post( $post_id );
				$post->post_status = 'draft';
				$result            = wp_update_post( $post );
				$this->log_debug( __METHOD__ . "(): Set post (#{$post_id}) status to \"draft\"." );
				break;
			case 'delete':
				$result = wp_delete_post( $post_id );
				$this->log_debug( __METHOD__ . "(): Deleted post (#{$post_id})." );
				break;
		}

		return $result;
	}

	// ------- ADMIN FUNCTIONS/HOOKS -----------//
	public function init_admin() {

		parent::init_admin();

		// add actions to allow the payment status to be modified
		add_action( 'gform_payment_status', array( $this, 'admin_edit_payment_status' ), 3, 3 );
		add_action( 'gform_payment_date', array( $this, 'admin_edit_payment_date' ), 3, 3 );
		add_action( 'gform_payment_transaction_id', array( $this, 'admin_edit_payment_transaction_id' ), 3, 3 );
		add_action( 'gform_payment_amount', array( $this, 'admin_edit_payment_amount' ), 3, 3 );
		add_action( 'gform_after_update_entry', array( $this, 'admin_update_payment' ), 4, 2 );
	}

	/**
	 * Add supported notification events.
	 *
	 * @param array $form The form currently being processed.
	 *
	 * @return array
	 */
	public function supported_notification_events( $form ) {
		if ( ! $this->has_feed( $form['id'] ) ) {
			return false;
		}

		return apply_filters(
			'gravityforms_redsys_supported_notification_events',
			array(
				'complete_payment' => esc_html__( 'Payment Completed', 'gravityformsredsys' ),
			),
			$form
		);
	}

	public function admin_edit_payment_status( $payment_status, $form, $entry ) {
		if ( $this->payment_details_editing_disabled( $entry ) ) {
			return $payment_status;
		}

		// create drop down for payment status
		$payment_string  = gform_tooltip( 'redsys_edit_payment_status', '', true );
		$payment_string .= '<select id="payment_status" name="payment_status">';
		$payment_string .= '<option value="' . $payment_status . '" selected>' . $payment_status . '</option>';
		$payment_string .= '<option value="Paid">Paid</option>';
		$payment_string .= '</select>';

		return $payment_string;
	}

	public function admin_edit_payment_date( $payment_date, $form, $entry ) {
		if ( $this->payment_details_editing_disabled( $entry ) ) {
			return $payment_date;
		}

		$payment_date = $entry['payment_date'];
		if ( empty( $payment_date ) ) {
			$payment_date = gmdate( 'y-m-d H:i:s' );
		}

		$input = '<input type="text" id="payment_date" name="payment_date" value="' . $payment_date . '">';

		return $input;
	}

	public function admin_edit_payment_transaction_id( $transaction_id, $form, $entry ) {
		if ( $this->payment_details_editing_disabled( $entry ) ) {
			return $transaction_id;
		}

		$input = '<input type="text" id="redsys_transaction_id" name="redsys_transaction_id" value="' . $transaction_id . '">';

		return $input;
	}

	public function admin_edit_payment_amount( $payment_amount, $form, $entry ) {
		if ( $this->payment_details_editing_disabled( $entry ) ) {
			return $payment_amount;
		}

		if ( empty( $payment_amount ) ) {
			$payment_amount = GFCommon::get_order_total( $form, $entry );
		}

		$input = '<input type="text" id="payment_amount" name="payment_amount" class="gform_currency" value="' . $payment_amount . '">';

		return $input;
	}

	public function admin_update_payment( $form, $entry_id ) {
		check_admin_referer( 'gforms_save_entry', 'gforms_save_entry' );

		// update payment information in admin, need to use this function so the lead data is updated before displayed in the sidebar info section
		$entry = GFFormsModel::get_lead( $entry_id );

		if ( $this->payment_details_editing_disabled( $entry, 'update' ) ) {
			return;
		}

		// get payment fields to update
		$payment_status = rgpost( 'payment_status' );
		// when updating, payment status may not be editable, if no value in post, set to lead payment status
		if ( empty( $payment_status ) ) {
			$payment_status = $entry['payment_status'];
		}

		$payment_amount      = GFCommon::to_number( rgpost( 'payment_amount' ) );
		$payment_transaction = rgpost( 'redsys_transaction_id' );
		$payment_date        = rgpost( 'payment_date' );

		$status_unchanged = $entry['payment_status'] == $payment_status;
		$amount_unchanged = $entry['payment_amount'] == $payment_amount;
		$id_unchanged     = $entry['transaction_id'] == $payment_transaction;
		$date_unchanged   = $entry['payment_date'] == $payment_date;

		if ( $status_unchanged && $amount_unchanged && $id_unchanged && $date_unchanged ) {
			return;
		}

		if ( empty( $payment_date ) ) {
			$payment_date = gmdate( 'y-m-d H:i:s' );
		} else {
			// format date entered by user
			$payment_date = date( 'Y-m-d H:i:s', strtotime( $payment_date ) );
		}

		global $current_user;
		$user_id   = 0;
		$user_name = 'System';
		if ( $current_user && $user_data = get_userdata( $current_user->ID ) ) {
			$user_id   = $current_user->ID;
			$user_name = $user_data->display_name;
		}

		$entry['payment_status'] = $payment_status;
		$entry['payment_amount'] = $payment_amount;
		$entry['payment_date']   = $payment_date;
		$entry['transaction_id'] = $payment_transaction;

		// if payment status does not equal approved/paid or the lead has already been fulfilled, do not continue with fulfillment
		if ( ( $payment_status == 'Approved' || $payment_status == 'Paid' ) && ! $entry['is_fulfilled'] ) {
			$action['id']             = $payment_transaction;
			$action['type']           = 'complete_payment';
			$action['transaction_id'] = $payment_transaction;
			$action['amount']         = $payment_amount;
			$action['entry_id']       = $entry['id'];

			$this->complete_payment( $entry, $action );
			$this->fulfill_order( $entry, $payment_transaction, $payment_amount );
		}
		// update lead, add a note
		GFAPI::update_entry( $entry );
		GFFormsModel::add_note(
			$entry['id'],
			$user_id,
			$user_name,
			sprintf(
				esc_html__(
					'Payment information was manually updated. Status: %1$s. Amount: %2$s. Transaction ID: %3$s. Date: %4$s',
					'gravityformsredsys'
				),
				$entry['payment_status'],
				GFCommon::to_money( $entry['payment_amount'], $entry['currency'] ),
				$payment_transaction,
				$entry['payment_date']
			)
		);
	}

	public function fulfill_order( &$entry, $transaction_id, $amount, $feed = null ) {

		if ( ! $feed ) {
			$feed = $this->get_payment_feed( $entry );
		}

		$form = GFFormsModel::get_form_meta( $entry['form_id'] );
		if ( rgars( $feed, 'meta/delayPost' ) ) {
			$this->log_debug( __METHOD__ . '(): Creating post.' );
			$entry['post_id'] = GFFormsModel::create_post( $form, $entry );
			$this->log_debug( __METHOD__ . '(): Post created.' );
		}

		if ( rgars( $feed, 'meta/delayNotification' ) ) {
			// sending delayed notifications
			$notifications = $this->get_notifications_to_send( $form, $feed );
			GFCommon::send_notifications( $notifications, $form, $entry, true, 'form_submission' );
		}

		do_action( 'gform_redsys_fulfillment', $entry, $feed, $transaction_id, $amount );
		if ( has_filter( 'gform_redsys_fulfillment' ) ) {
			$this->log_debug( __METHOD__ . '(): Executing functions hooked to gform_redsys_fulfillment.' );
		}

	}

	/**
	 * Retrieve the IDs of the notifications to be sent.
	 *
	 * @param array $form The form which created the entry being processed.
	 * @param array $feed The feed which processed the entry.
	 *
	 * @return array
	 */
	public function get_notifications_to_send( $form, $feed ) {
		$notifications_to_send  = array();
		$selected_notifications = rgars( $feed, 'meta/selectedNotifications' );

		if ( is_array( $selected_notifications ) ) {
			// Make sure that the notifications being sent belong to the form submission event, just in case the notification event was changed after the feed was configured.
			foreach ( $form['notifications'] as $notification ) {
				if ( rgar( $notification, 'event' ) != 'form_submission' || ! in_array(
					$notification['id'],
					$selected_notifications
				)
				) {
					continue;
				}

				$notifications_to_send[] = $notification['id'];
			}
		}

		return $notifications_to_send;
	}

	private function is_valid_initial_payment_amount( $entry_id, $amount_paid ) {

		// get amount initially sent to redsys
		$amount_sent = gform_get_meta( $entry_id, 'payment_amount' );
		if ( empty( $amount_sent ) ) {
			return true;
		}

		$epsilon    = 0.00001;
		$is_equal   = abs( floatval( $amount_paid ) - floatval( $amount_sent ) ) < $epsilon;
		$is_greater = floatval( $amount_paid ) > floatval( $amount_sent );

		// initial payment is valid if it is equal to or greater than product/subscription amount
		if ( $is_equal || $is_greater ) {
			return true;
		}

		return false;

	}

	public function redsys_fulfillment( $entry, $redsys_config, $transaction_id, $amount ) {
		// no need to do anything for redsys when it runs this function, ignore
		return false;
	}

	/**
	 * Editing of the payment details should only be possible if the entry was processed by Redsýs, if the payment status is Pending or Processing, and the transaction was not a subscription.
	 *
	 * @param array  $entry The current entry
	 * @param string $action The entry detail page action, edit or update.
	 *
	 * @return bool
	 */
	public function payment_details_editing_disabled( $entry, $action = 'edit' ) {
		if ( ! $this->is_payment_gateway( $entry['id'] ) ) {
			// Entry was not processed by this add-on, don't allow editing.
			return true;
		}

		$payment_status = rgar( $entry, 'payment_status' );
		if ( $payment_status == 'Approved' || $payment_status == 'Paid' || rgar( $entry, 'transaction_type' ) == 2 ) {
			// Editing not allowed for this entries transaction type or payment status.
			return true;
		}

		if ( $action == 'edit' && rgpost( 'screen_mode' ) == 'edit' ) {
			// Editing is allowed for this entry.
			return false;
		}

		if ( $action == 'update' && rgpost( 'screen_mode' ) == 'view' && rgpost( 'action' ) == 'update' ) {
			// Updating the payment details for this entry is allowed.
			return false;
		}

		// In all other cases editing is not allowed.
		return true;
	}

	// # SUBMISSION ----------------------------------------------------------------------------------------------------
	// # HELPERS -------------------------------------------------------------------------------------------------------

	/**
	 * Retrieve the settings to use when making the request to Redsys API.
	 *
	 * @param bool|array $feed False or the feed currently being processed.
	 *
	 * @return array
	 */
	public function get_api_settings( $feed = false ) {
		if ( ! $feed ) {
			$feed = $this->current_feed;
		}

		if ( $feed && rgars( $feed, 'meta/apiSettingsEnabled' ) ) {
			$meta     = $feed['meta'];
			$settings = array(
				'gf_redsys_mode'     => rgar( $meta, 'overrideMode' ),
				'gf_redsys_merchant' => rgar( $meta, 'overrideMerchant' ),
				'gf_redsys_terminal' => rgar( $meta, 'overrideTerminal' ),
				'gf_redsys_secret'   => rgar( $meta, 'overrideSecret' ),
			);
		} else {
			$settings = $this->get_plugin_settings();
		}

		return $settings;
	}


	/**
	 * Validate the API credentials.
	 *
	 * @return bool
	 */
	public function is_valid_api_credentials() {
		// get api credentials
		$settings = $this->get_plugin_settings();
		global $valid_username;
		$valid_username = $this->is_valid_credentials( $settings );

		return $valid_username;
	}

	/**
	 * Validate the credentials.
	 *
	 * @param array $settings The plugin settings.
	 *
	 * @return bool
	 */
	public function is_valid_credentials( $settings ) {
		/*
				$client = $this->get_client($settings['username'], $settings['password']);
				$client->test();

				return $client->succeeded();*/
		// @todo
		return true;

	}

	/**
	 * Helper to check if the vendor, partner and password settings are valid.
	 *
	 * @return bool
	 */
	public function check_valid_api_credential_setting() {
		global $valid_username;

		return $valid_username;
	}


	/**
	 * Prepare the transaction arguments.
	 *
	 * @param array $feed The feed object currently being processed.
	 * @param array $submission_data The customer and transaction data.
	 * @param array $form The form object currently being processed.
	 * @param array $entry The entry object currently being processed.
	 *
	 * @return array
	 */
	public function prepare_credit_card_transaction( $feed, $submission_data, $form, $entry ) {

		$feed_name = rgar( $feed['meta'], 'feedName' );
		$this->log_debug( __METHOD__ . "(): Preparing transaction arguments based on feed #{$feed['id']} - {$feed_name}." );

		$args             = array();
		$args['serial']   = str_pad( rand(), 12, '0', STR_PAD_LEFT ) . '-' . time();
		$args['consumer'] = array(
			'name'         => $submission_data['card_name'],
			'card_number'  => $submission_data['card_number'],
			'expire_month' => str_pad( $submission_data['card_expiration_date'][0], 2, '0', STR_PAD_LEFT ),
			'expire_year'  => substr( $submission_data['card_expiration_date'][1], -2 ),
			'cvv'          => $submission_data['card_security_code'],

		);

		$args['transactions'] = array();
		foreach ( $submission_data['line_items'] as $line_item ) {
			$args['transactions'][] = array(
				'amount'  => array(
					'total'    => round( $line_item['unit_price'] * $line_item['quantity'] * 100 ),
					'currency' => GFCommon::get_currency(),
				),
				'concept' => array(
					'description' => ( isset( $line_item['name'] ) && trim( $line_item['name'] ) != '' ) ? $line_item['name'] : 'Producto',
				),
			);
		}
		$args['concept'] = array(
			'description' => $form['description'],
			'meta'        => $form['title'],
		);

		return $args;
	}

	/**
	 * Prepare the appropriate error message for the transaction result.
	 *
	 * @param array $response The response from the Payflow API.
	 *
	 * @return string
	 */
	public function get_error_message( $response ) {
		$code = $response['RESULT'];

		switch ( $code ) {
			case '50':
				$message = esc_html__(
					'This credit card has been declined by your bank. Please use another form of payment.',
					'gravityformsredsys'
				);
				break;

			case '24':
				$message = esc_html__( 'The credit card has expired.', 'gravityformsredsys' );
				break;

			case '1021':
				$message = esc_html__( 'The merchant does not accept this type of credit card.', 'gravityformsredsys' );
				break;

			case '12':
			case '23':
				$message = esc_html__(
					'There was an error processing your credit card. Please verify the information and try again.',
					'gravityformsredsys'
				);
				break;

			default:
				$message = esc_html__(
					'There was an error processing your request. Your credit card was not charged. Please try again.',
					'gravityformsredsys'
				);
		}

		$message = '<!-- Error: ' . $code . ' -->' . $message;

		return $message;
	}

	/**
	 * Convert feed into config for hooks backwards compatibility.
	 *
	 * @param array $feed The current feed object.
	 * @param array $submission_data The customer and transaction data.
	 *
	 * @return array
	 */
	private function get_config( $feed, $submission_data ) {

		$config = array();

		$config['id']        = $feed['id'];
		$config['form_id']   = $feed['form_id'];
		$config['is_active'] = $feed['is_active'];

		$config['meta']['type']               = rgar( $feed['meta'], 'transactionType' );
		$config['meta']['update_post_action'] = rgar( $feed['meta'], 'update_post_action' );

		$config['meta']['redsys_conditional_enabled'] = rgar( $feed['meta'], 'feed_condition_conditional_logic' );
		if ( $feed['meta']['feed_condition_conditional_logic'] ) {
			$config['meta']['redsys_conditional_field_id'] = $feed['meta']['feed_condition_conditional_logic_object']['conditionalLogic']['rules'][0]['fieldId'];
			$config['meta']['redsys_conditional_operator'] = $feed['meta']['feed_condition_conditional_logic_object']['conditionalLogic']['rules'][0]['operator'];
			$config['meta']['redsys_conditional_value']    = $feed['meta']['feed_condition_conditional_logic_object']['conditionalLogic']['rules'][0]['value'];
		}

		$config['meta']['api_settings_enabled'] = rgar( $feed['meta'], 'apiSettingsEnabled' );
		$config['meta']['gf_redsys_mode']       = rgar( $feed['meta'], 'overrideMode' );
		$config['meta']['gf_redsys_merchant']   = rgar( $feed['meta'], 'overrideMerchant' );
		$config['meta']['gf_redsys_terminal']   = rgar( $feed['meta'], 'overrideTerminal' );
		$config['meta']['gf_redsys_secret']     = rgar( $feed['meta'], 'overrideSecret' );

		$config['meta']['custom_fields']['description'] = rgar( $feed['meta'], 'description' );

		return $config;

	}

	// --------- Submission Process.
	function get_redsys() {
		if ( ! isset( $this->redsys ) ) {
			if ( ! class_exists( '\Redsys\RedsysAPI', false ) ) {
				require_once REDSYS_FILE_PATH;
			}
			$this->redsys = apply_filters( 'woocommerce_redsys_getredsys', new \Redsys\RedsysAPI() );
		}

		return $this->redsys;
	}

	/**
	 * Get Redsýs language code
	 * */
	function _getLanguange() {
		global $polylang;
		$lng = substr( get_bloginfo( 'language' ), 0, 2 );
		if ( function_exists( 'qtrans_getLanguage' ) ) {
			$lng = qtrans_getLanguage();
		}
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$lng = ICL_LANGUAGE_CODE;
		}
		switch ( $lng ) {
			case 'en':
				return '002';
			case 'ca':
				return '003';
			case 'fr':
				return '004';
			case 'de':
				return '005';
			case 'dk':
				return '006';
			case 'it':
				return '007';
			case 'sk':
				return '008';
			case 'pt':
				return '009';
			case 'va':
				return '010';
			case 'po':
				return '011';
			case 'gl':
				return '012';
			case 'eu':
				return '013';
			default:
				return '001';
		}

		return '001';
	}

	public function get_slug() {
		return $this->_slug;
	}
}
