<?php
namespace app\admin\controller;
use think\Controller;
use app\home\model\Users;
use app\home\model\Userinst;
use app\home\model\Projects;
use think\Db;
use think\Cache;
use think\Request;

class User extends Controller
{
    //ipm用户列表
    /**
     * @return \think\response\Json
     */
    public function selectUser(){
        if(request()->isGet()) {
            $openid= input('openid');
            if(!isset($openid)  || empty($openid)){
                return json ();
            }
            //首先判断是不是IPM 用户
            $userTbale=new Users();
            $res=$userTbale->select_users($openid);
            if($res){
                $UserinstTbal=new Userinst();
                //然后再查是否是设计院用户
                $read=$UserinstTbal->select_users($openid);
                if($read){
                    $result['company_id'] = $read[0]['id'];
                    $result['company_name'] = $read[0]['name'];
                    $result['status'] = $read[0]['status'];
                    $result['nickname'] = $read[0]['nickname'];
                    $result['headimgurl'] = $read[0]['headimgurl'];
                    $result['remark'] = $read[0]['remark'];
                    return json ($result);
                }else{
                    $result['company_id'] = -1;
                    $result['company_name'] = "";
                    $result['status'] = -1;
                    return json ($result);
                }
            }else{
                $res['company_id'] = -1;
                $res['company_name'] = "";
                $res['status'] = -1;
                return json ($res);
            }
        }
    }
    //imp 用户查询
    public function ipm_user_list(){
        $arr= $this->request->param();
        $company_id=$arr['company_id'];
        $currentpage=$arr['currentpage'];
        $itemsPerPage=$arr['itemsPerPage'];
        $userTable=new Users();
        $UserinstTable=new Userinst();
        //这个公司下面的设计管理系统用户
        $res=$userTable->ipm_users($currentpage,$itemsPerPage);
        foreach($res as $kk=>$vv){
            $id=$res[$kk]['openid'];
            $res1=$UserinstTable->inst_userlist($company_id,$id,$currentpage,$itemsPerPage);
            if($res1){
                $res[$kk]['company_id']=1;
            }else{
                $res[$kk]['company_id']=0;
            }
        }
        return json ($res);

    }
    //设计院用户查询
    public function ipm_inst_userlist(){
        $UserinstTable=new Userinst();
        $currentpage=1;
        $itemsPerPage=20;
        $res=$UserinstTable->inst_user_list($currentpage,$itemsPerPage);
        return json ($res);

    }
    //  修改用户
    public function update_inst_user(){
        $arr= $this->request->param();
        $data['telphone']=$arr['telphone'];
        $data['qq']=$arr['qq'];
        $data['nickname']=$arr['nickname'];
        $data['openid']=$arr['openid'];
        $UserTable=new Users();
        $res=$UserTable->update_ipm_users($data);
        if($res){
            $res['success'] = true;
            $res['message'] = "success";
            return json ($res);
        }else{
            $res['success'] = false;
            $res['message'] = 'error';
            return json ($res);
        }

    }
    //本人所参与的所有项目信息
    public  function user_project_list(){
        if(request()->isGet()) {
            $openid= input('openid');
            if(!isset($openid) || empty($openid)){
                return json ();
            }
            $userTbale=new Users();
            $subproject_list=$userTbale->subproject_user_list($openid);
            $project_list = array();    //总项目列表

            //遍历用户参与的所有子项目
            foreach ($subproject_list as $kk => $vv) {
                $project_id = $subproject_list[$kk]['project_id'];
                $list=$userTbale->project_config_users($project_id);
                $bFound = false;//是否找到总项目
                foreach($project_list as $k=>$one_project)
                {

                    if($one_project['project_id'] == $list[0]['project_id'])
                    {
                        $bFound = true;
                        $project_list[$k]['subproject_list'][] = $subproject_list[$kk];
                        break;
                    }
                }
                if(!$bFound)
                {
                    $one_project['project_id'] = $list[0]['project_id'];
                    $one_project['name'] = $list[0]['name'];
                    $one_project['creator_id'] = $list[0]['creator_id'];
                    $one_project['creator_nickname'] = $list[0]['creator_nickname'];
                    $one_project['config_id'] = $list[0]['config_id'];
                    $one_project['config_name'] = $list[0]['config_name'];
                    $fileNameArry = explode(".",  $one_project['config_name']);
                    //获取数组最后一位
                    $fileNameTitle = end($fileNameArry);
                    $one_project['config_url'] = SET_URL."/design_institute/public/PjrFiles/".$list[0]['company_id'].'/'.'configFiles/'.$list[0]['config_id'].'.'.$fileNameTitle;
                    $one_project['state'] = $list[0]['state'];
                    $one_project['start_time_plan'] = $list[0]['start_time_plan'];
                    $one_project['end_time_plan'] = $list[0]['end_time_plan'];
                    $one_project['start_time_real'] = $list[0]['start_time_real'];
                    $one_project['end_time_real'] = $list[0]['end_time_real'];
                    $one_project['subproject_list'][] =  $subproject_list[$kk];

                    $project_list[] = $one_project;
                }
            }
            return json($project_list);
        }
    }
    public function Userlist(){
        //get 接收
        if(request()->isGet()) {
            $company_id = input('company_id');
            $currentpage = 1;
            $itemsPerPage = 20;
            // $status = input('status');
            if (!isset($company_id) || empty($company_id)) {
                return json('111');
            }

            $userTbale = new Userinst();
            //根据company_id 和status来查设计管理平台用户
            $res = $userTbale->select_Liset($company_id);
            foreach ($res as $k => $v) {
                $openid = $res[$k]['openid'];
                $projectTable = new Projects ();
                //这个人所参与的项目列表
                $res[$k]['projectlist'] = $projectTable->project_name($openid, $currentpage, $itemsPerPage);
                foreach ($res[$k]['projectlist'] as $kk => $vv) {
                    $sub_id = $res[$k]['projectlist'][$kk]['sub_id'];
                    //这个人所参与的子项目下面的任务名称
                    $res[$k]['projectlist'][$kk]['task_name'] = $projectTable->task_name($sub_id, $openid, $currentpage, $itemsPerPage);
                }
            }
        }
        return json($res);
    }
    // 添加用户
    public  function add_user(){
        $arr= $this->request->param();
        if (!isset($arr['openid']) || empty($arr['openid']) ) {
            return json('111');
        }
        if (!isset($arr['company_id']) || empty($arr['company_id']) || !is_numeric($arr['company_id']) || $arr['company_id']<1) {
            return json('222');
        }
        if (!isset($arr['status']) || empty($arr['status']) || !is_numeric($arr['status']) || $arr['status']<1) {
            return json('333');
        }
        $data['openid']=$arr['openid'];
        $data['company_id']=$arr['company_id'];
        $data['status']=$arr['status'];
        $data['create_time']=date("Y-m-d H:i:s");
        $data['update_time']=date("Y-m-d H:i:s");
        $userTable= new Users();
        $res=$userTable->select_users($data['openid']);
        if($res){
            $user_id=Db::table('ipm_inst_user')->insertGetId($data);
            if($user_id){
                $res['success'] = true;
                $res['message'] = "success";
                return json ($res);
            }
        }else{
            //不是IPM用户
            return json('111');
        }
    }
    //添加设计院管理平台用户
    public function add_ipminst_user(){
        $arr= $this->request->param();
        if (!isset($arr['openid']) || empty($arr['openid']) ) {
            return json('openid为空');
        }
        if (!isset($arr['company_id'])  || !is_numeric($arr['company_id'])) {
            return json('company_id为空');
        }
        if (!isset($arr['role_id'])  || !is_numeric($arr['role_id'])) {
            return json('role_id为空');
        }
        $arr1 = array(
            "openid" =>$arr['openid'],
            "company_id" =>1,
            "status" =>1,
            "create_time" => date("Y-m-d H:i:s"),
            "update_time" => date("Y-m-d H:i:s")
        );
        $result=Db::table('ipm_inst_user')->where('openid',$arr['openid'])->find();
        if($result){
            $res['success'] = false;
            $res['message'] = "用户存在";
        }else{
            $user_id=Db::table('ipm_inst_user')->insert($arr1);
            if($user_id){
                $data=array(
                    'openid'=>$arr['openid'],
                    'roles'=>$arr['role_id']
                );
                $result1=Db::table('ipm_inst_user_roles')->insert($data);
                if($result1){
                    $res['success'] = true;
                    $res['message'] = "success";
                }else{
                    $res['success'] = false;
                    $res['message'] = "error";
                }
            }

        }

        return json ($res);
    }
    //删除设计院管理平台用户
    public function del_ipminst_user(){
        $arr= $this->request->param();
        if (!isset($arr['openid']) || empty($arr['openid']) ) {
            return json('111');
        }
        $openid=$arr['openid'];
        $user_id=Db::table('ipm_inst_user')->where('openid',$openid)->delete();
        if($user_id){
            $res['success'] = true;
            $res['message'] = "success";
            return json ($res);
        }
    }
    private function checkRequestData()
    {

        $json = file_get_contents("php://input");
        if (empty($json)) {
            $res['success'] = false;
            $res['message'] = 'Empty RequestData';
            return json ($res);

        }

        $read = json_decode($json,true);
        if (is_null($read)) {
            $res['success'] = false;
            $res['message'] = "json_decode_error";
            return json ($res);

        }
        return $read;
    }
}