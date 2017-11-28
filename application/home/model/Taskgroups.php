<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Taskgroups extends Model{
    protected $table = 'ipm_inst_subproject_taskgroup';
    public function add_taskgroup($data){
        $list = DB::table($this->table)->insertGetId($data);
        return $list;
    }
    public  function taskparter_list($subprj_id){
        $list = DB::table($this->table)
            ->alias('a')
            ->join('ipm_user b','a.creator_id=b.openid')
            ->field(['a.id','a.name','a.creator_id','b.nickname','a.role_id','a.create_time','a.subproject_id'])
            ->where('subproject_id',$subprj_id)->select();
        return $list;

    }

    public function del_taskgroup($taskgroup_id){
        $list = DB::table($this->table)->where('id',$taskgroup_id)->delete();
        return $list;
    }
    public function taskgroup_id($subprj_id){
        $list = DB::table($this->table)->where('subproject_id',$subprj_id)->select();
        return $list;
    }
    public function select_id($subproject_id){
        $list= Db::query("    select b.id as taskgroup_id,b.name,a.creator_id,a.changer_id,a.urgent,c.openid,count(a.taskgroup_id)as num from ipm_inst_subproject_task as a
                                     left join ipm_inst_subproject_taskgroup as b on
                                     a.taskgroup_id=b.id
                                     left join  ipm_inst_subproject_taskparter as c on
                                     c.task_id=a.id where b.subproject_id='$subproject_id' group by a.taskgroup_id
                         ");
        return $list;
    }

}

