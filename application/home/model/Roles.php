<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Roles extends Model
{
    protected $table = 'ipm_inst_role';
    public  function roleList(){
        $list = DB::table($this->table)
            ->select();
        return $list;
    }
    public  function value_role($openid){
        $list = DB::table('ipm_inst_subproject_user')
            ->field(['role_id'])
            ->where('openid',$openid)
            ->select();
        return $list;
    }

    public function role_user_list($subproject_id){
        $list= Db::query("  select a.subproject_id,a.openid,a.create_time,GROUP_CONCAT(a.role_id) as role_id,c.headimgurl,c.nickname from  ipm_inst_subproject_user as a
                                    left join  ipm_inst_role as b on a.role_id=b.id
                                    left join  ipm_user as c on a.openid=c.openid
                                    where  a.subproject_id=$subproject_id
                                     group by a.openid
                         ");
        return $list;
    }
}