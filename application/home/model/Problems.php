<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Problems extends Model
{
    protected $table = 'ipm_inst_problem';
    public  function problemList($company_id,$subproject_id){
        $list = DB::table($this->table)
            ->alias('a')
            ->join('ipm_user b','a.creator_id=b.openid')
            ->field(['a.*','b.nickname'])
            ->where("company_id='$company_id' and subproject_id='$subproject_id'")
            ->select();
        return $list;
    }
}