<?php 
	$url = "http://www.sharewithu.com/apps/school/index.php";

//School name	Rank	Location	类别	Full-time Enrollment	GPA	GMAT	TOEFL	GRE	Acceptance rate	起薪 $	就业率	国际学生比例
	include_once('../simple_html_dom.php');

	$html = file_get_html($url);

	$table = $html->find('#t_body',0);
	$index = 0;

	$_school_name_eng 	= '';
	$_school_name_chs 	= '';
	$_rank 	      	= '';
	$_location_city		= '';
	$_location_state	= '';
	$_type 			= '';
	$_full_time_enrollment  = '';
	$_gpa					= '';
	$_gmat					= '';
	$_toefl					= '';
	$_gre 					= '';
	$_acceptance_rate		= '';
	$_money					= '';
	$_job_rate				= '';
	$_international_stu		= '';


	foreach($table->find('tr') as $tr)
	{
		$index ++;
		$tds = $tr->find('td');

		$__name = explode('<br />',nl2br($tds[0]->plaintext));
		echo1($_school_name_eng  = trim($__name[1]));

		echo1($_school_name_chs  = trim($__name[0]));
		echo1($_rank		   = $tds[1]->plaintext);

		$__loc = explode('<br />',nl2br($tds[2]->plaintext));

		echo1($_location_city   = $__loc[0]);
		echo1($_location_state  = trim($__loc[1]));
		echo1($_type		   = $tds[3]->plaintext);
		echo1($_full_time_enrollment	  = $tds[4]->plaintext);
		echo1($_gpa	       = $tds[5]->plaintext);
		echo1($_gmat		   = $tds[6]->plaintext);
		echo1($_toefl		   = $tds[7]->plaintext);
		echo1($_gre          = $tds[8]->plaintext);
		echo1($_acceptance_rate  = $tds[9]->plaintext);
		echo1($_money  = $tds[10]->plaintext);
		echo1($_job_rate  = $tds[11]->plaintext);
		echo1($_international_stu  = $tds[12]->plaintext);
		// foreach ($tds as $td) {
		// 	echo1($td->plaintext . "    ";
		// }
		echo1("\n");
	}

	echo1($index);


	function echo1($value='')
	{
		echo $value . "  ";
	}
 ?>