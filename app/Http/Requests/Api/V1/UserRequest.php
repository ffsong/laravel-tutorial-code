<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|between:3,25|regex:/^[A-Za-z0-9\-\_]+$/|unique:users,name',
            'password' => 'required|string|min:6',
            'verification_key' => 'required|string',
            'verification_code' => 'required|string',
        ];

    }

    public function attributes()
    {
        return [
            'verification_key' => '短信验证码 key',
            'verification_code' => '短信验证码',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '用户名必须填写',
            'name.between' => '用户名应为3-25个字符',
            'name.regex' => '用户名格式为数字字母下划线',
            'name.unique' => '用户名已存在',
            'password.required' => '密码不能为空',
            'password.min' => '密码最少6个字符',
            'verification_code.required' => '验证码必填',
            'verification_code.string' => '验证码格式错误',
        ];
    }

}
