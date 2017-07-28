<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class Index extends Common
{
    /**
     * 首页
     * @return string
     */
    public function index()
    {
        $user_id = $this->user_id;
        $map_doc['r.r_id'] = ['gt',0];
        $map_doc['r.r_user_id'] = ['eq',$user_id];
        $doc_info = Db::name('doc_read')->alias('r')->join('doc d','d.doc_id = r.r_doc')->join('user u','u.user_id = d.doc_user_id','left')->where($map_doc)->order('r_id DESC')->field('r.*,u.user_username,d.*')->limit(3)->select();
        $map_article['r.r_id'] = ['gt',0];
        $map_article['r.r_user_id'] = ['eq',$user_id];
        $article_info = Db::name('article_read')->alias('r')->join('article a','a.article_id = r.r_article_id')->join('user u','u.user_id = a.article_user_id','left')->where($map_article)->order('r_id DESC')->field('r.*,u.user_username,a.*')->limit(3)->select();
        $map_mail['r.r_id'] = ['gt',0];
        $map_mail['r.r_uid'] = ['eq',$user_id];
        $mail_info_receive = Db::name('mail_read')->alias('r')->join('mail m','m.mail_id = r.r_mail_id')->join('user u','u.user_id = m.mail_user_id','left')->where($map_mail)->order('r_id DESC')->field('r.*,u.user_username,m.*')->limit(3)->select();
        $map_mail['r_time'] = ['eq',0];
        $mail_info_unread = Db::name('mail_read')->alias('r')->join('mail m','m.mail_id = r.r_mail_id')->join('user u','u.user_id = m.mail_user_id','left')->where($map_mail)->order('r_id DESC')->field('r.*,u.user_username,m.*')->limit(3)->select();
        unset($map_mail['r_time']);
        $map_mail['r_tab'] = ['eq',2];
        $mail_info_ready = Db::name('mail_read')->alias('r')->join('mail m','m.mail_id = r.r_mail_id')->join('user u','u.user_id = m.mail_user_id','left')->where($map_mail)->order('r_id DESC')->field('r.*,u.user_username,m.*')->limit(3)->select();
        $map_0['r.r_id'] = ['gt',0];//需要操作的ID大于0
        if($this->user_is_super == 0){ //不是超级管理员的情况下
            $map_0['r.r_user_id'] = ['eq',$this->user_id];//需要当前的用户读取的
        }
        $map_0['r.r_action'] = ['neq',3];
        $no_sign_count = Db::name('doc_read')->alias('r')->where($map_0)->group('r_doc')->count();//未签收
        $this->view->assign('no_sign',$no_sign_count);
        $this->view->assign('user_info',$this->user_info);
        $this->view->assign('doc_info',$doc_info);
        $this->view->assign('article_info',$article_info);
        $this->view->assign('mail_info_receive',$mail_info_receive);
        $this->view->assign('mail_info_unread',$mail_info_unread);
        $this->view->assign('mail_info_ready',$mail_info_ready);
        $this->view->assign('publish_scope', $this->get_publish_user());
        return $this->view->fetch();
    }

    public function diary_add()
    {
        $this->view->assign('user_info',$this->user_info);
        return $this->view->fetch();
    }
    public function diary_share()
    {
        $this->view->assign('user_info',$this->user_info);
        return $this->view->fetch();
    }
    public function diary_attention()
    {
        $this->view->assign('user_info',$this->user_info);
        return $this->view->fetch();
    }
    public function diary_default()
    {
        $this->view->assign('user_info',$this->user_info);
        return $this->view->fetch();
    }
    public function diary_review()
    {
        $this->view->assign('user_info',$this->user_info);
        return $this->view->fetch();
    }
    public function calendar()
    {
        $this->view->assign('user_info',$this->user_info);
        return $this->view->fetch();
    }
    public function calendar_task()
    {
        $this->view->assign('user_info',$this->user_info);
        return $this->view->fetch();
    }
    public function calendar_loop()
    {
        $this->view->assign('user_info',$this->user_info);
        return $this->view->fetch();
    }
}
