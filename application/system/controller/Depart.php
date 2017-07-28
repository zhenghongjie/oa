<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 9:25
 * function:部门用户管理
 */
namespace app\system\controller;
use think\Db;

class Depart extends Common{
    public function index(){
        $map1['d.depart_id'] = ['gt',0];
        $depart_list = Db::name('depart')
            ->alias('d')
            ->join('depart p','p.depart_id=d.depart_pid','left')
            ->where($map1)
            ->field('d.*,p.depart_title as p_title')
            ->order('d.depart_pid ASC,d.depart_sort ASC')
            ->select();
        $this->assign('depart',$depart_list);
        return $this->view->fetch();
    }
    public function user_list(){
        $map1['u.user_id'] = ['gt',0];
        $user_list = Db::name('user')
            ->alias('u')
            ->join('station s','u.user_station_id=s.station_id','left')
            ->join('user_role r','u.user_role_id=r.role_id','left')
            ->join('depart d','u.user_depart_id=d.depart_id','left')
            ->where($map1)
            ->field('u.*,r.role_title,s.station_title,d.depart_title')
            ->paginate($this->page_num,false,[
                'query'=>request()->param()
            ]);
        $this->assign('user_list',$user_list);
        return $this->view->fetch();
    }

    /**
     * 功能：新增用户
     */
    public function new_user(){
        if('POST' == $this->method){
            $post_data = input('post.');
            if(!isset($post_data['user_username']) || trim($post_data['user_username']) == ''){
                $post_data['user_username'] = $post_data['user_mobile'];
            }
            $validate_user = new \app\base\validate\User();
            $result = $validate_user->scene('new')->check($post_data);
            if(!$result){
                return $this->error($validate_user->getError());
            }
            $post_data['user_create_time'] = date('Y-m-d',time());
            $post_data['user_password'] = md5($post_data['user_password']);
            Db::startTrans();
            try{
                Db::name('user')->insertGetId($post_data);
                Db::name('station')->where(['station_id'=>$post_data['user_station_id']])->setInc('station_count',1);
                Db::commit();
                return $this->success('设置成功',url('user_list'));
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            $this->view->assign('depart_list',$this->get_all_depart());
            $this->view->assign('station_list',$this->get_all_station());
            $this->view->assign('role_list',$this->get_all_role());
            return $this->view->fetch();
        }
    }

    /**
     * 功能：编辑用户
     */
    public function edit_user(){
        if('POST' == $this->method){
            $post_data = input('post.');
            if(!isset($post_data['user_username']) || trim($post_data['user_username']) == ''){
                $post_data['user_username'] = $post_data['user_mobile'];
            }
            $validate_user = new \app\base\validate\User();
            $result = $validate_user->scene('edit')->check($post_data);
            if(!$result){
                return $this->error($validate_user->getError());
            }
            if(isset($post_data['user_password']) && trim($post_data['user_password']) != ''){
                $post_data['user_password'] = md5($post_data['user_password']);
            }
            $res = Db::name('user')->update($post_data);
            if(false !== $res){
                return $this->success('设置成功',url('user_list'));
            }else{
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            $user_id = input('user_id');
            $map['user_id'] = ['eq',$user_id];
            $user_info = Db::name('user')->where($map)->field('user_password',true)->find();
            $this->view->assign('user_info',$user_info);
            $this->view->assign('depart_list',$this->get_all_depart());
            $this->view->assign('station_list',$this->get_all_station());
            $this->view->assign('role_list',$this->get_all_role());
            return $this->view->fetch();
        }
    }
    private function get_all_depart(){
        $map['depart_id'] = ['gt',0];
        $depart_list = Db::name('depart')->where($map)->field('depart_id,depart_title,depart_pid')->order('depart_pid ASC')->select();
        return $depart_list;
    }
    private function get_all_station(){
        $map['station_id'] = ['gt',0];
        $station_list = Db::name('station')->where($map)->field('station_id,station_title')->order('station_sort ASC')->select();
        return $station_list;
    }
    private function get_all_role(){
        $map['role_id'] = ['gt',0];
        $role_list = Db::name('user_role')
            ->where($map)->select();

        return $role_list;
    }
    /**
     * 功能：新增部门
     */
    public function new_depart(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $validate_depart = new \app\base\validate\Depart();
            $result = $validate_depart->scene('new')->check($post_data);
            if(!$result){
                return $this->error($validate_depart->getError());
            }
            $res = Db::name('depart')->insertGetId($post_data);
            if(false !== $res){
                return $this->success('设置成功',url('index'));
            }else{
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            $this->view->assign('all_depart',$this->get_all_depart());
            return $this->view->fetch();
        }
    }

    /**
     * 功能：编辑部门
     */
    public function edit_depart(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $validate_depart = new \app\base\validate\Depart();
            $result = $validate_depart->scene('edit')->check($post_data);
            if(!$result){
                return $this->error($validate_depart->getError());
            }
            $res = Db::name('depart')->update($post_data);
            if(false !== $res){
                return $this->success('设置成功',url('index'));
            }else{
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            $depart_id = input('depart_id');
            if(intval($depart_id) == 0){
                return $this->error('请指定要编辑的部门');
            }
            $map['depart_id'] = ['eq',$depart_id];
            $depart_info = Db::name('depart')->where($map)->find();
            if(!$depart_info){
                return $this->error('部门不存在');
            }
            $this->view->assign('depart_info',$depart_info);
            $this->view->assign('all_depart',$this->get_all_depart());
            return $this->view->fetch();
        }
    }

    /**
     * 功能：删除部门
     */
    public function delete_depart(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $depart_id = $post_data['depart_id'];
            Db::startTrans();
            try{
                $map['depart_id'] = ['in',$depart_id];
                Db::name('depart')->where($map)->delete();
                $map1['user_depart_id'] = ['in',$depart_id];
                Db::name('user')->where($map1)->setField('user_depart_id',0);
                Db::commit();
                return $this->success('设置成功',url('index'));
            }catch(\PDOException $e){
                Db::rollback();
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            return $this->error('不正确的请求方式');
        }
    }

    /**
     * 部门用户管理
     * @return string
     */
    public function set_depart_user(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $depart_id = $post_data['depart_id'];
            if(intval($depart_id) == 0){
                return $this->error('请指定部门');
            }
            $user_list = $post_data['user_list'];
            Db::startTrans();
            try{
                if(count($user_list) == 0){
                    $map1['user_depart_id'] = ['eq',$depart_id];
                    Db::name('user')->where($map1)->setField('user_depart_id',0);
                }else{
                    $map2['user_id'] = ['in',$user_list];
                    Db::name('user')->where($map2)->setField('user_depart_id',$depart_id);
                    $map3['user_id'] = ['not in',$user_list];
                    $map3['user_depart_id'] = ['eq',$depart_id];
                    Db::name('user')->where($map3)->setField('user_depart_id',0);
                }
                Db::commit();
                return $this->success('设置成功');
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            $map2['u.user_id'] = ['gt',0];
            $all_user = Db::name('user')
                ->alias('u')
                ->join('depart d','d.depart_id=u.user_depart_id','left')
                ->join('station s','s.station_id=u.user_station_id','left')
                ->where($map2)
                ->select();
            $this->assign('all_user',$all_user);
            return $this->view->fetch();
        }


    }
}