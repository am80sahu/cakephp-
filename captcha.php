<?php if (!class_exists('CaptchaConfiguration')) { return; }

// BotDetect PHP Captcha configuration options
// more details here: https://captcha.com/doc/php/captcha-options.html
// ----------------------------------------------------------------------------

$config = array(
    /*
    |--------------------------------------------------------------------------
    | Captcha configuration for example page
    |--------------------------------------------------------------------------
    */
	
    'ExampleCaptcha' => array(
        'UserInputID' => 'CaptchaCode',
        'ImageWidth' => 200,
        'ImageHeight' => 60,
		'ImageStyle' => ImageStyle::BlackOverlap,
    ),

    /*
    |--------------------------------------------------------------------------
    | Captcha configuration for contact page
    |--------------------------------------------------------------------------
    */
    'ContactCaptcha' => array(
        'UserInputID' => 'CaptchaCode',
        'CodeLength' => CaptchaRandomization::GetRandomCodeLength(4, 6),
        'ImageStyle' => ImageStyle::AncientMosaic,
    ),

    /*
    |--------------------------------------------------------------------------
    | Captcha configuration for login and register page
    |--------------------------------------------------------------------------
    */
    'AuthCaptcha' => array(
        'UserInputID' => 'CaptchaCode',
        'CodeLength' => CaptchaRandomization::GetRandomCodeLength(4, 7),
        'CodeStyle' => CodeStyle::Alpha,
        'CustomLightColor' => '#9966FF',
    ),

    // Add more your Captcha configuration here...
);
