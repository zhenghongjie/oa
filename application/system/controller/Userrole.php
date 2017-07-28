<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 17:19
 * 功能：角色权限管理
 */
namespace app\system\controller;
use think\Db;

class Userrole extends Common{
    /**
     * 功能：首页
     * @return string
     */
    public function index(){
        $map['role_id'] = ['gt',0];
        $role_list = Db::name('user_role')
            ->where($map)
            ->select();
        $this->view->assign('role',$role_list);
        $map1['user_role_id'] = ['gt',0];
        return  $this->view->fetch();
    }

    /**
     * 功能：添加角色
     * @return string|void
     */
    public function new_role(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $validate_role = new \app\base\validate\Role();
            $result = $validate_role->scene('new')->check($post_data);
            if(!$result){
                return $this->error($validate_role->getError());
            }
            $res = Db::name('user_role')->insertGetId($post_data);
            if(false !== $res){
                return $this->success('设置成功',url('index'));
            }else{
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            return $this->view->fetch();
        }
    }

    /**
     * 编辑角色
     * @return string|void
     */
    public function edit_role(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $validate_role = new \app\base\validate\Role();
            $result = $validate_role->scene('edit')->check($post_data);
            if(!$result){
                return $this->error($validate_role->getError());
            }
            $res = Db::name('user_role')->update($post_data);
            if(false !== $res){
                return $this->success('设置成功',url('index'));
            }else{
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            $role_id = input('role_id');
            if(intval($role_id) == 0){
                return $this->error('请指定要编辑的角色');
            }
            $map['role_id'] = ['eq',$role_id];
            $role_info = Db::name('user_role')->where($map)->find();
            if(!$role_info){
                return $this->error('角色不存在');
            }
            $this->view->assign('role_info',$role_info);
            return $this->view->fetch();
        }
    }

    /**
     * 功能：删除角色
     */
    public function delete_role(){
        if('POST' == $this->method){
            $role_id = input('post.role_id');
            if(intval($role_id) == 0){
                return $this->error('请指定要删除的角色');
            }
            Db::startTrans();
            try{
                $map1['role_id'] = ['eq',$role_id];
                Db::name('user_role')->where($map1)->delete();
                $map2['user_role_id'] = ['eq',$role_id];
                Db::name('user')->where($map2)->setField('user_role_id',0);
                Db::commit();
                return $this->success('设置成功',url('index'));
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            return $this->error('不正确的请求方式');
        }
    }

    /**
     * 功能：角色成员管理
     */
    public function set_role_user(){
        if('POST' == $this->method){
            $role_id = input('post.role_id');
            if(intval($role_id) == 0){
                return $this->error('请指定角色');
            }
            $post_data = input('post.');
            $user_list = $post_data['user_list'];

            Db::startTrans();
            try{
                if(count($user_list) == 0){
                    $map2['user_role_id'] = ['eq',$role_id];
                    Db::name('user')->where($map2)->setField('user_role_id',0);
                }else{
                    $map1['user_id'] = ['in',$user_list];
                    Db::name('user')->where($map1)->setField('user_role_id',$role_id);
                    $map3['user_id'] = ['not in',$user_list];
                    $map3['user_role_id'] = ['eq',$role_id];
                    Db::name('user')->where($map3)->setField('user_role_id',0);
                }
                Db::commit();
                return $this->success('设置成功',url('index'));
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            $all_user = Db::name('user')->where(1)->select();
            $this->view->assign('all_user',$all_user);
            $role_id = input('role_id');
            if(intval($role_id) == 0){
                return $this->error('请指定角色');
            }
            $map1['user_role_id'] = ['eq',$role_id];
            $role_user = Db::name('user')->where($map1)->select();
            $this->view->assign('role_user',$role_user);
           return $this->view->fetch();
        }
    }
    public function set_role_rule(){
        if('POST' == $this->method){
            $post_data = input('post.');
            dump($post_data);
            exit;
        }else if('GET'== $this->method){
            return $this->view->fetch();
        }

    }
}