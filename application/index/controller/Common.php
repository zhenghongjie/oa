<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Common extends Controller{
    protected $user_id;
    protected $user_is_super;
    protected $method;
    protected $param;
    protected $page_num=10;
    protected $user_info;
    function _initialize()
    {
        parent::_initialize();
        $this->user_id = session('user_id');
        if(intval($this->user_id) == 0){
            return $this->redirect(url('login/login'));
        }
        $this->user_is_super = session('user_is_super');
        $this->user_info = session('user_info');
        $this->method = $this->request->method();
        $this->param = $this->request->param();
        $this->view->assign('date_lunar',get_date_lunar());
    }
    /**
     * 功能：获取用户列表，分部门，角色，和岗位
     * @return mixed
     */
    protected function get_publish_user(){
        $s_map['station_id'] = ['gt',0];
        $publish['station'] = Db::name('station')->where($s_map)->field('station_id as id,station_title as title')->select();//岗位
        $r_map['role_id'] = ['gt',0];
        $publish['role']  = Db::name('user_role')->where($r_map)->field('role_id as id ,role_title as title')->select();//角色
        $d_map['depart_id'] = ['gt',0];
        $publish['depart'] = Db::name('depart')->where($d_map)->field('depart_id as id ,depart_title as title')->select();//部门
        $u_map['user_id'] = ['gt',0];
        $user = Db::name('user')->where($u_map)->field('user_id ,user_real_name,user_station_id,user_depart_id,user_role_id')->select();
        if($user){
            foreach ($user as $k=>$v){
                if(count($publish['station']) > 0){

                    foreach ($publish['station'] as $k1=>$v1){
                        if($v1['id'] == $v['user_station_id']){
                            $publish['station'][$k1]['user'][] = $v;
                        }
                    }
                }
                if(count($publish['role']) > 0){
                    foreach ($publish['role'] as $k2=>$v2){
                        if($v2['id'] == $v['user_role_id']){
                            $publish['role'][$k2]['user'][] = $v;
                        }
                    }
                }
                if(count($publish['depart']) > 0){
                    foreach ($publish['depart'] as $k3=>$v3){
                        if($v3['id'] == $v['user_depart_id']){
                            $publish['depart'][$k3]['user'][] = $v;
                        }
                    }
                }
            }
        }
        $publish['user'] = $user;//单独的用户数据
        return $publish;
    }
    //获取公文,邮件，信息的发布范围
    protected function get_doc_scope($scope_str){
        $publish_arr = explode(',',$scope_str);
        $publish_depart = [];//发布部门ID
        $publish_role = [];//发布角色ID
        $publish_user = [];//发布用户列表
        $publish_station = [];//岗位
        $publish = [];//发布范围
        $publish['depart']=[];
        $publish['role']=[];
        $publish['station']=[];
        $publish['user']=[];
        foreach ($publish_arr as $k=>$v){
            $tmp_v = explode('_',$v);//分隔
            if($tmp_v[0] == 'd'){
                $publish_depart[] = $tmp_v[1];//部门ID
            }
            if($tmp_v[0] == 'u'){ //用户ID
                $publish_user[] = $tmp_v[1];//用户ID
            }
            if($tmp_v[0] == 'r'){ //角色
                $publish_role[] = $tmp_v[1]; //角色ID
            }
            if($tmp_v[0] == 's'){ //岗位
                $publish_station[] = $tmp_v[1];//岗位ID
            }
        }
        if(count($publish_depart) > 0){
            $map_d['depart_id'] = ['in',$publish_depart];
            $publish['depart'] = Db::name('depart')->where($map_d)->field('depart_id,depart_title')->select();//发布的部门范围
        }
        if(count($publish_role) > 0) {
            $map_r['role_id'] = ['in', $publish_role];
            $publish['role'] = Db::name('user_role')->where($map_r)->field('role_id,role_title')->select();//发布的角色范围
        }
        if(count($publish_station) > 0) {
            $map_s['station_id'] = ['in', $publish_station];
            $publish['station'] = Db::name('station')->where($map_s)->field('station_id,station_title')->select();//发布的岗位范围
        }
        if(count($publish_user) > 0) {
            $map_u['user_id'] = ['in', $publish_user];
            $publish['user'] = Db::name('user')->where($map_u)->field('user_id,user_real_name')->select();//发布的用户岗位
        }
        return $publish;
    }
    //设置需要阅读的人的数据信息
    protected function set_doc_scope($scope_str=''){
        $publish_arr = explode(',',$scope_str);

        $publish_depart = [];//发布部门ID
        $publish_role = [];//发布角色ID
        $publish_user = [];//发布用户列表
        $publish_station = [];//岗位
        foreach ($publish_arr as $k=>$v){
            $tmp_v = explode('_',$v);//分隔
            if($tmp_v[0] == 'd'){
                $publish_depart[] = $tmp_v[1];//部门ID
            }
            if($tmp_v[0] == 'u'){ //用户ID
                $publish_user[] = $tmp_v[1];//用户ID
            }
            if($tmp_v[0] == 'r'){ //角色
                $publish_role[] = $tmp_v[1]; //角色ID
            }
            if($tmp_v[0] == 's'){ //岗位
                $publish_station[] = $tmp_v[1];//岗位ID
            }
        }

        $str = 'SELECT user_id,user_depart_id FROM n_user WHERE user_status != 3  AND (';
        if(count($publish_depart) > 0){
            $str .= ' user_depart_id in ('.implode(',',$publish_depart).') OR';
        }
        if(count($publish_station) > 0){
            $str .= ' user_station_id in ('.implode(',',$publish_station).') OR';
        }
        if(count($publish_user) > 0){
            $str .= ' user_id in ('.implode(',',$publish_user).') OR';
        }
        if(count($publish_role) > 0){
            $str .= ' user_role_id in ('.implode(',',$publish_role).') OR';
        }
        $str = substr($str,0,strlen($str) - 2);//去掉最后一个 'OR
        $str .= ')';

        $user_list = Db::query($str);


        return $user_list;
    }
    /**
     * 功能：去掉重复的元素
     * @param array $data
     * @return array
     */
    protected function delete_repeat($data=[],$obj_id='r_doc',$user_id="r_user_id"){
        $temp = [];
        $rs = [];
        if(count($data) > 0){
            foreach ($data as $key=>$item) {
                $temp_str = $item[$obj_id].'@'.$item[$user_id];
                if(!in_array($temp_str,$temp)){
                    $temp[] = $temp_str;
                    $rs[] = $item;
                }
            }
        }
        return $rs;


    }
    protected function get_doc_cate($type='1'){
        $map['cate_type'] = ['eq',$type];
        $list  = Db::name('doc_cate')->where($map)->select();
        return $list;
    }
}