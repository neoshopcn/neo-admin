<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

/**
 * 轻量验证码（GD），无需第三方扩展库
 */
class CaptchaService
{
    public function generate(): string
    {
        $cfg = config('captcha');
        $length = max(3, min(8, (int) $cfg['length']));
        $charset = (string) $cfg['charset'];
        $code = '';
        $max = strlen($charset) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= $charset[random_int(0, $max)];
        }

        Session::put($cfg['session_key'], [
            'code' => strtolower($code),
            'expires_at' => now()->addSeconds((int) $cfg['expire_seconds'])->timestamp,
        ]);

        return $code;
    }

    public function validate(?string $input): bool
    {
        $cfg = config('captcha');
        $key = $cfg['session_key'];
        $payload = Session::get($key);
        Session::forget($key);

        if (! is_array($payload) || empty($payload['code'])) {
            return false;
        }

        if (($payload['expires_at'] ?? 0) < now()->timestamp) {
            return false;
        }

        return hash_equals((string) $payload['code'], strtolower(trim((string) $input)));
    }

    /**
     * @return resource|\GdImage
     */
    public function makeImage(string $code)
    {
        $cfg = config('captcha');
        $w = (int) $cfg['width'];
        $h = (int) $cfg['height'];

        $im = imagecreatetruecolor($w, $h);
        $bg = imagecolorallocate($im, 245, 247, 250);
        $fg = imagecolorallocate($im, 48, 49, 51);
        $noise = imagecolorallocate($im, 180, 187, 196);

        imagefilledrectangle($im, 0, 0, $w, $h, $bg);

        for ($i = 0; $i < (int) $cfg['noise_dots']; $i++) {
            imagesetpixel($im, random_int(0, $w - 1), random_int(0, $h - 1), $noise);
        }

        for ($i = 0; $i < (int) $cfg['noise_lines']; $i++) {
            imageline(
                $im,
                random_int(0, $w),
                random_int(0, $h),
                random_int(0, $w),
                random_int(0, $h),
                $noise
            );
        }

        $len = strlen($code);
        $step = $w / ($len + 1);
        for ($i = 0; $i < $len; $i++) {
            $ch = $code[$i];
            $x = (int) ($step * ($i + 0.35));
            $y = (int) ($h * 0.72);
            imagestring($im, 5, $x, $y - 18, $ch, $fg);
        }

        return $im;
    }
}
