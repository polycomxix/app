<?php
	//header('Content-type: application/json');
	$root = "/var/www/html/app";
	require_once($root."/php/connect.php");
	/*---------------DEFINE---------------*/
	const MAX_NO_CHECKBOX = 8;
	const MAX_NO_CHOICE = 12;
	const MAX_NO_ACT = 6;
	const NO_QUESTION_PART2 = 10;
	const NO_QUESTION_PART3 = 26;

	/*-------------END DEFINE-------------*/



	$response_array=[];
	$response_array['status'] = 'success';

	

	if(isset($_POST["param"]))
	{
		$data = json_decode($_POST["param"]);
		//echo $data->{"txt_country"};
		//print_r($data);


		if($data->{"condition"}==1) //new survey record
		{
			$pid = null;
			//Check new user or current user
			$conn = CreateConnection();
			mysqli_query($conn,"START TRANSACTION");
			try{

				if(!isset($data->{"pid"}))//create new user
				{
					$gender = $data->{'txt_gender'};
					$birthday = $data->{'txt_birthday'};
					$occupation = $data->{'txt_occup'};
					$country = $data->{'txt_country'};
					$usecom = $data->{'txt_usecom'};
					if($occupation == "other")
						$occupation = $data->{'txt_other_occup'};

					$sql = 	"INSERT INTO tb_user (gender, birthday, occupation, country, usecom, agreement) ".
							"VALUES ('$gender','$birthday','$occupation','$country','$usecom','1')";
					//echo ($sql);
					if (mysqli_query($conn, $sql)) {
					    $pid = mysqli_insert_id($conn);
					    //echo "Insert ID:".$pid."\r\n";
					   
					} else {
					    //echo "Error: " . $sql . "<br>" . mysqli_error($conn);
					    //echo mysqli_error($conn)."\r\n";
					    $response_array['status'] = 'fail';
					}
				}
				else
				{
					$pid = $data->{"pid"};
				}
				

				for($i=1; $i<=NO_QUESTION_PART2; $i++)
				{
					$question = "q".$i;

						if($i==2 || $i==6) //checkbox
						{	
							for($j=1; $j<=MAX_NO_CHECKBOX; $j++)
							{
								if(isset($data->{"txt_b_q".$i."_".$j}))
								{
									//echo $question."-".$data->{"txt_b_q".$i."_".$j}."\r\n";
									$choice = $data->{"txt_b_q".$i."_".$j};
									if($choice=="other")
										$choice = $data->{"txt_other_bq".$i};
									if(!Insert_Usage($pid, $question, $choice, null, null, $conn))
									{
										$response_array['status'] = 'fail';
										break;
									}
								}
							}
						}
						else if($i>6 && $i<=10) //with rate
						{
							//likert_b_q7_1
							for($j=1; $j<=MAX_NO_CHOICE; $j++)
							{
								if(isset($data->{"txt_b_q".$i."_".$j}))
								{
									$answer = $data->{"txt_b_q".$i."_".$j};
									$rate = $data->{"likert_b_q".$i."_".$j};
									$act = null;
									if($i==9 && ($j==1 || $j==2))//Facebook & Twitter
									{
										for($k=1; $k<= MAX_NO_ACT; $k++)
										{
											//act_b_q9_1_1
											if(isset($data->{"act_b_q".$i."_".$j."_".$k}))
											{
												$act .= $data->{"act_b_q".$i."_".$j."_".$k}."-";
												//echo $question."-".$answer."-".$rate."-".$act."\r\n";
												
											}
										}
									}
									else
									{
										//echo $question."-".$answer."-".$rate."\r\n";
									}
									if($answer=="other")
										$answer = $data->{"txt_other_bq".$i};

									if(!Insert_Usage($pid, $question, $answer, $rate, $act, $conn))
									{
										$response_array['status'] = 'fail';
										break;
									}

								}
							}
							//echo $question."-".$data->{"txt_b_q".$i}."\r\n";
						}
						else //normal
						{
							if(isset($data->{"txt_b_q".$i}))
							{
								//echo $question."-".$data->{"txt_b_q".$i}."\r\n";

								$choice = $data->{"txt_b_q".$i};
								if(!Insert_Usage($pid, $question, $choice, null, null, $conn))
								{
									$response_array['status'] = 'fail';
									break;
								}
							}
						}
						//echo $data->{"txt_b_q".$i}."\r\n";
						
				}
				mysqli_query($conn,"COMMIT");
				$response_array['pid'] = $pid;
			}catch (Exception $e)
			{
				mysqli_query($conn,"ROLLBACK");
			}	
			CloseConnection($conn);
		}
		else if ($data->{"condition"}==2 && isset($data->{"pid"})) {
			$pid=$data->{"pid"};
			//print_r($data);
			$conn = CreateConnection();
			mysqli_query($conn,"START TRANSACTION");
			$point = 0;
			try{
				for($i=1; $i<=NO_QUESTION_PART3; $i++)
				{
					$question = "q".$i;
					if(isset($data->{"likert_c_q".$i}))
					{
						//echo $question."-".$data->{"likert_c_q".$i}."\r\n";
						$rate = $data->{"likert_c_q".$i};
						if(!Insert_Addiction($pid, $question, $rate, $conn))
						{
							$response_array['status'] = 'fail';
							break;
						}
						if($i>=7)
						{

							switch ($rate) {
								case 'always':
									$point+= 5;
									break;
								case 'often':
									$point+= 4;
									break;
								case 'frequently':
									$point+= 3;
									break;
								case 'occasionally':
									$point+= 2;
									break;
								case 'rarely':
									$point+= 1;
									break;
								default:
									$point += 0;
									break;
							}
						}
						$response_array['total'] = $point;
					}
				}
				mysqli_query($conn,"COMMIT");
				$response_array['pid'] = $pid;
			}catch (Exception $e)
			{
				mysqli_query($conn,"ROLLBACK");
			}
			CloseConnection($conn);
		}
	}
	else
	{
		$response_array['status'] = 'fail';
	}

	header('Content-Type: application/json');
	echo json_encode($response_array);

	function Insert_Usage($id, $q, $c, $r, $a, $con)
	{
		$sql = 	"INSERT INTO tb_survey_usage (pid, question, choice, rate, activity) ".
				"VALUES ('$id','$q','$c','$r','$a')";

		if (!mysqli_query($con, $sql)) {
			return mysqli_error($con)."\r\n";
		}
		return true;
	}
	function Insert_Addiction($id, $q, $r, $con)
	{
		$sql = 	"INSERT INTO tb_survey_addiction (pid, question, rate) ".
				"VALUES ('$id','$q', '$r')";
		//echo $sql."\r\n";

		if (!mysqli_query($con, $sql)) {
			return mysqli_error($con)."\r\n";
		}
		return true;
	}
?>