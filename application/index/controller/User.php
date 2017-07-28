<?php
namespace app\index\controller;
class User extends Common{
    /**
     * 首页
     * @return string
     */
    public function index(){
        return $this->view->fetch();
    }

    /**
     * 添加用户
     * @return string
     */
    public function add_user(){
        if ('POST' == $this->method) {
            $post_data = input('post.');
            dump($post_data);
        } else if ('GET' == $this->method) {
            return $this->view->fetch();
        }
    }

    /**
     * 功能：编辑用户
     * @return string
     */
    public function edit_user(){
        if ('POST' == $this->method) {
            $post_data = input('post.');
            dump($post_data);
        } else if ('GET' == $this->method) {
            return $this->view->fetch();
        }
    }

    /**
     * 角色列表
     * @return string
     */
    public function user_role(){
       return $this->view->fetch();
    }

    /**
     * 功能：添加角色
     * @return string
     */
    public function add_user_role(){
        if ('POST' == $this->method) {
            $post_data = input('post.');
            dump($post_data);
        } else if ('GET' == $this->method) {
            return $this->view->fetch();
        }
    }

    /**
     * 编辑角色
     * @return string
     */
    public function edit_user_role(){
        if ('POST' == $this->method) {
            $post_data = input('post.');
            dump($post_data);
        } else if ('GET' == $this->method) {
            return $this->view->fetch();
        }
    }

    /**
     * 删除角色
     * @return string
     */
    public function delete_user_role(){
        if ('POST' == $this->method) {
            $post_data = input('post.');
            dump($post_data);
        } else if ('GET' == $this->method) {
            return $this->view->fetch();
        }
    }

    /**
     * 功能;权限列表
     * @return string
     */
    public function user_rule(){
        return $this->view->fetch();
    }

    /**
     * 功能：添加权限
     * @return string
     */
    public function add_user_rule(){
        if ('POST' == $this->method) {
            $post_data = input('post.');
            dump($post_data);
        } else if ('GET' == $this->method) {
            return $this->view->fetch();
        }
    }

    /**
     * 编辑用户权限
     * @return string
     */
    public function edit_user_rule(){
        if ('POST' == $this->method) {
            $post_data = input('post.');
            dump($post_data);
        } else if ('GET' == $this->method) {
            return $this->view->fetch();
        }
    }

    /**
     * @return string
     * 删除权限
     */
    public function delete_user_rule(){
        if ('POST' == $this->method) {
            $post_data = input('post.');
            dump($post_data);
        } else if ('GET' == $this->method) {
            return $this->view->fetch();
        }
    }

    /**
     * 设置角色权限
     * @return string
     */
    public function set_role_rule(){
        if ('POST' == $this->method) {
            $post_data = input('post.');
            dump($post_data);
        } else if ('GET' == $this->method) {
            return $this->view->fetch();
        }
    }

    /**
     * 添加角色的用户
     * @return string
     */
    public function add_role_user(){
        if ('POST' == $this->method) {
            $post_data = input('post.');
            dump($post_data);
        } else if ('GET' == $this->method) {
            return $this->view->fetch();
        }
    }

}
