<?php
/**
 * 功能：信息中心
 */
namespace app\index\controller;
use think\Db;

class  Article extends Common{
    /**
     * 信息管理
     * @return string
     */
    public function index()
    {
        $map['r_id'] = ['gt',0];//需要操作的ID大于0
        if($this->user_is_super == 0){ //不是超级管理员的情况下
            $map['r_user_id'] = ['eq',$this->user_id];//需要当前的用户读取的
            $map2['a.article_user_id'] = ['eq',$this->user_id]; //自己发布的
        }else{
            $map2['a.article_user_id'] = ['gt',0]; //所有人
        }
        $title = input('title');
        if(trim($title) != ''){
            $map['a.article_title'] = ['like','%'.$title.'%'];
        }
        $map['r.r_time'] = ['neq',0];
        $read_count = Db::name('article_read')->alias('r')->join('article a','a.article_id = r.r_article_id','left')->where($map)->group('a.article_id')->count();//已读
        $map['r.r_time'] = ['eq',0];
        $no_read_count = Db::name('article_read')->alias('r')->join('article a','a.article_id = r.r_article_id','left')->where($map)->group('a.article_id')->count();//未读
        unset($map['r.r_time']);
        $index_type = input('type');
        $type_name = '';
        if($index_type == 0){
            $type_name = '已发布';
        }else if($index_type == 1){
            $type_name = '未读';
            $map['r.r_time'] = ['eq',0];
        }else if($index_type == 2){
            $type_name = '已读';
            $map['r.r_time'] = ['neq',0];
        }else if($index_type == 3){
            $type_name = '待审核';
            $map['a.article_check_status'] = ['eq',1];
        }else if($index_type == 4){
            $type_name = '草稿';
            unset($map['r_id']);
            $map['article_status'] = ['eq',2];
            $map['article_user_id'] = ['eq',$this->user_id];
            $map['article_id'] = ['gt',0];
        }
        $article_reader_list = [];
        if($index_type == 4){
            $article_reader_list = Db::name('article')->where($map)->field('article_id as r_article_id')->select();
        }else{
            $article_reader_list = Db::name('article_read')->alias('r')->join('article a','a.article_id = r.r_article_id','left')->where($map)->select(); //这个是需要用户阅读的信息的数据
        }
        if($article_reader_list){
            $article_id = indexs($article_reader_list,'r_article_id'); //读取要读取的文件ID
            $map2['a.article_id'] = ['in',$article_id];
        }else{
            $map2['a.article_user_id'] = ['eq',0];
        }
        $article_list = Db::name('article')
            ->alias('a')
            ->join('user u','u.user_id = a.article_user_id','left')
            ->join('article_read r','r.r_article_id = a.article_id','left')
            ->where($map2)
            ->field('a.*,u.user_real_name')
            ->group('a.article_id')
            ->paginate($this->page_num,false,[
                'query'=>request()->param()
            ]);
        $this->view->assign('user_id',$this->user_id);
        $this->view->assign('is_super',$this->user_is_super);
        $this->view->assign('type_name',$type_name);
        $this->view->assign('article_list',$article_list); //公文列表
        $this->view->assign('no_read_count',$no_read_count);
        $this->view->assign('read_count',$read_count);
        return $this->view->fetch();
    }

    /**
     * 详情
     */
    public function show()
    {
        $article_id = input('article_id');
        if(intval($article_id) == 0){
            return $this->error('请指定信息');
        }
        $map['a.article_id'] = ['eq',$article_id];
        $detail_info = Db::name('article')
            ->alias('a')
            ->join('user u','u.user_id = a.article_user_id')
            ->join('doc_cate dc','dc.cate_id = a.article_c_id and dc.cate_type=3','left')
            ->where($map)
            ->field('a.*,u.user_real_name,dc.cate_title')
            ->find();//信息内容
        if(!$detail_info){
            return $this->error('该信息内容不存在');
        }
        if($detail_info['article_type'] == 3){
            $this->redirect($detail_info['article_url']);
        }else if($detail_info['article_type'] == 2){
            $detail_info['article_images'] = unserialize($detail_info['article_images']);//图片列表
        }
        $map1['r_user_id'] = ['eq',$this->user_id];
        $map1['r_article_id'] = ['eq',$article_id];
        $map1['r_time'] = ['eq',0];//未阅读
        if(Db::name('article_read')->where($map1)->find()){
            $update_info = [
                'r_time'=>time()
            ];
            Db::name('article_read')->where($map1)->update($update_info);
        }
        $detail_info['article_annex'] = unserialize($detail_info['article_annex']);//附件列表
        $publish = $this->get_doc_scope($detail_info['article_publish']);//获取发布范围
        $map_read['r.r_article_id'] = ['eq',$article_id];
        $read_list = Db::name('article_read')
            ->alias('r')
            ->join('user u','r.r_user_id = u.user_id','left')
            ->join('depart d','u.user_depart_id = d.depart_id','left')
            ->where($map_read)
            ->field('r.*,u.user_real_name,d.depart_title')
            ->select();
        if($read_list){
            $new_read = [];
            foreach ($read_list as $k=>$v){
                $new_read[$v['r_depart_id']]['depart_title'] = $v['depart_title'];
                $new_read[$v['r_depart_id']]['user'][] = $v;
            }
            $read_list = $new_read;
        }
        $this->view->assign('publish',$publish);//发布范围
        $this->view->assign('detail',$detail_info);//详情
        $this->view->assign('read_list',$read_list);//阅读列表
        return $this->view->fetch();
    }

    /**
     * 功能：起草信息
     */
    public function add_article()
    {
        if ('POST' == $this->method) {
            $post_data = input('post.');
            unset($post_data['check']);
            $post_data['article_user_id'] = $this->user_id;
            $validate_article = new \app\base\validate\Article();
            if(intval($post_data['article_type']) == 0){
                return $this->error('请指定信息类型');
            }
            if($post_data['article_type'] == 2){
                $result = $validate_article->scene('new_images')->check($post_data);
            }else if($post_data['article_type'] == 3){
                $result = $validate_article->scene('new_url')->check($post_data);
            }else{
                $result = $validate_article->scene('new_content')->check($post_data);
            }
            if(!$result){
                return $this->error($validate_article->getError());
            }
            if($post_data['article_type'] == 2){
                $post_data['article_images'] = serialize($post_data['article_images']);
            }
            if(isset($post_data['article_annex']) && count($post_data['article_annex']) > 0){
                $post_data['article_annex'] = serialize($post_data['article_annex']);
            }
            $post_data['article_create_time'] = time();
            Db::startTrans();
            try{
                $article_id = Db::name('article')->insertGetId($post_data);//插入数据
                if($post_data['article_status'] != 2){
                    $post_data['article_publish'] = $post_data['article_publish'].',u_1';
                    $read_list = $this->get_read_list($post_data['article_publish'],$article_id);//设置发布范围

                    if(count($read_list) > 0){
                        Db::name('article_read')->insertAll($read_list);//批量插入需要阅读的人的信息
                    }
                }
                Db::commit();
                return $this->success('发布成功',url('index'));
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('发布失败');
            }
        } else if ('GET' == $this->method) {
            $this->view->assign('cate_list',$this->get_doc_cate(3));
            $this->view->assign('publish_scope', $this->get_publish_user());
            return $this->view->fetch();
        }

    }

    /**
     * 功能：编辑信息
     * @return string|void
     */
    public function edit_article(){
        if('POST' == $this->method){
            $post_data = input('post.');
//            dump($post_data);exit;
            unset($post_data['check']);
            $post_data['article_user_id'] = $this->user_id;
            $validate_article = new \app\base\validate\Article();
            if(intval($post_data['article_type']) == 0){
                return $this->error('请指定信息类型');
            }
            if($post_data['article_type'] == 2){
                $result = $validate_article->scene('new_images')->check($post_data);
            }else if($post_data['article_type'] == 3){
                $result = $validate_article->scene('new_url')->check($post_data);
            }else{
                $result = $validate_article->scene('new_content')->check($post_data);
            }
            if(!$result){
                return $this->error($validate_article->getError());
            }
            if($post_data['article_type'] == 2){
                $post_data['article_images'] = serialize($post_data['article_images']);
            }
            if(isset($post_data['article_annex']) && count($post_data['article_annex']) > 0){
                $post_data['article_annex'] = serialize($post_data['article_annex']);
            }
            if($post_data['article_need_check'] == 1){
                $post_data['article_check_status'] = 1;
                $post_data['article_check_remark'] = '';
                $post_data['article_check_time'] = 0;
            }
            if($post_data['article_status'] == 2){
                $mail_info = Db::name('article')->where(['article_id'=>['eq',$post_data['article_id']]])->find();
                if($mail_info['article_status'] == 1){
                    return $this->error('已发布信息不可设为草稿');
                }
            }
            Db::startTrans();
            try{
                Db::name('article')->update($post_data);//插入数据
                if($post_data['article_status'] != 2){
                    $read_list = $this->get_read_list($post_data['article_publish'],$post_data['article_id'],'edit');//设置发布范围
                    if(count($read_list) > 0){
                        $read_list = $this->delete_repeat($read_list,'r_article_id','r_user_id');;//去除重复的
                        Db::name('article_read')->insertAll($read_list);//批量插入需要阅读的人的信息
                    }
                }
                Db::commit();
                return $this->success('编辑成功');
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('编辑失败，请重试');
            }
        }else if('GET' == $this->method){
            $article_id = input('article_id');
            if(intval($article_id) == 0){
                return $this->error('请指定要编辑的信息');
            }
            $map['article_id'] = ['eq',$article_id];
            if($this->user_is_super == 0){
                $map['article_user_id'] = ['eq',$this->user_id];//TODO，这里需要加上权限验证部分
            }
            $info = Db::name('article')->where($map)->find();
            if(!$info){
                return $this->error('该信息不存在');
            }
            $this->view->assign('info',$info);
            $this->view->assign('publish_scope', $this->get_publish_user());
            $this->view->assign('cate_list',$this->get_doc_cate(3));
            return $this->view->fetch();
        }
    }

    /**
     * 功能：删除信息
     * @return string
     */
    public function delete_article()
    {
        if ('POST' == $this->method) {
            $article_id = input('article_id');
            if(intval($article_id) == 0){
                return $this->error('请指定要删除的信息');
            }
            $article_map['article_id'] = ['eq',$article_id];
            $article_read_map['r_article_id'] = ['eq',$article_id];
            Db::startTrans();
            try{
                Db::name('article')->where($article_map)->delete();
                Db::name('article_read')->where($article_read_map)->delete();
                Db::commit();
                return $this->success('删除成功');
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('删除失败，请重试');
            }
        } else if ('GET' == $this->method) {
            return $this->error('请求错误');
        }
    }

    /**
     * @throws \think\Exception
     * 功能：信息评论
     */
    public function comment_article(){
        if ('POST' == $this->method) {
            $data = input('post.');
            if(intval($data['article_id']) == 0){
                return $this->error('请指定要评论的信息');
            }else if(trim($data['comment']) == ''){
                return $this->error('评论内容不能为空');
            }
            $map['r_article_id'] = ['eq',$data['article_id']];
            $map['r_user_id'] = ['eq',$this->user_id];
            $article_info = Db::name('article_read')->where($map)->find();
            if(!$article_info){
                return $this->error('该信息不存在');
            }
            $article_info['r_comment'] = $data['comment'];
            $article_info['r_comment_time'] = time();
            Db::startTrans();
            try{
                Db::name('article_read')->update($article_info);
                Db::commit();
                return $this->success('评论信息成功');
            }catch (\PDOException $e){
                Db::rollback();
                return $this->error('评论信息失败，请重试');
            }
        }else if ('GET' == $this->method) {
            return $this->error('请求错误');
        }
    }

    /**
     * @param string $scope_str
     * @param int $article_id
     * @param string $type
     * @return array
     */
    private function get_read_list($scope_str='',$article_id=0,$type='new'){
        $user_list  = $this->set_doc_scope($scope_str);
        $read_list = [];
        if(count($user_list) > 0){
            foreach ($user_list as $k=>$v){
                $read_list[] = [
                    'r_article_id'=>$article_id,
                    'r_user_id'=>$v['user_id'],
                    'r_depart_id'=>$v['user_depart_id'],
                ];
            }
        }
        if('new' != $type){
            $old_read = Db::name('article_read')->where(['r_article_id'=>$article_id])->select();
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
                    Db::name('article_read')->where($map)->delete();
                }
            }
        }
        return $read_list;
    }
    /**
     * 预览页1
     */
    public function add_article_pre(){
        return $this->view->fetch();
    }
    /**
     * 预览页2
     */
    public function add_article_img(){
        return $this->view->fetch();
    }
}