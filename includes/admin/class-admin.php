<?php
// ═══════════════════════════ :هشدار: ═══════════════════════════

// ‫ تمامی حقوق مادی و معنوی این افزونه متعلق به سایت پیامیتو به آدرس payamito.com می باشد
// ‫ و هرگونه تغییر در سورس یا استفاده برای درگاهی غیراز پیامیتو ،
// ‫ قانوناً و شرعاً غیرمجاز و دارای پیگرد قانونی می باشد.

// © 2022 Payamito.com, Kian Dev Co. All rights reserved.

// ════════════════════════════════════════════════════════════════


?><?php

if (!class_exists("Payamito_Elementor_Admin")) {

    class Payamito_Elementor_Admin
    {
        /**
         * The single instance of the class.
         *
         * @var Payamito_Elementor_Admin
         * @since 1.0.0
         */
        protected static $_instance = null;

        /**
         * Main Payamito_Elementor_Admin Instance.
         *
         * Ensures only one instance of Payamito_Elementor_Admin is loaded or can be loaded.
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
            add_action('elementor/editor/before_enqueue_scripts', [$this, 'admin_enqueue']);
        }
        public function admin_enqueue()
        {
            if (isset($_GET['action']) and  $_GET['action'] === "elementor") {
                wp_register_script('id-selector', PAYAMITO_EL_PUBLIC_URL . 'js/id-selector.js', ['jquery'], ELEMENTOR_PRO_VERSION, true);
                wp_enqueue_script('id-selector');
            }
        }

        public function include()
        {
            require_once  __DIR__ . '/class-updater.php';
        }
        public function define_constant()
        {
            if (!defined("PAYAMITO_EL_ADMIN_URL")) {
                define('PAYAMITO_EL_ADMIN_URL', plugins_url('/', PAYAMITO_EL_PLUGIN_FILE) . 'includes/admin/assets/');
            }
            if (!defined("PAYAMITO_EL_ADMIN_CORE_DIR")) {
                define('PAYAMITO_EL_ADMIN_CORE_DIR', __DIR__ . '/');
            }
        }
    }
}
if (is_admin()) {
    Payamito_Elementor_Admin::get_instance();
}
