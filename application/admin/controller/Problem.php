<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Cache;
use app\home\model\Problems;
use think\Request;

class Problem extends Controller
{

    public function problem_list(){
        if(request()->isGet()) {
            $company_id = input('company_id');
            $project_id = input('project_id');
            $subproject_id = input('subproject_id');
            if (!isset($company_id) || empty($company_id) || !is_numeric($company_id) || $company_id < 1) {
                return json();
            }
            if (!isset($subproject_id) || empty($subproject_id) || !is_numeric($subproject_id) || $subproject_id < 1) {
                return json();
            }
            $userTbale = new Problems();
            //问题列表，条件$company_id，$subproject_id
            $res = $userTbale->problemList($company_id,$subproject_id);

            if($res){
                foreach($res as $k=>$v){
                    $attachment=$res[$k]['attachment'];
                    $attachpic=$res[$k]['attachpic'];
                    if($attachment  ) {
                        //attachment的路径
                        $res[$k]['attachment'] = SET_URL . "/design_institute/public/PjrFiles/" . $company_id . '/' . $project_id . '/' . $subproject_id . '/' . '20' . '/' . $attachment;
                    }
                    if($attachpic){
                        $res[$k]['attachpic']= SET_URL."/design_institute/public/PjrFiles/".$company_id.'/'.$project_id.'/'.$subproject_id.'/'.'21'.'/'.$attachpic;
                    }
                }
            }

            return json($res);
        }

    }
    public function add_problem(){
        $arr= $this->request->param();
        $company_id=$arr['company_id'];
        $project_id=$arr['project_id'];
        $subproject_id=$arr['subproject_id'];
        $creator_id=$arr['creator_id'];
        $type_id=$arr['type_id'];
        $subtype_id=$arr['subtype_id'];
        $title=$arr['title'];
        $description=$arr['description'];
            if (!isset($company_id) ||  empty($company_id) || !is_numeric($company_id)) {
                return json('111');
            }
            if (!isset($project_id) ||  empty($project_id) || !is_numeric($project_id)) {
                return json('222');
            }
            if (!isset($subproject_id) || empty($subproject_id) || !is_numeric($project_id)) {
                return json('333');
            }
            if (!isset($creator_id) || empty($creator_id)) {
                return json('444');
            }
            if (!isset($type_id) || empty($type_id)) {
                return json('555');
            }
            if (!isset($title) || empty($title)) {
                return json('666');
            }
            if (!isset($description) || empty($description)) {
                return json('777');
            }
            //上传过来的数据
            $file = request()->file('file');
            //如果$file为空，则先插入一条数据
            if(empty($file)){
                //封装成一个数组
                $arr=array(
                    'company_id' => $company_id,
                    'project_id' => $project_id,
                    'subproject_id' => $subproject_id,
                    'creator_id' => $creator_id,
                    'type_id' => $type_id,
                    'subtype_id' => $subtype_id,
                    'state' => 1,
                    'title' => $title,
                    'description' => $description,
                    "create_time"=>date("Y-m-d H:i:s"),
                    "update_time"=>date("Y-m-d H:i:s")
                );
                $result=Db::table('ipm_inst_problem')->insertGetId($arr);
                if($result){
                    $this->redirect(SET_URLS."/design_inst/#/index/forum/new_forum?rs=1",'导入成功 ！页面跳转中...');
                }
            }else{

                $info = $file->validate(['ext'=>'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'PjrFiles'. DS .$company_id.DS.$project_id.DS.$subproject_id.DS.'20','');
                if($info){
                    //获取文件名
                    $name= $info->getSaveName();
                    //是中文就转码
                    $attachment = iconv("GB2312","UTF-8",  $name);
                    //封装数组
                    $arr=array(
                        'company_id' => $company_id,
                        'project_id' => $project_id,
                        'subproject_id' => $subproject_id,
                        'creator_id' => $creator_id,
                        'type_id' => $type_id,
                        'subtype_id' => $subtype_id,
                        'title' => $title,
                        'state' => 1,
                        'description' => $description,
                        'attachment' => $attachment,
                        "create_time"=>date("Y-m-d H:i:s")
                    );
                    //数据库插入数据
                    $result=Db::table('ipm_inst_problem')->insertGetId($arr);
                    if($result){
                        //跳转页面
                        $this->redirect(SET_URLS."/design_inst/#/index/forum/new_forum?rs=1",'导入成功 ！页面跳转中...');
                    }
                }else {
                    echo $file->getError();
                }
            }

    }
    public function problem_return(){
        //post  或者get 接收都可以
        $arr= $this->request->param();
        $solve_result=$arr['solve_result'];
        $id=$arr['id'];
        $changer_id=$arr['changer_id'];
        $company_id=$arr['company_id'];
        $project_id=$arr['project_id'];
        $subproject_id=$arr['subproject_id'];
        $no=$arr['no'];
        if (!isset($company_id) || empty($company_id)) {
            return json('111');
        }
        if (!isset($id) || empty($id)) {
            return json('222');
        }
        if (!isset($project_id) || empty($project_id)) {
            return json('333');
        }
        if (!isset($subproject_id) || empty($subproject_id)) {
            return json('444');
        }
        if (!isset($solve_result) || empty($solve_result)) {
            return json('555');
        }
        if (!isset($changer_id) || empty($changer_id)) {
            return json('666');
        }
        //接收上传过来的文件
        $file = request()->file('file');
        //如果没有上传文件则先修改数据
        if(empty($file)) {
            $arr = array(
                'solve_result' => $solve_result,
                'changer_id' => $changer_id,
                "create_time" => date("Y-m-d H:i:s")
            );
            //     修改数据
            $result = Db::table('ipm_inst_problem')->where('id',$id)->update($arr);
            if($result){
                //     跳转页面
                $this->redirect(SET_URLS."/design_inst/#/index/forum/forum_detail/$no",'导入成功 ！页面跳转中...');
            }
        }else{
            //     文件的格式，大小限定。以及已原文件名保存的目录
            $info = $file->validate(['ext'=>'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'PjrFiles'. DS .$company_id.DS.$project_id.DS.$subproject_id.DS.'21','');
            if($info){
                //获取文件名
                $name= $info->getSaveName();
                //中文转码
                $attachpic = iconv("GB2312","UTF-8",  $name);
                //封装成数组
                $arr = array(
                    'solve_result' => $solve_result,
                    'changer_id' => $changer_id,
                    'attachpic' => $attachpic,
                    "create_time" => date("Y-m-d H:i:s")
                );
                //修改数据表
                $result = Db::table('ipm_inst_problem')->where('id',$id)->update($arr);
                if($result){
                    //     跳转页面
                    $this->redirect(SET_URLS."/design_inst/#/index/forum/forum_detail/$no",'导入成功 ！页面跳转中...');
                }
            }else {
                echo $file->getError();
            }
        }
    }


}