<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Cache;
use app\home\model\Configs;
use think\Request;

class Config extends Controller
{
    public  function Config_list(){
        if(request()->isGet()) {
            $company_id = input('company_id');
            if (!isset($company_id) ||  empty($company_id) || !is_numeric($company_id) || $company_id<1) {
                return json();
            }
            $userTbale = new Configs();
            //根据company_id来查找这个创建的配置文件
            $res = $userTbale->configList($company_id);
            return json($res);
        }
    }
    public function upload_Config(){
//        $basedir = dirname(__FILE__);
//        echo $basedir;die();
        $arr= $this->request->param();
        $openid=$arr['openid'];
        if (!isset($openid) || empty($openid)) {
            return json('openid empty');
        }
        $company_id=$arr['company_id'];
        if (!isset($company_id) ||  empty($company_id)) {
            return json();
        }
        //upload传过来的文件名
        $fileName = $_FILES["file"]["name"];
        $fileArray= explode(".", $fileName);
        $endfileName = end($fileArray);
        if($endfileName=='zip' || $endfileName=='rar'){
            $arr=array(
                'company_id' => $company_id,
                'name' => $fileName,
                'creator_id' => $openid,
                "create_time"=>date("Y-m-d H:i:s"),
                "update_time"=>date("Y-m-d H:i:s"),
            );
            // 数据表插入一条数据
            $config_id=Db::table('ipm_inst_configuration')->insertGetId($arr);
            //根据id拿到单个字段
            $fileNames=Db::table('ipm_inst_configuration')->where('id',$config_id)->value('name');
            //以.分割
            $fileNameArry = explode(".", $fileNames);
            //获取数组最后一位
            $fileNameTitle = end($fileNameArry);
            if($config_id){
                //上传过来的数据
                $file = request()->file('file');
                //设置大小。允许的格式，存放的位置（以插入的Id来命名）
                $info = $file->validate(['size'=>156780000,'ext'=>'zip,rar'])->move(ROOT_PATH . 'public' . DS . 'PjrFiles'. DS .$company_id.DS.'configFiles',$config_id.'.'.$fileNameTitle);
                if($info)
                {
                    //跳转页面
                    $this->redirect(SET_URLS."/design_inst/#/index/project/configuration",'导入成功 ！页面跳转中...');
                }else{
                    //错误信息
                    echo $file->getError();
                }
            }
        }else{
            $this->success('上传文件的后缀不符合', SET_URLS."/design_inst/#/index/project/configuration");
          //  $this->redirect(SET_URLS."/design_inst/#/index/project/configuration",'上传文件的后缀不符合');
        }




    }
    //删除配置表
    public function del_config(){
        $arr= $this->request->param();
        $config_id=$arr['config_id'];
        if (!isset($config_id) || empty($config_id)) {
            return json('111');
        }
        //先查找配置文件有没有在使用中

        $result1=Db::table('ipm_inst_configuration')
            ->field(['company_id','name'])
            ->where("id='$config_id'")
            ->select();
        if($result1){
            $company_id=$result1[0]['company_id'];
            $filename=$result1[0]['name'];
            $fileNameArry = explode(".", $filename);
            //获取数组最后一位
            $fileNameTitle = end($fileNameArry);
            $result=Db::table('ipm_inst_configuration')
                ->alias('a')
                ->join('ipm_inst_project b','b.config_id=a.id')
                ->where("a.id='$config_id'")
                ->select();
            if($result){

                $res['success'] = false;
                $res['message'] = "error";
                return json($res);
            }else{
                $dir=ROOT_PATH . 'public' . DS . 'PjrFiles'. DS .$company_id.DS.'configFiles'.DS.$config_id.'.'.$fileNameTitle; //路径
                $result=Db::table('ipm_inst_configuration')->where('id',$config_id)->delete();
                if($result){
                    if (file_exists($dir)) {
                        //删除当前文件夹：
                        if(unlink($dir)) {
                            $res['success'] = true;
                            $res['message'] = "success";
                            return json($res);
                        }else{
                            return json('false');
                        }
                    }

                }else{
                    $res['success'] = false;
                    $res['message'] = "error";
                    return json($res);
                }



           }

        }else{
            return json();
        }
    }

}