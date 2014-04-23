<?
include_once("../session.php");
include_once("header.php");
class summary
{
	function view_form($db_object,$common,$default,$user_id,$gbl_met_value,$post_var,$technical,$interpersonal,$err,$date_format)
	{

		$comments_lastratingarray = array();
		$results_lastratingarray = array();
		$committed_array = array();
		$rater1_array = array();
		$rater2_array = array();
		$color_array =array();
	
		while(list($key,$value)=@each($post_var))
		{
			$$key = $value;
			if(ereg("fCommit_",$key))
			{
				$committed_array[] = $key;
			}
			if(ereg("^check_comment_lastrating",$key))
			{
				$comments_lastratingarray[] = $key;
			}
			if(ereg("^check_result_lastrating",$key))
			{
				$results_lastratingarray[] = $key;
			}
			if(ereg("^fRater1_",$key))
			{
				$rater1_array[] = $key;			
			}
			if(ereg("^fRater2_",$key))
			{
				if($$key!="")
				{
					$rater2_array[] = $key;
				}
			}
			if(ereg("^fRater3_",$key))
			{
				if($$key!="")
				{
					$rater3_array[] = $key;
				}
			}
					
		}

		$path = $common->path;
		if($uid!="")
		{
			$user_id = $uid;
		}	
		$val['uid'] = $uid;
	//read html template
		$filename = $path."templates/performance/performance_summary2.html";
		$file = $common->return_file_content($db_object,$filename);

	//read text template
		$flname = $path."templates/performance/performance_summary_report.txt";
		$file_text = $common->return_file_content($db_object,$flname);
		$open = "report/performance_summary_report_$user_id.txt";	
		$fp=fopen("$open","w");
	
		$file = $common->is_module_purchased($db_object,$path,$file,$common->lfvar);
		$file_text = $common->is_module_purchased($db_object,$path,$file_text,$common->lfvar);

	//table declaration
		$app_sel_objective = $common->prefix_table("approved_selected_objective");
		$user_table = $common->prefix_table("user_table");
		$approved_feedback = $common->prefix_table("approved_feedback");
		$config_table = $common->prefix_table("config");
		$rating_table = $common->prefix_table("rating");
		$performance_feedback = $common->prefix_table("performance_feedback");
		$position_table = $common->prefix_table("position");
		$approved_affected = $common->prefix_table("approved_affected");
		$skill = $common->prefix_table("skills");
		$approveduser_objective = $common->prefix_table("approveduser_objective");		
		
	//display the username
		$name = $common->name_display($db_object,$user_id);
		$val['uname'] = $name;
	//from user_table
		$userqry = "select username,position from $user_table where user_id='$user_id'";
		$userres = $db_object->get_a_line($userqry);
		$val['username'] = $userres['username'];
		$self = $userres['username'];
		
//check
		$position = $userres['position'];
	//immediate boss
		$bossid = $common->immediate_boss($db_object,$user_id);

	//from user table 
		$bossqry = "select username from $user_table where user_id = '$bossid'";
		$boss = $db_object->get_a_line($bossqry);
		$val['bossname'] = $boss['username'];
		$bossname = $boss['username'];

		
	//from approved_selected_objective
		$selobj = "select sl_id,o_id,objective_$default as objective,priority,committed_no,percent from $app_sel_objective 
				where user_id='$user_id' and status='A' order by sl_id";
		$selres = $db_object->get_rsltset($selobj);

	//from Config
		$boss=0;
		$conqry = "select person_affected from $config_table";
		$conres = $db_object->get_a_line($conqry);
		$noofperson = $conres['person_affected'];
		$boss = 1;	
	//Total rater is 4 (without self),noofperson(the raters we have selected) + boss (boss's rating)
		$totalperson = $noofperson + $boss ;
		//echo $totalperson;
	//from rating
	//" gbl_met_value " - Global value we have given for expectation met(constant)
		$ratqry = "select rval from $rating_table where rval='$gbl_met_value'";
		$ratres = $db_object->get_a_line($ratqry);
		$r_val = $ratres['rval'];
	//met expectation point
		$metexpectation = $r_val * $totalperson;

//Patterns
	//onjective loop
		$pattern = "/<{objective_loopstart}>(.*?)<{objective_loopend}>/s";
		preg_match($pattern,$file,$arr);
		$match = $arr[0];
		$str="";

		preg_match($pattern,$file_text,$arr_text);
		$match_text = $arr_text[0];

	//raters loop
		$pattern1 = "/<{rater_loopstart}>(.*?)<{rater_loopend}>/s";
		preg_match($pattern1,$file,$arr1);
		$match1 = $arr1[0];
		$str1="";
		preg_match($pattern1,$file_text,$arr1_text);
		$match1_text = $arr1_text[0];

	//ratercomment loop
		$pattern2 = "/<{ratercomment_loopstart}>(.*?)<{ratercomment_loopend}>/s";
		preg_match($pattern2,$file,$arr2);
		$match2 = $arr2[0];
		$str2 = "";
		preg_match($pattern2,$file_text,$arr2_text);
		$match2_text = $arr2_text[0];

	//skill techtime loop
		//no of skills
		$pattern3="/<{skill_loopstart}>(.*?)<{skill_loopend}>/s";
		preg_match($pattern3,$file,$arr3);
		$match3=$arr3[0];
		$str3="";
		preg_match($pattern3,$file_text,$arr3_text);
		$match3_text=$arr3_text[0];

	//view rating loop
		$pattern4="/<{view_rating_loopstart}>(.*?)<{view_rating_loopend}>/s";	
		preg_match($pattern4,$file,$arr4);
		$match4=$arr4[0];
		$str4="";
		preg_match($pattern4,$file_text,$arr4_text);
		$match4_text=$arr4_text[0];
	//view over all rating
		$pattern5="/<!--overall_loopstart-->(.*?)<!--overall_loopend-->/s";
	
	//view comparison 1
		$pattern6 = "/<{rateravg_loopstart}>(.*?)<{rateravg_loopend}>/s";
		preg_match($pattern6,$file,$arr6);
		$match6 = $arr6[0];
		$str6="";
		preg_match($pattern6,$file_text,$arr6_text);
		$match6_text = $arr6_text[0];
		$pattern7 = "/<!--overall1_loopstart-->(.*?)<!--overall1_loopend-->/s";
	//view comparison 2
		$pattern8 = "/<{rateravg1_loopstart}>(.*?)<{rateravg1_loopend}>/s";
		preg_match($pattern8,$file,$arr8);
		$match8 = $arr8[0];		
		$str8="";
		preg_match($pattern8,$file_text,$arr8_text);
		$match8_text = $arr8_text[0];
	
		$pattern9 = "/<!--overall2_loopstart-->(.*?)<!--overall2_loopend-->/s";
//end pattern
		$count = 0;

	//main loop start
			
		for($i=0;$i<count($selres);$i++)
		{	

			$str1="";
			$str2="";
			$str3="";
			$str4="";
			$str6="";
			$str8="";
		
			$str1_text="";
			$str2_text="";
			$str3_text="";
			$str4_text="";
			$str6_text="";
			$str8_text="";
			
			$ratersname = "";	
			$comments = "";
			$appdate = "";
			$AvgExpect = "";
			$AvgFulfilled ="";
			$Qaccomplish = "";
			$Qcommitted="";
			$Qmetexpectation="";
			$Qactual="";
			$Cfulfill = "";
			$Qfulfilled="";
			$overallavg="";
			$overallavg1="";
			$overallavg2="";
			$Ccommit="";
			$Caccomplish="";
			$date_array = "";
			$fulfilled_array = "";
			$to_date="";
			$from_date="";
			$rt_val="";
			$da_val="";
			$over_all1="";
			$combine="";
			$skillres=array();
			$overflag=0;
			$count = $count + 1;
			$objective = $selres[$i]['objective'];			
			$o_id = $selres[$i]['o_id'];
			$priority = $selres[$i]['priority'];
			$sl_id = $selres[$i]['sl_id'];
			$checkcumulative = $selres[$i]['percent'];
			$color_array = array();			
			$color_array[$bossname] = 'boss';
			$color_array[$self] = 'self';

	//get all  metrics for the given o_id
			$oqry = "select met_id from $approveduser_objective where o_id='$o_id' and 
				user_id='$user_id'";
			$ores = $db_object->get_a_line($oqry);
			$met_id = $ores['met_id'];
			$mqry = "select o_id from $approveduser_objective where met_id='$met_id' and 
				user_id='$user_id'";
			$mres = $db_object->get_single_column($mqry);
			$aver  = count($mres);
			$oid = implode("','",$mres);

		$get = $common->get_fullfilled($db_object,$o_id,$user_id,$dates);

				
//get the raters rated value
			$Ratervalue = "select sum(r_id) from $approved_feedback where o_id in ('$oid')
					 and user_id='$user_id' and status<>'1' and status<>'2'";
			$Resvalue = $db_object->get_single_column($Ratervalue);
			$actual = $Resvalue[0];
			$actual = @($actual/$aver);
			$actual = @sprintf("%01.2f",$actual);

		//calculation for met expectation value
			$expected = @($actual/$metexpectation);
			$expected = $expected * 100;
		//-------
			$fulfilled = $get['Cfulfill'];

		//All form display value
			$expected = @sprintf("%01.2f",$expected);
			$fulfilled = @sprintf("%01.2f",$fulfilled);
			
			$committed = $get['Ccommit'];
			$accomplish = $get['Caccomplish'];
		
			$perqry1 = "select user_id from $performance_feedback where request_from='$user_id' and 
				status='A' and latest='N' and sl_id='$sl_id' and user_id<>'$user_id' and user_id<>'$bossid'";
			$perres1 = $db_object->get_single_column($perqry1);

		//raters loop
			 //$perres1 - (no of raters who has finished rating/except self and current user)
			$allrater = array();
			for($j=0;$j<count($perres1);$j++)
			{
				$cnt = $j+1;
				$rater_id = $perres1[$j];
				$allrater[] = $rater_id;
				$raterqry = "select username from $user_table where
						 user_id='$perres1[$j]'";				
				$raterres = $db_object->get_a_line($raterqry);
				$ratername = $raterres['username'];
				$rate = "rater".($j+1);
				$color_array[$ratername] = $rate;
				$str1.= preg_replace("/<{(.*?)}>/e","$$1",$match1);
				$str1_text.= preg_replace("/<{(.*?)}>/e","$$1",$match1_text);
			}
			$temp = preg_replace($pattern1,$str1,$match);
			$temp_text = preg_replace($pattern1,$str1_text,$match_text);

//to to show the color difference
			$color_array  = @array_unique($color_array);
			$color_key = @array_keys($color_array);
			for($ar=0;$ar<count($color_array);$ar++)
			{
				$ky = $color_key[$ar];
				$vl = $color_array[$ky];
				$com = "$ky = $vl";
				$combine .= $com.":";
			}
			
			$combine = substr($combine,0,-1);

//------view raters comments results-------
		
		$rt_id = array();		
		if(($next!="")||($save!=""))
		{
				$key = $committed_array[$k];
				list($name,$sl_id1,$o_id1)=split("_",$key);
				$checkcomment = "check_comment_lastrating_$sl_id"."_$o_id";

	// View rater's comments for this objective starts		
				
		//check whether comment last rating is checked or not
				$active = "";
				if($$checkcomment!="")
				{
					$active = " and active = 'A'";
				}
		//check whether comment since date is checked or not
			$chksince = "check_since_date_$sl_id";
				$sincedate = "";
				if($$chksince!="")
				{
					$fsin  = "fSince_date_$sl_id";
					$fsince = $$fsin;
					$spt = split("/",$fsince);
					$fsince = $spt[2]."-".$spt[0]."-".$spt[1];
					$today = date("Y-m-d");					
					$sincedate  = "and date_format(approved_date,'%Y-%m-%d') between '$fsince' and '$today'";
				}
		//check whether comment from and to is checked or not
			$chkfromto = "check_from_date_$sl_id";
			$chkfrom  = "fComment_from_$sl_id";
			$chkto = "fComment_to_$sl_id";
			$tofrom = "";
				
			if($$chkfromto!="")
			{
				$chk = $$chkfromto;
				
				if(($$chkfrom !="")&&($$chkto!=""))
				{
					$fFrom = $$chkfrom;
					$fTo = $$chkto;
					$sfr = split("/",$fFrom);
					$fFrom = $sfr[2]."-".$sfr[0]."-".$sfr[1];
					$sto = split("/",$fTo);
					$fTo =  $sto[2]."-".$sto[0]."-".$sto[1];
					$tofrom = "and date_format(approved_date,'%Y-%m-%d') between '$fFrom' and '$fTo'";
				}
			}
		//check whether Year to date comments is checked or not
			$fyrtodt = "fYeartodate_comments_".$sl_id;
			
				//Bossid
					$boss1 = "fBoss1_$sl_id";
					$boss1 = $$boss1;					
					if($boss1=='on')
					{
						$rt_id[] = $bossid;
					}
				//Ratersid
					$rt1count = count($rater1_array);
					if($rt1count != 0 )
					{
						for($m=0;$m<count($rater1_array);$m++)
						{
							$key = $rater1_array[$m];
							list($name,$r_id,$sl_id2)=split("_",$key);
							$rter1 = "fRater1_".$r_id."_".$sl_id;
							$rater1 = $$rter1;
							if($rater1=='on')
							{
								$rt_id[] = $r_id;
							}//end if
						}//m loop end
					}
				//Selfid
					$self1 = "fSelf1_".$sl_id;
					$self1 = $$self1;
					if($self1=='on')
					{
						$rt_id[]=$user_id;
					}
						$rtid = implode("','",$rt_id);

			//$$fyrtodt - check where the year to date is checked

				if(($active!="")||($sincedate!="")||($tofrom!="")||($$fyrtodt!=""))
				{
									
					if(($sincedate!="")||($tofrom!="")||($$fyrtodt!=""))
					{
						$ooid = "o_id  in ('$oid')";
					}
					else
					{
						$ooid = "o_id ='$o_id'";
					}

				//main qry		
					$qry = "select raters_id,effective,date_format(approved_date,'$date_format') as date,username from
						 $approved_feedback,$user_table where $ooid and $approved_feedback.user_id='$user_id' and $user_table.user_id=raters_id 
						 $active $sincedate $tofrom order by raters_id ";
						
					$last = $db_object->get_rsltset($qry);

					for($l=0;$l<count($last);$l++)
					{															
						$comments = $last[$l]['effective'];
						$appdate = $last[$l]['date'];
						$id = $last[$l]['raters_id'];						
						$ratersname = $last[$l]['username'];
						$str2.=preg_replace("/\<\{(.*?)\}\>/e","$$1",$match2);
						$str2_text .= preg_replace("/\<\{(.*?)\}\>/e","$$1",$match2_text);
					}//end for l
				
				$skillqry = "select tech1,tech2,inter1,inter2,date_format(approved_date,'$date_format') as date,username,$user_table.user_id from
				$approved_feedback,$user_table where $ooid and $approved_feedback.user_id='$user_id' and  
				$approved_feedback.raters_id<>'$user_id' and $approved_feedback.raters_id<>'$bossid' and $user_table.user_id=raters_id 
				$active $sincedate $tofrom order by raters_id";
				$skillres = $db_object->get_rsltset($skillqry);					
				}

				$temp1 = preg_replace($pattern2,$str2,$temp);
				$temp1_text = preg_replace($pattern2,$str2_text,$temp_text);
		//skill loop start
			//perres  = no of raters
			$perqry = "select user_id from $performance_feedback where request_from='$user_id' and 
				status='A' and latest='N' and sl_id='$sl_id'";
			$perres = $db_object->get_single_column($perqry);
			$split = @implode("','",$perres);

			$getname = "select username from $user_table where user_id in ('$split')";
			$nameres = $db_object->get_single_column($getname);
										
				for($n=0;$n<count($skillres);$n++)
				{

					$tech1="";
					$tech2="";
					$inter1="";
					$inter2="";
					$skill_array= array();
					$skill_array[] = $skillres[$n]['tech1'];
					$skill_array[] = $skillres[$n]['tech2'];
					$skill_array[] = $skillres[$n]['inter1'];
					$skill_array[] = $skillres[$n]['inter2'];
			
					$tech1_id = $skillres[$n]['tech1'];
					$tech2_id = $skillres[$n]['tech2'];
					$inter1_id = $skillres[$n]['inter1'];
					$inter2_id = $skillres[$n]['inter2'];
									
					$splitskill = implode("','",$skill_array);
					$skillname = "select skill_name,skill_id from $skill where skill_id
							in ('$splitskill')";
					$skillnameres = $db_object->get_rsltset($skillname);
					
					$skillnameres = $common->return_Keyedarray($skillnameres,skill_id,skill_name);	
					//echo "$skillname<br>";
					//print_r($skillnameres);
					//echo "<hr>";				
					$tech1 = $skillnameres[$tech1_id];
					$tech2 = $skillnameres[$tech2_id];
					$inter1 = $skillnameres[$inter1_id];
					$inter2 = $skillnameres[$inter2_id];
					$ratername = $skillres[$n]['username'];
					$skilldate = $skillres[$n]['date'];
					$u_id  = $user_id;
					$admin_id = $skillres[$n]['user_id'];
					$str3.=preg_replace("/<{(.*?)}>/e","$$1",$match3);
					$str3_text.=preg_replace("/<{(.*?)}>/e","$$1",$match3_text);
				}//n loop end

				$temp2 = preg_replace($pattern3,$str3,$temp1);
				$temp2_text = preg_replace($pattern3,$str3_text,$temp1_text);

		//View rater's comments for this objective ends

		//View result for this objective starts

			//Check whether result since last rating is checked or not
				$resflag3=0;				
				$checkresult = "check_result_lastrating_".$sl_id."_".$o_id;
									
				if($$checkresult!="")
				{
					$resflag3 =1;
					$qry = "select date_format(approved_date,'%Y-%m-%d') as appdate from $app_sel_objective 
						where o_id='$o_id' and user_id='$user_id' order by sl_id";
					$res = $db_object->get_single_column($qry);
					$from_date = $res[0];
					$to_date = date("Y-m-d");
				}
		
			//check whether Result since date id checked or not
				$resultsince = "check_result_date_".$sl_id;
				$fResult_date = "fResult_date_".$sl_id;
				$resultsincedate = "";
				$resflag = "";
				if($$resultsince!="")
				{					
					
					if($$fResult_date!="")
					{
						$resultdate = $$fResult_date;
						$sp = split("/",$resultdate);
						$resdate = $sp[2]."-".$sp[0]."-".$sp[1];
						$today = date("Y-m-d");
						$resultsincedate  = "and date_format(approved_date,'%Y-%m-%d') between '$resdate' and '$today'";
						$resflag=1;
						$from_date= $resdate;
						$to_date = $today;
					}						
				}
				
			//check whether Result from and to checked
				$Restofrom="";
				$resflag1 = "";
				$chkresultfrom = "check_Rfrom_date_".$sl_id;
				$Rfrom = "fResult_from_".$sl_id;
				$Rto = "fResult_to_".$sl_id;
				if($$chkresultfrom!="")
				{
					if(($$Rfrom!="")&&($$Rto!=""))
					{
						$fFromres = $$Rfrom;
						$fTores  = $$Rto;
						$fsp = split("/",$fFromres);
						$fResfrom = $fsp[2]."-".$fsp[0]."-".$fsp[1];
						$tsp = split("/",$fTores);
						$fResto = $tsp[2]."-".$tsp[0]."-".$tsp[1];
						$from_date = $fResfrom;
						$to_date = $fResto;			
						$Restofrom = "and date_format(approved_date,'%Y-%m-%d') between '$fResfrom' and '$fResto'";
						$resflag1 = 1;
					}
				}

			//check whether year to date results checked or not
				$resflag2="";
				$Ryrtodate = "fYeartodate_result_".$sl_id;
				if($$Ryrtodate!="")
				{
					$resflag2 = 1;
				}
			//oid												
				if(($resflag==1)||($resflag1==1)||($resflag2==1))
				{
					$ooid = "o_id  in ('$oid')";
						
				}
				else
				{					
					$ooid = "o_id ='$o_id'";
				}
				
				if(($$checkresult!="")||($resflag==1)||($resflag1==1)||($resflag2==1)||($resflag3==1))
				{
			//query starts
			//connect
				$Qselobj = "select sl_id,o_id,objective_$default as objective,priority,committed_no,percent,
					    date_format(approved_date,'%Y-%m-%d') as appdate from $app_sel_objective 
						where $ooid and user_id='$user_id' $resultsincedate $Restofrom order by sl_id";
				//echo $Qselobj;
				$Qselres = $db_object->get_rsltset($Qselobj);
				//echo "$Qselobj<br>";
				//print_r($Qselres);
				
				$ctt = count($Qselres);
				$committed_array = array();				
				$accomplish_array = array();
				
					for($x=0;$x<count($Qselres);$x++)
					{														
						$Qcommit = $Qselres[$x]['committed_no'];						
						$Qprior = $Qselres[$x]['priority'];
						$Qsl_id = $Qselres[$x]['sl_id'];
						$all_date[] = $Qselres[$x]['appdate'];
						$Qo_id = $Qselres[$x]['o_id'];
						$Qcheckcumulative = $Qselres[$x]['percent'];
						$Qdeliqry = "select idelivered from $approved_feedback where user_id='$user_id' and active='A'
								and vaccept='A' and status='2' and o_id='$Qo_id'";
						$Qdelires = $db_object->get_a_line($Qdeliqry);
						$Qaccomp = $Qdelires['idelivered'];						
					//calculation for raters selected rating value
						$Raterval = "select sum(r_id) from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and status<>'1' and status<>'2'";
						$Resval = $db_object->get_single_column($Raterval);
						$Qact = $Resval[0];		
				//-------------

					//calculation for Fulfilled Year-To-Date
						$Qfulfilled  = $common->fulfilled($db_object,$user_id,$Qsl_id,$Qcommit,$Qaccomp,$Qcheckcumulative);											
					//----------
						$Aexpected = $Aexpected + $Qexpected;						
						$Afulfill = $Afulfill + $Qfulfilled ;
						$Qcommitted  = $Qcommitted + $Qcommit;						
						$Qaccomplish = $Qaccomplish + $Qaccomp;
						$committed_array[] = $Qcommitted;
						$date_array[] = $Qselres[$x]['appdate'];						
						$accomplish_array[] = $Qaccomplish;
						$fulfilled_array[] = $Qfulfilled;
					}//x loop ends
						
						if($$Ryrtodate!="")
						{
							$from_date = min($all_date);
							$to_date = max($all_date);
						}
								//echo "from = $from_date to = $to_date";							
						$im_date = @implode(",",$date_array);							
						$date_array = $im_date;
						$im_ful = @implode(",",$fulfilled_array);							
						$fulfilled_array = $im_ful;
						
					if(($resflag==1)||($resflag1==1)||($resflag2==1)||($resflag3==1))
					{	
						$Qactual = @($Qact/$aver);
						$AvgExpect = @($Qactual/$metexpectation);
						$AvgExpect = $AvgExpect * 100;
						$AvgFulfilled = @($Afulfill / count($Qselres));
						$AvgExpect = @sprintf("%01.2f",$AvgExpect);
						$AvgFulfilled = @sprintf("%01.2f",$AvgFulfilled);
						$Qmetexpectation = $metexpectation;							
					}

		//View result for this objective ends	
		}//qry if loop

	//View ratings for this objective by starts
			$ratid  =array();
			$rid  = array();
			$avg = array();
			$rval_array = array();
			$rval_array = array();
	//rater1_array - all raters id,except self and boss id will be there 
	//raters rating
		for($t=0;$t<count($rater1_array);$t++)
		{
			
			$key = $rater1_array[$t];
			if($$key!="")
			{
				list($name,$rtr_id,$rsl_id) = split("_",$key);
				$cRaterck = "fRater1_".$rtr_id."_".$sl_id;
				if($$cRaterck!="")
				{
					$ratid[] = $rtr_id;
				}
			}
		}
							
		//boss1 rating
			$fBoss1 = "fBoss1_".$bossid."_".$sl_id;			
			if($$fBoss1!="")
			{
				$ratid[] = $bossid;
			}
		//self rating
			$fSelf1 = "fSelf1_".$sl_id;

			if($$fSelf1!="")
			{
				$ratid[]=$user_id;			
			}
		//Over all
			$allrater[] = $user_id;
			$allrater[] = $bossid;
			$overall = implode("','",$allrater);
			$fOverall1 = "fOverall1_".$sl_id;
			$space="";
			$from_array = array();
			$from_array1 = array();
			$from_array2 = array();
			$to_array = array();
			$to_array1 = array();
			$to_array2 = array();
			$cal_array = array();
			$cal_array1 = array();
			$cal_array2 = array();
			$tdate_array = array();
			$date_get = array();
			$date_get1 = array();
			$date_get2 = array();

			if($$fOverall1!="")
			{
				$Ratingoverval = "select r_id from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and (status='0' or status='2') and raters_id in ('$overall') order by fd_id";
				$Ratingoverres = $db_object->get_single_column($Ratingoverval);

				$getdate = "select date_format(approved_date,'%Y-%m-%d') from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and (status='0' or status='2') and raters_id in ('$overall') order by fd_id";
				$getdateres = $db_object->get_single_column($getdate);
					
				$dqry ="select min(date_format(approved_date,'%Y-%m-%d')) as min,
					max(date_format(approved_date,'%Y-%m-%d')) as max
					from $approved_feedback where o_id in ('$oid')
					and user_id='$user_id' and (status='0' or status='2') and raters_id in ('$overall')";
				$dres = $db_object->get_a_line($dqry);

				$value = @array_sum($Ratingoverres);
				$avgovercount = count($Ratingoverres);
				$overallavg = @($value/$avgovercount);
				$overallavg = @sprintf("%01.2f",$overallavg);
				$from_array[] = $dres[min];
				$to_array[] = $dres[max];
			}
				
			if($overallavg!="")
			{
					$val['average'] = $overallavg;
					$val['over'] = $err['cOverallavg'];
			}
			else
			{
					$temp2  = preg_replace($pattern5,$space,$temp2);
					$temp2_text  = preg_replace($pattern5,$space,$temp2_text);
			}
			for($s=0;$s<count($ratid);$s++)
			{
				
				if($ratid[$s]==$user_id)
				{
					$status= "status='2'";
				}
				else
				{
					$status= "status='0'";
				}
		
					$Ratingval = "select r_id from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and $status and raters_id ='$ratid[$s]' order by fd_id";
					$Ratingres = $db_object->get_single_column($Ratingval);
					
					$getdt = "select (date_format(approved_date,'%Y-%m-%d')) as min from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and $status and raters_id ='$ratid[$s]' order by fd_id";				
					$getdtres = $db_object->get_single_column($getdt);
					$date_get[] = $getdtres;

					$datqry = "select min(date_format(approved_date,'%Y-%m-%d')) as min ,max(date_format(approved_date,'%Y-%m-%d')) as max 
						from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and $status and raters_id ='$ratid[$s]'";
					$dtres = $db_object->get_a_line($datqry);

					$avgcount = count($Ratingres);
					$avgvalue = @array_sum($Ratingres);					
					$avg[] = @($avgvalue/$avgcount);
					$rid[] = $ratid[$s];

					$from_array[] = $dtres[min];
					$to_array[] = $dtres[max];
					$cal_array[] = $Ratingres;
					
			}



				$fromarray = @min($from_array);
				$toarray = @max($to_array);
//to display graph		
				$from_date1="";
				$to_date1="";
				$from_date1 = $fromarray;
				$to_date1  = $toarray;
				$ctdate = count($ratid);
				$pattern_viewrating = "/<{viewratinggraph_start}>(.*?)<{viewratinggraph_end}>/s";
				if(($ctdate!=0)||($$fOverall1!=""))
				{

				}
				else
				{
					$temp2 = preg_replace($pattern_viewrating,"",$temp2);
				}
							
			$rname_array = array();	
			$rname_array1 = array();
			$rname_array2 = array();


			for($r=0;$r<count($avg);$r++)
			{
				$ratingvalue = $avg[$r];
			
				$rnameqry = "select username from $user_table where user_id='$rid[$r]'";
				$rnameres = $db_object->get_a_line($rnameqry);
				$ratersname = $rnameres['username'];
				$ratingvalue = $avg[$r];

				$rtr_val = $cal_array[$r];
				$rt_val .= @join(",",$rtr_val);
				$rt_val = $rt_val.":";
				$dat_val = $date_get[$r];
				$da_val .= @join(",",$dat_val);
				$da_val .= ":";
				$rname_array[] = $ratersname;
				$rval_array[] = @sprintf("%01.2f",$ratingvalue);		
				$str4.=preg_replace("/<{(.*?)}>/e","$$1",$match4);
				$str4_text.=preg_replace("/<{(.*?)}>/e","$$1",$match4_text);

			}				
				$rt_val = substr($rt_val,0,-1);	
				$da_val = substr($da_val,0,-1);
				$rname = @join(",",$rname_array);
				$rval = $rt_val;
				$rvalavg = @join(",",$rval_array);
				$temp2 = preg_replace($pattern4,$str4,$temp2);
				$temp2_text = preg_replace($pattern4,$str4_text,$temp2_text);

		//View ratings for this objective by ends


		//Compare Rating1 between starts
			$ratid1  =array();
			$rid1  = array();
			$avg1 = array();
			$allrater1 = array();	
		//rater1_array - all raters id,except self and boss id will be there 
		//raters rating

		for($x=0;$x<count($rater2_array);$x++)
		{			
			$key = $rater2_array[$x];
			if($$key!="")
			{
				list($name,$rtr_id1,$rsl_id) = split("_",$key);
				$cRaterck1 = "fRater2_".$rtr_id1."_".$sl_id;
				if($$cRaterck1!="")
				{
					$ratid1[] = $rtr_id1;
				}
			}
		}

		//boss2 rating
			$fBoss2 = "fBoss2_".$bossid."_".$sl_id;			
			if($$fBoss2!="")
			{
				$ratid1[] = $bossid;
			}
		//self rating
			$fSelf2 = "fSelf2_".$sl_id;

			if($$fSelf2!="")
			{
				$ratid1[]=$user_id;			
			}
			
		//Over all
			$allrater1[] = $user_id;
			$allrater1[] = $bossid;
			$overall1 = implode("','",$allrater1);
			$fOverall2 = "fOverall2_".$sl_id;
	
			if($$fOverall2!="")
			{
				$Ratingoverval1 = "select r_id from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and (status='0' or status='2') and raters_id in ('$overall1')";
				$Ratingoverres1 = $db_object->get_single_column($Ratingoverval1);
				$value1 = @array_sum($Ratingoverres1);
				$avgovercount1 = count($Ratingoverres1);
				$overallavg1 = @($value1/$avgovercount1);				
				
			}
				
			if($overallavg1!="")
			{
				$val['average1'] = $overallavg1;
				$val['over1'] = $err['cOverallavg'];
				$over_all1 = $overallavg1;
				$overflag=1;
			}
			else
			{
				$temp2  = preg_replace($pattern7,$space,$temp2);
				$temp2_text  = preg_replace($pattern7,$space,$temp2_text);
			}

			for($y=0;$y<count($ratid1);$y++)
			{
				
				if($ratid1[$y]==$user_id)
				{
					$status= "status='2'";
				}
				else
				{
					$status= "status='0'";
				}
		
					$Ratingval1 = "select r_id from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and $status and raters_id ='$ratid1[$y]'";
					
					$Ratingres1 = $db_object->get_single_column($Ratingval1);
					$avgcount1 = count($Ratingres1);

					$getdt1 = "select (date_format(approved_date,'%Y-%m-%d')) as min from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and $status and raters_id ='$ratid1[$y]' order by fd_id";				
					$getdtres1 = $db_object->get_single_column($getdt1);
					$date_get1[] = $getdtres1;

					$datqry1 = "select min(date_format(approved_date,'%Y-%m-%d')) as min ,max(date_format(approved_date,'%Y-%m-%d')) as max 
						from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and $status and raters_id ='$ratid1[$y]'";
					$dtres1 = $db_object->get_a_line($datqry1);

					$avgvalue1 = @array_sum($Ratingres1);					
					$avg1[] = @($avgvalue1/$avgcount1);
					$rid1[] = $ratid1[$y];

					$from_array1[] = $dtres1[min];
					$to_array1[] = $dtres1[max];
					$cal_array1[] = $Ratingres1;
			}

			$merge_val = array();
			$merge_cal = array();
			$merge_date =array();


			for($z=0;$z<count($avg1);$z++)
			{
				$ratingvalue1 = $avg1[$z];
				$rnameqry1 = "select username from $user_table where user_id='$rid1[$z]'";
				$rnameres1 = $db_object->get_a_line($rnameqry1);
				$ratersname1 = $rnameres1['username'];
				$ratingvalue1 = $avg1[$z];
			
				$rtr_val1 = $cal_array1[$z];
				$merge_cal[$ratersname1] = $rtr_val1;
				$rt_val1 .= @join(",",$rtr_val1);
				$rt_val1 = $rt_val1.":";
				$dat_val1 = $date_get1[$z];
				$merge_date[$ratersname1] = $dat_val1;
				$da_val1 .= @join(",",$dat_val1);
				$da_val1 .= ":";
				$rname_array1[] = $ratersname1;
				$rval_array1[] = $ratingvalue1;
				$merge_val[$ratersname1] = $ratingvalue1;
				$str6.=preg_replace("/<{(.*?)}>/e","$$1",$match6);
				$str6_text.=preg_replace("/<{(.*?)}>/e","$$1",$match6_text);
			}
				$rt_val1 = substr($rt_val1,0,-1);	
				$da_val1 = substr($da_val1,0,-1);
												
				$rname1 = @join(",",$rname_array1);
				$rval1 = $rt_val1;
				$rvalavg1 = @join(",",$rval_array1);
				$temp2 = preg_replace($pattern6,$str6,$temp2);
				$temp2_text = preg_replace($pattern6,$str6_text,$temp2_text);
			
	//Compare Rating1 between ends


	//Compare Rating2 between starts
			$ratid2  =array();
			$rid2  = array();
			$avg2 = array();
		//rater2_array - all raters id,except self and boss id will be there 
		//raters rating

		for($a=0;$a<count($rater3_array);$a++)
		{
			
			$key = $rater3_array[$a];
			if($$key!="")
			{
				list($name,$rtr_id2,$rsl_id) = split("_",$key);
				$cRaterck2 = "fRater3_".$rtr_id2."_".$sl_id;
				if($$cRaterck2!="")
				{
					$ratid2[] = $rtr_id2;
				}
			}
		}

		//boss2 rating
			$fBoss3 = "fBoss3_".$bossid."_".$sl_id;			
			if($$fBoss3!="")
			{
				$ratid2[] = $bossid;
			}
		//self rating
			$fSelf3 = "fSelf3_".$sl_id;

			if($$fSelf3!="")
			{
				$ratid2[]=$user_id;			
			}
			
		//Over all
			$allrater2[] = $user_id;
			$allrater2[] = $bossid;
			$overall2 = implode("','",$allrater2);
			$fOverall3 = "fOverall3_".$sl_id;
						
			if($$fOverall3!="")
			{
				$Ratingoverval2 = "select r_id from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and (status='0' or status='2') and raters_id in ('$overall2')";
				$Ratingoverres2 = $db_object->get_single_column($Ratingoverval2);
				$value2 = @array_sum($Ratingoverres2);
				$avgovercount2 = count($Ratingoverres2);
				$overallavg2 = @($value2/$avgovercount2);
			}				
			if($overallavg2!="")
			{
				$val['average2'] = $overallavg2;
				$val['over2'] = $err['cOverallavg'];
				$over_all1 = $overallavg2;
				$overflag=1;
			}
			else
			{
				$temp2  = preg_replace($pattern9,$space,$temp2);
				$temp2_text  = preg_replace($pattern9,$space,$temp2_text);
			}
			
			
			for($b=0;$b<count($ratid2);$b++)
			{
				if($ratid2[$b]==$user_id)
				{
					$status= "status='2'";
				}
				else
				{
					$status= "status='0'";
				}
		
					$Ratingval2 = "select r_id from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and $status and raters_id ='$ratid2[$b]'";					
					$Ratingres2 = $db_object->get_single_column($Ratingval2);
					$avgcount2 = count($Ratingres2);

					$getdt2 = "select (date_format(approved_date,'%Y-%m-%d')) as min from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and $status and raters_id ='$ratid2[$b]' order by fd_id";				
					$getdtres2 = $db_object->get_single_column($getdt2);
					$date_get2[] = $getdtres2;

					$datqry2 = "select min(date_format(approved_date,'%Y-%m-%d')) as min ,max(date_format(approved_date,'%Y-%m-%d')) as max 
						from $approved_feedback where o_id in ('$oid')
						 and user_id='$user_id' and $status and raters_id ='$ratid2[$b]'";
					$dtres2 = $db_object->get_a_line($datqry2);

					$avgvalue2 = @array_sum($Ratingres2);					
					$avg2[] = @($avgvalue2/$avgcount2);
					$rid2[] = $ratid2[$b];

					$from_array2[] = $dtres2[min];
					$to_array2[] = $dtres2[max];
					$cal_array2[] = $Ratingres2;
			}	
				
////to display graph
				$from_date2="";
				$to_date2="";
				$from_merge = @array_merge($from_array1,$from_array2);
				$to_merge = @array_merge($to_array1,$to_array2);
				$fromarray1 = @min($from_merge);
				$toarray1 = @max($to_merge);

			for($d=0;$d<count($avg2);$d++)
			{
				$ratingvalue2 = $avg2[$d];
				$rnameqry2 = "select username from $user_table where user_id='$rid2[$d]'";
				$rnameres2 = $db_object->get_a_line($rnameqry2);
				$ratersname2 = $rnameres2['username'];
				$ratingvalue2 = $avg2[$d];
				$rtr_val2 = $cal_array2[$d];
				$merge_cal[$ratersname2] = $rtr_val2;
				$rt_val2 .= @join(",",$rtr_val2);
				$rt_val2 = $rt_val2.":";
				$dat_val2 = $date_get2[$d];
				$merge_date[$ratersname2] = $dat_val2;
				$da_val2 .= @join(",",$dat_val2);
				$da_val2 .= ":";
				$rname_array2[] = $ratersname2;
				$rval_array2[] = $ratingvalue2;	
				$merge_val[$ratersname2]  = $ratingvalue2;
				$str8.=preg_replace("/<{(.*?)}>/e","$$1",$match8);
				$str8_text.=preg_replace("/<{(.*?)}>/e","$$1",$match8_text);
			}


				$rt_val2 = substr($rt_val2,0,-1);	
				$da_val2 = substr($da_val2,0,-1);
				$rname2 = @join(",",$rname_array2);
				$rval2 = $rt_val2;
				$rname_merge   = @array_merge($rname_array1,$rname_array2);

				$rnamemerge = @array_unique($rname_merge);
				$rname_mg = @array_values($rnamemerge);
				$mg_val="";
				$jn_cal="";
				$date_mg="";
				for($na=0;$na<count($rname_mg);$na++)
				{
					$nname = $rname_mg[$na]	;
					$mg_val .= @sprintf("%01.2f",$merge_val[$nname]);					
					$mg_val .=",";
					$mg_cal =$merge_cal[$nname];
					$jn_cal .= @join(",",$mg_cal);
					$jn_cal .= ":";
					$mg_date = $merge_date[$nname];
					$date_mg .= @join(",",$mg_date);
					$date_mg .=":";					
				}//na loop
				$jn_cal = substr($jn_cal,0,-1);
				$mg_val = substr($mg_val,0,-1);
				$date_mg = substr($date_mg,0,-1);	
				$name_r = @join(",",$rname_mg);
				$val_mg = $mg_val;
				$rvalavg2 = @join(",",$rval_array2);

				$pattern_show = "/<{viewcomparegraph_start}>(.*?)<{viewcomparegraph_end}>/s";
				if(($name_r=="")&&($overflag!=1))
				{
					$temp2 = preg_replace($pattern_show,"",$temp2);
				}
				$temp3 = preg_replace($pattern8,$str8,$temp2);
				$temp3_text = preg_replace($pattern8,$str8_text,$temp2_text);
		//Compare Rating2 between ends
				

				$graph = "fGraph_".$sl_id;
				$pattern_viewresult = "/<viewresultgraph_start}>(.*?)<viewresultgraph_end}>/s";
				if($$graph=="")
				{
					
						$pattern_gp = "/<{graph_loopstart}>(.*?)<{graph_loopend}>/s";
						$temp3 = preg_replace($pattern_gp,"",$temp3);					
				}
				else
				{

					if(($resflag!=1)&&($resflag1!=1)&&($resflag2!=1)&&($resflag3!=1))
					{
						$temp3 = preg_replace($pattern_viewresult,"",$temp3);

					}
					$pattern_qr = "/<{queryresult_loopstart}>(.*?)<{queryresult_loopend}>/s";
					$temp3 = preg_replace($pattern_qr,"",$temp3);
				}
		}//end next if
		
	//main replace
		$str.=preg_replace("/<{(.*?)}>/e","$$1",$temp3);
		$str_text.=preg_replace("/<{(.*?)}>/e","$$1",$temp3_text);
		}//i loop ends
	//main loop end 

		$file = preg_replace($pattern,$str,$file);
		
		$pattern_self = "/<!--ifself_loopstart-->(.*?)<!--ifself_loopend-->/s";
		if($uid=="")
		{
			$file = preg_replace($pattern_self,"",$file);
		}

		$file_text = preg_replace($pattern,$str_text,$file_text);
		$mailqry = "select email from $user_table where user_id='$user_id'";
		$mailres = $db_object->get_a_line($mailqry);
		$val['email'] = $mailres['email'];

		$file = $common->direct_replace($db_object,$file,$val);
		$file_text = preg_replace("/<!--(.*?)-->/e","",$file_text);
		$val['today'] = date("Y/m/d");
		$file_text = $common->direct_replace($db_object,$file_text,$val);

		echo $file;
		
		fwrite($fp,$file_text); 
		fclose ($fp);	
		
		
	}//end view

}//end class
	$ob = new summary;

//	print_r($post_var);


	$ob->view_form($db_object,$common,$default,$user_id,$gbl_met_value,$post_var,$gbl_tech_skill,$gbl_inter_skill,$error_msg,$gbl_date_format);
include_once("footer.php");
?>


