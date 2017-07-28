<?php
/**
 * 部门管理
 */
namespace app\index\controller;
class Depart extends Common{
    public function index(){
        return $this->view->fetch();
    }
    public function add_depart(){
        if ('POST' == $this->method) {
            $post_data = input('post.');
            dump($post_data);
        } else if ('GET' == $this->method) {
            return $this->view->fetch();
        }
    }
    public function edit_part(){
        if ('POST' == $this->method) {
            $post_data = input('post.');
            dump($post_data);
        } else if ('GET' == $this->method) {
            return $this->view->fetch();
        }
    }
    public function delete_depart(){
        if ('POST' == $this->method) {
            $post_data = input('post.');
            dump($post_data);
        } else if ('GET' == $this->method) {
            return $this->view->fetch();
        }
    }
}