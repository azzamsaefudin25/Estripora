<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CaptchaController extends Controller
{
    public function generate()
    {
        // Generate random captcha code
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $captcha_code = '';
        for ($i = 0; $i < 6; $i++) {
            $captcha_code .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Store in session
        session(['captcha_code' => $captcha_code]);

        // Create image
        $width = 150;
        $height = 50;
        $image = imagecreate($width, $height);

        // Colors
        $bg_color = imagecolorallocate($image, 255, 255, 255);
        $text_color = imagecolorallocate($image, 0, 0, 0);
        $noise_color = imagecolorallocate($image, 100, 120, 180);
        $line_color = imagecolorallocate($image, 64, 64, 64);

        // Fill background
        imagefill($image, 0, 0, $bg_color);

        // Add noise dots
        for ($i = 0; $i < 100; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $noise_color);
        }

        // Add noise lines
        for ($i = 0; $i < 5; $i++) {
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
        }

        // Add text
        $font_size = 5;
        $text_width = imagefontwidth($font_size) * strlen($captcha_code);
        $text_height = imagefontheight($font_size);
        $x = ($width - $text_width) / 2;
        $y = ($height - $text_height) / 2;

        imagestring($image, $font_size, $x, $y, $captcha_code, $text_color);

        // Output image
        ob_start();
        imagepng($image);
        $image_data = ob_get_contents();
        ob_end_clean();

        imagedestroy($image);

        return response($image_data)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
