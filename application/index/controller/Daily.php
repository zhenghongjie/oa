<?php
/**
 * Created by PhpStorm.
 * User: 作者
 * Date: 2017/6/26
 * Time: 9:35
 * 考勤
 */
namespace app\index\controller;
use think\Db;
header("Content-Type:text/html;charset=utf-8");
class Daily extends Common{
    public function index(){
        $depart_map['depart_id'] = ['gt',0];
        $depart_list = Db::name('depart')->where($depart_map)->field('depart_id,depart_title')->select();
        $this->view->assign('depart_list',$depart_list);
        $user_map['user_id'] = ['gt',0];
        $user_list = Db::name('user')->where($user_map)->field('user_id,user_depart_id,user_real_name')->select();
        $this->view->assign('user_list',$user_list);
        $map['daily_id'] = ['gt',0];
        $daily_list = Db::name('daily')->where($map)->order('daily_name ASC,daily_date_time ASC')->select();
        if($daily_list){
            $new_list= [];
            foreach ($daily_list as $k=>$v){
                $new_list[$v['daily_depart_name']][$v['daily_date']]['user'][$v['daily_name']][] = [
                    'daily_time'=>$v['daily_time'],
                    'daily_time_str'=>$v['daily_time_str'],
                    'daily_date'=>$v['daily_date'],
                ];
            }
            foreach ($new_list as $k=>$v) {
                foreach ($v as $k1=>$v1){
                    $new_list[$k][$k1]['count'] = ['later'=>0,'early'=>0,'absence'=>0];
                    foreach ($v1['user'] as $k2=>$v2){
                        if(isset($v2[0])){
                            $str_t1 = strtotime(date('Y-m-d 08:30:00',$v2[0]['daily_time']));
                            if($str_t1 < $v2[0]['daily_time']){
                                $new_list[$k][$k1]['count']['later'] +=1;
                            }

                        }
                        if(isset($v2[1])){
                            $str_e1 = strtotime(date('Y-m-d 12:00:00',$v2[1]['daily_time']));
                            if($str_e1 > $v2[1]['daily_time']){
                                $new_list[$k][$k1]['count']['early'] +=1;
                            }

                        }
                        if(isset($v2[2])){
                            $str_t2 = strtotime(date('Y-m-d 14:00:00',$v2[2]['daily_time']));
                            if($str_t2 < $v2[2]['daily_time']){
                                $new_list[$k][$k1]['count']['later'] +=1;
                            }

                        }
                        if(isset($v2[3])){
                            $str_e2 = strtotime(date('Y-m-d 18:00:00',$v2[3]['daily_time']));
                            if($str_e2 > $v2[3]['daily_time']){
                                $new_list[$k][$k1]['count']['early'] +=1;
                            }

                        }
                    }

                }
            }
            dump($new_list);exit;
        }

        $this->view->assign('daily_list',$daily_list);
        return $this->view->fetch();
    }
    public function import_daily(){
        if('POST' == $this->method){
            $date = input('post.daily_date');
            if(trim($date) == ''){
                return $this->error('请选择日期');
            }
            if(strtotime($date) > time()){
                return $this->error('超出时间范围');
            }
            require_once EXTEND_PATH.'PHPExcel/PHPExcel.php';

            if(@is_uploaded_file($_FILES['file']['tmp_name'])){
                $split = explode('.',$_FILES['file']['name']);
                if($split[1] != 'xls'){
                    return $this->error('必须是97-2003格式的表格，xls格式');
                }
                $info = upload('excel/','');
                $file_path = '/data/wwwroot/ny.dmgc.us/public/uploads/'.$info['file']['savepath'].$info['file']['savename'];
                chmod($file_path,0777);
//                $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
                $objPHPExcel = $objReader->load($file_path);//加载文件
                $sheet = $objPHPExcel->getSheet(0);//取得sheet(0)表
                $total_cols = $sheet->getHighestColumn();//取得总列数
                $highestRow = $sheet->getHighestRow(); // 取得总行数
                $list = [];
                for($i=2;$i<=$highestRow;$i++)
                {
                    $tmp = [
                        'daily_depart_name'=>$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(ord('A') - 65, $i)->getValue(),
                        'daily_name'=>$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(ord('B') - 65, $i)->getValue(),
                        'daily_no'=>$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(ord('C') - 65, $i)->getValue(),
                        'daily_time_str'=>$this->to_date($objPHPExcel->getActiveSheet()->getCellByColumnAndRow(ord('D') - 65, $i)->getValue()),
                        'daily_time'=>$this->to_time($objPHPExcel->getActiveSheet()->getCellByColumnAndRow(ord('D') - 65, $i)->getValue()),
                        'daily_machine_no'=>$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(ord('E') - 65, $i)->getValue(),
                        'daily_number'=>$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(ord('F') - 65, $i)->getValue(),
                        'daily_method'=>$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(ord('G') - 65, $i)->getValue(),
                        'daily_card_no'=>$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(ord('H') - 65, $i)->getValue(),
                        'daily_create_time'=>time(),
                        'daily_date'=>$date,
                        'daily_date_time'=>strtotime($date)
                    ];
                    $list[] = $tmp;
                }
                unlink($file_path);//删除原来的临时文件
                if(count($list) > 0){
                    Db::startTrans();
                    try{
                        $delete_map['daily_date'] = ['eq',$date];
                        Db::name('daily')->where($delete_map)->delete();//删除原来的
                        Db::name('daily')->insertAll($list);//插入新的
                        Db::commit();

                        return $this->success('导入成功',url('index'));
                    }catch (\PDOException $e){
                        Db::rollback();
                        return $this->error('导入失败');
                    }
                }else{
                    return $this->error('没有数据');
                }

            }else{
                return $this->error('没有上传文件');
            }

        }else{
            return $this->view->fetch();
        }
    }

    /**
     * PHP 的时间函数是从1970-1-1日开始计算的，单位是秒数
     * EXCEL的是从1900-1-1日开始算的单位是天数
     *  EXCEL中 1970-1-1 代表的数字,我查了是25569
     * 实测有8个小时的偏差
     * @param $float
     * @return bool|string
     */
    private function to_date($float){

        $time = ($float - 25569) * 24*60*60-8*60*60; //获得秒数
        return date('Y-m-d H:i:s', $time);   //出来 2011-10-31
    }

    /**
     * 转换成时间戳
     * @param $float
     * @return mixed
     */
    private function to_time($float){
        $time = ($float - 25569) * 24*60*60-8*60*60; //获得秒数
        return $time;
    }
}