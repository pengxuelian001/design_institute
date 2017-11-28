<?php
namespace app\admin\controller;
use app\home\model\Projects;
use app\home\model\Subprojects;
use app\home\model\Defaulttaskgroup;
use app\home\model\Defaulttasks;
use app\home\model\Taskgroups;
use app\home\model\Tasks;
use app\home\model\Roles;
use think\Controller;
use think\Db;
use ZipArchive;
use think\Cache;
use app\home\Util\PHPZip;

use think\Request;
use think\Loader;

class Project extends Controller
{
    //添加项目
    public  function  add_project(){
            $arr= $this->request->param();
            if (!isset($arr['company_id']) || empty($arr['company_id'])) {
                return json('111');
            }
            if (!isset($arr['config_id']) || empty($arr['config_id'])) {
                return json('222');
            }
            if (!isset($arr['start_time_plan']) || empty($arr['start_time_plan'])) {
                return json('333');
            }
            if (!isset($arr['end_time_plan']) || empty($arr['end_time_plan'])) {
                return json('444');
            }
            if (!isset($arr['prjname']) || empty($arr['prjname'])) {
                return json('555');
            }
            $creator_id =$arr['creator_id'];
            $company_id = $arr['company_id'];
            $name = $arr['prjname'];
            $config_id = $arr['config_id'];
            $start_time_plan = $arr['start_time_plan'];
            $end_time_plan = $arr['end_time_plan'];
            $create_sub = $arr['create_sub'];
            //先判断create_sub是否为true
            if($create_sub=='true'){
                //封装成一个数组
                $arr = array(
                    "name" => $name,
                    "company_id" =>$company_id,
                    "creator_id" => $creator_id,
                    "config_id" => $config_id,
                    "state" => 1,
                    "start_time_plan" => $start_time_plan,
                    "end_time_plan" => $end_time_plan,
                    "start_time_real" => date("Y-m-d H:i:s"),
                    "create_time" => date("Y-m-d H:i:s"),
                    "update_time" => date("Y-m-d H:i:s")
                );
                //自增一条 记录
                $id = Db::table('ipm_inst_project')->insertGetId($arr);
                if($id){
                    //创建一个文件夹读写权限
                    $paths = FILE_PATH . "/" . $company_id . '/configFiles';
                    if (!file_exists($paths)) {
                        if (mkdir($paths, 0777, true)) {
                        }
                    }
                    $arr1 = array(
                    "project_id" => $id,
                    "name" =>$name.'#',
                    "state" => 1,
                    "start_time_plan" => date("Y-m-d H:i:s"),
                    "end_time_plan" => date("Y-m-d H:i:s"),
                    "start_time_real" => date("Y-m-d H:i:s"),
                    "create_time" => date("Y-m-d H:i:s"),
                    "update_time" => date("Y-m-d H:i:s")
                     );
                    //自增一条 记录
                    $id1 = Db::table('ipm_inst_subproject')->insertGetId($arr1);
                    if($id1){
                        $paths = FILE_PATH . "/" . $company_id . '/' . $id . '/' . $id1;
                        if (!file_exists($paths)) {
                            if (mkdir($paths, 0777, true)) {
                            }
                        }
                        $arr3 = array(
                            "subproject_id" => $id1,
                            "openid" =>$creator_id,
                            "role_id" => 1,
                            "create_time" => date("Y-m-d H:i:s"),
                            "update_time" => date("Y-m-d H:i:s")
                        );
                        //自增一条 记录
                        $user_role = Db::table('ipm_inst_subproject_user')->insertGetId($arr3);
                        if($user_role){
                            $default_taskgroupTable= new Defaulttaskgroup();
                            //查询taskgroup 的所有数据
                            $list=$default_taskgroupTable->default_taskgroup_List();
                            //循环
                            foreach($list as $k=>$v){
                                $data['name']=$list[$k]['name'];
                                $data['role_id']=$list[$k]['role_id'];
                                $data['creator_id']=$creator_id;
                                $data['subproject_id']=$id1;
                                $data['create_time']=date("Y-m-d H:i:s");
                                $data['update_time']=date("Y-m-d H:i:s");
                                //自增一条 记录
                                $result= Db::table('ipm_inst_subproject_taskgroup')->insertGetId($data);
                                $defualt_task = new Defaulttasks();
                                // 格局taskgroup_id来查询default_task的数据
                                $list2 = $defualt_task->default_taskList($list[$k]['id']);
                                foreach($list2 as $kk=>$vv){
                                    $data1['name']=$list2[$kk]['name'];
                                    $data1['taskgroup_id']=$result;
                                    $data1['creator_id']=$creator_id;
                                    $data1['create_time']=date("Y-m-d H:i:s");
                                    $data1['update_time']=date("Y-m-d H:i:s");
                                    // 自增一条记录
                                    $result1= Db::table('ipm_inst_subproject_task')->insertGetId($data1);
                                    if($result1){
                                        $res['success'] = true;
                                        $res['message'] = "success";
                                    }
                                }
                            }
                        }
                    }
                }
            }else{
                $arr = array(
                    "name" => $name,
                    "company_id" =>$company_id,
                    "creator_id" => $creator_id,
                    "config_id" => $config_id,
                    "state" => 1,
                    "start_time_plan" => $start_time_plan,
                    "end_time_plan" => $end_time_plan,
                    "create_time" => date("Y-m-d H:i:s")
                );
                //自增一条 记录
                $id = Db::table('ipm_inst_project')->insertGetId($arr);
                if($id){
                    $paths = FILE_PATH . "/" . $company_id . '/configFiles';
                    if (!file_exists($paths)) {
                        //创建文件夹
                        if (mkdir($paths, 0777, true)) {

                        }
                    }
                    $paths = FILE_PATH . "/" . $company_id . '/' .$id;
                    if (!file_exists($paths)) {
                        if (mkdir($paths, 0777, true)) {

                        }
                    }
                    $res['success'] = true;
                    $res['message'] = "success";

                }
            }
        return json ($res);

    }
    //查看所有项目信息
    public function any_company_list(){
        $projectTable= new Projects();
        //查询所有项目的所有信息
        $list=$projectTable->any_company_list();
        $currentpage=1;
        $itemsPerPage=20;
        if($list){
            foreach($list as $k=>$v){
                $prj_id = $list[$k]['project_id'];
                $company_id = $list[$k]['company_id'];
                $config_id=$list[$k]['config_id'];
                $config_name=$list[$k]['config_name'];
                $arr=explode(".",$config_name);
                $suffix=$arr[1];
                $list[$k]['config_url'] = SET_URL."/design_institute/public/PjrFiles/".$company_id.'/configFiles/'.$config_id.'.'.$suffix;
                //根据project_id来查询项目信息以及子项目信息
                $list[$k]['subproject_list']=$projectTable->subproject_project_list($prj_id,$currentpage,$itemsPerPage);
            }

        }else{
            return json();
        }
        return json($list);
    }
    //任务看板
    public function month_task_list($subprj_id){
        //获取当月的第一天
        $start_time=date('Y-m-01', strtotime(date("Y-m-d")));
        $str='%Y-%m-%d';
        $ye=  explode('-', $start_time)[0];
        $me=  explode('-', $start_time)[1];
        //获取当月的最后一天
        $var = date("t",strtotime($start_time));
        $TaskgroupsTable=new Taskgroups();
        $taskTable= new Tasks();
        //根据subprj_id来查询总任务
        $res=$TaskgroupsTable->taskparter_list($subprj_id);
        //从第一天开始，小于每个月的最后一天，依次循环
        for($d=1;$d<=$var;$d++){
            //年月日拼接
            $time = $ye.'-'.$me.'-'.$d;
            $result[$d]['time'] = $time;
            $result[$d]['sum'] = 0;
            $result[$d]['sum_1'] = 0;
            foreach($res as $k=>$v){
                $taskgroup_id=$res[$k]['id'];
                //根据taskgroup_id 并且state=1的来查询子任务数
                $rel= $taskTable->select_TaskList($taskgroup_id,$time,$str);
                //根据taskgroup_id 来查询子任务数
                $rel_1= $taskTable->select_TaskList_1($taskgroup_id,$time,$str);
                //拼接
                $result[$d]['sum'] = intval($result[$d]['sum']) + intval($rel[0]['sum']);
                $result[$d]['sum_1'] = intval($result[$d]['sum_1']) + intval($rel_1[0]['sum_1']);
            }
        }
        return $result;
    }

    //根据company_id查看所有项目的所有信息
    public  function  project_list(){
        $arr= $this->request->param();
        if (!isset($arr['company_id']) || empty($arr['company_id'])) {
            return json();
        }
            $company_id = $arr['company_id'];
            $currentpage = $arr['currentpage'];
            $itemsPerPage = $arr['itemsPerPage'];
            $projectTable= new Projects();
            if(empty($arr['openid']) || !isset($arr['openid'])){
                $list=$projectTable->user_project_list($company_id,$currentpage,$itemsPerPage);

                if($list){
                    foreach($list as $k=>$v){
                        $prj_id = $list[$k]['project_id'];
                        $config_id=$list[$k]['config_id'];
                        $config_name=$list[$k]['config_name'];
                        $arr=explode(".",$config_name);
                        $suffix = end($arr);
                        $list[$k]['config_url'] = SET_URL."/design_institute/public/PjrFiles/".$company_id.'/'.'configFiles/'.$config_id.'.'.$suffix;
                        $list[$k]['subproject_list']=$projectTable->subproject_project_list($prj_id,$currentpage,$itemsPerPage);
                    }
                }else{
                    return json();
                }
            }else{
                $openid = $arr['openid'];
                $list=$projectTable->user_project_list($company_id,$currentpage,$itemsPerPage);
                if($list){
                    foreach($list as $k=>$v){
                        $prj_id = $list[$k]['project_id'];
                        $config_id=$list[$k]['config_id'];
                        $config_name=$list[$k]['config_name'];
                        $arr=explode(".",$config_name);
                        $suffix = end($arr);
                        $list[$k]['config_url'] = SET_URL."/design_institute/public/PjrFiles/".$company_id.'/'.'configFiles/'.$config_id.'.'.$suffix;
                        $list[$k]['subproject_list']=$projectTable->subproject_project_list($prj_id,$currentpage,$itemsPerPage);
                        foreach($list[$k]['subproject_list'] as $kk=>$vv){
                            $sub_prjid=$list[$k]['subproject_list'][$kk]['subproject_id'];
                            $aaa=$projectTable->subproject_role_list($sub_prjid,$openid,$currentpage,$itemsPerPage);
                            //   $list[$k]['subproject_list'][$kk]['statics'] = $this->month_task_list($sub_prjid);
                            if($aaa){
                                foreach($aaa as $key=>$val ){
                                    $id=$aaa[$key]['role_id'];
                                    if($id==3){
                                        $list[$k]['subproject_list'][$kk]['zg_roles']= true;
                                    }else{
                                        $list[$k]['subproject_list'][$kk]['zg_roles']= false;
                                    }
                                }
                            }else{
                                $list[$k]['subproject_list'][$kk]['zg_roles']= false;
                            }
                        }
                    }
                }else{
                    return json();
                }
            }
            //根据company_id查询所有项目configuration，user信息
              return json($list);

    }
    public function Download_lists(){
        $arr= $this->request->param();
        $company_id=$arr['company_id'];
        $project_id=$arr['project_id'];
        $cur_file =ROOT_PATH . 'public' . DS . 'PjrFiles'.DS.$company_id.DS.$project_id;
        //new 一个对象
        $PHPZip = new PHPZip();
        $a=$PHPZip->ZipAndDownload($cur_file);
        if($a){
           echo 111;
        }
    }
    public function Download_list(){
        $arr= $this->request->param();
        $company_id=$arr['company_id'];
        $project_id=$arr['project_id'];
        $subproject_id=$arr['subproject_id'];
        $SubprojectTable= new Subprojects();
        $list=$SubprojectTable->file_subprj_project_list($subproject_id,$project_id,$company_id);
        $list1=$SubprojectTable->subpr_project_config_list($project_id,$company_id,$subproject_id);
        foreach($list as $k=>$v){
            $names=$list[$k]['name'];
            $fileNameArry = explode(".", $names);
            //获取数组最后一位
            $fileNameTitle = end($fileNameArry);
            $type=$list[$k]['type'];
            $file_id=$list[$k]['file_id'];
            $str = md5($file_id.$type."prj");
            $res['prj_file'][$k]['filename'] = $names;
            $res['prj_file'][$k]['file']= SET_URL."/design_institute/public/PjrFiles/".$company_id.'/'.$project_id.'/'.$subproject_id.'/'.$str.'.'.$fileNameTitle;
        }

        foreach($list1 as $k=>$v){
            $name=$list1[$k]['config_name'];
            $fileNameArry = explode(".", $name);
            //获取数组最后一位
            $fileNameTitle = end($fileNameArry);
            $config_id=$list1[$k]['config_id'];
            $res['prj_conf'][$k]['filename']=$name;
            $res['prj_conf'][$k]['file']=SET_URL."/design_institute/public/PjrFiles/".$company_id.'/'.'configFiles'.'/'.$config_id.'.'.$fileNameTitle;

        }
        return  json ($res) ;

    }
    function addFileToZip($path, $zip) {
        $handler = opendir($path); //打开当前文件夹由$path指定。
        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") {//文件夹文件名字为'.'和‘..’，不要对他们进行操作
                if (is_dir($path . "/" . $filename)) {// 如果读取的某个对象是文件夹，则递归
                    addFileToZip($path . "/" . $filename, $zip);
                } else { //将文件加入zip对象
                    $zip->addFile($path . "/" . $filename);
                }
            }
        }
        @closedir($path);
    }
    public function download(){
        $arr= $this->request->param();
        $company_id=$arr['company_id'];
        $project_id=$arr['project_id'];
        $cur_file =ROOT_PATH . 'public' . DS . 'PjrFiles'.DS.$company_id.DS.$project_id;
        $zip = new ZipArchive();
        if ($zip->open('2.zip', ZipArchive::CREATE) === TRUE) {
           // $this->addFileToZip($cur_file, $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法

            //重命名
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $file = $zip->getNameIndex($i);
                if (pathinfo(basename($file), PATHINFO_EXTENSION)) {
                    $zip->renameIndex($i, dirname($file) . '/' . $i . '.' . pathinfo(basename($file), PATHINFO_EXTENSION));
                }
            }
            $zip->close(); //关闭处理的zip文件
        }
    }
    //   删除项目
    public function del_project(){
        $arr= $this->request->param();
        if (!isset($arr['project_id']) || empty($arr['project_id'])){
            return json('111');
        }
        $project_id=$arr['project_id'];
        $state = Db::table('ipm_inst_project')->where('id',$project_id)->value('state');
        if(isset($state) && $state==1 ){
            $subprojectTable= new Subprojects();
            $sublist=$subprojectTable->get_state($project_id);

            if(empty($sublist) || !isset($sublist)){
                $id = Db::table('ipm_inst_project')->where('id',$project_id)->delete();
                if($id){
                    $res['success'] = true;
                    $res['message'] = "success";
                    return json ($res);
                }
            }else{
                $a=false;
                foreach($sublist as $k=>$v){
                    if($sublist[$k]['state']!=1){
                        $a = true;
                        break;
                    }
                }
                if($a)
                {
                    $res1['success'] = false;
                    $res1['message'] = 'state error';
                    return json ($res1);
                }else{
                    $id = Db::table('ipm_inst_project')->where('id',$project_id)->delete();
                    if($id){
                        $id1=Db::table('ipm_inst_subproject')
                            ->where("project_id='$project_id'")
                            ->select();
                        $id3 = Db::table('ipm_inst_subproject')->where('project_id',$project_id)->delete();
                        if($id3){
                            foreach($id1 as $k=>$v){
                                $subproject_id=$id1[$k]['id'];
                                $id2 = Db::table('ipm_inst_subproject_user')->where('subproject_id',$subproject_id)->delete();
                                if($id2){
                                    $res['success'] = true;
                                    $res['message'] = "success";
                                    return json ($res);
                                }
                            }
                        }
                    }
                }
            }


        }else{
            $res['success'] = false;
            $res['message'] = "error";
            return json ($res);
        }
    }
    public function project_subprojectlist(){
        $arr= $this->request->param();
        if (!isset($arr['prj_id']) || empty($arr['prj_id'])){
            return json('prj_id  不能为空');
        }
        $subprojectTable=new Subprojects();
        $res=$subprojectTable->subproject_List($arr['prj_id']);
        if($res){
            return json($res);
        }else{
            return json();
        }
    }

    private function checkRequestData()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods:post');
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