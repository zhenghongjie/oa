<?php
namespace app\index\controller;
use think\Db;
header("Content-Type:text/html;charset=utf-8");
class  Doc extends Common{
    /**
     * 公文管理
     * @return string
     */
    public function index(){
        $map['r.r_id'] = ['gt',0];//需要操作的ID大于0
        if($this->user_is_super == 0){ //不是超级管理员的情况下
            $map['r.r_user_id'] = ['eq',$this->user_id];//需要当前的用户读取的
            $map2['d.doc_user_id'] = ['eq',$this->user_id]; //自己发布的
        }else{
            $map2['d.doc_user_id'] = ['gt',0]; //所有人
        }
        $title = input('title');
        if(trim($title) != ''){
            $map['d.doc_title'] = ['like','%'.$title.'%'];
        }
        $cate = input('cate_id');
        if(intval($cate) != 0){
            $map['d.doc_c_id'] = ['eq',$cate];
        }
        $map['r.r_action'] = ['neq',3];
        $no_sign_count = Db::name('doc_read')->alias('r')->join('doc d','d.doc_id=r.r_doc','left')->where($map)->group('d.doc_id')->count();//未签收
        $map['r.r_action'] = ['eq',3];
        $sign_count = Db::name('doc_read')->alias('r')->join('doc d','d.doc_id=r.r_doc','left')->where($map)->group('d.doc_id')->count();//已签收
        unset($map['r.r_action']);
        $index_type = input('type');
        $type_name = '';
        if($index_type == 0){
            $type_name = '全部';
        }else if($index_type == 1){
            $type_name = '未签收';
            $map['r.r_action'] = ['neq',3];
        }else if($index_type == 2){
            $type_name = '签收';
            $map['r.r_action'] = ['eq',3];
        }else if($index_type == 3){
            $type_name = '待审核';
            $map['d.doc_check_status'] = ['eq',1];
        }else if($index_type == 4){
            $type_name = '草稿';
            unset($map['r.r_user_id']);
            unset($map['r.r_id']);
            $map['doc_id'] = ['gt',0];
            $map['doc_user_id'] = ['eq',$this->user_id];
            $map['doc_state'] = ['eq',2];
        }
        $doc_reader_list = [];
        if($index_type == 4){
            $doc_reader_list = Db::name('doc')->where($map)->field('doc_id as r_doc')->select();
        }else{
            $doc_reader_list = Db::name('doc_read')->alias('r')->join('doc d','d.doc_id = r.r_doc','left')->where($map)->select(); //这个是需要用户阅读的公文的数据
        }
        if($doc_reader_list) {
            $doc_id = indexs($doc_reader_list, 'r_doc'); //读取要读取的文件ID
            $map2['d.doc_id'] = ['in', $doc_id];
        }else{
            $map2['d.doc_user_id'] = ['eq', 0];
        }
        $doc_list = Db::name('doc')
            ->alias('d')
            ->join('user u','u.user_id=d.doc_user_id','left')
            ->join('doc_read r','r.r_doc=d.doc_id','left')
            ->where($map2)
            ->field('d.*,u.*,count(r.r_id) as no_sign')
            ->group('d.doc_id')
            ->paginate($this->page_num,false,[
                'query'=>request()->param()
            ]);
        $this->view->assign('user_id',$this->user_id);
        $this->view->assign('is_super',$this->user_is_super);
        $this->view->assign('type_name',$type_name);
        $this->view->assign('doc_list',$doc_list); //公文列表
        $this->view->assign('no_sign',$no_sign_count);
        $this->view->assign('sign_count',$sign_count);
        return $this->view->fetch();
    }

    //公文详情
    public function detail(){
        $doc_id = input('doc_id');
        if(intval($doc_id) == 0){
            return $this->error('请指定公文');
        }
        $map['d.doc_id'] = ['eq',$doc_id];
        $detail_info = Db::name('doc')
            ->alias('d')
            ->join('user u','u.user_id=d.doc_user_id')
            ->join('doc_cate dc','dc.cate_id=d.doc_c_id and dc.cate_type=2','left')
            ->where($map)
            ->field('d.*,u.user_real_name,dc.cate_title')
            ->find();//公文信息
        if(!$detail_info){
            return $this->error('公文不存在');
        }
        $map1['r_user_id'] = ['eq',$this->user_id];
        $map1['r_doc'] = ['eq',$doc_id];
        $map1['r_action'] = ['eq',1];//未操作的
        if(Db::name('doc_read')->where($map1)->find()){
            $update_info = [
                'r_action'=>2,
                'r_update_time'=>time()
            ];
            Db::name('doc_read')->where($map1)->update($update_info);
        }
        $detail_info['doc_annex'] = unserialize($detail_info['doc_annex']);//附件列表
        $publish = $this->get_doc_scope($detail_info['doc_publish']);//获取发布范围
        $cc_scope = $this->get_doc_scope($detail_info['doc_cc']);//获取抄送范围
        $map_read['r.r_doc'] = ['eq',$doc_id];
        $read_list = Db::name('doc_read')
            ->alias('r')
            ->join('user u','r.r_user_id=u.user_id','left')
            ->join('depart d','u.user_depart_id=d.depart_id','left')
            ->where($map_read)
            ->field('r.*,u.user_real_name,d.depart_title')

            ->select();
//        dump($cc_scope);exit;
        $this->view->assign('publish',$publish);//发布范围
        $this->view->assign('cc_scope',$cc_scope);//抄送范围
        $this->view->assign('detail',$detail_info);//详情
        $this->view->assign('read_list',$read_list);//阅读列表
        return $this->view->fetch();
    }

    //获取公文需要的读取人列表
    private function get_read_list($scope_str='',$doc_id=0,$type='new'){
        $user_list  = $this->set_doc_scope($scope_str);
        $read_list = [];
        if(count($user_list) > 0){
            foreach ($user_list as $k=>$v){
                $read_list[] = [
                    'r_doc'=>$doc_id,
                    'r_action'=>'1',
                    'r_user_id'=>$v['user_id'],
                    'r_depart_id'=>$v['user_depart_id'],
                    'r_time'=>time()
                ];
            }
        }
        if('new' != $type){
            $old_read = Db::name('doc_read')->where(['r_doc'=>$doc_id])->select();
            $del_arr = [];
            if($old_read){
                foreach ($old_read as $k=>$v){
                    $is_exits = false;
                    foreach ($read_list as $k1 =>$v1){
                        if($v1['r_user_id'] == $v['r_user_id']){
                            $is_exits = true;
                            unset($read_list[$k1]); //防止重复添加
                        }
                    }
                    if(!$is_exits){
                        array_push($del_arr,$v['r_user_id']);
                    }
                }
                if($del_arr){
                    $map['r_user_id'] = ['in',$del_arr];
                    Db::name('doc_read')->where($map)->delete();
                }
            }

        }
        return $read_list;
    }

    /**
     * @throws \think\Exception
     * 功能：公文签收
     */
    public function sign_doc(){
        if('POST' == request()->method()){
            $post_data = input('post.');
            $doc_id = input("post.doc_id");
            $map['r_doc'] = ['eq',$doc_id];
            $map['r_user_id'] = ['eq',$this->user_id];
            $map['r_action'] = ['neq',3];
            if(Db::name('doc_read')->where($map)->find()){
                $update_info = [
                    'r_action'=>3,
                ];
                if(isset($post_data['comment']) && trim($post_data['comment']) != ''){
                    $update_info['r_comment'] = $post_data['comment'];
                    $update_info['r_time'] = time();
                }
               $res = Db::name('doc_read')->where($map)->update($update_info);
            }else{
                if($this->user_is_super == 0){
                    return $this->error('不能操作');
                }else{
                    $user_info = Db::name('user')->find($this->user_id);
                    $insert_info = [
                        'r_doc'=>$doc_id,
                        'r_action'=>3,
                        'r_user_id'=>$this->user_id,
                        'r_update_time'=>time(),
                        'r_time'=>time(),
                        'r_depart_id'=>$user_info['user_depart_id']
                    ];
                    if(isset($post_data['comment']) && trim($post_data['comment']) != ''){
                        $insert_info['r_comment'] = $post_data['comment'];
                        $insert_info['r_time'] = time();
                    }
                    $res =  Db::name('doc_read')->insert($insert_info);
                }
            }
            if(false !== $res){
                return $this->success('签收成功',url('index'));
            }else{
                return $this->error('签收失败');
            }
        }else{
            return $this->error('非法请求');
        }
    }

    /**
     * 功能：文件批示
     */
    public function instructions_doc(){
        if('POST' == $this->method){
            $data = input('post.');
            if(intval($data['doc_id']) == 0){
                return $this->error('请指定要批示的公文');
            }else if(trim($data['comment']) == ''){
                return $this->error('公文批示不能为空');
            }
            $map['r_doc'] = ['eq',$data['doc_id']];
            $map['r_user_id'] = ['eq',$this->user_id];
            $doc_info = Db::name('doc_read')->where($map)->find();
            if(!$doc_info){
                if($this->user_is_super == 0){
                    return $this->error('该公文信息不存在');
                }else{
                    $doc_info = [
                        'r_doc'=>$data['doc_id'],
                        'r_action'=>2,
                        'r_comment'=>$data['comment'],
                        'r_user_id'=>$this->user_id,
                        'r_time'=>time(),
                        'r_depart_id'=>$this->user_info['user_depart_id'],
                        'r_update_time'=>time()
                    ];
                    $flag = 1;
                }

            }else{
                $doc_info['r_comment'] = $data['comment'];
                $doc_info['r_update_time'] = time();
                $flag = 0;
            }
            Db::startTrans();
            try{
                if($flag == 1){
                    Db::name('doc_read')->insert($doc_info);
                }else if($flag == 0){
                    Db::name('doc_read')->update($doc_info);
                }
                Db::commit();
                return $this->success('批示公文成功',url('index'));
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('批示公文失败，请重试');
            }
        }else if('GET' == $this->method){
            return $this->error('请求错误');
        }
    }

    /**
     * 功能：起草公文
     */
    public function add_doc(){
        if('POST' == $this->method){
            $post_data = input('post.');
            unset($post_data['check']);
            $post_data['doc_user_id'] = $this->user_id;
            $validate_doc = new \app\base\validate\Doc();
            $result = $validate_doc->scene('new')->check($post_data);
            if(!$result){
                return $this->error($validate_doc->getError());
            }
            if(isset($post_data['doc_annex']) && count($post_data['doc_annex']) > 0){
                $post_data['doc_annex'] = serialize($post_data['doc_annex']);
            }
            $post_data['doc_time'] = time();

            Db::startTrans();
            try{
                $doc_id = Db::name('doc')->insertGetId($post_data);//插入数据
                if($post_data['doc_state'] != 2){
                    $cc_read = [];
                    $post_data['doc_publish'] = $post_data['doc_publish'].',u_1';
                    $publish_read = $this->get_read_list($post_data['doc_publish'],$doc_id);//设置发布范围

                    if(isset($post_data['doc_cc']) && trim($post_data['doc_cc']) != ''){
                        $cc_read = $this->get_read_list($post_data['doc_cc'],$doc_id);//设置抄送范围
                    }
                    $read_list = array_merge($publish_read,$cc_read);//所有需要阅读的范围

                    if(count($read_list) > 0){
                        $read_list = $this->delete_repeat($read_list);//去除重复的
                        Db::name('doc_read')->insertAll($read_list);//批量插入需要阅读的人的信息
                    }
                }
                Db::commit();
                return $this->success('发布成功',url('index'));
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('发布失败');
            }
        }else if('GET' == $this->method){
            $map['r.r_id'] = ['gt',0];//需要操作的ID大于0
            if($this->user_is_super == 0){ //不是超级管理员的情况下
                $map['r.r_user_id'] = ['eq',$this->user_id];//需要当前的用户读取的
            }
            $map['r.r_action'] = ['neq',3];
            $no_sign_count = Db::name('doc_read')->alias('r')->where($map)->group('r_doc')->count();//未签收
            $this->view->assign('no_sign',$no_sign_count);
            $this->view->assign('cate_list',$this->get_doc_cate(1));
            $this->view->assign('publish_scope', $this->get_publish_user());
            return $this->view->fetch();
        }

    }

    /**
     * 功能：编辑公文
     * @return string
     */
    public function edit_doc(){
        if('POST' == $this->method){
            $post_data = input('post.');
            if(intval($post_data['doc_id']) == 0){
                return $this->error('请指定要编辑的公文');
            }

            unset($post_data['check']);
            $post_data['doc_user_id'] = $this->user_id;
            $validate_doc = new \app\base\validate\Doc();
            $result = $validate_doc->scene('edit')->check($post_data);
            if(!$result){
                return $this->error($validate_doc->getError());
            }
            if(isset($post_data['doc_annex']) && count($post_data['doc_annex']) > 0){
                $post_data['doc_annex'] = serialize($post_data['doc_annex']);
            }else{
                $post_data['doc_annex'] = '';
            }
            if($post_data['doc_need_check'] == 1){
                $post_data['doc_check_status'] = 1;
                $post_data['doc_check_remark'] = '';
                $post_data['doc_check_time'] = 0;
            }
            $doc_info = Db::name('doc')->where(['doc_id'=>['eq',$post_data['doc_id']]])->find();
            if(!$doc_info){
                return $this->error('公文不存在');
            }
            if($doc_info['doc_state'] == 1 && $post_data['doc_state'] == 2){
               return $this->error('已发布公文不可设为草稿');
            }
            Db::startTrans();
            try{
                Db::name('doc')->update($post_data);//插入数据
                if($post_data['doc_state'] != 2){
                    $publish_list = $post_data['doc_publish'];
                    if(isset($post_data['doc_cc']) && trim($post_data['doc_cc']) != ''){
                        $publish_list .= $post_data['doc_cc'];
                    }
                    $read_list = $this->get_read_list($publish_list,$post_data['doc_id'],'edit');//设置发布范围
                    if(count($read_list) > 0){
                        $read_list = $this->delete_repeat($read_list);//去除重复的
                        Db::name('doc_read')->insertAll($read_list);//批量插入需要阅读的人的信息
                    }
                }
                Db::commit();
                return $this->success('编辑成功',url('index'));
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('编辑失败，请重试');
            }
        }else if('GET' == $this->method){
            $doc_id = input('doc_id');
            if(intval($doc_id) == 0){
                return $this->error('请指定要编辑的公文');
            }
            $map['doc_id'] = ['eq',$doc_id];
            if($this->user_is_super == 0){
                $map['doc_user_id'] = ['eq',$this->user_id];//TODO，这里需要加上权限验证部分
            }
            $info = Db::name('doc')->where($map)->find();

            if(!$info){
                return $this->error('公文不存在');
            }
            if(isset($info['doc_annex']) && strlen($info['doc_annex']) > 0){
                $info['doc_annex'] = unserialize($info['doc_annex']);
            }
            $map_0['r.r_id'] = ['gt',0];//需要操作的ID大于0
            if($this->user_is_super == 0){ //不是超级管理员的情况下
                $map_0['r.r_user_id'] = ['eq',$this->user_id];//需要当前的用户读取的
            }
            $map_0['r.r_action'] = ['neq',3];
            $no_sign_count = Db::name('doc_read')->alias('r')->where($map_0)->group('r_doc')->count();//未签收
            $this->view->assign('no_sign',$no_sign_count);
            $this->view->assign('info',$info);
            $this->view->assign('publish_scope', $this->get_publish_user());
            $this->view->assign('cate_list',$this->get_doc_cate(1));
            return $this->view->fetch();
        }
    }

    /**
     * 功能：删除公文
     * @return string
     */
    public function delete_doc(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $doc_id = $post_data['doc_id'];
            if(intval($doc_id) == 0){
                return $this->error('请指定要删除的公文');
            }
            $map['doc_id'] = ['eq',$doc_id];
            $doc_info = Db::name('doc')->where($map)->find();
            if(!$doc_info){
                return $this->error('该公文信息不存在');
            }else if($this->user_id != 1 && $doc_info['doc_user_id'] != $this->user_id){
                return $this->error('你没有删除该公文的权限');
            }
            $map_doc_read['r_doc'] = ['eq',$doc_id];
            Db::startTrans();
            try{
                Db::name('doc')->where($map)->delete();
                Db::name('doc_read')->where($map_doc_read)->delete();
                Db::commit();
                return $this->success('删除公文成功',url('index'));
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('删除公文失败，请重试');
            }
        }else if('GET' == $this->method){
            return $this->error('请求错误');
        }
    }

    /**
     * 分发公文
     */
    public function depart_doc(){
        if('POST' == $this->method){
            $data = input('post.');
            unset($data['check']);
            if(intval($data['doc_id']) == 0){
                return $this->error('请指定要批示的公文');
            }
            $map['r_doc'] = ['eq',$data['doc_id']];
            $map['r_user_id'] = ['eq',$this->user_id];
            $doc_info = Db::name('doc_read')->where($map)->find();
            if(!$doc_info){
                if($this->user_is_super == 0){
                    return $this->error('该公文信息不存在');
                }else{
                    $doc_info = [
                        'r_doc'=>$data['doc_id'],
                        'r_action'=>2,
                        'r_comment'=>$data['comment'],
                        'r_user_id'=>$this->user_id,
                        'r_time'=>time(),
                        'r_depart_id'=>$this->user_info['user_depart_id'],
                        'r_update_time'=>time()
                    ];
                    $flag = 1;
                }
            }else{
                if(isset($data['comment']) && trim($data['comment']) != ''){
                    $doc_info['r_comment'] = $data['comment'];
                    $doc_info['r_action'] = 2;
                    $doc_info['r_update_time'] = time();
                }
                $flag = 0;
            }
            Db::startTrans();
            try{
                $read_list = $this->get_read_list($data['doc_publish'],$data['doc_id']);//设置发布范围
                Db::name('doc_read')->insertAll($read_list);//批量插入需要阅读的人的信息
                if($flag == 1){
                    Db::name('doc_read')->insert($doc_info);
                }else if($flag == 0){
                    Db::name('doc_read')->update($doc_info);
                }
                Db::commit();
                return $this->success('分发公文成功',url('index'));
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('分发公文失败，请重试');
            }
        }else if('GET' == $this->method){
            $doc_id = input('doc_id');
            if(intval($doc_id) == '0'){
                return $this->error('请指定要分发的公文');
            }
            $this->view->assign('doc_id',$doc_id);
            $this->view->assign('publish_scope', $this->get_publish_user());
            return $this->view->fetch();
        }
    }
}