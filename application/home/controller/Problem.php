<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;

class Problem extends Controller
{
	function run(){
           include('GlobalHelp.php');
           return new GlobalHelp();
	}

	public  function  problemuser_list()
	{
        if(request()->isGet()) 
		{
            $subproject_id = input('subproject_id');
			$start = input('start');
			$sql = "SELECT DISTINCT a.creator_id,c.nickname as creator_nickname
			        from ipm_inst_problem a 
					left join ipm_user c on a.creator_id=c.openid";
			if(isset($subproject_id))
			{
				$sql = $sql." where  a.subproject_id = '$subproject_id'";
			}
			$problemcreatorlist = Db::query($sql);
			
			$sql = "SELECT DISTINCT a.changer_id,c.nickname as changer_nickname
			        from ipm_inst_problem a 
					left join ipm_user c on a.changer_id=c.openid";
			if(isset($subproject_id))
			{
				$sql = $sql." where  a.subproject_id = '$subproject_id'";
			}
			$problemdatachangerlist = Db::query($sql);

			$outputList = array();
			$outputList['creator'] = array();
			$outputList['changer'] = array();
            foreach($problemcreatorlist as $k=>$v)
			    $outputList['creator'][] = $v;
			foreach($problemdatachangerlist as $k=>$v)
                $outputList['changer'][] = $v;
            return json($outputList);			
        }
		else
		{
			return json();
		}
	}
	
    public  function UploadProblemInfo()
	{
        if(request()->isPost()) 
		{
            $read = file_get_contents("php://input");
		    if (empty($read))
			{
               $jsonarr["success"] = false;
			   $jsonarr["state"] = -1;
			   $jsonarr["message"] = '上传问题参数缺失';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
            }
			$json = json_decode(trim($read,chr(239).chr(187).chr(191)),true);
			if (is_null($json)) 
			{
               $jsonarr["success"] = false;
			   $jsonarr["state"] = -1;
			   $jsonarr["message"] = '上传问题参数格式不对';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
			}
			$company_id = $json['company_id'];
			$prj_id = $json['prj_id'];
			$subproject_id = $json['subproject_id'];
			$creator_id = $json['creator_id'];
			$type_id = $json['type_id'];
			$subtype_id = $json['subtype_id'];
			$prjState = $json['prjState'];
			$problemGrade = $json['problemGrade'];
			$title = $json['title'];
			$description = $json['description'];
			$changer_id = $json['changer_id'];
			if (!isset($company_id) || !isset($prj_id)||!isset($subproject_id) || !isset($creator_id)
				||!isset($type_id) || !isset($subtype_id)||!isset($prjState) || !isset($problemGrade)
			    ||!isset($title) || !isset($description)|| !isset($changer_id))
			{
			   $jsonarr["success"] = false;
			   $jsonarr["state"] = -1;
			   $jsonarr["message"] = '上传问题数据参数缺失';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
			}
			$help=$this->run();
			if (!$help->isPrjValidUser($subproject_id,$creator_id) ||!$help->isValidCompany($company_id)
				||!$help->isValidPrjAndSubPrj($prj_id,$subproject_id))
			{
				$jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '上传问题记录数据不对';
				$result = json_encode($jsonarr);
				ob_clean();
	            echo $result;
		        return;
			}
			$update_time = date("Y-m-d H:i:s");
			$creat_time = date("Y-m-d H:i:s");
			
            $dataInsert = ['id' => -1, 'company_id' => $company_id,'project_id' => $prj_id,'subproject_id' => $subproject_id,'creator_id' => $creator_id,
				'type_id' => $type_id,'subtype_id' => $subtype_id,'prjState' => $prjState,'problemGrade' => $problemGrade,'title' => $title,'description'  => $description,
				'state' => 1,'changer_id' => $changer_id,'update_time' => $update_time,'create_time' => $creat_time];
			var_dump($dataInsert);
			 $fileId = Db::table('ipm_inst_problem')->insertGetId($dataInsert);
			 $jsonarr["success"] = true;
			 $jsonarr["state"] = $fileId;
			 $jsonarr["message"] = '上传成功';
			 $result = json_encode($jsonarr);
			 ob_clean();
	         echo $result;
		     return;
        }
		else
		{
			 $jsonarr["success"] = false;
			 $jsonarr["state"] = -1;
			 $jsonarr["message"] = '请求方式错误';
			 $result = json_encode($jsonarr);
			 ob_clean();
	         echo $result;
		     return;
		}
    }
	
	public  function  UploadProblemFiles()
	{
        if(request()->isPost())
		{
            $problem_id = input('problem_id');
			$subproject_id = input('subproject_id');
			if(isset($problem_id) || isset($subproject_id))
			{
				$help=$this->run();
				if (!$help->isPrjValidProblem($problem_id) || !$help->isValidSubPrj($subproject_id))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '数据无效';
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
			   //查询公司id和项目id
				$prjInfo= Db::query("select DISTINCT a.id as prj_id,a.company_id as company_id 
				                    from ipm_inst_project a 
				                    WHERE  a.id IN(SELECT DISTINCT b.project_id from ipm_inst_subproject b WHERE b.id='$subproject_id')");
				
				$company_id = $prjInfo[0]['company_id'];	
                $prj_id = $prjInfo[0]['prj_id'];
				
			    $update_time = date("Y-m-d H:i:s");
			    $creat_time = date("Y-m-d H:i:s");
				if (empty($_FILES))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '上传文件为空';
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
				if ($_FILES["file"]["error"] != 0)
                {
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '文件上传有错误';
					$result = json_encode($jsonarr);
					ob_clean();
	                echo $result;
		            return;
                }
				
                $fileName = $_FILES["file"]["name"];  //获取post过来的文件名	
                $fileNameArry = explode(".", $fileName);
                if(empty($fileNameArry))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '上传文件数据不对';
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
				
                //获取文件后缀
				$fileNameTitle = end($fileNameArry);
				
				//将文件数据插入到ipm_inst_file中 获取创建的自增ID
				$dataInsert = ['id' => -1, 'problem_id' => $problem_id,
				'update_time' => $update_time,'create_time' => $creat_time];
				$fileId = Db::table('ipm_inst_problem_files')->insertGetId($dataInsert);
				//重命名POST文件名 保存到&path目录下面	
				$str = md5($fileId."jpg");

				$path = FILE_PATH.$company_id.'/'.$prj_id.'/'.$subproject_id.'/'.$str.'.'.$fileNameTitle;
				if (!move_uploaded_file($_FILES["file"]["tmp_name"],$path))
				{
					Db::query("delete from ipm_inst_problem_files a where a.id='$fileId'");
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '文件移动到服务器失败';
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
				
				$jsonarr["success"] = true;
				$jsonarr["state"] = $fileId;
				$jsonarr["message"] = '上传文件成功';
				$result = json_encode($jsonarr);
				ob_clean();
	            echo $result;
		        return;
			}
			else
			{
				$jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '上传文件参数缺失';
				$result = json_encode($jsonarr);
				ob_clean();
	            echo $result;
		        return;
			}
        }
		else
		{
			$jsonarr["success"] = false;
			$jsonarr["state"] = -1;
			$jsonarr["message"] = '请求方式错误';
			$result = json_encode($jsonarr);
			ob_clean();
	        echo $result;
		    return;
		}
	}
	
	public  function  ProblemInfo_list()
	{
        if(request()->isGet()) 
		{
            $company_id = input('company_id');
            $project_id = input('project_id');
            $subproject_id = input('subproject_id');
			$start = input('start');
			$count = input('count');
			$prjState = input('prjState');
			$problemGrade  = input('problemGrade');
			$title  = input('title');
			$state  = input('state');
			$creator_id  = input('creator_id');
			$changer_id  = input('changer_id');
			$type_id  = input('type_id');
			$subtype_id  = input('subtype_id'); 
			$keyword  = input('keyword'); 
			if(!isset($company_id))
			    return json();
			$help=$this->run();
			$sql = "SELECT DISTINCT a.id,b.id as subprj_id,b.name as subprj_name,f.name as prj_name, f.id as prj_id,a.creator_id,c.nickname as creator_nickname,a.type_id,d.name as type_name,a.subtype_id,e.name as subtype_name,c.headimgurl as creator_headimgurl,a.prjState,
			        a.problemGrade,a.title,a.description,a.state,a.changer_id,a.update_time,a.create_time  
			        from ipm_inst_problem a 
					left join ipm_user c on a.creator_id=c.openid 
					left join ipm_inst_subproject b on a.subproject_id=b.id 
					left join ipm_inst_project f on f.id = b.project_id 
					left join ipm_inst_problem_type d on a.type_id=d.id 
					left join ipm_inst_problem_subtype e on a.subtype_id=e.id 
					where  a.company_id=$company_id";
			if(isset($project_id))
			{
				$sql = $sql." and a.project_id =$project_id ";
			}
			if(isset($subproject_id))
			{
				$sql = $sql." and  a.subproject_id = '$subproject_id'";
			}		
			if(isset($prjState))
			{
				$sql = $sql." and  a.prjState = '$prjState'";
			}
			if(isset($problemGrade))
			{
				$sql = $sql." and  a.problemGrade = '$problemGrade'";
			}
			if(isset($title))
			{
				$sql = $sql." and  a.title = '$title'";
			}
			if(isset($state))
			{
				$sql = $sql." and  a.state = '$state'";
			}
			if(isset($creator_id))
			{
				$sql = $sql." and  a.creator_id = '$creator_id'";
			}
			if(isset($changer_id))
			{
				$sql = $sql." and  a.changer_id = '$changer_id'";
			}
			if(isset($type_id))
			{
				$sql = $sql." and  a.type_id = '$type_id'";
			}
			if(isset($subtype_id))
			{
				$sql = $sql." and  a.subtype_id = '$subtype_id'";
			}
			if(isset($keyword))
			{
				$sql = $sql." and  (a.title LIKE '%$keyword%' OR a.description LIKE '%$keyword%' OR a.create_time LIKE '%$keyword%')";
			}
			if(isset($start) && isset($count))
			{
				$sql = $sql." LIMIT ".$start.",".$count;
			}
			
			$problemdatalist = Db::query($sql);
			if(empty($problemdatalist))
			  return json();	
			$problemState=array('','待解决','待审核','已解决','已删除');		
			foreach ($problemdatalist as $k => $v)
			{
				if($problemdatalist[$k]['state'] < 0 || $problemdatalist[$k]['state'] > 4)
					continue;
				$problemdatalist[$k]['state_name'] = $problemState[$problemdatalist[$k]['state']];
			    $tmpproblemid = $problemdatalist[$k]['id'];
				$problemdatalist[$k]['changer_name'] = $help->getUserName($problemdatalist[$k]['changer_id']);
				$problemdatalist[$k]['changer_headimgurl'] = $help->getUserheadimgurl($problemdatalist[$k]['changer_id']);
				$sql = "SELECT DISTINCT id from ipm_inst_problem_files where problem_id = $tmpproblemid";
				$problemfilelist = Db::query($sql);
				if(empty($problemfilelist))
			    {
			      continue;	
			    }
				foreach ($problemfilelist as $kk => $vv)
				{
					$str = md5($problemfilelist[$kk]['id']."jpg");
					$project_idTmp = $problemdatalist[$k]['prj_id']; 
					$subproject_idTmp = $problemdatalist[$k]['subprj_id']; 
					$problemfilelist[$kk] = SET_URL."/design_institute/public/PjrFiles/".$company_id.'/'.$project_idTmp.'/'.$subproject_idTmp.'/'.$str.".jpg";
				}
				$problemdatalist[$k]['file_list'] = $problemfilelist;
            }
					
            $outputList = array();
            foreach($problemdatalist as $k=>$v)
              $outputList[] = $v;
            return json($outputList);			
        }
		else
		{
			return json();
		}
    }
	
	public  function  Solveproblem()
	{
		if(request()->isPost())
		{
			$update_time = date("Y-m-d H:i:s");
			$creat_time = date("Y-m-d H:i:s");
			$read = file_get_contents("php://input");
		    if (empty($read))
			{
               $jsonarr["success"] = false;
			   $jsonarr["state"] = -1;
			   $jsonarr["update_time"] = '';
			   $jsonarr["message"] = '修改问题状态数据缺失';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
            }
			$json = json_decode(trim($read,chr(239).chr(187).chr(191)),true);
			if (is_null($json)) 
			{
               $jsonarr["success"] = false;
			   $jsonarr["state"] = -1;
			   $jsonarr["update_time"] = '';
			   $jsonarr["message"] = '修改问题状态数据格式错误';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
			}
            $openid = $json['openid'];
            $problemid = $json['problemid'];
			$state = $json['state'];
			if(isset($openid) && isset($state) && isset($problemid))
			{
		      $problemData= Db::query("select a.state 
                               from ipm_inst_problem a
                               where a.id='$problemid' and a.changer_id = '$openid'");
			 
			  if(!isset($problemData) || empty($problemData))
			  {
				  $jsonarr["success"] = false;
			      $jsonarr["state"] = -1;
				  $jsonarr["update_time"] = '';
			      $jsonarr["message"] = '该问题负责人不是所指定用户';
			      $result = json_encode($jsonarr);
			      ob_clean();
	              echo $result;
				  return;
			  }
			  $problemPrevState = $problemData[0]['state'];
			  if($problemPrevState == $state)
			  {
				  $jsonarr["success"] = false;
			      $jsonarr["state"] = -1;
				  $jsonarr["update_time"] = '';
			      $jsonarr["message"] = '该问题已是要修改的状态';
			      $result = json_encode($jsonarr);
			      ob_clean();
	              echo $result;
				  return;
			  }
			
			  Db::query("UPDATE ipm_inst_problem SET state = '$state',update_time = '$update_time' WHERE id = '$problemid'");
			  $jsonarr["success"] = true;
			  $jsonarr["state"] = $state;
			  $jsonarr["update_time"] = $update_time;
			  $jsonarr["message"] = '修改问题状态成功';
			  $result = json_encode($jsonarr);
			  ob_clean();
	          echo $result;
		      return;
			}
			else
			{
				$jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["update_time"] = '';
				$jsonarr["message"] = '修改问题状态数据缺失';
				$result = json_encode($jsonarr);
				ob_clean();
	            echo $result;
		        return;
			}
        }
		else
		{
			$jsonarr["success"] = false;
			$jsonarr["state"] = -1;
			$jsonarr["update_time"] = '';
			$jsonarr["message"] = '请求方式错误';
			$result = json_encode($jsonarr);
			ob_clean();
	        echo $result;
		    return;
		}
	}
}