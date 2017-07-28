<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 17:24
 */
namespace app\base\validate;
use think\Validate;

class Role extends Validate{
    protected $rule=[
        'role_title'=>'require|unique:user_role'
    ];
    protected $message=[
        'role_title.require'=>'请指定角色',
        'role_title.unique'=>'角色已存在'
    ];
    protected $scene=[
        'new'=>['role_title'],
        'edit'=>['role_title'],
    ];
}