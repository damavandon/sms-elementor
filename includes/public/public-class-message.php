<?php
// ═══════════════════════════ :هشدار: ═══════════════════════════

// ‫ تمامی حقوق مادی و معنوی این افزونه متعلق به سایت پیامیتو به آدرس payamito.com می باشد
// ‫ و هرگونه تغییر در سورس یا استفاده برای درگاهی غیراز پیامیتو ،
// ‫ قانوناً و شرعاً غیرمجاز و دارای پیگرد قانونی می باشد.

// © 2022 Payamito.com, Kian Dev Co. All rights reserved.

// ════════════════════════════════════════════════════════════════


class Payamito_Elementor_Message
{

    public static $otp_count = 4;

    public static $OTP = null;

    public static function otp_tag_value($tag)
    {
        switch ($tag) {
            case 'otp':
                self::$OTP = Payamito_OTP::payamito_generate_otp(self::$otp_count);
                return self::$OTP;
                break;
            case "site_title":
                return get_bloginfo("name");
                break;
        }
    }
    public static function prepare_pattern(array $pattern)
    {
        $ready_pattern = [];
        foreach ($pattern as $value => $tag) {
            $ready_pattern[$tag] = self::otp_tag_value($value);
        }
        return $ready_pattern;
    }
    public static function prepare_message(string $message)
    {
       
    }
    
}
