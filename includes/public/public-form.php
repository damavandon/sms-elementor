<?php
// ═══════════════════════════ :هشدار: ═══════════════════════════

// ‫ تمامی حقوق مادی و معنوی این افزونه متعلق به سایت پیامیتو به آدرس payamito.com می باشد
// ‫ و هرگونه تغییر در سورس یا استفاده برای درگاهی غیراز پیامیتو ،
// ‫ قانوناً و شرعاً غیرمجاز و دارای پیگرد قانونی می باشد.

// © 2022 Payamito.com, Kian Dev Co. All rights reserved.

// ════════════════════════════════════════════════════════════════

?><?php

use ElementorPro\Modules\Forms\Module;
use ElementorPro\Plugin;

if (!class_exists('Payamito_Elementor_Form')) {
    class Payamito_Elementor_Form
    {
        public $form_id;
        public $form = null;
        public $settings = [];
        public $otp_settings = [];
        public $user_settings = [];
        public $admin_settings = [];

        public function __construct(string $form_id, string $post_id)
        {
            $elementor = Plugin::elementor();
            $document = $elementor->documents->get($post_id);
            $this->form_id = $form_id;
            if ($document) {
                $this->form = Module::find_element_recursive($document->get_elements_data(), $form_id);
            }
            if ($this->form === null) {
                return;
            }
            $this->prepare_settings();
        }
        public function is_otp_active()
        {
            return $this->otp_settings['active'];
        }

        public function get_settings()
        {
            return $this->settings;
        }
        public function prepare_settings()
        {
            $set = [];
            $settings = $this->form['settings'];
            $otp_slug = Payamito_Elementor_Control::get_otp_slug();
            $user_slug = Payamito_Elementor_Control::get_user_slug();
            $admin_slug = Payamito_Elementor_Control::get_admin_slug();
            ///////////////////////////////////////////////////////////////otp
            $set['OTP']['active']  = $this->form['settings'][$otp_slug . 'enable'] === 'true' ? true : false;
            $set['OTP']['pattern_active'] = $this->form['settings'][$otp_slug . 'pattern_enable'] === 'true' ? true : false;
            $set['OTP']['pattern'] = is_array($settings[$otp_slug . 'pattern']) ? $settings[$otp_slug . 'pattern'] : [];
            $set['OTP']['pattern_id'] = $settings[$otp_slug . 'pattern_id'] ? $settings[$otp_slug . 'pattern_id'] : '';
            $set['OTP']['message'] = $settings[$otp_slug . 'message'] ? $settings[$otp_slug . 'message'] : '';
            $set['OTP']['count']   = $settings[$otp_slug . 'count'] ? $settings[$otp_slug . 'count'] : 4;
            $set['OTP']['resend_time'] = $settings[$otp_slug . 'resend_time'] ? $settings[$otp_slug . 'resend_time'] : 30;
            ///////////////////////////////////////////////////////////////user
            $set['user']['active']  = $this->form['settings'][$user_slug . 'enable'] === 'true' ? true : false;
            $set['user']['pattern_active'] = $this->form['settings'][$user_slug . 'pattern_enable'] === 'true' ? true : false;
            $set['user']['pattern'] = is_array($settings[$user_slug . 'pattern']) ? $settings[$user_slug . 'pattern'] : [];
            $set['user']['pattern_id'] = $settings[$user_slug . 'pattern_id'] ? $settings[$user_slug . 'pattern_id'] : '';
            $set['user']['phone_field'] = $settings[$user_slug . 'field_id'] ? $settings[$user_slug . 'field_id'] : '';
            $set['user']['message'] = $settings[$user_slug . 'message'] ? $settings[$user_slug . 'message'] : '';
            ///////////////////////////////////////////////////////////////admin
            $set['admin']['active']  = $this->form['settings'][$admin_slug . 'enable'] === 'true' ? true : false;
            $set['admin']['pattern_active'] = $this->form['settings'][$admin_slug . 'pattern_enable'] === 'true' ? true : false;
            $set['admin']['pattern'] = is_array($settings[$admin_slug . 'pattern']) ? $settings[$admin_slug . 'pattern'] : [];
            $set['admin']['pattern_id'] = $settings[$admin_slug . 'pattern_id'] ? $settings[$admin_slug . 'pattern_id'] : '';
            $set['admin']['phone']   = is_array($settings[$admin_slug . 'number']) ? $settings[$admin_slug . 'number'] : [];
            $set['admin']['message'] = $settings[$admin_slug . 'message'] ? $settings[$admin_slug . 'message'] : '';

            $this->settings = $set;
            $this->otp_settings = $set['OTP'];
            $this->user_settings = $set['user'];
            $this->admin_settings = $set['admin'];
        }
        public function otp_prepare_pattern(array $pattern)
        {
            $prepared_pattern = [];
            foreach ($pattern as $item) {
                $prepared_pattern[$item['tag']] = $item['user_tag'];
            }
            Payamito_Elementor_Message::$otp_count = $this->otp_settings['count'];
            $pattern = Payamito_Elementor_Message::prepare_pattern($prepared_pattern);
            return $pattern;
        }

        public function get_otp()
        {
            return Payamito_Elementor_Message::$OTP;
        }

        public function prepare_pattern(array $pattern, array $fields)
        {
            $prepared_pattern = [];
            $ready_pattern = [];
            foreach ($pattern as $item) {
                $prepared_pattern[$item['tag']] = $item['user_tag'];
            }
            foreach ($prepared_pattern as $field => $tag) {
                foreach ($fields as $id => $value) {
                    if ($id === $field) {
                        $ready_pattern[$tag] = $value;
                    }
                }
            }
            return $ready_pattern;
        }

        public function pattern_send(string $phone, array $pattern, string $id)
        {

            $result = payamito_send_pattern($phone, $pattern, $id, Payamito_Elementor_Loader::$slug);
            if ($result > 10000) {
                return ['result' => true, 'message' => $result];
            } else {
                return ['result' => false, 'message' =>  payamito_code_to_message($result)];
            }
        }
        public function prepare_otp_message(string $message)
        {
            if (empty($message)) {
                return "";
            }
            $replced = self::explode($message);
            foreach ($replced as  $item) {
                if($item==='OTP'){
                    $message=str_replace(['OTP'], Payamito_Elementor_Message::otp_tag_value('otp'), $message);
                }
                if($item==='site_title'){
                    $message=str_replace(['site_title'], Payamito_Elementor_Message::otp_tag_value('site_title'), $message);
                }
            }
            return $message;
        }
        public function prepare_message(string $message, array $sent_data)
        {
            if (empty($message)) {
                return "";
            }
            $search=[];
            $replced = self::explode($message);
            foreach ($replced as  $item) {
                foreach ($sent_data as $field_id => $data) {
                    if ($item === $field_id) {
                        array_push($search, $field_id);
                    }
                }
            }
            $text = str_replace(array_values($search), array_values($sent_data), $message);
            $text = self::str_replace($text);
            return $text;
        }
        public static function explode(string $message)
        {

            $message = trim(str_replace(PHP_EOL, ' /n ', $message));
            $search = explode(" ", $message);
            return $search;
        }
        public static function str_replace( string $text)
        {
            $text = trim(str_replace(' /n ', PHP_EOL, $text));
            return $text;
        }

        public function text_send($phone, $message)
        {

            $result =(int)  payamito_send($phone, $message, Payamito_Elementor_Loader::$slug);
            if ($result == 1) {
                return ['result' => true, 'message' => __("successful", 'payamito-elementor')];
            } else {
                return  ['result' => false, 'message' =>  payamito_code_to_message($result)];
            }
        }
    }
}
