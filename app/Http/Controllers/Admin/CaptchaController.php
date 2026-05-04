<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CaptchaService;
use Symfony\Component\HttpFoundation\Response;

class CaptchaController extends Controller
{
    public function image(CaptchaService $captcha): Response
    {
        $code = $captcha->generate();
        $im = $captcha->makeImage($code);

        ob_start();
        imagepng($im);
        $binary = ob_get_clean();
        imagedestroy($im);

        return response($binary, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
