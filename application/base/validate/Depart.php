<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 11:38
 */
namespace app\base\validate;
use think\Validate;

class Depart extends Validate{
    protected $rule=[
        'depart_title'=>'require|unique:depart',
    ];
    protected $message=[
        'depart_title.require'=>'请输入部门名称',
        'depart_title.unique'=>'部门已存在'
    ];
    protected $scene=[
        'new'=>['depart_title'],
        'edit'=>['depart_title']
    ];
}