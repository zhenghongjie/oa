<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 10:50
 * 功能：岗位分类验证器
 */
namespace app\base\validate;
use think\Validate;
class Stationcate extends Validate{
    protected $rule=[
        'c_title'=>'require|unique:station_cate'
    ];
    protected $message=[
        'c_title.require'=>'请输入岗位分类名称',
        'c_title.unique'=>'岗位分类名称已经存在'
    ];
    protected $scene = [
        'new'=>['c_title'],
        'edit'=>['c_title']
    ];
}