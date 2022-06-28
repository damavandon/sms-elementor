<?php
// ═══════════════════════════ :هشدار: ═══════════════════════════

// ‫ تمامی حقوق مادی و معنوی این افزونه متعلق به سایت پیامیتو به آدرس payamito.com می باشد
// ‫ و هرگونه تغییر در سورس یا استفاده برای درگاهی غیراز پیامیتو ،
// ‫ قانوناً و شرعاً غیرمجاز و دارای پیگرد قانونی می باشد.

// © 2022 Payamito.com, Kian Dev Co. All rights reserved.

// ════════════════════════════════════════════════════════════════

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}


class Payamito_Elementor_Phone_Field
{
	public	$contorol;
	public $form;
	public function __construct()
	{

		add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueue_scripts']);
		add_filter('elementor_pro/forms/field_types', [$this, 'register_field']);
		add_action('elementor_pro/forms/render_field/phone', [$this, 'render'], 10, 3);
		add_action('elementor_pro/init', [$this, 'register_action'], 10);
		add_action('elementor/widget/render_content', [$this, 'render_form_scripts'], -1, 2);
		add_action('wp_ajax_nopriv_payamito_el_verification', [$this, 'ajax_verification']);
		add_action('wp_ajax_payamito_el_verification', [$this, 'ajax_verification']);
		$this->contorol = new Payamito_Elementor_Control();
	}


	public function ajax_verification()
	{
		session_start();
		$form_id = sanitize_text_field($_POST['form_id']);
		$post_id = $_POST['post_id'];
		$form = new Payamito_Elementor_Form($form_id, $post_id);
		$show_otp = false;
		if (!$form->is_otp_active()) {
			die;
		}

		$phone = sanitize_text_field($_POST['phone_number']);
		$phone = _payamito_el_delete_0($phone);
		if (!is_null(_payamito_el_get_session($phone))) {
			$show_otp = true;
		}
		if (!payamito_verify_moblie_number($phone)) {
			_payamito_el_set_data_session($phone, ['field_message' => __("Please enter a phone validate", 'payamito-elementor')]);
			wp_send_json(['error' => true, 'show_otp' => $show_otp, 'message' => __("Please enter a phone validate", 'payamito-elementor')]);
			die;
		}

		if ($form->otp_settings['active']) {
			$session = _payamito_el_get_session($phone);
			if ($session !== null) {
				$send_time = $session['send_time'];
				$is_resend_time = _payamito_el_resent_time_check($send_time, $form->otp_settings['resend_time']);
				if ($is_resend_time !== true) {
					wp_send_json(['error' => true, 'show_otp' => $show_otp]);
					die;
				}
			}
			if ($form->otp_settings['pattern_active']) {
				$pattern = $form->otp_prepare_pattern($form->otp_settings['pattern']);
				if (is_array($pattern)) {
					$result = payamito_send_pattern($phone, $pattern, $form->otp_settings['pattern_id'], Payamito_Elementor_Loader::$slug);
					if ($result > 10000) {
						$OTP = $form->get_otp();
						_payamito_el_set_session($phone, ['OTP' => $OTP]);
						wp_send_json(['error' => false, 'message' => __("successful", 'payamito-elementor')]);
						die;
					} else {
						_payamito_el_set_data_session($phone, ['otp_message' => payamito_code_to_message($result)]);
						wp_send_json(['error' => true, 'show_otp' => $show_otp]);
						die;
					}
				}
			} else {
				Payamito_Elementor_Message::$otp_count=$form->otp_settings['count'];
				$message = $form->prepare_otp_message($form->otp_settings['message']);
				$OTP = Payamito_Elementor_Message::$OTP;
				$result = payamito_send($phone, $message, Payamito_Elementor_Loader::$slug);
				if ($result >10000) {
					$OTP = $form->get_otp();
					_payamito_el_set_session($phone, ['OTP' => $OTP]);
					wp_send_json(['error' => false, 'message' => __("successful", 'payamito-elementor')]);
					die;
				} else {
					_payamito_el_set_data_session($phone, ['otp_message' => payamito_code_to_message($result)]);
					wp_send_json(['error' => true, 'show_otp' => $show_otp]);
					die;
				}
			}
		}
	}
	public function render_form_scripts($widget_content, $element)
	{
		if (is_admin()) {
			return $widget_content;
		}
		$type_elememt = $element->get_name();
		if ($type_elememt !== 'form') {
			return $widget_content;
		}
		$settings = $element->get_settings();
		$form_id = $element->get_data()['id'];
		if (!in_array('payamito_action', $settings['submit_actions'])) {
			return $widget_content;
		}

		$field_id = '0';
		foreach ($settings['form_fields'] as $field) {

			if ($field['field_type'] === $this->get_type()) {
				$field_id = $field['_id'];
			}
		}
		if ($field_id === '0') {
			return $widget_content;
		}

		wp_enqueue_script('otp-action', PAYAMITO_EL_PUBLIC_URL . 'js/otp-action.js', ['jquery'], ELEMENTOR_PRO_VERSION, true);
		wp_add_inline_script('otp-action', 'const PAYAMITO_EL_OTP_ACTION = ' . json_encode(array(
			'field_id' => $field_id,
			'form_id' => $form_id,
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce("payamito_el_verification"),
			'resend_text' => __("Resend", 'payamito-elementor'),
			'otp_text' => __("OTP", 'payamito-elementor'),
			'otp_resend_time' => $settings['otp_resend_time'],
			'otp_second_text' => __("Second", 'payamito-elementor'),
		)), 'before');

		return	$widget_content;
	}
	public function register_field($field_types)
	{
		$field_types[$this->get_type()] = $this->get_name();

		return $field_types;
	}

	public function enqueue_scripts()
	{
		wp_enqueue_script('phone-type-field', PAYAMITO_EL_PUBLIC_URL . 'js/phone-type-field.js', ['jquery'], ELEMENTOR_PRO_VERSION, true);
		wp_add_inline_script('phone-type-field', 'const PAYAMITO_EL_PHONE_FIELD = ' . json_encode(array(
			'field_type' => $this->get_type(),
			'field_name' => $this->get_name(),
		)), 'before');
	}

	public function get_type()
	{
		return 'phone';
	}
	public static function get_field_type()
	{
		return "phone";
	}
	public function get_name()
	{
		return __('Phone', 'payamito-elementor');
	}



	public function render($item, $item_index, $form)
	{
		$form->add_render_attribute('input' . $item_index, 'class', 'elementor-field-textual');
		$form->add_render_attribute('input' . $item_index, 'type', $this->get_type(), true);
		$form->add_render_attribute('input' . $item_index, 'placeholder', $item['field_label']);
		echo '<input ' . $form->get_render_attribute_string('input' . $item_index) . '>';
	}

	public function register_action()
	{
		$obj = $this->contorol;

		// Register the action with form widget
		\ElementorPro\Plugin::instance()->modules_manager->get_modules('forms')->add_form_action($obj->get_name(), $obj);
	}
}
/**
 * Class Sendmsms_Action_After_Submit
 * Custom elementor form action after submit to redirect to smsalert
 * Sendmsms_Action_After_Submit
 */

class Payamito_Elementor_Control extends \ElementorPro\Modules\Forms\Classes\Action_Base
{

	private static $otp_slug = "otp_";
	private static $user_slug = "user_";
	private static $admin_slug = "admin_";

	public $fields_id = [];

	public static function get_otp_slug()
	{
		return self::$otp_slug;
	}
	public static function get_user_slug()
	{
		return self::$user_slug;
	}
	public static function get_admin_slug()
	{
		return self::$admin_slug;
	}


	/**
	 * Get Name
	 *
	 * Return the action name
	 *
	 * @access public
	 * @return string
	 */

	public function get_name()
	{
		return 'payamito_action';
	}

	/**
	 * Get Label
	 *
	 * Returns the action label
	 *
	 * @access public
	 * @return string
	 */

	public function get_label()
	{
		return __('Payamito ', 'payamito-elementor');
	}

	/**
	 * Register Settings Section
	 *
	 * Registers the Action controls
	 *
	 * @access public
	 * @param \Elementor\Widget_Base $widget
	 */

	public function register_settings_section($widget)
	{


		$widget->start_controls_section(
			'section_payamito',
			[
				'label' => __('Payamito', 'payamito-elementor'),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);

		//////////////////////////////////////////otp
		$this->otp_settings($widget);
		//////////////////////////////////////////user
		$this->user_settings($widget);
		////////////////////////////////////////////////////////admin
		$this->admin_settings($widget);

		$widget->end_controls_section();
	}

	public function otp_settings($widget)
	{
		$slug = self::$otp_slug;
		$repeater = new Elementor\Repeater();

		$repeater->add_control(
			'tag',
			[
				'label' => __('Tag OTP', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'render_type' => 'none',

				'options' => [
					"otp" => __("OTP", 'payamito-elementor'),
					"site_title" => __("Site title", 'payamito-elementor')
				],
			]
		);
		$repeater->add_control(
			'user_tag',
			[
				'label' => esc_html__('your Tag', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__('Your tag', 'payamito-elementor'),
				'default' => '0',
			]
		);
		$widget->add_control(
			'otp_heading',
			[
				'label' => __('OTP', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::HEADING,
			]
		);
		$widget->add_control(
			$slug . 'enable',
			[
				'label' => __('OTP verification', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __('On', 'payamito-elementor'),
				'label_off' => __('Off', 'payamito-elementor'),
				'return_value' => 'true',
				'default' => 'false',
			]
		);

		$widget->add_control(
			$slug . 'pattern_enable',
			[
				'label' => __('OTP Pattern Enable', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __('On', 'payamito-elementor'),
				'label_off' => __('Off', 'payamito-elementor'),
				'return_value' => 'true',
				'default' => 'false',
				'condition' => [
					$slug . 'enable' => 'true'
				],
			]
		);
		$widget->add_control(
			$slug . 'pattern',
			[
				'type' =>  \Elementor\Controls_Manager::REPEATER,
				'label' => __('Add OTP Pattern', 'payamito-elementor'),
				'fields' => $repeater->get_controls(),
				'condition' => [
					$slug . 'enable' => 'true',
					$slug . 'pattern_enable' => 'true',
				],
			]
		);
		$widget->add_control(
			$slug . 'pattern_id',
			[
				'label' => esc_html__('Pattern ID', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__('Pattern ID', 'payamito-elementor'),
				'condition' => [
					$slug . 'enable' => 'true',
					$slug . 'pattern_enable' => 'true',
				],
			]
		);
		$widget->add_control(
			$slug . 'message',
			[
				'label' => __('OTP Message', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'placeholder' => __('Write yout text or use fields shortcode', 'payamito-elementor'),
				'label_block' => true,
				'render_type' => 'none',
				'default' => __('Your confirm code is OTP site_title .', 'payamito-elementor'),
				'classes' => '',
				'description' => __('Use OTP or site_title  for send form data or write your custom text.', 'payamito-elementor'),
				'condition' => [
					$slug . 'enable' => 'true',
					$slug . 'pattern_enable!' => 'true',
				],
			]

		);
		$widget->add_control(
			$slug . 'count',
			[
				'label' => __('OTP Count', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '4',
				'condition' => [
					$slug . 'enable' => 'true'
				],
			]
		);
		$widget->add_control(
			$slug . 'resend_time',
			[
				'label' => __('OTP Resend Time', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '30',
				'condition' => [
					$slug . 'enable' => 'true'
				],
			]
		);
	}
	public function user_settings($widget)
	{
		$slug = self::$user_slug;
		$repeater = new Elementor\Repeater();

		$repeater->add_control(
			'tag',
			[
				'label' => __('Variable: ID field', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'render_type' => 'none',
				'classes' => 'payamito_el_tag',
			]
		);
		$repeater->add_control(
			'user_tag',
			[
				'label' => esc_html__('your Tag', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__('your Tag', 'payamito-elementor'),
				'default' => '0',
			]
		);
		$widget->add_control(
			$slug . 'heading',
			[
				'label' => __('User ', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::HEADING,
			]
		);
		$widget->add_control(
			$slug . 'enable',
			[
				'label' => __('User SMS', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __('On', 'payamito-elementor'),
				'label_off' => __('Off', 'payamito-elementor'),
				'return_value' => 'true',
				'default' => 'false',
			]
		);
		$widget->add_control(
			$slug . 'field_id',
			[
				'label' => __('Field ID', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'render_type' => 'none',
				'condition' => [
					$slug . 'enable' => 'true'
				],
			]
		);
		$widget->add_control(
			$slug . 'pattern_enable',
			[
				'label' => __('User Pattern Enable', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __('On', 'payamito-elementor'),
				'label_off' => __('Off', 'payamito-elementor'),
				'return_value' => 'true',
				'default' => 'false',
				'condition' => [
					$slug . 'enable' => 'true'
				],
			]
		);
		$widget->add_control(
			$slug . 'pattern_id',
			[
				'label' => esc_html__('Pattern ID', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__('example:34562', 'payamito-elementor'),
				'condition' => [
					$slug . 'enable' => 'true',
					$slug . 'pattern_enable' => 'true',
				],
			]
		);
		$widget->add_control(
			$slug . 'pattern',
			[
				'type' =>  \Elementor\Controls_Manager::REPEATER,
				'label' => __('User Pattern', 'payamito-elementor'),
				'fields' => $repeater->get_controls(),
				'condition' => [
					$slug . 'enable' => 'true',
					$slug . 'pattern_enable' => 'true',
				],
			]
		);
		$widget->add_control(
			$slug . 'message',
			[
				'label' => __('User Message', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'placeholder' => __('Write yout text or use fields shortcode', 'payamito-elementor'),
				'label_block' => true,
				'render_type' => 'none',
				'default' => sprintf(__('Hello %1$s.', 'payamito-elementor'), 'name'),
				'classes' => '',
				'description' => __('Use fields ids for send form data or write your custom text.', 'payamito-elementor'),
				'condition' => [
					$slug . 'enable' => 'true',
					$slug . 'pattern_enable!' => 'true',
				],
			]

		);
	}
	public function admin_settings($widget)
	{
		$slug = self::$admin_slug;


		$widget->add_control(
			$slug . 'heading',
			[
				'label' => __('Admin', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::HEADING,
			]
		);

		$widget->add_control(
			$slug . 'enable',
			[
				'label' => __('Admin SMS', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __('On', 'payamito-elementor'),
				'label_off' => __('Off', 'payamito-elementor'),
				'return_value' => 'true',
				'default' => 'false',
			]
		);
		$repeater = new Elementor\Repeater();
		$repeater->add_control(
			'phone',
			[
				'label' => __('Phone', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'render_type' => 'none',
			]
		);
		$widget->add_control(
			$slug . 'number',
			[
				'type' =>  \Elementor\Controls_Manager::REPEATER,
				'label' => __('Admin Phones', 'payamito-elementor'),
				'fields' => $repeater->get_controls(),
				'condition' => [
					$slug . 'enable' => 'true',
				],

			]
		);
		$widget->add_control(
			$slug . 'pattern_enable',
			[
				'label' => __('admin Pattern Enable', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __('On', 'payamito-elementor'),
				'label_off' => __('Off', 'payamito-elementor'),
				'return_value' => 'true',
				'default' => 'false',
				'condition' => [
					$slug . 'enable' => 'true'
				],
			]
		);
		$widget->add_control(
			$slug . 'pattern_id',
			[
				'label' => esc_html__('Pattern ID', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__('example:34562', 'payamito-elementor'),
				'condition' => [
					$slug . 'enable' => 'true',
					$slug . 'pattern_enable' => 'true',
				],
			]
		);
		$repeater = new Elementor\Repeater();
		$repeater->add_control(
			'tag',
			[
				'label' => __('Variable: ID field', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'classes' => 'payamito-option-id',
				'render_type' => 'none',
				'classes' => 'payamito_el_tag',
				'options' => []
			]
		);
		$repeater->add_control(
			'user_tag',
			[
				'label' => esc_html__('your Tag', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__('your Tag', 'payamito-elementor'),
				'default' => '0',
			]
		);
		$widget->add_control(
			$slug . 'pattern',
			[
				'type' =>  \Elementor\Controls_Manager::REPEATER,
				'label' => __('Admin Pattern', 'payamito-elementor'),
				'fields' => $repeater->get_controls(),
				'condition' => [
					$slug . 'enable' => 'true',
					$slug . 'pattern_enable' => 'true',
				],

			]
		);

		$widget->add_control(
			$slug . 'message',
			[
				'label' => __('Admin Message', 'payamito-elementor'),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'placeholder' => __('Submit new form with phone field_id', 'payamito-elementor'),
				'label_block' => true,
				'render_type' => 'none',
				'classes' => '',
				'description' => __('Use fields ids for send form data or write your custom text.', 'payamito-elementor'),
				'separator' => 'after',
				'condition' => [
					$slug . 'enable' => 'true',
					$slug . 'pattern_enable!' => 'true',
				],

			]
		);
	}

	/**
	 * On Export
	 *
	 * Clears form settings on export
	 * @access Public
	 * @param array $element
	 */

	public function on_export($element)
	{
		return $element;
	}


	/**
	 * Runs the action after submit
	 *
	 * @access public
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 */

	public function run($record, $ajax_handler)
	{

		if (!$ajax_handler->is_success) {
			return;
		}
		$error = false;
		$form_id = sanitize_text_field($_POST['form_id']);
		$post_id = $_POST['post_id'];
		$sent_data = $record->get('sent_data');
		$phone_id = null;
		$phone = null;
		$phone_value = null;
		$OTP = null;
		$otp_id = 'payamito_el_otp_input';
		$fields = $record->get('fields');
		$this->form = new Payamito_Elementor_Form($form_id, $post_id);
		$otp_settings = $this->form->otp_settings;
		if ($otp_settings['active']) {
			foreach ($fields as $field) {
				if ($field['type'] === Payamito_Elementor_Phone_Field::get_field_type()) {
					$phone_id = $field['id'];
				}
			}
			if ($phone_id === null) {
				$error = true;
				return	$ajax_handler->add_error_message(__("Not found phone field .please contact support", 'payamito-elementor'));
			}
			$phone = $fields[$phone_id];
			$phone_value = payamito_to_english_number($phone['value']);
			$error_message = _payamito_el_get_session($phone_value);
			session_start();
			$session = _payamito_el_get_session($phone_value);

			if (!payamito_verify_moblie_number($phone_value)) {
				$error = true;

				return	$ajax_handler->add_error($phone_id, __("Please enter a valid phone", 'payamito-elementor'));
			}
			if (is_null($session)) {
				$error = true;
				$send = $this->send($this->form->otp_settings, $sent_data, $phone_value, 'OTP');

				if ($send['result'] === true) {
					$OTP = $this->form->get_otp();
					_payamito_el_set_session($phone_value, ['OTP' => $OTP]);
					$ajax_handler->add_error_message(__("We sent you a OTP code to confirm phone please fill OTP input with it", 'payamito-elementor'));
				} else {
					$ajax_handler->add_error_message($send['message']);
				}
				return;
			}
			if (isset($error_message['otp_message']) && !empty($error_message['otp_message'])) {
				$error = true;
				$ajax_handler->add_error_message($error_message['otp_message']);
				_payamito_el_delete_message_session($phone_value, 'otp_message');
				return;
			}
			if (isset($error_message['field_message']) && !empty($error_message['field_message'])) {
				$error = true;
				$ajax_handler->add_error_message($error_message['field_message']);
				_payamito_el_delete_message_session($phone, 'field_message');
				return;
			}

			if (!isset($_POST['payamito_el_otp_input'])) {
				$error = true;
				return	$ajax_handler->add_error_message(__("Please enter OTP sended to your phone", 'payamito-elementor'));
			}

			$OTP = payamito_to_english_number(sanitize_text_field($_POST['payamito_el_otp_input']));
			if ($session['OTP'] != $OTP || is_null($OTP)) {
				$error = true;
				return	$ajax_handler->add_error_message(__("OTP is not correct", 'payamito-elementor'));
			}
			if (!is_numeric($OTP)) {
				$error = true;
				$ajax_handler->add_error_message(__("OTP is not correct", 'payamito-elementor'));
				return;
			}

			if (count($ajax_handler->errors) === 0) {
				_payamito_el_delete_session($phone_value);
			}
		}
		if ($error === false) {
			$this->send($this->form->user_settings, $sent_data, null, 'user');
			$this->send($this->form->admin_settings, $sent_data, null, 'admin');
		}
	}
	public function send(array $settings, array $sent_data, $phone_number = null, string $user_type)
	{
		if ($settings['active'] === false) {
			return false;
		}
		$phone = [];
		if ($user_type === 'user') {
			$phone = payamito_to_english_number($sent_data[$settings['phone_field']]);
			$verify = payamito_verify_moblie_number($phone);
			if ($verify === false) {
				return false;
			}
			$phone = [$phone];
		}
		if ($user_type === 'admin') {
			foreach ($settings['phone'] as $item) {
				$item = payamito_to_english_number($item['phone']);
				if (payamito_verify_moblie_number($item)) {
					array_push($phone, $item);
				}
			}
		}
		if ($user_type === "OTP") {
			$phone = [$phone_number];
		}


		if ($settings['pattern_active'] === true) {
			if ($user_type === "OTP") {
				$pattern = $this->form->otp_prepare_pattern($settings['pattern']);
			} else {
				$pattern = $this->form->prepare_pattern($settings['pattern'], $sent_data);
			}
			foreach ($phone as $item) {
				$send = $this->form->pattern_send($item, $pattern, $settings['pattern_id']);
			}
		} else {
			if ($user_type === "OTP") {
				$message = $this->form->prepare_otp_message($settings['message']);
			} else {
				$message = $this->form->prepare_message($settings['message'], $sent_data);
			}
			foreach ($phone as $item) {
				$send = $this->form->text_send($item, $message);
			}
		}
		return $send;
	}
}

new Payamito_Elementor_Phone_Field;
