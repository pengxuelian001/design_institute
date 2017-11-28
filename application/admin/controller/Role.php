<?php
namespace app\admin\controller;
use think\Controller;
use app\home\model\Roles;
use app\home\model\Taskgroups;
use think\Db;
use think\Cache;

class Role extends Controller
{
    public function select_role(){
        $userTbale=new Roles();
        $res=$userTbale->roleList();
        return json ($res);
    }
    public function add_role(){
        $arr= $this->request->param();
        if (!isset($arr['subprj_id']) || empty($arr['subprj_id'])) {
            return json('222');
        }
        if (!isset($arr['openid']) || empty($arr['openid'])) {
            return json('333');
        }
        if (!isset($arr['role_id']) || empty($arr['role_id'])) {
            return json('444');
        }
        $subproject_id =$arr['subprj_id'];
        //  $creator_id = $arr['creator_id'];
        $openid = $arr['openid'];
        $role_id = $arr['role_id'];
        foreach($role_id as $k=>$v){
            $data['role_id']=$v;
            $data['subproject_id'] = $subproject_id;
            $data['openid'] = $openid;
            $data['create_time'] = date("Y-m-d H:i:s");
            $data['update_time'] = date("Y-m-d H:i:s");
            $id = Db::table('ipm_inst_subproject_user')->insert($data);
            if($id){
                $res['success'] = true;
                $res['message'] = "success";
            }
        }
        return json($res);


    }
    // 查看某个项目已分配的人员和权限
    public function project_role_list(){
        $arr= $this->request->param();
        $subproject_id= $arr['subproject_id'];
        if (!isset($subproject_id) || empty($subproject_id)) {
            return json('subproject_id empty');
        }
       if(!isset($arr['role_id']) || empty($arr['role_id'])){
           $roleTable= new Roles();
           $subproject_list=$roleTable->role_user_list($subproject_id);
           foreach ($subproject_list as $kk => $vv) {
               $res = "," . $subproject_list[$kk]['role_id'];
               $subproject_list[$kk]['roles']['var_1'] = 'false';
               $subproject_list[$kk]['roles']['var_2'] = 'false';
               $subproject_list[$kk]['roles']['var_3'] = 'false';
               $subproject_list[$kk]['roles']['var_4'] = 'false';
               $subproject_list[$kk]['roles']['var_5'] = 'false';
               $subproject_list[$kk]['roles']['var_6'] = 'false';
               $subproject_list[$kk]['roles']['var_7'] = 'false';

               if (stripos($res, '1')) {
                   $subproject_list[$kk]['roles']['var_1'] = 'true';
               }
               if (stripos($res, '2')) {
                   $subproject_list[$kk]['roles']['var_2'] = 'true';
               }
               if (stripos($res, '3')) {
                   $subproject_list[$kk]['roles']['var_3'] = 'true';
               }
               if (stripos($res, '4')) {
                   $subproject_list[$kk]['roles']['var_4'] = 'true';
               }
               if (stripos($res, '5')) {
                   $subproject_list[$kk]['roles']['var_5'] = 'true';
               }
               if (stripos($res, '6')) {
                   $subproject_list[$kk]['roles']['var_6'] = 'true';
               }
               if (stripos($res, '7')) {
                   $subproject_list[$kk]['roles']['var_7'] = 'true';
               }
           }
       }else{
           $roleTable= new Roles();
           $subproject_list=$roleTable->role_user_list($subproject_id,$arr['role_id']);
           foreach ($subproject_list as $kk => $vv) {
               // $ipm_inst_role = $roleTable->roleList();
//            foreach ($ipm_inst_role as $kkk => $vvv) {
//                echo '<pre>';
//                print_R($subproject_list);
//                if ($subproject_list[$kk]['role_id'] == $ipm_inst_role[$kkk]['id']) {
//
//                    $subproject_list[$kk]['roles']['var_' . $kkk] = 'true';
//                } else {
//                    $subproject_list[$kk]['roles']['var_' . $kkk] = 'false';
//                }
//
//            }

               $res = "," . $subproject_list[$kk]['role_id'];
               $subproject_list[$kk]['roles']['var_1'] = 'false';
               $subproject_list[$kk]['roles']['var_2'] = 'false';
               $subproject_list[$kk]['roles']['var_3'] = 'false';
               $subproject_list[$kk]['roles']['var_4'] = 'false';
               $subproject_list[$kk]['roles']['var_5'] = 'false';
               $subproject_list[$kk]['roles']['var_6'] = 'false';
               $subproject_list[$kk]['roles']['var_7'] = 'false';

               if (stripos($res, '1')) {
                   $subproject_list[$kk]['roles']['var_1'] = 'true';
               }
               if (stripos($res, '2')) {
                   $subproject_list[$kk]['roles']['var_2'] = 'true';
               }
               if (stripos($res, '3')) {
                   $subproject_list[$kk]['roles']['var_3'] = 'true';
               }
               if (stripos($res, '4')) {
                   $subproject_list[$kk]['roles']['var_4'] = 'true';
               }
               if (stripos($res, '5')) {
                   $subproject_list[$kk]['roles']['var_5'] = 'true';
               }
               if (stripos($res, '6')) {
                   $subproject_list[$kk]['roles']['var_6'] = 'true';
               }
               if (stripos($res, '7')) {
                   $subproject_list[$kk]['roles']['var_7'] = 'true';
               }
           }
       }

      return json($subproject_list);
    }

    //删除项目权限表
    public function del_project_role(){
        $arr= $this->request->param();
        $subproject_id= $arr['subproject_id'];
        $openid= $arr['openid'];
        if (!isset($subproject_id) || empty($subproject_id)) {
            return json('111');
        }
        if (!isset($openid) || empty($openid)) {
            return json('222');
        }
        $result=Db::table('ipm_inst_subproject_user') ->where("subproject_id='$subproject_id' and openid='$openid'")->delete();
        if($result){
            $res['success'] = true;
            $res['message'] = "success";
            return json($res);
        }
    }
     public function test($n){
         echo $n.'&nbsp;&nbsp;';
         if($n>0){
            $this-> test($n-1);
         }else{
             echo '<---->';

         }
        // echo $n.'&nbsp;&nbsp;';

     }
    public function digui(){
        $a=$this->test(10);
        echo $a;
    }

}