<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/12
 * Time: 15:08
 */
namespace  app\system\controller;
class  Index extends Common{
    public function index(){
        return $this->redirect(url('station/index'));
    }
}