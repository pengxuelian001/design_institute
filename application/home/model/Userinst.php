<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Userinst extends Model
{
    protected $table = 'ipm_inst_user';
//    public function inst_user_list(){
//        $list = DB::table($this->table)
//            ->select();
//        return $list;
//    }
    public function inst_user_list($currentpage,$itemsPerPage){
        $list= Db::query("select * from  ipm_inst_user limit ".($currentpage-1)*$itemsPerPage .','.$itemsPerPage);
        return $list;
    }
//    public  function inst_userlist($company_id,$openid){
//        $list = DB::table($this->table)
//            ->where("company_id='$company_id' and openid='$openid'")
//            ->select();
//        return $list;
//    }
    public  function inst_userlist($company_id,$openid,$currentpage,$itemsPerPage){
        $list = Db::query("select * from  ipm_inst_user where company_id='$company_id' and openid='$openid' limit ".($currentpage-1)*$itemsPerPage .','.$itemsPerPage);
        return $list;
    }
    public  function select_users($openid){
        $data= Db::table('ipm_inst_user')
            ->alias('a')
            ->join('ipm_inst_company b','a.company_id=b.id')
            ->join('ipm_user c','a.openid=c.openid')
            ->field(['b.id','b.name','a.status','c.nickname','c.headimgurl','c.remark'])
            ->where("a.openid='$openid'")
            ->select();
        return $data;
    }
    public  function select_Liset($company_id){
        $list = DB::table($this->table)
            ->alias('a')
            ->join('ipm_user b','a.openid=b.openid')
            ->field(['a.openid','b.nickname','b.headimgurl','a.company_id','a.status','a.create_time','a.update_time'])
            ->where("company_id='$company_id'")
            ->select();
        return $list;

    }
}