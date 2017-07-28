<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 获取指定键名的值
 * @param $arr 输入数组
 * @param $index 键名
 * @return array 键值数组
 */
function indexs($arr,$index){
    $tmp = array();
    foreach($arr as $k=>$v){
        if( isset($v[$index]) && !in_array($v[$index],$tmp)){
            $tmp[] = $v[$index];
        }
    }
    return $tmp;

}
function get_date_lunar(){
    $str = '';
    $str .= date("Y年m月d日").',';
    $lunar = new \Lunar();
    $lunar_arr = $lunar->convertSolarToLunar(date("Y"),date("m"),date("d"));
    $week_array=array("日","一","二","三","四","五","六");
    $week = date('w');
    $str .= '星期'.$week_array[$week].',';
    $str .= '农历'.$lunar_arr[1].$lunar_arr[2];
    return $str;
}
/**
 * 上传方法
 * @param string $savaPath 保存路径
 * @param array $exts 文件类型
 * @param int $maxSize 最大容量
 * @return array|bool
 */
function upload($savePath='',$saveName='uniqid',$exts=['jpg', 'gif', 'png', 'jpeg','doc','docx','pdf','xls','xlsx'],$maxSize=50*1024*1024){
    $setting=config('UPLOAD_SITEIMG_OSS');
    header('content-type:text/html;charset=utf-8;');
    $upload = new \think\Upload($setting);// 实例化上传类
    $upload->maxSize   =     $maxSize ;// 设置附件上传大小
    $upload->exts      =     $exts;// 设置附件上传类型
    $upload->rootPath  =     './uploads/';//设置的上传根目录
    $upload->autoSub   =     true;
    $upload->savePath  =      $savePath; // 设置附件上传目录
    $upload->saveName = $saveName;//保存的文件名
    // 上传文件
    $info   =   $upload->upload();
    if(!$info) {
        return false;
    }else{
        return $info;       //返回一个数组

    }
}
function get_file_size($len=0){
    $out = '';
    $i=4;
    while($i){
        if(($out=$len/pow(1024,$i))>1.0||$i==1){
            switch($i){
                case 4: {printf("%.2f TB",$out);break;}
                case 3: {printf("%.2f GB",$out);break;}
                case 2: {printf("%.2f MB",$out);break;}
                case 1: {printf("%.2f KB",$out);break;}
            }
            break;
        }
        $i--;
    }

}