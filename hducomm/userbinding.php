<?php
$openid = $this->message['from'];
$msg =  $this->message['content'];
$sql = "SELECT  * FROM ".tablename('studentlist')." WHERE openid = :openid";
$array = array(':openid'=>$openid);
$res = pdo_fetch($sql,$array);
$reply = "";
if($res)
{
	$reply = "该微信号已绑定成功，请勿重复绑定。";
}
else
{
	$keyword = preg_split("/[\s,]+/", $msg);
	$key = $keyword[0];
	$name = $keyword[1];
	$studentid = $keyword[2];
	if($key == "绑定")
	{
		$sql = "SELECT * FROM ".tablename('studentlist')." WHERE name = :name and studentid = :studentid";
		$array = array(':name'=>$name,':studentid'=>$studentid);
		$res = pdo_fetch($sql,$array);
		if($res)
		{
			if(empty($res['openid']))
			{
				$res2 = pdo_update('studentlist', $data = array('openid'=>$openid), $filter = array('name'=>$name,'studentid'=>$studentid), $gule = 'AND');
				if($res2 == 1)
				{
					$reply = "绑定成功";
				}
				else
				{
					$reply = "绑定失败，请稍后再试";
				}
			}
			else
			{
				$reply = "{$name}同学已被其他微信号绑定，请勿重复绑定，如有问题请及时于管理员联系。";
			}
		}
		else
		{
			$reply = "绑定失败，输入信息有误，请再次确认。绑定格式 “绑定 姓名 学号”进行绑定，如：”绑定 李运环 13084123“。";
		}
	}
}
return $this->respText($reply);