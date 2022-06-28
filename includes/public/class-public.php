<?php
// ═══════════════════════════ :هشدار: ═══════════════════════════

// ‫ تمامی حقوق مادی و معنوی این افزونه متعلق به سایت پیامیتو به آدرس payamito.com می باشد
// ‫ و هرگونه تغییر در سورس یا استفاده برای درگاهی غیراز پیامیتو ،
// ‫ قانوناً و شرعاً غیرمجاز و دارای پیگرد قانونی می باشد.

// © 2022 Payamito.com, Kian Dev Co. All rights reserved.

// ════════════════════════════════════════════════════════════════


?><?php

if (!class_exists("Payamito_Elementor_Public")) {

    class Payamito_Elementor_Public
    {
        /**
         * The single instance of the class.
         *
         * @var Payamito_Elementor_Admin
         * @since 1.0.0
         */
        protected static $_instance = null;

        /**
         * Main Payamito_Elementor_Public Instance.
         *
         * Ensures only one instance of Payamito_Elementor_Public is loaded or can be loaded.
         *
         * @since 1.0.0
         * @static
         * @return Payamito_Elementor_Admin - Main instance.
         */
        public static function get_instance()
        {
            if (is_null(self::$_instance)) {

                self::$_instance = new self();
            }
            return self::$_instance;
        }
        public function __construct()
        {
            $this->define_constant();
            $this->include();
        }
        public function include()
        { 
             require_once __DIR__ . '/functions.php';
             require_once payamito_el_load_core().'payamito.php';
             require_once __DIR__ . '/public-class-message.php';
             require_once __DIR__ . '/public-elementor-control.php';
             require_once __DIR__ . '/public-form.php';
        }
        public function define_constant()
        {
            if (!defined("PAYAMITO_EL_PUBLIC_URL")) {
                define('PAYAMITO_EL_PUBLIC_URL', plugins_url('/', PAYAMITO_EL_PLUGIN_FILE) . 'includes/public/assets/');
            }

           
        }
    }
}
Payamito_Elementor_Public::get_instance();
