<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/27
 * Time: 16:06
 */
namespace app\base\validate;
use think\Validate;

class Mail extends Validate{
    protected $rule=[
        'mail_title'=>'require',
        'mail_receive'=>'require',
        'mail_content'=>'require'
    ];
    protected $message=[
        'mail_title.require'=>'请指定邮件标题',
        'mail_receive.require'=>'请指定发布范围',
        'mail_content.require'=>'请指定邮件内容',
    ];
    protected $scene=[
        'new'=>['mail_title','mail_receive','mail_content'],
        'edit'=>['mail_title','mail_receive','mail_content']
    ];
}