<?php

include_once("../session.php");


class fit
{
	function avg_percent_fit_iskill($db_object,$common,$image,$user_id)
	{
		$user_table		= $common->prefix_table("user_table");
		
		$model_factors_1    =$common->prefix_table("model_factors_1");
		
		$model_percent_fit  =$common->prefix_table("models_percent_fit");
		
		$family_position    =$common->prefix_table("family_position");
		
		$users=$common->return_direct_reports($db_object,$user_id);
	

		for($i=0;$i<count($users);$i++)
		{
			$user=$users[$i];
			
			$sql="select position from $user_table where user_id='$user'";
					
			$pos_res=$db_object->get_a_line($sql);
			
			$pos=$pos_res[position];
						
			$sql="select family_id from $family_position where position_id='$pos'";
	
			$fly_res=$db_object->get_single_column($sql);
						
			if(count($fly_res)>0)
			{					
				$family_ids=@implode(",",$fly_res);

				$fly="(".$family_ids.")";
					
				$sql="select model_id from $model_factors_1 where family in $fly";
	
				$model_arr=$db_object->get_single_column($sql);
				
				if(count($model_arr)>0)
				{
					$model_ids=@implode(",",$model_arr);
					
					$models="(".$model_ids.")";
					
					$sql="select avg(percent_fit) as percent from $model_percent_fit where model_id in $models 
					
					and user_id='$user' and skill_type='t'";
				
					$percent_arr=$db_object->get_a_line($sql);
					
					$total_percent+=$percent_arr[0];
				}
			}
			
		}
		
		if(count($users)>=1)
		{
			$percent=$total_percent/count($users);
			
					$total=100;
		
			$heads = array(
    array("Tech Percentage Fit", 3, "c"),  
    );
				
		$array=array($percent,$total);
			
		$vals=$image->return_Array($array);
		
		$image->init(150,150, $vals);
		
		$image->draw_heading($heads);
	
		$image->set_legend_percent();

		 $image->display($filename);
		}
		else
		{
				$heads = 
	array(
	    			array("No employee",3,"c"),
	    			array("under this boss", 3, "c")
    		);



	$image = ImageCreate(150, 150); 

	$white = ImageColorAllocate($image,255,255,255);
	
	$black = ImageColorAllocate($image,0,0,0);
	
	
	ImageString($image, $heads[0][1],10,0, $heads[0][0],
	
   	$black);
	
	ImageString($image, $heads[1][1],10,15, $heads[1][0],
	
   	$black);
	
    
   	//ImageString($image,3,50,50,'heading',16);

	ImagePng($image);
		}
		
	
	}
}
$obj=new fit();

$obj->avg_percent_fit_iskill($db_object,$common,$image,$user_id);
?>
