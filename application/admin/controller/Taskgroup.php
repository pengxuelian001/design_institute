<?php
namespace app\admin\controller;
use app\home\model\Taskparters;
use app\home\model\Tasks;
use think\Controller;
use app\home\model\Taskgroups;
use app\home\model\Subprojectusers;
use think\Db;
use think\Cache;
use think\Request;

class Taskgroup extends Controller
{
    public  function add_taskgroup(){
        $arr= $this->request->param();
        if (!isset($arr['subprj_id']) || empty($arr['subprj_id'])) {
            return json('111');
        }
        if (!isset($arr['openid']) || empty($arr['openid']) ) {
            return json('222');
        }
        if (!isset($arr['task_group_name']) || empty($arr['task_group_name']) ) {
            return json('333');
        }
        if (!isset($arr['role_id']) || empty($arr['role_id']) ) {
            return json('444');
        }
        //封装成一个数组
            $data['subproject_id']=$arr['subprj_id'];
            $data['creator_id']=$arr['openid'];
            $data['name']=$arr['task_group_name'];
            $data['role_id']=$arr['role_id'];
            $data['create_time']=date("Y-m-d H:i:s");
            $data['update_time']=date("Y-m-d H:i:s");
            //new model 类
           // $taskgroupTbale= new Taskgroups();
            //添加一条数据
            $res=Db::table('ipm_inst_subproject_taskgroup')->insertGetId($data);
            if($res){
                $res1['success'] = true;
                $res1['message'] = "add success";
            }else{
                $res1['success'] = false;
                $res1['message'] = 'error';
            }
        return json ($res1);

    }
    public function taskgroup_task_list(){
        if(request()->isGet())
        {
            $subproject_id = input('subprj_id');
            $taskgroup_id  = input('taskgroup_id');
            $urgent  = input('urgent');
            $state  = input('state');
            $creator_id  = input('creator_id');
            $changer_id  = input('changer_id');
            $parter_id  = input('parter_id');
            $role_id  = input('role_id');
            $open_id  = input('open_id');
            $task_id = input('task_id');

            if(!isset($subproject_id))
                return json();
            $sql = "SELECT DISTINCT a.id,a.name,a.creator_id,c.nickname as creator_nickname,
			c.headimgurl as creator_headimgurl,a.role_id,a.update_time,a.create_time
			from ipm_inst_subproject_taskgroup a
			left join ipm_user c on a.creator_id = c.openid
			left join ipm_inst_subproject_task d on d.taskgroup_id = a.id
			where a.subproject_id  =$subproject_id";
            if(isset($taskgroup_id))
            {
                $sql = $sql." and  a.id = '$taskgroup_id'";
            }
            if(isset($role_id))
            {
                $sql = $sql." and  a.role_id = '$role_id'";
            }
            if(isset($task_id))
            {
                $sql = $sql." and  d.id = '$task_id'";
            }

            $list= Db::query($sql);
            if(!isset($list) || empty($list))
            {
                return json();
            }
            foreach($list as $k=>$v)
            {
                $sql = "SELECT DISTINCT a.id,a.name,a.creator_id,c.nickname as creator_nickname,
			             a.changer_id,a.urgent,a.state, a.remarks,a.start_time_plan,a.end_time_plan,a.start_time_real,
			             a.end_time_real,c.headimgurl as creator_headimgurl,a.update_time,a.create_time
			             from ipm_inst_subproject_task a
			             left join ipm_user c on a.creator_id = c.openid";
                if(isset($parter_id) || isset($open_id))
                {
                    $sql = $sql." left join ipm_inst_subproject_taskparter d on a.id = d.task_id";
                }
                $tmp_taskgroup_id = $list[$k]['id'];
                $sql = $sql." where a.taskgroup_id  = $tmp_taskgroup_id";
                if(isset($urgent))
                {
                    $sql = $sql." and  a.urgent = '$urgent'";
                }
                if(isset($state))
                {
                    $sql = $sql." and  a.state = '$state'";
                }
                if(isset($open_id))
                {
                    $sql = $sql." and ( a.creator_id = '$open_id' or  a.changer_id = '$open_id' or d.openid = '$open_id')";
                }
                if(isset($creator_id))
                {
                    $sql = $sql." and  a.creator_id = '$creator_id'";
                }
                if(isset($changer_id))
                {
                    $sql = $sql." and  a.changer_id = '$changer_id'";
                }
                if(isset($parter_id))
                {
                    $sql = $sql." and  d.openid = '$parter_id'";
                }
                if(isset($task_id))
                {
                    $sql = $sql." and  a.id = '$task_id'";
                }
                $taskList = Db::query($sql);

//                if(empty($taskList))
//                {
//                    unset($list[$k]);
//                    continue;
//                }
                $TaskpartersTable=new Taskparters();
                foreach($taskList as $kk=>$vv)
                {
                    $taskList[$kk]['changer_nickname'] = $this->getUserName($taskList[$kk]['changer_id']);
                    $taskList[$kk]['changer_headimgurl'] = $this->getUserheadimgurl($taskList[$kk]['changer_id']);
                    $task_idTmp= $taskList[$kk]['id'];
                    $taskList[$kk]['parter_list']=$TaskpartersTable->taskparter_id($task_idTmp);
                }
                $list[$k]['subtask_list'] = $taskList;
            }

            $outputList = array();
            foreach($list as $k=>$v)
                $outputList[] = $v;
            return json($outputList);
        }
        else
        {
            return json();
        }

    }

    public  function  getUserName($openid)
    {
        if(!isset($openid))
            return false;
        $read= Db::query("SELECT nickname FROM `ipm_user` where `openid` ='$openid'");
        if(!isset($read) || empty($read))
            return "";
        return $read[0]['nickname'];
    }

    public  function  getUserheadimgurl($openid)
    {
        if(!isset($openid))
            return false;
        $read= Db::query("SELECT headimgurl FROM `ipm_user` where `openid` ='$openid'");
        if(!isset($read) || empty($read))
            return "";
        return $read[0]['headimgurl'];
    }

    public function del_taskgroup(){
        $arr= $this->request->param();
        $taskgroup_id=$arr['taskgroup_id'];
        $taskgroupTable=new Taskgroups();
        $taskTable=new Tasks();
        $taskgrouplist=$taskTable->task_id($taskgroup_id);
        $a=false;
        foreach($taskgrouplist as $k=>$v){
            if($taskgrouplist[$k]['state']==2 or $taskgrouplist[$k]['state']==3){
                $a = true;
                     break;
            }
        }
        if($a)
        {
            $res1['success'] = false;
            $res1['message'] = 'state==2 or state==3';
            return json ($res1);
        }else{
            $res=$taskgroupTable->del_taskgroup($taskgroup_id);
            if($res){
                $result=$taskTable->del_task_taskgroup_id($taskgroup_id);
                    $res1['success'] = true;
                    $res1['message'] = "delete success";
                    return json ($res1);
            }
        }
    }

}