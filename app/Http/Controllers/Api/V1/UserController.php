<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\V1\UserRequest;
use App\User;
use GuzzleHttp\Client;

class UserController extends BaseController
{

    private $http;
    private $url; //获取access_token 授权地址

    public function __construct(Client $client)
    {
        $this->http = $client;
        $this->url = config('app.url').'/oauth/token';
    }

    /**
     * @api {post} v1/store 用户注册
     * @apiVersion 1.0.0
     * @apiName store
     * @apiGroup User
     *
     * @apiDescription 用户注册
     *
     * @apiParam  {String} name 用户名
     * @apiParam  {String} password 密码
     * @apiParam  {String} verification_key 短信key
     * @apiParam  {String} verification_code 短信验证码
     * @apiParamExample  {Object} 请求示例:
     * {
          name:'zxx1',
          password:'123456',
          verification_key:'zxx1ww',
          verification_code:0123,
     * }
     *
     * @apiSuccess (返回值) {string} token_type 令牌类型
     * @apiSuccess (返回值) {string} expires_in 过期时间
     * @apiSuccess (返回值) {string} access_token 访问令牌
     * @apiSuccess (返回值) {string} refresh_token 刷新令牌
     *
     * @apiSuccessExample {json} 成功示例:
      {
        "code": 200,
        "status": "success",
        "data": {
            "token_type": "Bearer",
            "expires_in": 1296000,
            "access_token": "eyJ0",
            "refresh_token": "wrer"
        }
      }
     *
     * @apiErrorExample (json) 错误示例:
     *     {"code":401,"status": "error","data":"用户认证失败"}
     */
    public function store(UserRequest $request)
    {
        $verifyData = \Cache::get($request->verification_key);

        if (!$verifyData) {
            return $this->failed('验证码已失效', 422);
        }

        if (!hash_equals($verifyData['code'], $request->verification_code)) {
            // 返回401
            return $this->response->errorUnauthorized('验证码错误');
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->password),
        ]);

        //注册直接登陆返回登陆凭证
        try{
            $response = $this->http->post('http://api.test/oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => '5',
                    'client_secret' => 'PqCvAPPgUMolZ5SHu9hjN1CtsvzZSu5PCJMARFx8',
                    'username' => $user->phone,
                    'password' => $request->password,
                    'scope' => '',
                ],
            ]);
        }catch (\Exception $e){
            throw new  \App\Exceptions\ApiException($e->getMessage());
        }


        // 清除验证码缓存
        \Cache::forget($request->verification_key);

        return $this->message(json_decode((string) $response->getBody(), true));
    }


    /**
     * @api {get} v1/index 获取用户信息
     * @apiVersion 1.0.0
     * @apiName index
     * @apiGroup User
     *
     * @apiDescription 获取用户信息
     *
     * @apiHeader {String} Authorization Bearer +空格+ access_token（访问令牌）
     * @apiHeaderExample {json} 头部示例:
     *     {
     *       "Authorization": "Bearer eyJ0eXAi"
     *     }
     *
     * @apiSuccess (返回值) {string} id 参数
     *
     * @apiSuccessExample {json} 成功示例:
    {
    "code": 200,
    "status": "success",
    "data": {
        "id": 12,
        "name": "zxx1",
        "phone": "18223699471",
        "email": null,
        "email_verified_at": null,
        "created_at": "2019-03-04 14:59:00",
        "updated_at": "2019-03-04 14:59:00",
        "avatar": "https://iocaffcdn.phphub.org/uploads/images/201710/30/1/TrJS40Ey5k.png",
        "introduction": null,
        "notification_count": 0
        }
    }
     *
     * @apiErrorExample (json) 错误示例:
     *     {"code":-1,"status":"error","data":{}}
     */
    public function me(Request $request)
    {
        return $this->message($request->user());
    }

}
