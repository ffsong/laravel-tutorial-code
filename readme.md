
## Laravel 接口通用
 
使用 Passport OAuth 认证


1. 跟新composer 

    `composer install`
   
2. 运行迁移文件
    
    `php artisan migrate`
    
3. 创建加密秘钥

    `php artisan passport:keys`

4. 创建客户端（passport:client 命令可以创建一个客户端，由于我们使用的是密码模式，所以需要增加 --password 参数。同时还可以增加 --name 参数为客户端起个名字）

    ` php artisan passport:client --password --name='laravel-api'`

5. 



> 默认注册流程:  1.填写手机号获取图片验证码 2.填写图片验证码发送短信验证码 3.填写短信验证码注册成功


