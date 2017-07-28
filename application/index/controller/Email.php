<?php
namespace app\index\controller;
use think\Db;

class  Email extends Common{
    /**
     * 邮件管理
     * @return string
     */
    public function index(){
        $map['m.mail_id'] = ['gt',0];
        if($this->user_is_super == 0){ //不是超级管理员的情况下
            $map2['m.mail_user_id'] = ['eq',$this->user_id]; //自己发布的
        }else{
            $map2['m.mail_user_id'] = ['gt',0]; //所有人
        }
        $title = input('title');
        if(trim($title) != ''){
            $map['m.mail_title'] = ['like','%'.$title.'%'];
        }
        $index_type = input('type');
        if($index_type == 0){
            $type_name = '内部收件箱';
            $map['r.r_status'] = ['eq',0];
        }else if($index_type == 1){
            $type_name = '代办邮件';
            $map['r.r_tab'] = ['eq',2];
            $map['r.r_status'] = ['eq',0];
        }else if($index_type == 2){
            $type_name = '草稿箱';
            $map['m.mail_status'] = ['eq',2];
        }else if($index_type == 3){
            $type_name = '已发送';
            $map['m.mail_status'] = ['eq',1];
        }else if($index_type == 4){
            $type_name = '已删除';
            $map['r.r_status'] = ['eq',-1];
        }
        $min_type = input('min_type');
        if(isset($min_type) && intval(input('min_type')) != 0){
            if($min_type == 1){
                $map['m.mail_annex'] = ['neq',''];
            }else if($min_type == 2){
                $map['r.r_time'] = ['eq',0];
            }
        }
        $mail_list=[];
        if($index_type == 0 || $index_type == 1 || $index_type == 4){
            $map['r.r_id'] = ['gt',0];//需要操作的ID大于0
            $map['r.r_uid'] = ['eq', $this->user_id];//需要当前的用户读取的
            $mail_list = Db::name('mail_read')
                ->alias('r')
                ->join('mail m','m.mail_id = r.r_mail_id')
                ->join('user u','m.mail_user_id = u.user_id','left')
                ->field('r.*,m.mail_title,m.mail_time,m.mail_type,m.mail_annex,u.user_real_name')
                ->where($map)
                ->group('m.mail_id')
                ->paginate($this->page_num,false,[
                    'query'=>request()->param()
                ]);
        }else{
            $mail_list = Db::name('mail')
                ->alias('m')
                ->join('user u','u.user_id = m.mail_user_id','left')
                ->where($map)
                ->field('m.*,u.user_real_name')
                ->group('m.mail_id')
                ->paginate($this->page_num,false,[
                    'query'=>request()->param()
                ]);
        }
        $this->view->assign('user_id',$this->user_id);
        $this->view->assign('is_super',$this->user_is_super);
        $this->view->assign('type_name',$type_name);
        $this->view->assign('mail_list',$mail_list);
        return $this->view->fetch();
    }

    /**
     * 功能：标记
     */
    public function tap_mail(){
        if('POST' == $this->method){
            $tap_id = input('post.tap_id');
            $tap_type = input('post.tap_type');
            if(trim($tap_id) == ''){
                return $this->error('请指定要标记的邮件');
            }
            if(intval($tap_type) == 0){
                return $this->error('请指定要标记的类型');
            }
            $tap_id = explode(',',$tap_id);
            $map['r_mail_id'] = ['in',$tap_id];
            $map['r_uid'] = ['eq',$this->user_id];
            Db::startTrans();
            try{
                if($tap_type == 3){//未读
                    Db::name('mail_read')->where($map)->setField('r_time',$tap_type);
                }else if($tap_type == 1){//已读
                    Db::name('mail_read')->where($map)->setField('r_time',time());
                }else if($tap_type == 2){//待处理
                    Db::name('mail_read')->where($map)->setField('r_tab',$tap_type);
                }
                Db::commit();
                return $this->success('标记成功');
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('标记失败，请重试');
            }
        }else if('GET' == $this->method){
            return $this->error('请求错误');
        }
    }

    /**
     * 功能:详情
     */
    public function show(){
        $mail_id = input('mail_id');
        if(intval($mail_id) == 0){
            return $this->error('请指定邮件');
        }
        $map['m.mail_id'] = ['eq',$mail_id];
        $mail_detail = Db::name('mail')->alias('m')->join('user u','u.user_id = m.mail_user_id','left')->field('m.*,u.user_real_name')->where($map)->find();
        $read_comment = [];
        $cc_list = [];
        $send_name = "";
        if(!$mail_detail){
            return $this->error('该邮件信息不存在');
        }else if($mail_detail['mail_user_id'] != $this->user_id){
            $map_0['r.r_id'] = ['gt',0];
            $map_0['r.r_mail_id'] = ['eq',$mail_id];
            $map_0['r.r_uid'] = ['eq',$this->user_id];
            $read_info = Db::name('mail_read')
                ->alias('r')
                ->join('mail m','m.mail_id = r.r_mail_id')
                ->join('user u','u.user_id = m.mail_user_id','left')
                ->field('r.*,u.user_real_name')
                ->where($map_0)
                ->find();
            $send_name = $read_info['user_real_name'];
            if(!$read_info){
                return $this->error('你没有读取该邮件的权限');
            }
        }else if($mail_detail['mail_user_id'] == $this->user_id){
            $map_1['r.r_id'] = ['gt',0];
            $map_1['r.r_mail_id'] = ['eq',$mail_id];
            $read_comment = Db::name('mail_read')->alias('r')->join('user u','u.user_id = r.r_uid','left')->where($map_1)->field('r.*,u.user_real_name')->select();//回复列表
            $send_name = "我";
        }
        $read_list = Db::name('mail_read')->alias('r')->join('user u','u.user_id = r.r_uid')->where(['r.r_mail_id'=>['eq',$mail_id]])->field('u.user_real_name')->group('r.r_uid')->select();
        if(trim($mail_detail['mail_cc']) != ''){
            $map_cc['r.r_mail_id'] = ['eq',$mail_detail['mail_id']];
            $map_cc['r.r_type'] = ['eq',2];
            $cc_list = Db::name('mail_read')
                ->alias('r')
                ->join('user u','u.user_id = r.r_uid')
                ->where($map_cc)
                ->field('u.user_real_name')
                ->select();
        }
        $mail_detail['mail_annex'] = unserialize($mail_detail['mail_annex']);
        $read_num = Db::name('mail_read')->where(['r_mail_id'=>['eq',$mail_id]])->group('r_uid')->count();
        $this->view->assign('send_name',$send_name);
        $this->view->assign('read_num',$read_num);
        $this->view->assign('read_list',$read_list);
        $this->view->assign('cc_list',$cc_list);
        $this->view->assign('read_comment',$read_comment);
        $this->view->assign('user_id',$this->user_id);
        $this->view->assign('is_super',$this->user_is_super);
        $this->view->assign('mail_detail',$mail_detail);
        $this->view->assign('publish_scope', $this->get_publish_user());
        return $this->view->fetch();
    }

    /**
     * 功能：起草邮件
     */
    public function add_email(){
        if('POST' == $this->method){
            $post_data = input('post.');
            unset($post_data['check']);
            $post_data['mail_user_id'] = $this->user_id;
            $validate_mail = new \app\base\validate\Mail();
            $result = $validate_mail->scene('new')->check($post_data);
            if(!$result){
                return $this->error($validate_mail->getError());
            }
            if(isset($post_data['mail_annex']) && count($post_data['mail_annex']) > 0){
                $post_data['mail_annex'] = serialize($post_data['mail_annex']);
            }
            $post_data['mail_time'] = time();
            Db::startTrans();
            try{
                $mail_id = Db::name('mail')->insertGetId($post_data);//插入数据
                if($post_data['mail_status'] != 2){
                    $cc_read = [];
                    $cc_s_read = [];
                    $post_data['mail_receive'] = $post_data['mail_receive'].',u_1';
                    $publish_read = $this->get_read_list($post_data['mail_receive'],$mail_id,'',1);//设置发布范围
                    if(isset($post_data['mail_cc']) && trim($post_data['mail_cc']) != ''){
                        $cc_read = $this->get_read_list($post_data['mail_cc'],$mail_id,'',2);//设置抄送范围
                    }
                    if(isset($post_data['mail_s_cc']) && trim($post_data['mail_s_cc']) != ''){
                        $cc_s_read = $this->get_read_list($post_data['mail_s_cc'],$mail_id,'',3);//设置密送范围
                    }
                    $read_list = array_merge($publish_read,$cc_read,$cc_s_read);//所有需要阅读的范围
                    if(count($read_list) > 0){
                        $read_list = $this->delete_repeat($read_list,'r_mail_id','r_uid');//去除重复的
                        Db::name('mail_read')->insertAll($read_list);//批量插入需要阅读的人的信息
                    }
                }
                Db::commit();
                return $this->success('发送成功',url('index'));
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('发送失败，请重试');
            }
        }else if('GET' == $this->method){
            $this->view->assign('cate_list',$this->get_doc_cate(2));
            $this->view->assign('publish_scope', $this->get_publish_user());
            return $this->view->fetch();
        }

    }

    /**
     * 功能：编辑邮件
     * @return string
     */
    public function edit_email(){
        if('POST' == $this->method){
            $post_data = input('post.');
            $post_data['mail_user_id'] = $this->user_id;
            $validate_mail = new \app\base\validate\Mail();
            $result = $validate_mail->scene('edit')->check($post_data);
            if(!$result){
                return $this->error($validate_mail->getError());
            }
            if(isset($post_data['mail_annex']) && count($post_data['mail_annex']) > 0){
                $post_data['mail_annex'] = serialize($post_data['mail_annex']);
            }
            if($post_data['mail_status'] == 2){
                $mail_info = Db::name('mail')->where(['mail_id'=>['eq',$post_data['mail_id']]])->find();
                if($mail_info['mail_status'] == 1){
                    return $this->error('已发布邮件不可设为草稿');
                }
            }
            Db::startTrans();
            try{
                Db::name('mail')->update($post_data);//插入数据
                if($post_data['mail_status'] != 2){
                    $cc_read = [];
                    $cc_s_read = [];
                    $publish_read = $this->get_read_list($post_data['mail_receive'],$post_data['mail_id'],'edit',1);//设置发布范围
                    if(isset($post_data['mail_cc']) && trim($post_data['mail_cc']) != ''){
                        $cc_read = $this->get_read_list($post_data['mail_cc'],$post_data['mail_id'],'edit',2);//设置抄送范围
                    }
                    if(isset($post_data['mail_s_cc']) && trim($post_data['mail_s_cc']) != ''){
                        $cc_s_read = $this->get_read_list($post_data['mail_s_cc'],$post_data['mail_id'],'edit',3);//设置密送范围
                    }
                    $read_list = array_merge($publish_read,$cc_read,$cc_s_read);//所有需要阅读的范围
                    if(count($read_list) > 0){
                        $read_list = $this->delete_repeat($read_list,'r_mail_id','r_uid');//去除重复的
                        Db::name('mail_read')->insertAll($read_list);//批量插入需要阅读的人的信息
                    }
                }
                Db::commit();
                return $this->success('发送成功');
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('发送失败，请重试');
            }
        }else if('GET' == $this->method){
            $mail_id = input('mail_id');
            if(intval($mail_id) == 0){
                return $this->error('请指定要编辑的邮件');
            }
            $map['mail_id'] = ['eq',$mail_id];
            if($this->user_is_super == 0){
                $map['mail_user_id'] = ['eq',$this->user_id];//TODO，这里需要加上权限验证部分
            }
            $info = Db::name('mail')->where($map)->find();
            if(!$info){
                return $this->error('邮件信息不存在');
            }
            $this->view->assign('info',$info);
            $this->view->assign('publish_scope', $this->get_publish_user());
            $this->view->assign('cate_list',$this->get_doc_cate(2));
            return $this->view->fetch();
        }
    }

    /**
     * 功能：标记为删除（内部收件箱，代办邮件）
     */
    public function set_delete(){
        if('POST' == $this->method){
            $mail_id = input('post.mail_id');
            if(trim($mail_id) == ''){
                return $this->error('请指定要删除的邮箱');
            }
            $mail_id_arr = explode(',',$mail_id);
            $map['r_mail_id'] = ['in',$mail_id_arr];
            $map['r_uid'] = ['eq',$this->user_id];
            Db::startTrans();
            try{
                Db::name('mail_read')->where($map)->setField('r_status',-1);
                Db::commit();
                return $this->success('删除成功');
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('删除失败，请重试');
            }
        }else if('GET' == $this->method){
            return $this->error('请求错误');
        }
    }

    /**
     * 功能：彻底删除已标记删除的邮件（已删除）
     */
    public function read_email_delete(){
        if('POST' == $this->method){
            $mail_id = input('post.mail_id');
            if(trim($mail_id) == ''){
                return $this->error('请指定要删除的邮箱');
            }
            $mail_id_arr = explode(',',$mail_id);
            $map['r_mail_id'] = ['in',$mail_id_arr];
            $map['r_uid'] = ['eq',$this->user_id];
            $map['r_status'] = ['eq',-1];
            Db::startTrans();
            try{
                Db::name('mail_read')->where($map)->delete();
                Db::commit();
                return $this->success('删除成功');
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('删除失败，请重试');
            }
        }else if('GET' == $this->method){
            return $this->error('请求错误');
        }
    }

    /**
     * 功能：恢复标记为删除的邮件（已删除）
     */
    public function reset_mail(){
        if('POST' == $this->method){
            $mail_id = input('post.mail_id');
            if(trim($mail_id) == ''){
                return $this->error('请指定要删除的邮箱');
            }
            $mail_id_arr = explode(',',$mail_id);
            $map['r_mail_id'] = ['in',$mail_id_arr];
            $map['r_uid'] = ['eq',$this->user_id];
            $map['r_status'] = ['eq',-1];
            Db::startTrans();
            try{
                Db::name('mail_read')->where($map)->setField('r_status',0);
                Db::commit();
                return $this->success('恢复成功');
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('恢复失败，请重试');
            }
        }else if('GET' == $this->method){
            return $this->error('请求错误');
        }
    }

    /**
     * 功能：彻底删除自己发的邮件（草稿箱，已发送）
     * @return string
     */
    public function email_delete(){
        if('POST' == $this->method){
            $mail_id = input('post.mail_id');
            if(trim($mail_id) == ''){
                return $this->error('请指定要删除的邮箱');
            }
            $mail_id_arr = explode(',',$mail_id);
            $map['mail_id'] = ['in',$mail_id_arr];
            Db::startTrans();
            try{
                $res = Db::name('mail')->where($map)->delete();
                Db::commit();
                return $this->success('删除成功');
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('删除失败，请重试');
            }
        }else if('GET' == $this->method){
            return $this->error('请求错误');
        }
    }

    /**
     * @throws \think\Exception
     * 功能：邮件回复
     */
    public function mail_comment(){
        if('POST' == $this->method){
            $data = input('post.');
            if(intval($data['r_mail_id']) == 0){
                return $this->error('请指定要回复的邮件');
            }
            $map['r_mail_id'] = ['eq',$data['r_mail_id']];
            $map['r_uid'] = ['eq',$this->user_id];
            if(trim($data['r_comment']) == ''){
                return $this->error('请输入回复内容');
            }
            Db::startTrans();
            try{
                Db::name('mail_read')->where($map)->update($data);
                Db::commit();
                return $this->success('回复成功');
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('回复失败；请重试');
            }
        }else if('GET' == $this->method){
            return $this->error('请求错误');
        }
    }

    public function ajax_set_tap(){
        if('POST' == $this->method){
            $data = input('post.');
            if(intval($data['mail_id']) == 0){
                return callback('0','请指定要标记的邮件');
            }
            if(intval($data['mail_tap']) == 0){
                return callback('0','标记类型异常');
            }
        }else if('GET' == $this->method){
            return callback(0,'请求错误');
        }
    }

    private function get_read_list($scope_str='',$mail_id=0,$type='new',$mail_type=1){
        $user_list  = $this->set_doc_scope($scope_str);
        $read_list = [];
        if(count($user_list) > 0){
            foreach ($user_list as $k=>$v){
                $read_list[] = [
                    'r_uid'=>$v['user_id'],
                    'r_mail_id'=>$mail_id,
                    'r_type'=>$mail_type
                ];
            }
        }
        if('new' != $type){
            Db::name('mail_read')->where(['r_mail_id'=>$mail_id])->delete();
        }
        return $read_list;
    }
}