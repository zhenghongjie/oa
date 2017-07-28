<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 10:08
 * 功能：岗位验证器
 */
namespace app\base\validate;
use think\Validate;

class Station extends Validate{
    protected $rule=[
        'station_title'=>'require|unique:station'
    ];
    protected $message=[
        'station_title.require'=>'请输入岗位名称',
        'station_title.unique'=>'岗位名称已经存在'
    ];
    protected $scene = [
        'new'=>['station_title'],
        'edit'=>['station_title']
    ];
}