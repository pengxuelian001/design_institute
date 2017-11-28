<?php
namespace app\admin\controller;
use app\home\model\Defaulttasks;
use think\Controller;
use think\Db;
use think\Cache;
class Defaulttask extends Controller
{
    public function defaulttask_list(){
        $defaulttaskTable= new defaulttasks();
        //  查询ipm_inst_default_task列表
        $arr=$defaulttaskTable->default_taskList();
        if($arr){
          return json($arr);
        }else{
            return json();
        }
    }
}