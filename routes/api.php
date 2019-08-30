<?php

use Illuminate\Http\Request;
use App\Http\Middleware\CheckAge;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/posts/fetch', 'PostController@fetch');

Route::namespace('Api\V1')->prefix('v1')->group(function (){


    //接口频率限制 用户每分钟访问频率不超过 60 次
    Route::middleware('throttle:60,1')->group(function (){
        // 发送短信验证码
        Route::post('verificationCodes', 'VerificationCodesController@store')->name('api.verificationCodes.store');

        //用户注册
        Route::post('register', 'UserController@store')->name('api.user.store');

        // 图片验证码
        Route::post('captchas', 'CaptchasController@store')
            ->name('api.captchas.store');
    });

    // 获取令牌- 密码登陆
    Route::post('authorizations', 'AuthorizationsController@store')
        ->name('api.authorizations.store');
    // 刷新令牌- 密码登陆
    Route::post('authorizations/current', 'AuthorizationsController@update')
        ->name('api.authorizations.update');

    //第三方登陆
    Route::post('socialStore', 'AuthorizationsController@socialStore')
        ->name('api.authorizations.socialStore');


    //需要授权认证的路由
    Route::middleware('auth:api')->group(function (){

        //获取用户信息
        Route::get('users', 'UserController@me');

        //登出
        Route::delete('/user/logout','AuthorizationsController@destroy');
    });


});
