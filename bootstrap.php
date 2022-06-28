<?php
// ═══════════════════════════ :هشدار: ═══════════════════════════

// ‫ تمامی حقوق مادی و معنوی این افزونه متعلق به سایت پیامیتو به آدرس payamito.com می باشد
// ‫ و هرگونه تغییر در سورس یا استفاده برای درگاهی غیراز پیامیتو ،
// ‫ قانوناً و شرعاً غیرمجاز و دارای پیگرد قانونی می باشد.

// © 2022 Payamito.com, Kian Dev Co. All rights reserved.

// ════════════════════════════════════════════════════════════════


?><?php

/**
 * @package   Payamito
 * @link      https://payamito.com/
 *
 * Plugin Name:       Payamito Elemenotor
 * Plugin URI:        https://payamito.com/lib
 * Description:       Payamito Elementor makes it able for you to easily send SMS or verify users  with OTP
 * Version:           1.0.0
 * Author:            Payamito
 * Author URI:        https://payamito.com/
 * Text Domain:       payamito-elementor     
 * Domain Path:       /languages
 * Requires PHP: 7.0
 */

 // don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!defined('PAYAMITO_EL_PLUGIN_FILE')) {

    define('PAYAMITO_EL_PLUGIN_FILE', __FILE__);
}
if (!defined("PAYAMITO_EL_DIR")) {
    define('PAYAMITO_EL_DIR', plugin_dir_path(__DIR__));
}

//all things start to be here
include_once __DIR__.'/includes/loader.php';