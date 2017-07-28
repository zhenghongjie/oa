<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/27
 * Time: 16:06
 */
namespace app\base\validate;
use think\Validate;

class Article extends Validate{
    protected $rule=[
        'article_title'=>'require',
        'article_publish'=>'require',
        'article_content'=>'require',
        'article_images'=>'require',
        'article_url'=>'require',
    ];
    protected $message=[
        'article_title.require'=>'请指定信息标题',
        'article_publish.require'=>'请指定发布范围',
        'article_content.require'=>'请指定内容',
        'article_images.require'=>'请指定图片',
        'article_url.require'=>'请指定连接地址',
    ];
    protected $scene=[
        'new_content'=>['article_title','article_publish','article_content'],
        'new_images'=>['article_title','article_publish','article_images'],
        'new_url'=>['article_title','article_publish','article_url'],
        'edit'=>['article_title','article_publish','article_content']
    ];
}