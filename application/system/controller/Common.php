<?php
namespace app\system\controller;
use think\Controller;

class Common extends Controller{
    protected $admin_id;
    protected $admin_is_super;
    protected $page_num = 30;
    protected $method;
    function _initialize()
    {
        parent::_initialize();
        $this->method = request()->method();
        $this->admin_id = session('admin_id');//用户信息
        $this->admin_is_super = session('admin_is_super');//超级管理员
        if(intval($this->admin_id) == 0 || $this->admin_is_super == 0){
            return $this->redirect(url('login/login'));
        }
    }
}