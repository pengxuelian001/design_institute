<?php
namespace app\admin\controller;
use think\Controller;
use app\home\model\Projects;
use think\Db;
use think\Cache;
use think\Request;

class Alter extends Controller
{
    //修改时间
    /**
     * @return \think\response\Json
     */
	 
	public function alter_time(){
		$arr= $this->request->param();
		switch($arr['type'])
		{
			case 1:
			$data['start_time_plan'] = date('Y-m-d H:i:s',strtotime($arr['time_var']));
			break;
			case 2:
			$data['end_time_plan'] = date('Y-m-d H:i:s',strtotime($arr['time_var']));
			break;
		}
		if($arr['prj_id'] == 0)
		{
			$arr = Db::table('ipm_inst_subproject')->where('id',$arr['subprj_id'])->update($data);
			if($arr)
			{
				$result['success'] = true;
				 return json ($result);
			}
			else{
				$result['success']=false;
				return json ($result);
				}
		}
		if($arr['subprj_id'] == 0)
		{
			$arr = Db::table('ipm_inst_project')->where('id',$arr['prj_id'])->update($data);
			if($arr)
			{
				$result['success'] = true;
				 return json ($result);
			}
			else{
				$result['success']=false;
				return json ($result);
				}
		}
	}
	 
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
 
}