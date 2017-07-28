<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class NewIndex extends Common
{
    /**
     * 首页
     * @return string
     */
    public function index()
    {
        $this->view->assign('user_info',$this->user_info);
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
