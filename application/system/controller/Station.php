<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 9:23
 * function:岗位管理
 */
namespace app\system\controller;
use think\Db;

class  Station extends Common{
    //首页
    public function index(){
        $map['s.station_id'] = ['gt',0];//大于0 的
        $map['s.station_status'] = ['eq',1];//未删除的
        $title = input('title');
        if(trim($title) != ''){
            $map['s.station_title'] = ['like','%'.$title.'%'];
        }
        $station = Db::name('station')
            ->alias('s')
            ->join('station_cate sc','sc.c_id=s.station_c_id','left')
            ->where($map)
            ->order('s.station_c_id ASC,s.station_sort ASC')
            ->paginate($this->page_num,false,[
            'query'=>request()->param()
        ]);
        $this->view->assign('station',$station);
        return $this->view->fetch();
    }

    /**
     * 功能：添加岗位
     */
    public function new_station(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $validate_station = new \app\base\validate\Station();
            $result = $validate_station->scene('new')->check($post_data);
            if(!$result){
                return $this->error($validate_station->getError());
            }
            $res = Db::name('station')->insertGetId($post_data);
            if(false !== $res){
                return $this->success('设置成功',url('index'));
            }else{
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            $this->view->assign('station_cate',$this->get_station_cate());
            return $this->view->fetch();
        }
    }
    private function get_station_cate(){
        $map['c_id'] = ['gt',0];
        $station_cate = Db::name('station_cate')->where($map)->select();
        return $station_cate;
    }
    /**
     * 功能：编辑岗位
     */
    public function edit_station(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $validate_station = new \app\base\validate\Station();
            $result = $validate_station->scene('edit')->check($post_data);
            if(!$result){
                return $this->error($validate_station->getError());
            }
            $res = Db::name('station')->update($post_data);
            if(false !== $res){
                return $this->success('设置成功',url('index'));
            }else{
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            $station_id = input('station_id');
            if(intval($station_id) == 0){
                return $this->error('请指定岗位');
            }
            $map['station_id'] = ['eq',$station_id];
            $map['station_status'] = ['eq',1];
            $station_info = Db::name('station')->where($map)->find();
            if(!$station_info){
                return $this->error('指定的岗位不存在');
            }
            $this->view->assign('station_cate',$this->get_station_cate());
            $this->view->assign('station_info',$station_info);
            return $this->view->fetch();
        }
    }
    /**
     * 功能：删除岗位
     */
    public function delete_station(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $station_id = $post_data['station_id'];
            if(count($station_id) == 0){
                return $this->error('请指定要删除的岗位');
            }
            Db::startTrans();
            try{
                $map['station_id'] = ['in',$station_id];
                Db::name('station')->where($map)->delete();
                $map1['user_station_id'] = ['in',$station_id];
                Db::name('user')->where($map1)->setField('user_station_id',0);
                Db::commit();
                return $this->success('删除成功',url('index'));
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('删除失败');
            }
        }else if('GET' == $this->method){
            return $this->error('不正确的请求方式');
        }
    }

    /**
     * 功能：添加岗位分类
     * @return string|void
     */
    public function new_station_cate(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $validate_station = new \app\base\validate\Stationcate();
            $result = $validate_station->scene('new')->check($post_data);
            if(!$result){
                return $this->error($validate_station->getError());
            }
            $res = Db::name('station_cate')->insertGetId($post_data);
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
     * 功能：编辑岗位分类
     * @return string|void
     */
    public function edit_station_cate(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $validate_station = new \app\base\validate\Stationcate();
            $result = $validate_station->scene('edit')->check($post_data);
            if(!$result){
                return $this->error($validate_station->getError());
            }
            $res = Db::name('cate_info')->update($post_data);
            if(false !== $res){
                return $this->success('设置成功',url('index'));
            }else{
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            $cate_id = input('cate_id');
            if(intval($cate_id) == 0){
                return $this->error('请指定岗位分类');
            }
            $map['c_id'] = ['eq',$cate_id];
            $cate_info = Db::name('station_cate')->where($map)->find();
            if(!$cate_info){
                return $this->error('指定的岗位分类不存在');
            }
            $this->view->assign('cate_info',$cate_info);
            return $this->view->fetch();
        }
    }

    /**
     * 功能：删除岗位分类
     */
    public function delete_station_cate(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $cate_id = $post_data['cate_id'];
            Db::startTrans();
            try{
                $map['c_id'] = ['eq',$cate_id];
                $res = Db::name('station_cate')->where($map)->delete();
                $map1['station_c_id'] = ['eq',$cate_id];
                Db::name('station')->where($map1)->setField('station_c_id',0);
                Db::commit();
                return $this->success('设置成功',url('index'));
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('设置失败');
            }

        }else if('GET' == $this->method){
           return $this->error('不合法的请求方式');
        }
    }

    /**
     * 功能：岗位成员管理
     * @return string|void
     */
    public function set_station_user(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $user_id = $post_data['user_id'];
            $station_id = $post_data['station_id'];
            if(intval($station_id) == 0){
                return $this->error('请指定岗位');
            }
            Db::startTrans();
            try{
                if(count($user_id) == 0){
                    Db::name('user')->where(['user_station_id'=>$station_id])->setField('user_station_id',0);
                }else{
                    $map1['user_id'] = ['in',$user_id];
                    Db::name('user')->where($map1)->setField('user_station_id',$station_id);
                    $map2['user_station_id'] = ['eq',$station_id];
                    $map2['user_id'] = ['not in',$user_id];
                    Db::name('user')->where($map2)->setField('user_station_id',0);
                }
                Db::commit();
                return $this->success('设置成功');
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('设置失败');
            }
        }else if('GET' == $this->method){
            $station_id = input('station_id');
            if(intval($station_id) == 0){
                return $this->error('请指定岗位');
            }
            $map['u.user_station_id'] = ['eq',$station_id];
            $user_list = Db::name('user')
                ->alias('u')
                ->join('depart d','d.depart_id=u.user_depart_id','left')
                ->where($map)
                ->field('u.user_id.u.user_real_name,d.depart_title')
                ->select();
            $this->view->assign('user_list',$user_list);//当前岗位已有的用户
            $all_user = Db::name('user')
                ->alias('u')
                ->join('depart d','d.depart_id=u.user_depart_id','left')
                ->join('station s','s.station_id=u.user_station_id','left')
                ->where(1)
                ->select();
            $this->view->assign('all_user',$all_user);//所有用户
            return $this->view->fetch();
        }
    }
}