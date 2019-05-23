<?php
/**
 * 用户登陆
 */
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Api\BaseController;
use App\User;
//use App\Http\Requests\Api\V1\AuthorizationRequest;
use Zend\Diactoros\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\AuthorizationServer;
use Illuminate\Http\Request;
use App\Helpers\PassportToken;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;

class AuthorizationsController extends BaseController
{

    use PassportToken;

    /**
     * @api {post} v1/authorizations 密码模式用户登陆
     * @apiVersion 1.0.0
     * @apiName authorizations
     * @apiGroup User
     *
     * @apiDescription 用户登录后，返回登陆凭证
     *
     * @apiParam  {String} grant_type  授权类型（password）
     * @apiParam  {Int} client_id  客户端id（2）
     * @apiParam  {String} client_secret  客户端密钥（17SCab7b7GPMpHU9x1tKQuBZsd7VTiHBBeu3iq2E）
     * @apiParam  {String} username  用户名
     * @apiParam  {String} password  密码
     * @apiParamExample  {Object} 请求示例:
     * {
     *     grant_type: 'password',
     *     client_id: 2,
     *     client_secret: '17SCab7b7GPMpHU9x1tKQuBZsd7VTiHBBeu3iq2E',
     *     username: 18298699875,
     *     password: 123566,
     * }
     *
     * @apiSuccess (返回值) {string} token_type 令牌类型
     * @apiSuccess (返回值) {string} expires_in 过期时间
     * @apiSuccess (返回值) {string} access_token 访问令牌
     * @apiSuccess (返回值) {string} refresh_token 刷新令牌
     *
     * @apiSuccessExample {json} 成功示例:
     * {
        "code": 200,
        "status": "success",
        "data": {
            "token_type": "Bearer",
            "expires_in": 1296000,
            "access_token": "eyJ0",
            "refresh_token": "wrer"
            }
     * }
     *
     * @apiErrorExample (json) 错误示例:
     *     {"code": 403,"status":'error',"data":"密码错误"}
     */

    public function store(AuthorizationRequest $originRequest, AuthorizationServer $server, ServerRequestInterface $serverRequest)
    {
        try {
            //返回的 Response 是 Zend\Diactoros\Respnose 的实例
            $data = $server->respondToAccessTokenRequest($serverRequest, new Psr7Response);
            return $this->message(json_decode($data->getBody(),true));
        } catch(OAuthServerException $e) {

            return $this->failed($e->getMessage(),403);
        }
    }
    /**
     * 第三防登陆入口
     *
     * return {"token_type":Bearer,"expires_in": 1296000, "access_token": "eyJ0eXAiOiJKV1QiLC","refresh_token":"eyJ0eXAiOiJKV1QiLC"}
     */

    public function socialStore()
    {
        $user = User::find(1);
        $result = $this->getBearerTokenByUser($user, '5', false);

        return $this->message($result);
    }


    /**
     * @api {post} v1/authorizations/current 更新令牌
     * @apiVersion 1.0.0
     * @apiName authorizations/current
     * @apiGroup User
     *
     * @apiDescription 重新获取验证令牌
     *
     * @apiParam  {String} grant_type  授权类型（refresh_token）
     * @apiParam  {Int} client_id  客户端id（2）
     * @apiParam  {String} client_secret  客户端密钥（17SCab7b7GPMpHU9x1tKQuBZsd7VTiHBBeu3iq2E）
     * @apiParam  {String} refresh_token  刷新令牌
     * @apiParamExample  {Object} 请求示例:
     * {
     *     grant_type: 'refresh_token',
     *     client_id: 2,
     *     client_secret: '17SCab7b7GPMpHU9x1tKQuBZsd7VTiHBBeu3iq2E',
     *     refresh_token: 'wrer',
     * }
     *
     * @apiSuccess (返回值) {string} token_type 令牌类型
     * @apiSuccess (返回值) {string} expires_in 过期时间
     * @apiSuccess (返回值) {string} access_token 访问令牌
     * @apiSuccess (返回值) {string} refresh_token 刷新令牌
     *
     * @apiSuccessExample {json} 成功示例:
     * {
        "code": 200,
        "status": "success",
        "data": {
                "token_type": "Bearer",
                "expires_in": 1296000,
                "access_token": "eyJ0",
                "refresh_token": "wrer"
            }
     * }
     * @apiErrorExample (json) 错误示例:
     *     {"code":403,"status":"没权限","data":{}}
     */
    public function update(AuthorizationServer $server, ServerRequestInterface $serverRequest)
    {
        try {

            $data = $server->respondToAccessTokenRequest($serverRequest, new Psr7Response);
            return $this->message(json_decode($data->getBody(),true));

        } catch(OAuthServerException $e) {

            return $this->failed($e->getMessage(),403);
        }
    }
    /**
     * @api {delete} v1/user/logout 退出登陆
     * @apiVersion 1.0.0
     * @apiName logout
     * @apiGroup User
     *
     * @apiDescription 退出登陆
     *
     * @apiHeader {String} Authorization Bearer +空格+ access_token（访问令牌）
     * @apiHeaderExample {json} 头部示例:
     *     {
     *       "Authorization": "Bearer eyJ0eXAi"
     *     }
     *
     * @apiSuccess (204) {string} code 参数
     *
     * @apiSuccessExample {json} 成功示例:
     * {"code": 200,"status": "success","data": {}}
     *
     * @apiError (错误) {string} data 用户未登陆
     * @apiErrorExample {json} 错误示例:
     * {
            "code": 401,
            "status": "未登录",
            "data": "Unauthenticated."
        }
     */
    public function destroy(Request $request)
    {
        if (!empty($request->user())) {
            $request->user()->token()->revoke();
            return $this->message('logout');
        } else {
            return $this->failed('The token is invalid',403);
        }
    }
}