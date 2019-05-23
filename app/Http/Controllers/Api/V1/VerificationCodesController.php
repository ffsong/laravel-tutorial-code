<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\V1\VerificationCodeRequest;

class VerificationCodesController extends BaseController
{

    /**
     * @api {post} v1/verificationCodes 发送短信验证码
     * @apiVersion 1.0.0
     * @apiName store
     * @apiGroup Verifica
     * @apiPermission 登录注册
     *
     * @apiDescription 注册发送短信
     *
     * @apiParam  {String} captcha_key 图片验证码 key
     * @apiParam  {String} captcha_code 图片验证码
     * @apiParamExample  {Object} Request-Example:
     * {
     *     captcha_key:'captcha-Hmhf7mKkBDlqxst',
     *     captcha_code:'3554gg',
     * }
     *
     * @apiSuccess (返回值) {string} verification_key 验证码key
     * @apiSuccess (返回值) {Object} expired_at 失效时间
     * @apiSuccess (返回值) {Object} code 验证码
     *
     * @apiSuccessExample {json} 成功示例:
     * {
        "code": 200,
        "status": success
        "data": {
            "verification_key": "18223699471",
            "expired_at": "2019-03-04 15:08:07",
            "code": "6688"
            }
        }
     *
     * @apiErrorExample (json) 错误示例:
     *     {"code":400,"status": error,"data":"验证码错误"}
     */
    public function store(VerificationCodeRequest $request)
    {
        $captchaData = \Cache::get($request->captcha_key);

        if (!$captchaData) {
            return $this->failed('图片验证码已失效', 422);
        }

        if (!hash_equals($captchaData['code'], $request->captcha_code)) {
            // 验证错误就清除缓存
            \Cache::forget($request->captcha_key);
            return $this->failed('验证码错误');
        }

        $phone = $captchaData['phone'];

        // 生成4位随机数，左侧补0
        $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);

        //日志代替短信发送
        \Log::info("【Lbbs社区】您的验证码是{$code}。如非本人操作，请忽略本短信");

        $key = 'verificationCode_'.str_random(15);
        $expiredAt = now()->addMinutes(10);
        // 缓存验证码 10分钟过期。
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);

        //方便测返回手机 短信验证码
        $data = [
            'verification_key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
            'code' => $code
        ];

        return $this->message($data);
    }
}
