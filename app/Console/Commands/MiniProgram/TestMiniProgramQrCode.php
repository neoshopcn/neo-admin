<?php

namespace App\Console\Commands\MiniProgram;

use App\Support\WechatMiniProgramUtils;
use EasyWeChat\Kernel\Exceptions\HttpException;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Throwable;

/**
 * 调用微信「获取小程序码」接口，将图片写入本地文件，便于联调。
 */
class TestMiniProgramQrCode extends Command
{
    protected $signature = 'miniprogram:test-qrcode
                            {app_code : 后台 miniprograms.app_code}
                            {--path=pages/index/index : getwxacode 必填页面 path（含可选 query，长度见微信文档）}
                            {--width=430 : 二维码宽度 px}
                            {--unlimited : 使用 getwxacodeunlimit（需 --scene）}
                            {--scene= : unlimited 场景值，最长 32 可见字符}
                            {--page= : unlimited 页面路径，可空}
                            {--output= : 保存路径；默认 storage/app/miniprogram-qrcode-时间戳.png}';

    protected $description = '测试拉取小程序码（getwxacode / getwxacodeunlimit）并保存为 PNG';

    public function handle(): int
    {
        $appCode = (string) $this->argument('app_code');
        $outputPath = (string) $this->option('output');

        if ($outputPath === '') {
            $outputPath = storage_path('app/miniprogram-qrcode-'.date('YmdHis').'.png');
        }

        try {
            $utils = WechatMiniProgramUtils::forAppCode($appCode);

            if ($this->option('unlimited')) {
                $scene = trim((string) $this->option('scene'));
                if ($scene === '') {
                    $this->components->error('使用 --unlimited 时必须提供 --scene');

                    return self::FAILURE;
                }

                $payload = [
                    'scene' => $scene,
                    'width' => (int) $this->option('width'),
                ];
                $page = trim((string) $this->option('page'));
                if ($page !== '') {
                    $payload['page'] = $page;
                }

                $this->info('请求 getwxacodeunlimit …');
                $binary = $utils->getUnlimitedQRCode($payload);
            } else {
                $payload = [
                    'path' => (string) $this->option('path'),
                    'width' => (int) $this->option('width'),
                ];

                $this->info('请求 getwxacode …');
                $binary = $utils->getQRCode($payload);
            }

            if (@file_put_contents($outputPath, $binary) === false) {
                $this->components->error('写入文件失败：'.$outputPath);

                return self::FAILURE;
            }

            $this->components->info('已保存小程序码：'.$outputPath);
            $this->line('大小：'.strlen($binary).' bytes');

            return self::SUCCESS;
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        } catch (HttpException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());
            if ($this->output->isVerbose()) {
                $this->line($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }
}
