<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Subprojects extends Model
{
    protected $table = 'ipm_inst_subproject';
    public function file_subprj_project_list($subproject_id,$project_id,$company_id){
        $data= Db::query(" select b.type,b.id as file_id,a.id as subproject_id,c.company_id,c.id as project_id,b.name from ipm_inst_subproject as a
                                     left join ipm_inst_file as b on
                                      a.id=b.subproject_id
                                     left join ipm_inst_project as c on
                                     c.id=a.project_id
                                     where a.id=$subproject_id and  a.project_id=$project_id and c.company_id=$company_id
                         ");
        return $data;
    }
    public function subpr_project_config_list($project_id,$company_id,$subproject_id){
        $data= Db::query("     select
                                     a.id as subproject_id,c.company_id,c.id as project_id,d.id as config_id,d.name as config_name
                                      from ipm_inst_subproject as a
                                    left join ipm_inst_project as c on
                                     c.id=a.project_id
                                    left join ipm_inst_configuration as d on
                                     d.id=c.config_id
                                      where  a.project_id=$project_id and c.company_id=$company_id and a.id=$subproject_id
                         ");
        return $data;
    }
    public function get_state($project_id){
        $list = DB::table($this->table)->where('project_id',$project_id)->select();
        return $list;
    }
    public  function subproject_List($prj_id){
        $list = DB::table($this->table)
            ->field(['id','name'])
            ->where("project_id='$prj_id'")
            ->select();
        return $list;

    }
    public function find_state($subprj_id,$state){
        $data= Db::query("select
                                 a.changed_state,a.update_time,a.create_time,a.prev_state,b.nickname,b.headimgurl
                                  from ipm_inst_subproject_state_change as a
                                  left JOIN ipm_user as b on
                                  a.openid=b.openid
                                  where  a.subproject_id='$subprj_id' and a.changed_state<='$state'
                         ");
        return $data;
    }
}