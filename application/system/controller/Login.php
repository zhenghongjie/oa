<?php
namespace app\system\controller;
use think\Controller;
use think\Db;
class Login extends Controller{
    public function login(){
        if('POST' == $this->request->method()){
            $username = input('post.username');
            $password = input('post.password');
            if(trim($username) == ''){
                return $this->error('请输入登录名');
            }
            if(trim($password) == ''){
                return $this->error('请输入密码');
            }
            $map['user_username'] = ['eq',$username];
            $info = Db::name('user')->where($map)->find();
            if(!$info){
                return $this->error('用户不存在');
            }
            if($info['user_password'] != md5($password)){
                return $this->error('密码错误');
            }
            if($info['user_is_super'] == 0){
                return $this->error('权限不足');
            }
            session('admin_id',$info['user_id']);
            session('admin_is_super',$info['user_is_super']);
            session('admin_info',$info);
            return $this->redirect(url('index/index'));
        }else if('GET' == $this->request->method()){
            return $this->view->fetch();
        }
    }
    public function logout(){
        session('admin_id',null);
        session('admin_is_super',null);
        session('admin_info',null);
        return $this->redirect(url('login'));
    }


}