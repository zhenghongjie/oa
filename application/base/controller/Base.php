<?php
/**
 * 功能：基础操作存放的地方，比如上传图片，编辑器等，不用权限检查
 * 时间：2016年12月10日10:51:51
 * 作者：冷江华
 */
namespace app\base\controller;
use think\Controller;
use think\Config;
use think\Db;
class Base extends Controller{
    /**
     * UEdtor 上传接口
     * 请求的地方路径：url('base/base/ueupload')
     */
    public function ue_upload(){
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("resource/ueditor1.4.3.3/php/config.json")), true);
        $action = $_GET['action'];
        switch ($action) {
            case 'config':
                echo json_encode($CONFIG);
                break;
            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $result = upload('ueditor/');
                if($result){
                    $temp = $result[$CONFIG['fileFieldName']];
                    $return = [
                        "state" => 'SUCCESS',
                        "url" => Config::get("image_domain").$temp['savepath'].$temp['savename'],
                        "title" => $temp['savename'],
                        "original" => $temp['name'],
                        "type" => $temp['type'],
                        "size" => $temp['size'],
                    ];
                }else{
                    $return = [
                        "state" => 'ERROR_FILE_MOVE',
                        "url" => "",
                        "title" => "",
                        "original" => "",
                        "type" => "",
                        "size" => "",
                    ];
                }
                return json_encode($return);
                break;
        }
    }

    /**
     * 功能：文件上传接口
     * @return \think\response\Json
     */
    public function upload_files(){
        $info = upload('annex/','',['doc','docx','xlsx','xls','pdf','jpg','png','jpeg']);//文件原名保存
        if(false !== $info){
            $data['file'] = Config::get('image_domain').$info['file']['savepath'].$info['file']['savename'];
            $data['file_size'] = $info['file']['size'];
            $data['file_name'] = $info['file']['savename'];
            $data['file_type'] = $info['file']['type'];
            return json(['state'=>1,'msg'=>'上传成功','data'=>$data]);
        }else{
            return json(['state'=>0,'msg'=>'上传失败','data'=>null]);
        }
    }
    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        dump($_FILES);exit;
        $file = request()->file('image');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
            echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            echo $info->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            echo $info->getFilename();
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }
}