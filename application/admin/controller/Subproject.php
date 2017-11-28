<?php
namespace app\admin\controller;
use think\Controller;
use app\home\model\Subprojects;
use app\home\model\Defaulttaskgroup;
use app\home\model\Defaulttasks;
use app\home\model\Taskgroups;
use think\Db;
use think\Cache;


class Subproject extends Controller
{
    public  function add_subproject(){
        $arr= $this->request->param();
        $project_id =$arr['prj_id'];
        $name = $arr['subprjname'];
        $company_id = $arr['company_id'];
        $creator_id = $arr['creator_id'];
        $start_time_plan = $arr['start_time_plan'];
        $end_time_plan = $arr['end_time_plan'];
        if (!isset($company_id) || empty($company_id)) {
            return json('company_id empty');
        }
        if (!isset($project_id) || empty($project_id)) {
            return json('project_id empty');
        }
        if (!isset($name) || empty($name)) {
            return json('name empty');
        }
        if (!isset($creator_id) || empty($creator_id)) {
            return json('creator_id empty');
        }
        if(!isset($start_time_plan) || empty($start_time_plan)){
            return json('start_time_plan  empty');

        }
            $arr1 = array(
                "project_id" => $project_id,
                "name" =>$name,
                "state" => 1,
                "start_time_plan" => $start_time_plan,
                "end_time_plan" => $end_time_plan,
                "start_time_real" => date("Y-m-d H:i:s"),
                "create_time" => date("Y-m-d H:i:s"),
                "update_time" => date("Y-m-d H:i:s")
            );
            //插入数据
            $id1 = Db::table('ipm_inst_subproject')->insertGetId($arr1);
            if($id1){
                //存放在本地的路劲
                $path = FILE_PATH . "/" . $company_id . '/' . $project_id . '/' . $id1 ;
                    if (!file_exists($path)) {
                        //设计读写权限
                        if (mkdir($path, 0777, true)) {

                        }
                    }
                //封装成一个数组
                $arr3 = array(
                    "subproject_id" => $id1,
                    "openid" =>$creator_id,
                    "role_id" => 1,
                    "create_time" => date("Y-m-d H:i:s"),
                    "update_time" => date("Y-m-d H:i:s")
                );
                $user_role = Db::table('ipm_inst_subproject_user')->insertGetId($arr3);
                if($user_role){
                    $default_taskgroupTable= new Defaulttaskgroup();
                    //ipm_inst_default_taskgroup表的所有数据
                    $list=$default_taskgroupTable->default_taskgroup_List();
                    foreach($list as $k=>$v){
                        $data['name']=$list[$k]['name'];
                        $data['role_id']=$list[$k]['role_id'];
                        $data['creator_id']=$creator_id;
                        $data['subproject_id']=$id1;
                        $data['update_time']=date("Y-m-d H:i:s");
                        $data['create_time']=date("Y-m-d H:i:s");
                        $result= Db::table('ipm_inst_subproject_taskgroup')->insertGetId($data);
                        $defualt_task = new Defaulttasks();
                        //查询ipm_inst_default_task条件为taskgroup_id的相关的数据
                        $list2 = $defualt_task->default_taskList($list[$k]['id']);
                        foreach($list2 as $kk=>$vv){
                            $data1['name']=$list2[$kk]['name'];
                            $data1['taskgroup_id']=$result;
                            $data1['creator_id']=$creator_id;
                            $data1['update_time']=date("Y-m-d H:i:s");
                            $data1['create_time']=date("Y-m-d H:i:s");
                            //添加
                            $result1= Db::table('ipm_inst_subproject_task')->insertGetId($data1);
                            if($result1){
                                $res['success'] = true;
                                $res['message'] = "success";
                            }
                        }
                    }
                }
            }
        return json ($res);
    }
    public function find_state(){
        $data= $this->request->param();
        if (!isset($data['subproject_id']) || empty($data['subproject_id'])) {
            return json('111');
        }
        //获取单个值
        $state = DB::table('ipm_inst_subproject')->where('id',$data['subproject_id'])->value('state');
        if($state){
            $subprj_id=$data['subproject_id'];
            $subprojectTable= new Subprojects();
            //$subprj_id 项目下小于等于$state的数据
            $arr=$subprojectTable->find_state($subprj_id,$state);
            if($arr) {
                for($i=1; $i<=$state;$i++)
                {
                    $result[$i]['state'] = $i;
//                    if($i==1)
//                    {
//                        $result[$i]['time'] = date("YmdHis",strtotime('2025-12-01 11:22:42'));
//                        echo $result[$i]['time'];
//                        foreach ($arr as $k => $v) {
//                            if(date("YmdHis",strtotime($v['create_time'])) <=  date("YmdHis",strtotime($result[$i]['time'])) &&
//                                $arr[$k]['prev_state'] == 1)
//                            {
//                                $result[$i]['state'] = 1;
//                                $result[$i]['nickname'] =$arr[$k]['nickname'];
//                                $result[$i]['headimgurl'] = $arr[$k]['headimgurl'];
//                                $result[$i]['time'] = $arr[$k]['create_time'];
//                            }
//                        }
//
//                    }
//                    else{
                     //   $result[$i]['time'] = date("YmdHis",strtotime('1889-11-13 11:22:42'));
                        foreach ($arr as $k => $v) {
                            //date("YmdHis",strtotime($v['update_time'])) >=  date("YmdHis",strtotime($result[$i]['time'])) &&
                            if($result[$i]['state'] == $arr[$k]['changed_state'])
                            {
                                $result[$i]['state'] = $arr[$k]['changed_state'];
                                $result[$i]['time'] = $arr[$k]['update_time'];
                                $result[$i]['nickname'] =$arr[$k]['nickname'];
                                $result[$i]['headimgurl'] = $arr[$k]['headimgurl'];
                            }
                        }
                    }
           //     }

            }else{
                $res['success'] = false;
                $res['message'] = "条件不满足";
                return json($res);
            }
        }else{
            $res['success'] = false;
            $res['message'] = "状态没有找到";
            return json($res);
        }
      // echo '<pre>';
    //   print_R($result);die();
       return json ($result);
    }
    public function del_subproject(){
        $arr= $this->request->param();
        if (!isset($arr['subproject_id']) || empty($arr['subproject_id'])) {
            return json('111');
        }
        $subproject_id =$arr['subproject_id'];
        //拿到state这个值
        $state = Db::table('ipm_inst_subproject')->where('id',$subproject_id)->value('state');
       if(isset($state) && $state==1 ){
           $result = Db::table('ipm_inst_subproject')->where('id',$subproject_id)->delete();
           if($result){
               $res['success'] = true;
               $res['message'] = "success";
               return json ($res);
           }else{
               $res['success'] = false;
               $res['message'] = "error1";
               return json ($res);
           }

       }else{
           $res['success'] = false;
           $res['message'] = "error";
           return json ($res);
       }
    }
}