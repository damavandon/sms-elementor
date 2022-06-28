<?php
// ═══════════════════════════ :هشدار: ═══════════════════════════

// ‫ تمامی حقوق مادی و معنوی این افزونه متعلق به سایت پیامیتو به آدرس payamito.com می باشد
// ‫ و هرگونه تغییر در سورس یا استفاده برای درگاهی غیراز پیامیتو ،
// ‫ قانوناً و شرعاً غیرمجاز و دارای پیگرد قانونی می باشد.

// © 2022 Payamito.com, Kian Dev Co. All rights reserved.

// ════════════════════════════════════════════════════════════════

?><?php

// don't call the file directly.
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('payamito_el_load_core')) {

    function payamito_el_load_core()
    {
        $core = get_option("payamito_core_version");
        if ($core === false) {
            return PAYAMITO_EL_CORE_DIR;
        }
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $core = unserialize($core);
        if (
            file_exists($core['core_path'])
            &&
            is_plugin_active($core['absolute_path'])
        ) {
            return $core['core_path'];
        } else {
            return PAYAMITO_EL_CORE_DIR;
        }
        return PAYAMITO_EL_CORE_DIR;
    }
}

if (!function_exists("_payamito_el_resent_time_check")) {
    function _payamito_el_resent_time_check($send_time, $period)
    {
        $period_send = (int)$period;
        if($period<10){
            $period=10;
        }
        $time_send = (int)$send_time;
        $R = current_time('timestamp') - $time_send;
        if ($R < $period_send) {
            return ($period_send - $R);
        }
        return true;
    }
}

if (!function_exists("_payamito_el_set_session")) {
    function _payamito_el_set_session(string $phone, array $data = [])
    {
        $phone=_payamito_el_delete_0($phone);
        $_SESSION['payamito_el'][$phone]['verified'] = false;
        $_SESSION['payamito_el'][$phone]['send_time'] = current_time("timestamp");
        if (isset($data['OTP'])) {
            $_SESSION['payamito_el'][$phone]['OTP'] = $data['OTP'];
        }
    }
}
if (!function_exists("_payamito_el_set_data_session")) {
    function _payamito_el_set_data_session(string $phone, array $data = [])
    {
       
        $phone=_payamito_el_delete_0($phone);

        if (isset($_SESSION['payamito_el'][$phone])) {
            foreach ($data as $i => $v) {
                $_SESSION['payamito_el'][$phone][$i] = $v;
            }
        } else {
            foreach ($data as $i => $v) {
                $_SESSION['payamito_el'][$phone][$i] = $v;
            }
        }
    }
}
if (!function_exists("_payamito_el_get_session")) {
    function _payamito_el_get_session($phone)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $phone=_payamito_el_delete_0($phone);
        if (isset($_SESSION['payamito_el'][$phone])) {
            return $_SESSION['payamito_el'][$phone];
        }
        return null;
    }
}
if (!function_exists("_payamito_el_delete_message_session")) {
    function _payamito_el_delete_message_session($phone, $message_type)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $phone=_payamito_el_delete_0($phone);
        if (isset($_SESSION['payamito_el'][$phone])) {
            unset($_SESSION['payamito_el'][$phone][$message_type]);
        }
    }
}
if (!function_exists("_payamito_el_delete_session")) {
    function _payamito_el_delete_session($phone)
    {
       $phone=_payamito_el_delete_0($phone);
        if (isset($_SESSION['payamito_el'][$phone])) {
            unset($_SESSION['payamito_el'][$phone]);
            return true;
        }
        return false;
    }
}
if (!function_exists("_payamito_el_no_intalled_elementor_pro")) {
    function _payamito_el_no_intalled_elementor_pro()
    {
        $elementor_url = "https://abzarwp.com/downloads/elementor/";
        $message =  __('Payamito Elementor  is not working because you need to activate the Elementor pro', 'payamito-elementor');
?>
        <div class="notice notice-error is-dismissible" style="padding: 2%;border: 2px solid #e39e06;">
            <p style="text-align: center;font-size: 19px;font-weight: 700;"><?php esc_html_e($message); ?></p>
            <p><a target="_blank" href="<?php echo esc_url($elementor_url) ?>" class="button-primary"> <?php esc_html_e('Install Elementor pro Now', 'payamito-elementor'); ?></a></p>
        </div>
    <?php
    }
}
if (!function_exists("_payamito_el_no_intalled_elementor")) {
    function _payamito_el_no_intalled_elementor()
    {
        $elementor_url = "https://wordpress.org/plugins/elementor/";
        $message =  __('Payamito Elementor  is not working because you need to activate the Elementor', 'payamito-elementor');
    ?>
        <div class="notice notice-error is-dismissible" style="padding: 2%;border: 2px solid #e39e06;">
            <p style="text-align: center;font-size: 19px;font-weight: 700;"><?php esc_html_e($message); ?></p>
            <p><a target="_blank" href="<?php echo esc_url($elementor_url) ?>" class="button-primary"> <?php esc_html_e('Install Elementor  Now', 'payamito-elementor'); ?></a></p>
        </div>
<?php
    }
}
if (!function_exists("_payamito_el_delete_0")) {
    function _payamito_el_delete_0($phone)
    {
        $phone = payamito_to_english_number(sanitize_text_field($phone));
        $zaro = $phone[0];
        if ($zaro == "0") {
            $phone = substr_replace($phone, "", 0, 1);
        }
        return $phone;
    }
}
if (!function_exists("payamito_elementor_set_locale")) {
    function payamito_elementor_set_locale()
    {
        	
		$dirname = str_replace('//', '/', wp_normalize_path(dirname(PAYAMITO_EL_PLUGIN_FILE))) ;
		$mo = $dirname . '/languages/' . 'payamito-elementor-' . get_locale() . '.mo';
		load_textdomain('payamito-elementor', $mo);
    }
}
