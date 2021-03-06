<?php
include("../session.php");
include("header.php");
class Employee_without_Boss
{
	function show_positions($common,$db_object,$user_id,$error_msg)
	{
	$path=$common->path;
	$xFile=$path."templates/career/core_data/employees_without_boss.html";
	$xTemplate=$common->return_file_content($db_object,$xFile);
	$position_table=$common->prefix_table("position");

	$user_table=$common->prefix_table("user_table");
	if($user_id!=1)
	{
$selqry="select $user_table.user_id,$user_table.username,
date_format($user_table.reg_date,'%m.%d.%Y.%i:%s') as reg_date ,
$user_table.added_by,$user_table.position,$position_table.boss_no
from $user_table left join $position_table
on $user_table.position=$position_table.pos_id
where $user_table.user_type='employee' and
($position_table.boss_no is null or $position_table.boss_no=0) and
$user_table.user_id<>1 and $user_table.admin_id='$user_id'";
	}
	else
	{
$selqry="select $user_table.user_id,$user_table.username,
date_format($user_table.reg_date,'%m.%d.%Y.%i:%s') as reg_date ,

$user_table.added_by,$user_table.position,$position_table.boss_no
from $user_table left join $position_table
on $user_table.position=$position_table.pos_id
where $user_table.user_type='employee' and
($position_table.boss_no is null or $position_table.boss_no=0) and
$user_table.user_id<>1";
	}
//-------------------if needed WE can add for a particular administrators direct reprorts
// and $user_table.admin_id='$user_id'";
//----------------------------

$userset=$db_object->get_rsltset($selqry);

if($userset[0]=="")
{
	echo $error_msg['cNoEmployeeNoBoss'];
	
	include_once("footer.php");exit;
}
for($i=0;$i<count($userset);$i++)
{
	
	$temp_username=$userset[$i]["username"];
	$temp_id=$userset[$i]["user_id"];
	$userset[$i]["username"]=$common->name_display($db_object,$temp_id);
$temp_added_user_id=$userset[$i]["added_by"];
$userset[$i]["added_by"]=$common->name_display($db_object,$temp_added_user_id);
}

$values["employee_loop"]=$userset;


$xTemplate=$common->multipleloop_replace($db_object,$xTemplate,$values,$sel_arr);
$vals=array();
$xTemplate=$common->direct_replace($db_object,$xTemplate,$vals);
	echo $xTemplate;

	}
}
$posobj= new Employee_without_Boss;
$posobj->show_positions($common,$db_object,$user_id,$error_msg); 
include("footer.php");
?>
