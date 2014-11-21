<?php
$openid = $this->message['from'];
$msg =  $this->message['content'];
$type = $this->message['type'];
$dep1 = array("学习部"=>"a","媒体中心"=>"b","宣传部"=>"c","办公室"=>"d","技术部"=>"e","文娱部"=>"f","公关部"=>"g","权服中心"=>"h","体育中心"=>"i");
$dep2 = array("无"=>"z","学习部"=>"a","媒体中心"=>"b","宣传部"=>"c","办公室"=>"d","技术部"=>"e","文娱部"=>"f","公关部"=>"g","权服中心"=>"h","体育中心"=>"i");
$change = array("长号"=>'longnum',"短号"=>'short',"第一意向部门"=>'department1',"第二意向部门"=>'department2',"服从调剂"=>'arrangement',"爱好"=>'hobby',"优势"=>'advantages',"目的"=>'purpose',"照片"=>'imgurl');
$line = "=================\n";
$errmsg = "Oops!~ 报名出错啦(>_<)，请稍后再试。\n{$line}报名系统退出";
$reply = "";
$sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
$array = array(':openid'=>$openid);
$res = pdo_fetch($sql,$array);
$step = $res['step'];
if(!$this->inContext)
{
    if(empty($step))
    {
        $res = pdo_insert('suadmission', $data = array('openid'=>$openid, 'step'=>0), $replace = true);
        if($res)
        {
            $reply = "通信工程学院学生会报名系统\n{$line}亲爱的小伙伴，欢迎报名通信工程学生会～非常期待才华横溢的你加入我们！报名大概需要10分钟就好，发送“帮助”查看具体报名说明，发送“取消”可以随时退出报名。\n{$line}1/9首先请告诉我们你的名字+学号，用空格隔开~";
            $this->beginContext(3600);        
        }
        else
        {
            $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
            $reply = $errmsg;
        }
    }
    else
    {
        switch($step)
        {
        case 99:
            $reply = "已报名成功，请勿重复报名，可发送“查看报名”查询报名情况。\n{$line}报名系统退出";
            break;
        default:
            $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
            $res = pdo_insert('suadmission', $data = array('openid'=>$openid, 'step'=>0), $replace = true);
            if($res)
            {
                $this->beginContext(3600);
                $reply = "通信工程学院学生会报名系统\n{$line}报名超时，请重新告诉我们你的名字+学号，用空格隔开";
            }
            else
            {
                $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                $reply = $errmsg;
            }
            break;
        }
    }
}
elseif($msg == "取消")
{
    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
    if($res)
    {
        $this->endContext();
        $reply = "报名取消\n{$line}报名系统退出";
    }
    else
    {
        $this->endContext();
        $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
        $reply = $errmsg;
    }
}
elseif($msg == "帮助")
{
    $reply = "微信报名共有9步，为保证其他功能的正常使用，报名将在30分钟之后自动结束呦～超时也不用担心哒，只需重新发送“报名”然后按顺序复制粘贴先前输入内容就OK啦！\n{$line}&lt;a href=&quot;http://m.wsq.qq.com/262827975&quot;&gt;点击查看如何报名以及各部门介绍&lt;/a&gt;";
}
else
{
    switch($step)
    {
    case 0:
        $keyword = preg_split("/[\s,]+/", $msg);
        $name = $keyword[0];
        $studentid = $keyword[1];
        $sql1 = "SELECT * FROM ".tablename('14freshmen')." WHERE studentid = :studentid AND name = :name";
        $array1 = array(':studentid'=>$studentid, ':name'=>$name);
        $res1 = pdo_fetch($sql1,$array1);
        $sql2 = "SELECT * FROM ".tablename('suadmission')." WHERE studentid = :studentid AND name = :name";
        $array2 = array(':studentid'=>$studentid, ':name'=>$name);
        $res2 = pdo_fetch($sql2,$array2);
        if($res1)
        {
            if($res2)
            {
                $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                if($res)
                {
                    $this->endContext();
                    $reply = "{$name}同学，你已通过其他微信号完成报名，如有非本人行为，请及时通知管理员。\n{$line}报名系统退出";
                }
                else
                {
                    $this->endContext();
                    $reply = $errmsg;
                }
            }
            else
            {
                $data = array('studentid'=>$studentid,'name'=>$name,'step'=>1);
                $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                if($res)
                {
                    $reply = "2/9请告诉我们你的联系方式,长号+短号，空格隔开，无短号则不填";
                }
                else
                {
                    $this->endContext();
                    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                    $reply = $errmsg;
                }
            }
        }
        else
        {
            $reply = "请正确输入自己的姓名+学号，用空格隔开。";
        }
        break;
    case 1:
        $keyword = preg_split("/[\s,]+/", $msg);
        $longnum = $keyword[0];
        $short = $keyword[1];
        if (preg_match("/^[0-9]*$/",$longnum) && strlen($longnum) == 11)
        {
            if(empty($short))
            {
                $short = '无';
            }
            if((preg_match("/^[0-9]*$/",$short) && strlen($short) == 6) || $short == '无')
            {
                $data = array('longnum'=>$longnum, 'short'=>$short, 'step'=>2);
                $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                if($res)
                {
                    $reply = "3/9请告诉我们你的第一、第二意向部门，以及是否服从调剂。\n{$line}第二意向部门若无可不填，默认服从调剂，不服从请填否。\n{$line}格式为：第一部门+第二部门+否，用空格隔开。部门标准名称：办公室、公关部、学习部、宣传部、文娱部、技术部、媒体中心、体育中心、权服中心\n{$line}&lt;a href=&quot;http://m.wsq.qq.com/262827975&quot;&gt;点击查看各部门介绍&lt;/a&gt;";
                }
                else
                {
                    $this->endContext();
                    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                    $reply = $errmsg;
                }
            }
            else
            {
                $reply = "请告诉我们正确的短号";
            }
        }
        else
        {
            $reply = "请正确输入手机号码，以便我们通知面试哦~格式：长号+短号，空格隔开，无短号则不填";
        }
        break;
    case 2:
        $keyword = preg_split("/[\s,]+/", $msg);
        $department1 = $keyword[0];
        $key2 = $keyword[1];
        $key3 = $keyword[2];
        if(!empty($dep1[$department1]))
        {
            if(empty($key2))
            {
                $department2 = "无";
                $arrangement = "是";
            }
            else
            {
                if(!empty($dep2[$key2]))
                {
                    $department2 = $key2;
                    if($key3 == "是"|| $key3 == "否")
                    {
                        $arrangement = $key3;
                    }
                    else
                    {
                        $arrangement = "是";
                    }
                }
                else
                {
                    if($key2 == "是"|| $key2 == "否")
                    {
                        $department2 = "无";
                        $arrangement = $key2;
                    }
                    else
                    {
                        $department2 = "无";
                        $arrangement = "是";
                    }
                }
            }
            $data = array('department1'=>$department1, 'department2'=>$department2, 'arrangement'=>$arrangement, 'step'=>3);
            $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
            if($res)
            {
                $reply = "4/9请告诉我们你的爱好及特长：(请不要超过50字)";
            }
            else
            {
                $this->endContext();
                $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                $reply = $errmsg;
            }
        }
        else
        {
            $reply = "格式为：第一部门+第二部门+否，用空格隔开。\n{$line}第二意向部门若无可不填，默认服从调剂，不服从请填否。\n{$line}部门标准名称：办公室、公关部、学习部、宣传部、文娱部、技术部、媒体中心、体育中心、权服中心";
        }
        break;
    case 3:
        if(strlen($msg) <= 150)
        {
            $data = array('hobby'=>$msg, 'step'=>4);
            $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
            if($res)
            {
                $reply = "5/9请告诉我们你认为自己的优缺点：(请不要超过100字)";
            }
            else
            {
                $this->endContext();
                $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                $reply = $errmsg;
            }
        }
        else
        {
            $reply = "输入长度过长，请不要超过50字。";
        }
        break;
    case 4:
        if(strlen($msg) <= 300)
        {
            $data = array('advantages'=>$msg, 'step'=>5);
            $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
            if($res)
            {
                $reply = "6/9请告诉我们你加入学生会的目的：(请不要超过150字)";
            }
            else
            {
                $this->endContext();
                $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                $reply = $errmsg;
            }
        }
        else
        {
            $reply = "输入长度过长，请不要超过100字。";
        }
        break;
    case 5:
        if(strlen($msg) <= 450)
        {
            $data = array('purpose'=>$msg, 'step'=>6);
            $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
            if($res)
            {
                $reply = "7/9请发送照片一张自己的照片";
            }
            else
            {
                $this->endContext();
                $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                $reply = $errmsg;
            }
        }
        else
        {
            $reply = "输入长度过长，请不要超过150字。";
        }
        break;
    case 6:
        if($type == "image")
        {
            $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
            $array = array(':openid'=>$openid);
            $res = pdo_fetch($sql,$array);
            $studentid = $res['studentid'];
            $picurl = "images/suadmission/{$studentid}.jpg";
            $pic_data = ihttp_get($this->message['picurl']);
            $upload = file_write($picurl,$pic_data['content']);
            $url = "http://commwx.hduhelp.com/resource/attachment/images/suadmission/{$studentid}.jpg";
            $data = array('imgurl'=>$url, 'step'=>7);
            $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
            if($res)
            {
                $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
                $array = array(':openid'=>$openid);
                $res = pdo_fetch($sql,$array);
                $studentid = $res['studentid'];
                $name = $res['name'];
                $longnum = $res['longnum'];
                $short = $res['short'];
                $department1 = $res['department1'];
                $department2 = $res['department2'];
                $arrangement = $res['arrangement'];
                $hobby = $res['hobby'];
                $adv = $res['advantages'];
                $purpose = $res['purpose'];
                $imgurl = $res['imgurl'];
                $reply = "8/9姓名：{$name}\n学号：{$studentid}\n长号：{$longnum}\n短号：{$short}\n第一意向部门：{$department1}\n第二意向部门：{$department2}\n服从调剂：{$arrangement}\n爱好：{$hobby}\n优势：{$adv}\n目的：{$purpose}\n照片：{&lt;a href=&quot;{$imgurl}&quot;&gt;点我查看照片&lt;/a&gt;}\n{$line}发送“提交”提交报名，若要修改，发送修改+内容如“修改第二意向部门”以修改对应内容,姓名、学号无法修改。";
            }
            else
            {
                $this->endContext();
                $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                $reply = $errmsg;
            }
        }
        else
        {
            $reply = "不要害羞，来一张嘛~";
        }
        break;
    case 7:
        $ret = preg_match('/修改(.+)/', $msg, $matches);
        $word = $matches[1];
        if($msg == "提交")
        {
            $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
            $array = array(':openid'=>$openid);
            $res = pdo_fetch($sql,$array);
            $studentid = $res['studentid'];             
            $department1 = $res['department1'];
            $department2 = $res['department2'];
            $sql = "SELECT * FROM ".tablename('14freshmen')." WHERE studentid = :studentid";
            $array = array(':studentid'=>$studentid);
            $res = pdo_fetch($sql,$array);
            $sex = $res['sex'];
            $class = $res['class'];
            $classid = $res['classid'];
            $data = array('sex'=>$sex, 'class'=>$class, 'classid'=>$classid);
            $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
            if($department1 == $department2)
            {
                $reply = "意向部门不要重复哦~ 请修改意向部门，若无第二意向部门请填无。";
            }
            else
            {
                $dp1 = $dep1[$department1];
                $dp2 = $dep2[$department2];
                $sql = "SELECT * FROM ".tablename('sudep')." WHERE 1";
                $res = pdo_fetch($sql);
                $dcode = $res[$dp1];
                $ncode = $dcode + 1;
                $code = "{$dp1}{$dcode}";
                $data = array($dp1=>$ncode);
                $res = pdo_update('sudep', $data);
                $data = array('code1'=>$code);
                $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                if($dp2 != 'z')
                {
                    $sql = "SELECT * FROM ".tablename('sudep')." WHERE 1";
                    $res = pdo_fetch($sql);
                    $dcode = $res[$dp2];
                    $ncode = $dcode + 1;
                    $code = "{$dp2}{$dcode}";
                    $data = array($dp2=>$ncode);
                    $res = pdo_update('sudep', $data);
                    $data = array('code2'=>$code);
                    $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                }
                $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
                $array = array(':openid'=>$openid);
                $res = pdo_fetch($sql,$array);
                $code1 = $res['code1'];
                $code2 = $res['code2'];
                $res = pdo_update('suadmission', $data = array('step'=>99), $filter = array('openid'=>$openid));
                if($res)
                {
                    $this->endContext();
                    $reply = "9/9报名完成，你的面试编号为{$code1}{$code2},请等待面试通知，发送“查看学生会报名”可以查看自己的报名情况，以及一轮面试之后的反馈。\n{$line}发送“修改学生会报名”可修改自己的报名信息\n{$line}报名系统退出";
                }
                else
                {
                    $this->endContext();
                    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                    $reply = $errmsg;
                }
            }
        }
        else
        {
            $key = $change[$word];
            if(!empty($key))
            {
                $data = array($key=>'rpl', 'step'=>8);
                $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                if($res)
                {
                    if($word == "第一意向部门" || $word == "第二意向部门")
                    {
                        $reply = "请输入{$word}\n{$line}部门标准名称：办公室、公关部、学习部、宣传部、文娱部、技术部、媒体中心、体育中心、权服中心";
                    }
                    else
                    {
                        $reply = "请输入{$word}";
                    }
                }
                else
                {
                    $this->endContext();
                    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                    $reply = $errmsg;
                }
            }
            else
            {
                $reply = "发送“提交”以提交报名，若要修改，发送修改+内容如“修改第二意向部门”以修改对应内容,姓名、学号无法修改。\n{$line}可修改内容：长号、短号、第一意向部、第二意向部门、服从调剂、爱好、优势、目的、照片";
            }
        }
        break;
    case 8:
        $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
        $array = array(':openid'=>$openid);
        $res = pdo_fetch($sql,$array);
        $studentid = $res['studentid'];
        $name = $res['name'];
        $longnum = $res['longnum'];
        $short = $res['short'];
        $department1 = $res['department1'];
        $department2 = $res['department2'];
        $arrangement = $res['arrangement'];
        $hobby = $res['hobby'];
        $adv = $res['advantages'];
        $purpose = $res['purpose'];
        $imgurl = $res['imgurl'];
        if($longnum == "rpl")
        {
            if(preg_match("/^[0-9]*$/",$msg) && strlen($msg) == 11)
            {
                $data = array('longnum'=>$msg, 'step'=>7);
                $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
                $array = array(':openid'=>$openid);
                $res = pdo_fetch($sql,$array);
                $longnum = $res['longnum'];
                if($res)
                {
                    $reply = "修改成功\n{$line}姓名：{$name}\n学号：{$studentid}\n长号：{$longnum}\n短号：{$short}\n第一意向部门：{$department1}\n第二意向部门：{$department2}\n服从调剂：{$arrangement}\n爱好：{$hobby}\n优势：{$adv}\n目的：{$purpose}\n照片：{&lt;a href=&quot;{$imgurl}&quot;&gt;点我查看照片&lt;/a&gt;}\n{$line}发送“提交”提交报名，若要修改，发送修改+内容如“修改第二意向部门”以修改对应内容,姓名、学号无法修改。";
                }
                else
                {
                    $this->endContext();
                    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                    $reply = $errmsg;
                }
            }
            else
            {
                $reply = "请正确输入长号。";
            }
        }
        elseif($short == "rpl")
        {
            if((preg_match("/^[0-9]*$/",$msg) && strlen($msg) == 6)||$msg == "无")
            {
                $data = array('short'=>$msg, 'step'=>7);
                $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
                $array = array(':openid'=>$openid);
                $res = pdo_fetch($sql,$array);
                $short = $res['short'];
                if($res)
                {
                    $reply = "修改成功\n{$line}姓名：{$name}\n学号：{$studentid}\n长号：{$longnum}\n短号：{$short}\n第一意向部门：{$department1}\n第二意向部门：{$department2}\n服从调剂：{$arrangement}\n爱好：{$hobby}\n优势：{$adv}\n目的：{$purpose}\n照片：{&lt;a href=&quot;{$imgurl}&quot;&gt;点我查看照片&lt;/a&gt;}\n{$line}发送“提交”提交报名，若要修改，发送修改+内容如“修改第二意向部门”以修改对应内容,姓名、学号无法修改。";
                }
                else
                {
                    $this->endContext();
                    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                    $reply = $errmsg;
                }
            }
            else
            {
                $reply = "请正确输入短号。";
            }
        }
        elseif($department1 == "rpl")
        {
            if(!empty($dep1[$msg]))
            {
                $data = array('department1'=>$msg, 'step'=>7);
                $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
                $array = array(':openid'=>$openid);
                $res = pdo_fetch($sql,$array);
                $department1 = $res['department1'];
                if($res)
                {
                    $reply = "修改成功\n{$line}姓名：{$name}\n学号：{$studentid}\n长号：{$longnum}\n短号：{$short}\n第一意向部门：{$department1}\n第二意向部门：{$department2}\n服从调剂：{$arrangement}\n爱好：{$hobby}\n优势：{$adv}\n目的：{$purpose}\n照片：{&lt;a href=&quot;{$imgurl}&quot;&gt;点我查看照片&lt;/a&gt;}\n{$line}发送“提交”提交报名，若要修改，发送修改+内容如“修改第二意向部门”以修改对应内容,姓名、学号无法修改。";
                }
                else
                {
                    $this->endContext();
                    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                    $reply = $errmsg;
                }
            }
            else
            {
                $reply = "部门标准名称：办公室、公关部、学习部、宣传部、文娱部、技术部、媒体中心、体育中心、权服中心";
            }
        }
        elseif($department2 == "rpl")
        {
            if(!empty($dep2[$msg]))
            {
                $data = array('department2'=>$msg, 'step'=>7);
                $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
                $array = array(':openid'=>$openid);
                $res = pdo_fetch($sql,$array);
                $department2 = $res['department2'];
                if($res)
                {
                    $reply = "修改成功\n{$line}姓名：{$name}\n学号：{$studentid}\n长号：{$longnum}\n短号：{$short}\n第一意向部门：{$department1}\n第二意向部门：{$department2}\n服从调剂：{$arrangement}\n爱好：{$hobby}\n优势：{$adv}\n目的：{$purpose}\n照片：{&lt;a href=&quot;{$imgurl}&quot;&gt;点我查看照片&lt;/a&gt;}\n{$line}发送“提交”提交报名，若要修改，发送修改+内容如“修改第二意向部门”以修改对应内容,姓名、学号无法修改。";
                }
                else
                {
                    $this->endContext();
                    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                    $reply = $errmsg;
                }
            }
            else
            {
                $reply = "部门标准名称：办公室、公关部、学习部、宣传部、文娱部、技术部、媒体中心、体育中心、权服中心";
            }
        }
        elseif($arrangement == "rpl")
        {
            if($msg == "是" || $msg == "否")
            {
                $data = array('arrangement'=>$msg, 'step'=>7);
                $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
                $array = array(':openid'=>$openid);
                $res = pdo_fetch($sql,$array);
                $arrangement = $res['arrangement'];
                if($res)
                {
                    $reply = "修改成功\n{$line}姓名：{$name}\n学号：{$studentid}\n长号：{$longnum}\n短号：{$short}\n第一意向部门：{$department1}\n第二意向部门：{$department2}\n服从调剂：{$arrangement}\n爱好：{$hobby}\n优势：{$adv}\n目的：{$purpose}\n照片：{&lt;a href=&quot;{$imgurl}&quot;&gt;点我查看照片&lt;/a&gt;}\n{$line}发送“提交”提交报名，若要修改，发送修改+内容如“修改第二意向部门”以修改对应内容,姓名、学号无法修改。";
                }
                else
                {
                    $this->endContext();
                    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                    $reply = $errmsg;
                }
            }
            else
            {
                $reply = "请填是或否";
            }
        }
        elseif($hobby == "rpl")
        {
            if(strlen($msg) <= 150)
            {
                $data = array('hobby'=>$msg, 'step'=>7);
                $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
                $array = array(':openid'=>$openid);
                $res = pdo_fetch($sql,$array);
                $hobby = $res['hobby'];
                if($res)
                {
                    $reply = "修改成功\n{$line}姓名：{$name}\n学号：{$studentid}\n长号：{$longnum}\n短号：{$short}\n第一意向部门：{$department1}\n第二意向部门：{$department2}\n服从调剂：{$arrangement}\n爱好：{$hobby}\n优势：{$adv}\n目的：{$purpose}\n照片：{&lt;a href=&quot;{$imgurl}&quot;&gt;点我查看照片&lt;/a&gt;}\n{$line}发送“提交”提交报名，若要修改，发送修改+内容如“修改第二意向部门”以修改对应内容,姓名、学号无法修改。";
                }
                else
                {
                    $this->endContext();
                    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                    $reply = $errmsg;
                }
            }
            else
            {
                $reply = "输入长度过长，请不要超过50字。";
            }
        }
        elseif($adv == "rpl")
        {
            if(strlen($msg) <= 300)
            {
                $data = array('adv'=>$msg, 'step'=>7);
                $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
                $array = array(':openid'=>$openid);
                $res = pdo_fetch($sql,$array);
                $adv = $res['adv'];
                if($res)
                {
                    $reply = "修改成功\n{$line}姓名：{$name}\n学号：{$studentid}\n长号：{$longnum}\n短号：{$short}\n第一意向部门：{$department1}\n第二意向部门：{$department2}\n服从调剂：{$arrangement}\n爱好：{$hobby}\n优势：{$adv}\n目的：{$purpose}\n照片：{&lt;a href=&quot;{$imgurl}&quot;&gt;点我查看照片&lt;/a&gt;}\n{$line}发送“提交”提交报名，若要修改，发送修改+内容如“修改第二意向部门”以修改对应内容,姓名、学号无法修改。";
                }
                else
                {
                    $this->endContext();
                    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                    $reply = $errmsg;
                }
            }
            else
            {
                $reply = "输入长度过长，请不要超过100字。";
            }
        }
        elseif($purpose == "rpl")
        {
            if(strlen($msg) <= 450)
            {
                $data = array('purpose'=>$msg, 'step'=>7);
                $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
                $array = array(':openid'=>$openid);
                $res = pdo_fetch($sql,$array);
                $purpose = $res['purpose'];
                if($res)
                {
                    $reply = "修改成功\n{$line}姓名：{$name}\n学号：{$studentid}\n长号：{$longnum}\n短号：{$short}\n第一意向部门：{$department1}\n第二意向部门：{$department2}\n服从调剂：{$arrangement}\n爱好：{$hobby}\n优势：{$adv}\n目的：{$purpose}\n照片：{&lt;a href=&quot;{$imgurl}&quot;&gt;点我查看照片&lt;/a&gt;}\n{$line}发送“提交”提交报名，若要修改，发送修改+内容如“修改第二意向部门”以修改对应内容,姓名、学号无法修改。";
                }
                else
                {
                    $this->endContext();
                    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                    $reply = $errmsg;
                }
            }
            else
            {
                $reply = "输入长度过长，请不要超过150字。";
            }
        }
        elseif($imgurl == "rpl")
        {
            if($type == "image")
            {
                $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
                $array = array(':openid'=>$openid);
                $res = pdo_fetch($sql,$array);
                $studentid = $res['studentid'];
                $picurl = "images/suadmission/{$studentid}.jpg";
                $pic_data = ihttp_get($this->message['picurl']);
                $upload = file_write($picurl,$pic_data['content']);
                $url = "http://commwx.hduhelp.com/resource/attachment/images/suadmission/{$studentid}.jpg";
                $data = array('imgurl'=>$url, 'step'=>7);
                $res = pdo_update('suadmission', $data, $filter = array('openid'=>$openid));
                $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
                $array = array(':openid'=>$openid);
                $res = pdo_fetch($sql,$array);
                $imgurl = $res['imgurl'];
                if($res)
                {
                    $reply = "修改成功\n{$line}姓名：{$name}\n学号：{$studentid}\n长号：{$longnum}\n短号：{$short}\n第一意向部门：{$department1}\n第二意向部门：{$department2}\n服从调剂：{$arrangement}\n爱好：{$hobby}\n优势：{$adv}\n目的：{$purpose}\n照片：{&lt;a href=&quot;{$imgurl}&quot;&gt;点我查看照片&lt;/a&gt;}\n{$line}发送“提交”提交报名，若要修改，发送修改+内容如“修改第二意向部门”以修改对应内容,姓名、学号无法修改。";
                }
                else
                {
                    $this->endContext();
                    $res = pdo_delete('suadmission', $filter = array('openid'=>$openid), $gule = 'AND');
                    $reply = $errmsg;
                }
            }
            else
            {
                $reply = "不要害羞，来一张嘛~";
            }
        }
        break;
    default:
        $this->endContext();
        $reply = "出错了,请速速联系管理员\n{$line}报名系统退出";
        break;
    }
}
return $this->respText($reply);