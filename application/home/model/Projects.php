<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Projects extends Model
{
    protected $table = 'ipm_inst_project';
    public  function company_projectList($company_id){
        $list = DB::table($this->table)
            ->alias('a')
            ->join('ipm_inst_company b','a.company_id=b.id')
            ->join('ipm_inst_configuration c','a.config_id=c.id')
            ->join('ipm_user d','a.creator_id=d.openid')
            ->where('a.company_id=:company_id')->bind(['company_id'=>"$company_id"])
            ->field('a.name,a.creator_id,a.state,a.start_time_plan,a.end_time_plan,a.start_time_real,a.end_time_real,b.name as company_name,c.name as config_name,d.nickName')
            ->select();
        return $list;

    }
    public function user_project_list($company_id,$currentpage,$itemsPerPage){
        $data= Db::query(" select a.id as project_id,a.name,a.creator_id,c.headimgurl,c.nickname as creator_nickname,b.id as config_id,b.name as config_name,a.state,
                                      DATE_FORMAT(a.start_time_plan, '%Y-%m-%d') AS start_time_plan,
                                      DATE_FORMAT(a.end_time_plan, '%Y-%m-%d')as end_time_plan,
                                      DATE_FORMAT(a.start_time_real, '%Y-%m-%d') as start_time_real,
                                      DATE_FORMAT(a.end_time_real, '%Y-%m-%d') as  end_time_real
                               from ipm_inst_project a
                               left join ipm_inst_configuration as b on a.config_id=b.id
                               left join ipm_user c on a.creator_id=c.openid
                               where a.company_id='$company_id' limit ".($currentpage-1)*$itemsPerPage .','.$itemsPerPage);

        return $data;
    }
    public function any_company_list(){
        $data= Db::query(" select a.id as project_id,a.name,a.company_id,a.creator_id,c.nickname as creator_nickname,b.id as config_id,b.name as config_name,a.state,
                                      DATE_FORMAT(a.start_time_plan, '%Y-%m-%d') AS start_time_plan,
                                      DATE_FORMAT(a.end_time_plan, '%Y-%m-%d')as end_time_plan,
                                      DATE_FORMAT(a.start_time_real, '%Y-%m-%d') as start_time_real,
                                      DATE_FORMAT(a.end_time_real, '%Y-%m-%d') as  end_time_real
                               from ipm_inst_project a
                               left join ipm_inst_configuration as b on a.config_id=b.id
                               left join ipm_user c on a.creator_id=c.openid
                         ");

        return $data;
    }
    public function subproject_project_list($prj_id,$currentpage,$itemsPerPage){
        $list= Db::query(" select id as subproject_id, project_id,name,state,
                                      DATE_FORMAT(start_time_plan, '%Y-%m-%d') as start_time_plan,
                                      DATE_FORMAT(end_time_plan, '%Y-%m-%d') as end_time_plan,
                                      DATE_FORMAT(start_time_real, '%Y-%m-%d') as start_time_real,
                                      DATE_FORMAT(end_time_real, '%Y-%m-%d') as end_time_real,
                                      DATE_FORMAT(create_time, '%Y-%m-%d') as create_time
                                      from  ipm_inst_subproject
                                    where project_id='$prj_id' limit ".($currentpage-1)*$itemsPerPage .','.$itemsPerPage);
        return $list;
    }
    public function subproject_role_list($sub_prjid,$openid,$currentpage,$itemsPerPage){
        $list= Db::query(" select role_id from  ipm_inst_subproject_user
                                    where subproject_id='$sub_prjid' and openid='$openid'  limit ".($currentpage-1)*$itemsPerPage .','.$itemsPerPage);
        return $list;
    }
//    public function project_name($openid){
//        $list= Db::query("    select a.id as project_id,b.id as sub_id,b.name as sub_name from ipm_inst_project as a
//                                     left join ipm_inst_subproject  as b on
//                                     a.id=b.project_id
//                                      where a.creator_id='$openid'  or  b.id in
//                                      (select c.subproject_id from ipm_inst_subproject_taskgroup c where c.creator_id='$openid')
//                         ");
//        return $list;
//    }
    public function project_name($openid,$currentpage,$itemsPerPage){
        $list= Db::query("    select DISTINCT b.id as sub_id,a.id as project_id,b.name as sub_name from ipm_inst_project as a
                                     left join ipm_inst_subproject  as b on
                                     a.id=b.project_id
                                     left join ipm_inst_subproject_taskgroup as d on
                                     b.id=d.subproject_id
                                     join ipm_inst_subproject_task as e on
                                     d.id=e.taskgroup_id
                                     join ipm_inst_subproject_taskparter as g on
                                     e.id=g.task_id
                                      where a.creator_id='$openid'  or  b.id in
                                      (select c.subproject_id from ipm_inst_subproject_taskgroup c where c.creator_id='$openid')
                                      or  d.id in (select f.taskgroup_id from ipm_inst_subproject_task f where f.creator_id='$openid' or changer_id='$openid')
                                      or e.id in (select h.task_id from ipm_inst_subproject_taskparter h where h.openid='$openid') limit " . ($currentpage - 1) * $itemsPerPage . ',' . $itemsPerPage);
        return $list;
    }
    public function subproject_name($openid){
        $list= Db::query("select a.id as taskgroup_id,b.name as sub_name from  ipm_inst_subproject_taskgroup as a
                                      left join  ipm_inst_subproject as b on
                                      a.subproject_id=b.id
                                      where a.creator_id='$openid'
                         ");
        return $list;
    }
    public function task_name($sub_id,$openid,$currentpage,$itemsPerPage){
        $list= Db::query("    select DISTINCT b.id as task_id,b.name,a.id from ipm_inst_subproject_taskgroup as a
                                       left join ipm_inst_subproject_task  as b on
                                      a.id=b.taskgroup_id
                                      left join ipm_inst_subproject_taskparter as c on
                                      b.id=c.task_id
                                      where ((a.subproject_id='$sub_id'  and  c.openid='$openid') or (a.subproject_id='$sub_id'  and b.changer_id='$openid')) and b.state='1' limit ".($currentpage-1)*$itemsPerPage .','.$itemsPerPage);
        return $list;
    }


}