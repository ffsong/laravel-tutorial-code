<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\V1\CaptchaRequest;
use Gregwar\Captcha\CaptchaBuilder;

class CaptchasController extends BaseController
{
    /**
     * @api {post} v1/captchas 获取图片验证码
     * @apiVersion 1.0.0
     * @apiName captchas
     * @apiGroup Captc
     *
     * @apiDescription 获取图片验证码
     *
     * @apiParam  {String} phone 参数
     * @apiParamExample  {Object} 请求示例:
     * {
     *     phone:18226988784,
     * }
     *
     * @apiSuccess (返回值) {string} captcha_key 图片验明证key
     * @apiSuccess (返回值) {string} captcha_image_content 验明证图片
     * @apiSuccess (返回值) {string} expired_at 过期时间
     *
     * @apiSuccessExample {json} 成功示例:
     * {
        "code": 200,
        "status": "success",
        "data": {
            "code": "测试验证码vt9gn",
            "captcha_key": "captcha-xts3G8ggDYRH4P4",
            "captcha_image_content": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAA",
             * "expired_at": "2019-03-04 16:11:00",
            }
        }
     *
     * @apiErrorExample (json) 错误示例:
     *     {"code":400,"status":"error","data":'错误'}
     */
    public function store(CaptchaRequest $request, CaptchaBuilder $captchaBuilder)
    {
        $key = 'captcha-'.str_random(15);
        $phone = $request->phone;

        $captcha = $captchaBuilder->build();
        $expiredAt = now()->addMinutes(2);
        \Cache::put($key, ['phone' => $phone, 'code' => $captcha->getPhrase()], $expiredAt);

        $result = [
            'code' => $captcha->getPhrase(), //方便测试加上图片验证码
            'captcha_key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
            'captcha_image_content' => $captcha->inline()
        ];

        return $this->message($result);
    }
}
