<?php
include("../session.php");
include("header.php");
class EmployeewithoutPosition
{
	function show_details($common,$db_object,$user_id)
	{
		$path=$common->path;
		$xFile=$path."templates/core/employee_without_position.html";
		$xTemplate=$common->return_file_content($db_object,$xFile);
		$user_table=$common->prefix_table("user_table");
		$position_table=$common->prefix_table("position");
$selqry="select $user_table.user_id,$user_table.username,$user_table.added_by,
$user_table.position,
date_format($user_table.reg_date,'%m.%d.%Y.%i:%s') as reg_date  from  $user_table left join $position_table on  $position_table.pos_id=$user_table.position where $user_table.position is null and $user_table.user_type<>'external'";
$userset=$db_object->get_rsltset($selqry);
for($i=0;$i<count($userset);$i++)
{	$temp_id=$userset[$i]["user_id"];
	$userset[$i]["username"]=$common->name_display($db_object,$temp_id);
	$temp_ad_user_id=$userset[$i]["added_by"];
	$userset[$i]["added_by"]=$common->name_display($db_object,$temp_ad_user_id);
}

$values["employee_loop"]=$userset;
$xTemplate=$common->multipleloop_replace($db_object,$xTemplate,$values,$sel_arr);
$xTemplate=$common->direct_replace($db_object,$xTemplate,$vals);

		echo $xTemplate;
	}
}
$empobj=new EmployeewithoutPosition;
$empobj->show_details($common,$db_object,$user_id);
include("footer.php");
?>
