<?php

namespace App\Support;

use App\Models\Miniprogram;
use EasyWeChat\Kernel\Exceptions\HttpException;
use EasyWeChat\Kernel\HttpClient\Response as EasyWeChatResponse;
use EasyWeChat\MiniApp\Application;
use EasyWeChat\MiniApp\Decryptor;
use EasyWeChat\Kernel\Exceptions\DecryptException;
use InvalidArgumentException;

/**
 * 按后台配置的 `app_code` 加载小程序参数，基于 EasyWeChat 小程序 Application 封装常用接口。
 *
 * @see https://easywechat.com/6.x/mini-app/
 * @see https://developers.weixin.qq.com/miniprogram/dev/server/API/user-login/api_code2session.html
 */
final class WechatMiniProgramUtils
{
    public function __construct(
        protected Application $application,
        protected Miniprogram $miniprogram,
    ) {}

    /**
     * 从数据库按 `app_code` 加载启用中的小程序（status = 1）。
     */
    public static function forAppCode(string $appCode): self
    {
        $row = Miniprogram::query()
            ->where('app_code', $appCode)
            ->where('status', 1)
            ->first();

        if (! $row) {
            throw new InvalidArgumentException("未找到或未启用的小程序标识 app_code：{$appCode}");
        }

        return self::fromModel($row);
    }

    /**
     * 使用已加载的模型实例（不校验 status，便于脚本或特殊场景）。
     */
    public static function fromModel(Miniprogram $miniprogram): self
    {
        self::assertCredentialComplete($miniprogram);

        return new self(
            new Application(self::configFromModel($miniprogram)),
            $miniprogram,
        );
    }

    public function application(): Application
    {
        return $this->application;
    }

    public function miniprogram(): Miniprogram
    {
        return $this->miniprogram;
    }

    /**
     * 登录凭证校验（code2Session / jscode2session）。
     *
     * 成功时返回含 openid、session_key，可能含 unionid。
     *
     * @return array{openid?:string,session_key?:string,unionid?:string,errcode?:int,errmsg?:string}
     *
     * @throws HttpException
     */
    public function code2Session(string $code): array
    {
        return $this->application->getUtils()->codeToSession($code);
    }

    /**
     * 手机号快速验证组件：用前端 `getPhoneNumber` 拿到的 code 换取手机号信息。
     *
     * @see https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-info/phone-number/getPhoneNumber.html
     *
     * @return array<string, mixed> 微信返回体（含 phone_info）
     *
     * @throws HttpException
     */
    public function getPhoneNumber(string $code): array
    {
        $response = $this->application->createClient()->request('POST', 'wxa/business/getuserphonenumber', [
            'json' => [
                'code' => $code,
            ],
        ])->toArray(false);

        if (($response['errcode'] ?? 0) !== 0) {
            throw new HttpException('getPhoneNumber error: '.json_encode($response, JSON_UNESCAPED_UNICODE));
        }

        if (empty($response['phone_info'])) {
            throw new HttpException('getPhoneNumber error: '.json_encode($response, JSON_UNESCAPED_UNICODE));
        }

        return $response;
    }

    /**
     * 获取小程序码（有限 scene，适用于具体 path）。
     *
     * @param  array<string, mixed>  $payload  path、width、auto_color、line_color、is_hyaline 等，见微信文档「获取小程序码」
     *
     * @see https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/getQRCode.html
     */
    public function getQRCode(array $payload): string
    {
        $response = $this->application->createClient()->request('POST', 'wxa/getwxacode', [
            'json' => $payload,
        ])->throw(false);

        return $this->unwrapQrCodeImageContent($response);
    }

    /**
     * 获取不限制的小程序码（unlimited，适用于 scene）。
     *
     * @param  array<string, mixed>  $payload  scene、page、check_path、env_version、width 等，见微信文档
     *
     * @see https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/getUnlimitedQRCode.html
     */
    public function getUnlimitedQRCode(array $payload): string
    {
        $response = $this->application->createClient()->request('POST', 'wxa/getwxacodeunlimit', [
            'json' => $payload,
        ])->throw(false);

        return $this->unwrapQrCodeImageContent($response);
    }

    /**
     * 解密会话信息。
     * 
     * @throws DecryptException
     */
    public function decryptSession(string $sessionKey, string $iv, string $ciphertext): array
    {
        return Decryptor::decrypt($sessionKey, $iv, $ciphertext);
    }

    /**
     * @return array<string, mixed>
     */
    protected static function configFromModel(Miniprogram $m): array
    {
        return [
            'app_id' => $m->app_id,
            'secret' => $m->app_secret,
            'token' => $m->token ?? '',
            'aes_key' => $m->aes_key ?? '',
            'http' => [
                'throw' => true,
                'timeout' => 10,
            ],
        ];
    }

    protected static function assertCredentialComplete(Miniprogram $miniprogram): void
    {
        if (trim((string) $miniprogram->app_id) === '' || trim((string) $miniprogram->app_secret) === '') {
            throw new InvalidArgumentException('小程序 app_id 或 app_secret 未配置，无法调用微信接口');
        }
    }

    /**
     * 成功时为 PNG 等图片二进制；失败时微信返回 JSON，在此统一转为可读异常。
     */
    protected function unwrapQrCodeImageContent(EasyWeChatResponse $response): string
    {
        $raw = $response->getContent(false);
        $trim = ltrim($raw);

        if ($trim !== '' && str_starts_with($trim, '{')) {
            $json = json_decode($trim, true);
            $code = $json['errcode'] ?? -1;
            $msg = $json['errmsg'] ?? 'unknown';

            throw new HttpException("getQRCode error [{$code}]: {$msg}");
        }

        if ($raw === '') {
            throw new HttpException('getQRCode error: empty response body');
        }

        return $raw;
    }
}
