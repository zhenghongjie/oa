<?php
namespace app\index\controller;
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
            session('user_id',$info['user_id']);
            session('user_is_super',$info['user_is_super']);
            session('user_info',$info);
            return $this->redirect(url('index/index'));
        }else if('GET' == $this->request->method()){
            return $this->view->fetch();
        }
    }
    public function logout(){
        session('user_id',null);
        session('user_is_super',null);
        session('user_info',null);
        return $this->redirect(url('login'));
    }


}