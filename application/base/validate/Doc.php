<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/27
 * Time: 16:06
 */
namespace app\base\validate;
use think\Validate;

class Doc extends Validate{
    protected $rule=[
        'doc_title'=>'require',
        'doc_no'=>'require',
        'doc_publish'=>'require',
        'doc_content'=>'require'
    ];
    protected $message=[
        'doc_title.require'=>'请指定公文标题',
        'doc_no.require'=>'请指定公文号',
        'doc_publish.require'=>'请指定发布范围',
        'doc_content.require'=>'请指定内容',
    ];
    protected $scene=[
        'new'=>['doc_title','doc_no','doc_publish','doc_content'],
        'edit'=>['doc_title','doc_no','doc_publish','doc_content']
    ];
}