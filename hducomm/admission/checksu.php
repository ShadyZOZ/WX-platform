<?php
$openid = $this->message['from'];
$msg =  $this->message['content'];
$dep = array("学习部"=>"a","媒体中心"=>"b","宣传部"=>"c","办公室"=>"d","技术部"=>"e","文娱部"=>"f","公关部"=>"g","权服中心"=>"h","体育中心"=>"i");
$line = "=================\n";
$sql = "SELECT * FROM ".tablename('su')." WHERE openid = :openid";
$array = array(':openid'=>$openid);
$res = pdo_fetch($sql,$array);
$dp = $res['dep'];
$reply = "";
if(!$this->inContext)
{
    if(empty($dp))
    {
        $sql = "SELECT * FROM ".tablename('suadmission')." WHERE openid = :openid";
        $array = array(':openid'=>$openid);
        $res = pdo_fetch($sql,$array);
        $step = $res['step'];
        if($step == 99)
        {
            $code1 = $res['code1'];
            $code2 = $res['code2'];
            $name = $res['name'];
            $department1 = $res['department1'];
            $department2 = $res['department2'];
            $arrangement = $res['arrangement'];
            $feedback1 = $res['feedback1'];
            $feedback2 = $res['feedback2'];
            $reply = "面试编号：{$code1}{$code2}\n具体面试时间及地点请参照各部长发送的短信\n姓名：{$name}\n第一意向部门：{$department1}\n第二意向部门：{$department2}\n服从调剂：{$arrangement}\n反馈1：{$feedback1}\n反馈2：{$feedback2}\n{$line}感谢报名通信学生会";
        }
        else
        {
            $reply = "未搜索到你的报名信息，你成功报名";
        }
    }
    else
    {
        $this->beginContext(1800);
        $reply = "进入查询模式(30分钟内有效，发送“退出”提前退出。)\n{$line}发送“概况”查看各部门报名人数\n{$line}各部长直接发送数字查看自己部门对应编号新生报名信息\n{$line}发送部门+数字可查看对应部门新生报名信息";
        #$reply = "今天只玩，不工作";
    }
}
elseif($msg == "退出")
{
    $this->endContext();
    $reply = "已退出查询模式";
}
elseif($msg == "概况")
{
    $sql = "SELECT * FROM ".tablename('sudep')." WHERE 1";
    $res = pdo_fetch($sql);
    $a = $res['a'] - 1;
    $b = $res['b'] - 1;
    $c = $res['c'] - 1;
    $d = $res['d'] - 1;
    $e = $res['e'] - 1;
    $f = $res['f'] - 1;
    $g = $res['g'] - 1;
    $h = $res['h'] - 1;
    $i = $res['i'] - 1;
    $sql1 = "SELECT * FROM ".tablename('suchange')." WHERE 1";
    $res1 = pdo_fetch($sql1);
    $ca = $res1['a'];
    $cb = $res1['b'];
    $cc = $res1['c'];
    $cd = $res1['d'];
    $ce = $res1['e'];
    $cf = $res1['f'];
    $cg = $res1['g'];
    $ch = $res1['h'];
    $ci = $res1['i'];
    $sum = $a + $b + $c + $d + $e + $f + $g + $h + $i - $ca - $cb - $cc - $cd - $ce - $cf - $cg - $ch - $ci;
    $reply = "各部门报名人数如下\n{$line}办公室：{$d} 转出：{$cd}\n学习部：{$a} 转出：{$ca}\n宣传部：{$c} 转出：{$cc}\n技术部：{$e} 转出：{$ce}\n文娱部：{$f} 转出：{$cf}\n公关部：{$g} 转出：{$cg}\n权服中心：{$h} 转出：{$ch}\n媒体中心：{$b} 转出：{$cb}\n体育中心：{$i} 转出：{$ci}\n报名总人次：{$sum}";
}
elseif(preg_match("/^[0-9]*$/",$msg) && $dp != 'zxt')
{
    $code = "{$dp}{$msg}";
    $sql = "SELECT * FROM ".tablename('suadmission')." WHERE code1 = :code OR code2 = :code";
    $array = array(':code'=>$code);
    $res = pdo_fetch($sql,$array);
    $code1 = $res['code1'];
    $code2 = $res['code2'];
    $time = $res['time'];
    $room = $res['room'];
    $studentid = $res['studentid'];
    $name = $res['name'];
    $sex = $res['sex'];
    $class = $res['class'];
    $classid = $res['classid'];
    $longnum = $res['longnum'];
    $short = $res['short'];
    $department1 = $res['department1'];
    $department2 = $res['department2'];
    $arrangement = $res['arrangement'];
    $hobby = $res['hobby'];
    $adv = $res['advantages'];
    $purpose = $res['purpose'];
    $imgurl = $res['imgurl'];
    $feedback = $res['feedback'];
    if(!empty($name))
    {
        $reply = "面试编号：{$code1}{$code2}\n姓名：{$name}\n性别：{$sex}\n学号：{$studentid}\n班级：{$class}\n班级号：{$classid}\n长号：{$longnum}\n短号：{$short}\n第一意向部门：{$department1}\n第二意向部门：{$department2}\n服从调剂：{$arrangement}\n爱好：{$hobby}\n优势：{$adv}\n目的：{$purpose}\n照片：{&lt;a href=&quot;{$imgurl}&quot;&gt;点我查看照片&lt;/a&gt;}\n部长反馈：{$feedback}";
    }
    else
    {
        $reply = "查不到此编号新生报名信息";
    }
}
else
{
    preg_match('/^(?P<department>办公室|公关部|学习部|宣传部|文娱部|技术部|媒体中心|体育中心|权服中心) *(?P<num>[0-9\d]{1,})$/i', $msg, $matches);
    $department = $matches['department'];
    $num = $matches['num'];
    $dp = $dep[$department];
    if(!empty($dp))
    {
        $code = "{$dp}{$num}";
        $sql = "SELECT * FROM ".tablename('suadmission')." WHERE code1 = :code OR code2 = :code";
        $array = array(':code'=>$code);
        $res = pdo_fetch($sql,$array);
        $code1 = $res['code1'];
        $code2 = $res['code2'];
        $studentid = $res['studentid'];
        $name = $res['name'];
        $sex = $res['sex'];
        $class = $res['class'];
        $classid = $res['classid'];
        $longnum = $res['longnum'];
        $short = $res['short'];
        $department1 = $res['department1'];
        $department2 = $res['department2'];
        $arrangement = $res['arrangement'];
        $imgurl = $res['imgurl'];
        $feedback1 = $res['feedback1'];
        $feedback2 = $res['feedback2'];
        if($res)
        {
            $reply = "面试编号：{$code1}{$code2}\n姓名：{$name}\n性别：{$sex}\n学号：{$studentid}\n班级：{$class}\n班级号：{$classid}\n长号：{$longnum}\n短号：{$short}\n第一意向部门：{$department1}\n第二意向部门：{$department2}\n服从调剂：{$arrangement}\n照片：{&lt;a href=&quot;{$imgurl}&quot;&gt;点我查看照片&lt;/a&gt;}\n反馈1：{$feedback1}\n反馈2：{$feedback2}";
        }
        else
        {
            $reply = "查不到此编号新生报名信息";
        }
    }
    else
    {
        $reply = "发送“概况”查看各部门报名人数\n{$line}各部长直接发送数字查看自己部门对应编号新生报名信息\n{$line}发送部门+编号可查看对应部门新生报名信息";
	}
}
return $this->respText($reply);