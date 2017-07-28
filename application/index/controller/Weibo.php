<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/17 0017
 * Time: 13:02
 */

namespace app\index\controller;


class Weibo extends Common
{

    /**
     * 信息管理
     * @return string
     */
    public function index()
    {
        return $this->view->fetch();
    }

    public function credit_rule()
    {
        return $this->view->fetch();
    }

    public function level_uid1()
    {
        return $this->view->fetch();
    }

    public function remind()
    {
        return $this->view->fetch();
    }
    public function uid()
    {
        return $this->view->fetch();
    }

    public function weibo_password()
    {
        return $this->view->fetch();
    }

    public function weibo()
    {
        return $this->view->fetch();
    }
    public function pesrsonal()
    {
        return $this->view->fetch();
    }
    public function pesrsonal_avatar()
    {
        return $this->view->fetch();
    }
    public function personal_follower()
    {
        return $this->view->fetch();
    }
    public function perisonal_following()
    {
        return $this->view->fetch();
    }
    public function personal_info()
    {
        return $this->view->fetch();
    }
    public function personal_index()
    {
        return $this->view->fetch();
    }
    public function personal_weibo()
    {
        return $this->view->fetch();
    }
    public function pesonal_history()
    {
        return $this->view->fetch();
    }
    public function credit()
    {
        return $this->view->fetch();
    }
    public function following()
    {
        return $this->view->fetch();
    }

    public function follower()
    {
        return $this->view->fetch();
    }

}