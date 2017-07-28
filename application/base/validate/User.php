<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 15:01
 * 功能：用户验证器
 */
namespace app\base\validate;
use think\Validate;

class User extends Validate{
    protected $rule=[
        'user_mobile'=>'require|unique:user|regex:1\d{10}',
        'user_username'=>'require|unique:user',
        'user_password'=>'require',
        'user_real_name'=>'require',
        'user_email'=>'email'
    ];
    protected $message=[
        'user_mobile.require'=>'手机必填',
        'user_mobile.unique'=>'手机已绑定',
        'user_mobile.regex'=>'手机格式不正确',
        'user_username.require'=>'用户名必填',
        'user_username.unique'=>'用户名已存在',
        'user_password.require'=>'请输入密码',
        'user_real_name.require'=>'请填写真实姓名',
        'user_email.email'=>'邮箱格式不正确'
    ];
    protected $scene=[
        'new'=>['user_mobile','user_username','user_password','user_real_name','user_email'],
        'edit'=>['user_mobile','user_username','user_real_name','user_email'],
    ];
}