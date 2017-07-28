<?php
namespace app\index\controller;
class  Officialdoc extends Common{
    /**
     * 公文管理
     * @return string
     */
    public function index(){
        return $this->view->fetch();
    }

    public function show_docid300(){
        return $this->view->fetch();
    }
    /**
     * 功能：起草公文
     */
    public function add_official(){
        if('POST' == $this->method){
            $post_data = input('post.');
            dump($post_data);
        }else if('GET' == $this->method){
            return $this->view->fetch();
        }

    }

    /**
     * 功能：编辑公文
     * @return string
     */
    public function official_draft(){
        if('POST' == $this->method){
            $post_data = input('post.');
            dump($post_data);
        }else if('GET' == $this->method){
            return $this->view->fetch();
        }
    }

    public function official_tid1(){
        if('POST' == $this->method){
            $post_data = input('post.');
            dump($post_data);
        }else if('GET' == $this->method){
            return $this->view->fetch();
        }
    }
    public function official_nosign(){
        if('POST' == $this->method){
            $post_data = input('post.');
            dump($post_data);
        }else if('GET' == $this->method){
            return $this->view->fetch();
        }
    }
    public function officialdoc_notallow(){
        if('POST' == $this->method){
            $post_data = input('post.');
            dump($post_data);
        }else if('GET' == $this->method){
            return $this->view->fetch();
        }
    }

    public function official_catid1(){
        if('POST' == $this->method){
            $post_data = input('post.');
            dump($post_data);
        }else if('GET' == $this->method){
            return $this->view->fetch();
        }
    }

    public function officialdoc_catid2(){
        if('POST' == $this->method){
            $post_data = input('post.');
            dump($post_data);
        }else if('GET' == $this->method){
            return $this->view->fetch();
        }
    }
    public function show_docid113(){
        if('POST' == $this->method){
            $post_data = input('post.');
            dump($post_data);
        }else if('GET' == $this->method){
            return $this->view->fetch();
        }
    }
    public function officialdoc_sign(){
        if('POST' == $this->method){
            $post_data = input('post.');
            dump($post_data);
        }else if('GET' == $this->method){
            return $this->view->fetch();
        }
    }


    /**
     * 功能：删除公文
     * @return string
     */
    public function delete_official(){
        if('POST' == $this->method){
            $post_data = input('post.');
            dump($post_data);
        }else if('GET' == $this->method){
            return $this->view->fetch();
        }
    }
}