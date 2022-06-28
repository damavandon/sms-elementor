<?php
// ═══════════════════════════ :هشدار: ═══════════════════════════

// ‫ تمامی حقوق مادی و معنوی این افزونه متعلق به سایت پیامیتو به آدرس payamito.com می باشد
// ‫ و هرگونه تغییر در سورس یا استفاده برای درگاهی غیراز پیامیتو ،
// ‫ قانوناً و شرعاً غیرمجاز و دارای پیگرد قانونی می باشد.

// © 2022 Payamito.com, Kian Dev Co. All rights reserved.

// ════════════════════════════════════════════════════════════════

// don't call the file directly.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("Payamito_Elementor_Loader")) :

    class Payamito_Elementor_Loader
    {
        /**
         * The single instance of the class.
         *
         * @var Payamito_Elementor_Loader
         * @since 1.0.0
         */
        protected static $_instance = null;

        public static $slug = 'payamito_el';

        /**
         * Main Payamito_Elementor_Loader Instance.
         *
         * Ensures only one instance of Payamito_Elementor_Loader is loaded or can be loaded.
         *
         * @since 1.0.0
         * @static
         * @see payamito_el()
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
            require_once __DIR__ . '/functions.php';
            add_action("plugins_loaded", [$this, 'start'], 99999);
            $this->define_constant();
            $this->install();
        }
        public function define_constant()
        {
            if (!defined("PAYAMITO_EL_CORE_DIR")) {
                define('PAYAMITO_EL_CORE_DIR', __DIR__ . '/public/core/payamito-core/');
            }

            if (!defined("PAYAMITO_EL_CORE_VERSION")) {
                define('PAYAMITO_EL_CORE_VERSION', '2.0.0');
            }
        }
        public function start()
        {
            payamito_elementor_set_locale();

            if (!self::is_elementor_pro_installed())  return $this->dependency('elementor_pro');
            if (!self::is_elementor_installed())  return $this->dependency("elementor");
            $this->init();
        }

        public function init()
        {
            require_once  __DIR__ . '/public/class-public.php';
            require_once  __DIR__ . '/admin/class-admin.php';
        }
        public static function is_elementor_pro_installed()
        {
            return defined('ELEMENTOR_PRO_VERSION');
        }

        public static function is_elementor_installed()
        {
            return defined('ELEMENTOR_VERSION');
        }
        public function install()
        {
            require_once  __DIR__ . '/class-install.php';
            register_activation_hook(PAYAMITO_EL_PLUGIN_FILE, ['Payamito_Elementor_Install', 'active']);
            register_deactivation_hook(PAYAMITO_EL_PLUGIN_FILE, ['Payamito_Elementor_Install', 'deactive']);
        }

        public function dependency($plugin)
        {
            if ($plugin === 'elementor_pro') {
                add_action('admin_notices', '_payamito_el_no_intalled_elementor_pro');
            }
            if ($plugin === 'elementor') {
                add_action('admin_notices', '_payamito_el_no_intalled_elementor');
            }
        }
        /**
         * Throw error on object clone
         *
         * The whole idea of the singleton design pattern is that there is a single
         * object therefore, we don't want the object to be cloned.
         *
         * @since 1.0.0
         * @return void
         */
        public function __clone()
        {
            // Cloning instances of the class is forbidden
            _doing_it_wrong(__FUNCTION__, __('Something went wrong.', 'payamito-elementor'), '1.0.0');
        }

        /**
         * Disable unserializing of the class
         *
         * @since 1.0.0
         * @return void
         */
        public function __wakeup()
        {
            // Unserializing instances of the class is forbidden
            _doing_it_wrong(__FUNCTION__, __('Something went wrong.', 'payamito-elementor'), '1.0.0');
        }
    }
endif;
Payamito_Elementor_Loader::get_instance();


