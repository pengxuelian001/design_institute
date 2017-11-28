<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Tasks extends Model{
    protected $table = 'ipm_inst_subproject_task';
    public  function add_task($data){
        $list = DB::table($this->table)->insertGetId($data);
        return $list;

    }
    public  function update_task($subtask_id,$data){
        $list = DB::table($this->table)->where('id',$subtask_id)->update($data);
        return $list;
    }
    public function task_id($taskgroup_id){
        $list = DB::table($this->table)->where('taskgroup_id',$taskgroup_id)->select();
        return $list;
    }
    public function task_state($task_id){
        $list = DB::table($this->table)->where('id',$task_id)->value('state');
        return $list;
    }
    public  function tasklist($taskgroup_id){
        $list = DB::table($this->table)
             ->alias('a')
            ->join('ipm_user b','a.changer_id=b.openid')
            ->field(
                    ['a.id as task_id','a.taskgroup_id','a.name','a.changer_id','b.nickname','a.urgent','a.state','a.remarks','a.start_time_plan','a.end_time_plan'

                    ])
            ->where('taskgroup_id',$taskgroup_id)
            ->select();
        return $list;
    }
    public  function tasklist1($taskid){
        $list = DB::table('ipm_inst_subproject_taskparter')
            ->alias('a')
            ->join('ipm_user b','a.openid=b.openid')
            ->field(
                ['a.openid','b.nickname'])
            ->where('task_id',$taskid)
            ->select();
        return $list;
    }
    public function select_TaskList($taskgroup_id,$time,$str){
        $list=Db::query("select count(state) as sum from ipm_inst_subproject_task where taskgroup_id='$taskgroup_id' and state!='1'
                                            AND
                                           date_format('$time','$str')>=date_format(update_time, '$str')and
                                            date_format('$time','$str')<=date_format(update_time, '$str')
                         ");
        return $list;
    }
//    public function select_TaskList_1($taskgroup_id,$time,$str){
//        $list=Db::query("select sum(state) as sum_1 from ipm_inst_subproject_task where taskgroup_id='$taskgroup_id' and
//                                           date_format('$time','$str')>=date_format(update_time, '$str')and
//                                            date_format('$time','$str')<=date_format(update_time, '$str')
//                         ");
//        return $list;
//    }
    public function select_TaskList_1($taskgroup_id,$time,$str){
        $list=Db::query("select count(state) as sum_1 from ipm_inst_subproject_task where taskgroup_id='$taskgroup_id' and
                                           date_format('$time','$str')>=date_format(create_time, '$str')and
                                            date_format('$time','$str')<=date_format(create_time, '$str')
                         ");
        return $list;
    }


    public function del_task($task_id){
        $list = DB::table($this->table)->where('id',$task_id)->delete();
        return $list;
    }
    public function del_task_taskgroup_id($taskgroup_id){
        $list = DB::table($this->table)->where('taskgroup_id',$taskgroup_id)->delete();
        return $list;
    }
    public function update_state($taskgroup_id,$data){
        $list = DB::table($this->table)->where('taskgroup_id',$taskgroup_id)->update($data);
        return $list;
    }
    public function check_chargerlist($subproject_id){
        $list=Db::query("SELECT DISTINCT a.changer_id,c.nickname as changer_Name,c.openid,c.headimgurl
                              from ipm_inst_subproject_task a
                              left join ipm_user c on a.changer_id = c.openid
                              left join ipm_inst_subproject_taskgroup d on d.id = a.taskgroup_id
                              where d.subproject_id = '$subproject_id' and d.role_id != 6 and a.changer_id !=''
                         ");
        return $list;
    }
}