<?php
namespace app\admin\controller;
use think\Controller;
use app\home\model\Tasks;
use app\home\model\Taskgroups;
use app\admin\controller\Config;
use think\Db;
use think\Cache;
use app\home\model\Taskparters;
use think\Request;

class Task extends Controller
{
    public function add_task(){
        $arr= $this->request->param();
        if (!isset($arr['creator_id']) || empty($arr['creator_id'])) {
            return json('creator_id empty');
        }
        if (!isset($arr['taskgroup_id']) || empty($arr['taskgroup_id']) ) {
            return json('taskgroup_id empty');
        }
        if (!isset($arr['end_time_plan']) || empty($arr['end_time_plan']) ) {
            return json('end_time_plan empty');
        }
        if (!isset($arr['subtask_name']) || empty($arr['subtask_name'])) {
            return json('subtask_name empty');
        }
        if (!isset($arr['changer_id']) || empty($arr['changer_id'])) {
            return json('changer_id empty');
        }

        $data['creator_id']=$arr['creator_id'];
        $data['taskgroup_id'] = $arr['taskgroup_id'];
        $data['changer_id'] = $arr['changer_id'];
        $data['urgent'] = $arr['urgent'];
        $data['state'] =1;
        $data['remarks'] = $arr['remarks'];
        $data['end_time_plan'] = $arr['end_time_plan'];
        $data['name'] = $arr['subtask_name'];
        $taskTable= new Tasks();
        $taskpartersTable= new Taskparters();
        if(isset($arr['subtask_id']) && !empty($arr['subtask_id'])){
            $taskData = DB::table('ipm_inst_subproject_task')->where('id',$arr['subtask_id'])->select();
            if($taskData){
                if($taskData[0]['state'] == 1){
                    if (!isset($arr['parter']) || empty($arr['parter'])) {
                        //负责人为空的更新创建时间

                            $subtask_id=$arr['subtask_id'];
                            $data['update_time']=date("Y-m-d H:i:s");
                            if(empty($taskData[0]['changer_id']))
                               $data['create_time']=date("Y-m-d H:i:s");
                            $task_id=$taskTable->update_task($subtask_id,$data);
                            if($task_id){
                                $data=$taskpartersTable->del_taskparter($subtask_id);
                                $res['success'] = true;
                                $res['message'] = "update success";
                                return json ($res);

                            }else{
                                return json ();
                            }
                    }else{
                        $parter = $arr['parter'];
                        $subtask_id=$arr['subtask_id'];
                        $data['update_time']=date("Y-m-d H:i:s");
                        $data['create_time']=date("Y-m-d H:i:s");
                        //修改
                        $task_id=$taskTable->update_task($subtask_id,$data);
                        if($task_id){
                            $data=$taskpartersTable->del_taskparter($subtask_id);
                            foreach($parter as $k=>$v) {
                                $data1['openid']=$v;
                                $data1['task_id']=$subtask_id;
                                $data1['update_time']= date("Y-m-d H:i:s");
                                $data1['create_time']= date("Y-m-d H:i:s");
                                $res1=$taskpartersTable->add_taskparter($data1);
                            }
                            $res['success'] = true;
                            $res['message'] = "update success";
                            return json ($res);

                        }else{
                            return json ();
                        }
                    }
                }else{
                    $res['message'] = "项目已完成，不能修改";
                    return json ($res);
                }

            }else{
                $res['message'] = "数据查询错误";
                return json ($res);
            }


        }else {
            if (!isset($arr['parter']) || empty($arr['parter'])) {
                //添加
                $data['create_time'] = date("Y-m-d H:i:s");
                $data['update_time'] = date("Y-m-d H:i:s");
                $task_id = $taskTable->add_task($data);
                if ($task_id) {
                    $res['success'] = true;
                    $res['message'] = "add success";
                    return json($res);

                } else {
                    return json();
                }
            }else{
                //添加
                $parter = $arr['parter'];
                $data['create_time'] = date("Y-m-d H:i:s");
                $data['update_time'] = date("Y-m-d H:i:s");
                $task_id = $taskTable->add_task($data);
                if ($task_id) {
                    foreach ($parter as $k => $v) {
                        $data1['openid'] = $v;
                        $data1['task_id'] = $task_id;
                        $data1['create_time'] = date("Y-m-d H:i:s");
                        $data1['update_time'] = date("Y-m-d H:i:s");
                        $res1 = $taskpartersTable->add_taskparter($data1);
                    }

                    $res['success'] = true;
                    $res['message'] = "add success";
                    return json($res);

                } else {
                    return json();
                }
            }
        }
    }
    public function month_task_list($subprj_id){
        //获取当月的第一天
        $start_time=date('Y-m-01', strtotime(date("Y-m-d")));
        // $start_time='2017-08-01';
        $str='%Y-%m-%d';
        $ye=  explode('-', $start_time)[0];
        $me=  explode('-', $start_time)[1];
        //获取当月的最后一天
        $var = date("t",strtotime($start_time));
        $TaskgroupsTable=new Taskgroups();
        $taskTable= new Tasks();
        $res=$TaskgroupsTable->taskparter_list($subprj_id);
        for($d=0;$d<=$var;$d++){
            $time = $ye.'-'.$me.'-'.$d;
            $result[$d]['time'] = $time;
            $result[$d]['sum'] = 0;
            $result[$d]['sum_1'] = 0;
            foreach($res as $k=>$v){
                $taskgroup_id=$res[$k]['id'];
                $rel= $taskTable->select_TaskList($taskgroup_id,$time,$str);
                $rel_1= $taskTable->select_TaskList_1($taskgroup_id,$time,$str);

                $result[$d]['sum'] = intval($result[$d]['sum']) + intval($rel[0]['sum']);
                $result[$d]['sum_1'] = intval($result[$d]['sum_1']) + intval($rel_1[0]['sum_1']);
            }
        }
        return json ($result);
    }
    public function edit_taks_state(){
        $arr= $this->request->param();
        $state=$arr['state'];
        $subprj_id=$arr['subprj_id'];
        $grouptaksTable=new Taskgroups();
        $taskTable=new Tasks();
        $res=$grouptaksTable->taskgroup_id($subprj_id);
        if($res){
            $data['state']=$state;
            $data['update_time'] = date("Y-m-d H:i:s");
            foreach($res as $key=>$val){
                $taskgroup_id=$res[$key]['id'];
                $arr=$taskTable->update_state($taskgroup_id,$data);
                if($arr){
                    $res['success'] = true;
                    $res['message'] = "update success";
                }
            }
        }else{
            return json ();
        }

        return json ($res);
    }
    // 删除任务
    public function del_task(){
        $arr= $this->request->param();
        $task_id=$arr['task_id'];
        $taskTable=new Tasks();
        $taskstate=$taskTable->task_state($task_id);
       if($taskstate==2 or $taskstate==3){
           $res['success'] = false;
           $res['message'] = 'state==2 or state==3';
           return json ($res);
       }else{
           $res=$taskTable->del_task($task_id);
           if($res){
               $res1['success'] = true;
               $res1['message'] = "delete success";
               return json ($res1);
           }else{
               $res1['success'] = false;
               $res1['message'] = 'error';
               return json ($res1);
           }
       }

    }
    public function select_check_charger(){
        $arr= $this->request->param();
        if (!isset($arr['subproject_id']) || empty($arr['subproject_id'])) {
            return json('subproject_id empty');
        }
        $taskTable=new Tasks();
        $res=$taskTable->check_chargerlist($arr['subproject_id']);
        if($res){
            return json ($res);
        }else{

            return json ();
        }
    }
}