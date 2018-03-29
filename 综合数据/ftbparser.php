<?php
	date_default_timezone_set('Asia/Shanghai');	
 	ini_set("allow_url_fopen", true);

	$app_id = array( 10=>"Colleges and Universities",
					 1619=>"Acting Schools",3124=>"Architecture Schools",1396=>"Dental Schools",
	 				 2264=>"Education Programs",618=>"Graduate Business Schools",419=>"Law Schools",
	 				 728=>"Medical Schools",2691=>"Music Schools",1919=>"Nursing Schools",
	 				 3547=>"Optometry Schools",2460=>"Pharmacy Schools",2490=>"Physical Therapy Schools",
	 				 2240=>"Psychology Programs",2533=>"Public Health Schools",2360=>"Veterinary Schools",
	 				);


	$mysqli = new mysqli('localhost', 'root', '1028', 'fu');
	if ($mysqli->connect_error) {
	    die('Connect Error (' . $mysqli->connect_errno . ') '  . $mysqli->connect_error);
	}
	$mysqli->query("SET NAMES 'utf8'");
	$mysqli->autocommit(TRUE);

	parseHtml();

	$mysqli->close();


	function parseHtml()
	{
		include_once('simple_html_dom.php');
		global $mysqli,$app_id;
		//建表
		cleanup_database("univ");
		createTableSQL();

		foreach ($app_id as $aid => $names) {
			//generate db name
			$dbname = getFilename($names);
			//variables
			//common
			$univ_logo	   = "";
			$univ_chs_name = "";
			$univ_eng_name = "";
			$univ_type     = "";
			$univ_rating = 0;
			$univ_character = array();
			$univ_under_fees 	=  "";
			$univ_grad_fees  	=  "";
			$univ_under_cost	=  "";
			$univ_grad_cost 	=  "";
			/*tab1*/
			$univ_about   		= "";
			$univ_calendar 		= "";
			$univ_scale			= "";
			$univ_buildtime     = "";
			$univ_schools		= array();
			//rank
			$univ_country_rank	= "";
			$univ_shanghairank  = "";
			$univ_qsrank		= "";
			$univ_therank		= "";
			//facts
			$univ_stu_count     = "";
			$univ_u_vs_g 		= "";
			$univ_internation	= "";
			//contacts
			$univ_country 		= "";
			$univ_region        = "";
			$univ_address       = "";
			$univ_zipcode		= "";
			$univ_tel  			= "";
			$univ_fax			= "";
			$univ_website       = "";
			$univ_email			= "";

			//tab2
			$univ_enroll_count 	= 0;
			$univ_apply_count  	= 0;
			$univ_accept_count	= 0;
			$univ_accept_rate   = 0;

			//Schoo Type
			$school_type 		= "";
			$school_name 	   	= "";
			$school_ba		   	= "";
			$school_years		= "";
			$school_accept_rate = "";
			$school_fees		= "";
			$school_salary		= "";
			$school_apply_url	= "";
			$school_url 		= "";



			//apply flow
			$apply_flow  		= "";


			//get file list
			$sql = <<<EOD
select ID,_full_title,logo,thumb from {$dbname};
EOD;
			if ($result = $mysqli->query($sql)) {
				while ($obj = $result->fetch_object()) {
					$sid = $obj->ID;
					$fullname = $obj->_full_title;
					$dblogo	  = $obj->logo;
					$dbthumb  = $obj->thumb;

					$filename = "./ftbhtml/{$dbname}/html/{$sid}_{$fullname}.html";
					echo $filename . "\n";
					$html = file_get_html($filename);
					//top over view
					$overview_features = $html->find('ul#overview-features',0);
					$lis = $overview_features->find('li');
					$t_under_count = getLastText($lis[0]->innertext);
					$t_total_count = getLastText($lis[1]->innertext);
					
					$t_accept_rate_total = getLastText($lis[2]->innertext);
					$t_apply_url   = $lis[3]->find('a',0)->href;
					//rating
					$t_rating      = $html->find('#detail-user-rating .ur-avg',0)->plaintext;

					//big name
					$h_detail_info = $html->find('#detail-info .fn',0);
					$t_engname 	   = $h_detail_info->innertext;
					
					//fast facts
					$fast_facts  =   $html->find('#detail-sections div[data-section-id=0] table');
					$h_facts	 =   $fast_facts[0];
					$h_ranking 	 =   $fast_facts[1];
							//characteric
					$t_char 	 = array();
					// $t_total_stu = "";
					foreach ($h_facts->find('.md-list li') as $lidx => $liele) {
						$t_fac = $liele->plaintext;
						$t_c   = explode(":", $t_fac);
						$t_char[trim($t_c[0])] = trim($t_c[1]);
						// if ($lidx == 0) {
						// 	$tmp = explode(" ", trim($t_c[1]));
						// 	$t_total_stu = $tmp[0] ;
						// }
					}
					$t_ranking = array();
					foreach ($h_ranking->find('.md-list li') as  $liele) {
						$t_rank = explode(":",$liele->plaintext);
						if (count($t_rank) == 2) {
							$t_ranking[$t_rank[0]] = $t_rank[1];	
						}
					}

					//overview
					//middle overview
					$h_detail_sections = $html->find('#detail-sections div[data-section-id="1"]',0);
					
					$h_right_sec   	= 	$h_detail_sections->find('.full-split-right',0);
					$h_bleft_sec  	= 	$h_detail_sections->find('.full-split-left',1);
					$t_website   	= 	$h_right_sec->find('.fieldediturl .fdata a',0)->href;
					$t_location 	= 	$h_right_sec->find('.address .fdata',0)->plaintext;
					$trs = $h_right_sec->find('tr');
					$t_region		=   getParentText($trs[2]->find('.fdata',0)->innertext);
					$t_tel			=   $h_right_sec->find('.fieldeditphone .fdata',0)->plaintext;

					$trs = $h_bleft_sec->find('tr');
					$_basic_ = 1;
					$tmp 		=  trim($trs[1]->find('.fname',0)->plaintext);
					$t_group  = "";
					if ($tmp == "Institution Group") {
						$t_group = getParentText($trs[1]->find('.fdata',0)->plaintext);
						$_basic_ = 2;
					}
					$t_category		=   getParentText($trs[$_basic_]->find('.fdata .has-help-text',0)->innertext);
					$t_type			=   getParentText($trs[$_basic_ + 1]->find('.fdata',0)->innertext);
					$t_calendar		=	mysql_str($trs[$_basic_ + 2]->find('.fdata',0)->plaintext);
					//use * as delimiter
					$t_religion = "";
					if ($_basic_ == 1) {
						$t_religion		=   mysql_str($trs[4]->find('.fdata',0)->plaintext);	
					}
					$t_locale_type	=   mysql_str($trs[5]->find('.fdata',0)->plaintext);
					$t_size			=	mysql_str($trs[6]->find('.fdata',0)->plaintext);
					$t_enroll_profile = mysql_str($trs[7]->find('.fdata',0)->plaintext);

					// if (isset($trs[8])) {
					// 	$t_application	=	mysql_str($trs[8]->find('.fdata',0)->plaintext);
					// 	// get application url
					// }

					$h_bright_sec	=  	$h_detail_sections->find('.full-split-right',1);
					$t_avail_degree	=	mysql_str($h_bright_sec->find('.md-list',0)->plaintext);

					$t_degrees = array('brief'  =>$t_enroll_profile,
									   'degree' =>$t_avail_degree);
					/*Admission Info*/
					$h_application	= $html->find('#detail-sections div[data-section-id=2] div[data-tab-id="id-4"]' ,0);
					$h_breakdown	= $h_application->find('table.ftb-table',0);
					$t_apply_count  = generateApplicationArray($h_breakdown->find('tr[row_id="1"]',0));
					$t_apply_rate	= generateApplicationArray($h_breakdown->find('tr[row_id="2"]',0));
					$t_accept_count	= generateApplicationArray($h_breakdown->find('tr[row_id="3"]',0));
					$t_total_enroll = generateApplicationArray($h_breakdown->find('tr[row_id="5"]',0));


					/*Tuition And Aid*/
					$h_cost 		= $html->find('#detail-sections div[data-section-id="4"] div[data-tab-id="id-6"]',0);
					$t_under_apply_fee = $h_cost->find('table div[data-field="applfeeu"]',0)->getAttribute('data-val');
					$t_under_instate = $h_cost->find('table div[data-field="chg2at3"]',0)->getAttribute('data-val');
					$t_under_outstate = $h_cost->find('table div[data-field="chg3at3"]',0)->getAttribute('data-val');
					$t_under_cost = array('instate'=>trim($t_under_instate),
										  'outstate'=>trim($t_under_outstate));				
					//Graduate
					$h_grad_cost	= $html->find('#detail-sections div[data-section-id="7"] div[data-tab-id="id-17"]',0);
					$t_graduate_apply_fee = $h_grad_cost->find('div[data-field="applfeeg"]',0)->getAttribute('data-val');
					$t_graduate_instate   = $h_grad_cost->find('div[data-field="tuition6"]',0)->getAttribute('data-val');
					$t_graduate_outstate  = $h_grad_cost->find('div[data-field="tuition6"]',0)->getAttribute('data-val');
					$t_grad_cost = array('instate'=>trim($t_graduate_instate),
										 'outstate'=>trim($t_graduate_outstate));
					//schools
					$h_grad_school		= $html->find('#detail-sections div[data-section-id="7"] .tab-nav');
					$h_grad_school_url 	= $html->find('#detail-sections div[data-section-id="7"] .purchase-button');
					$t_school_detail = array();
					foreach ($h_grad_school_url as $idx => $ele) {
						$tt_shool_name = trim($h_grad_school[$idx+1]->plaintext);
						$tt_school_url = trim($ele->href); 
						$t_school_detail[$tt_shool_name] = $tt_school_url;
					}

					$h_students 	= $html->find('#detail-sections div[data-section-id="5"] .detail-tab');
					$t_ethnicity	= $h_students[1]->find('table.ftb-table',0)->outertext;
					$h_international = $h_students[1]->find('table.ftb-table',0)->find('tr[row_id="8"] td');
					$t_international = $h_international[1]->plaintext;

					$h_gender		= $h_students[2]->find('table.ftb-table tr');
					$male 			= $h_gender[1]->find('td');
					$female 		= $h_gender[2]->find('td');
					$t_gender 		= array('male'=>trim($male[1]->plaintext),
											'female'=>trim($female[1]->plaintext));

					$univ_logo	   		= $dblogo;
					$univ_chs_name 		= "";
					$univ_eng_name 		= mysql_str($t_engname);
					$univ_type     		= mysql_str($t_group . "*" . $t_type . "*" .  $t_category . "*" . $t_religion);
					$univ_rating   		= $t_rating;
					$univ_character 	= mysql_str($t_char);
					$univ_under_fees 	= mysql_str($t_under_apply_fee);
					$univ_grad_fees  	= mysql_str($t_graduate_apply_fee);
					$univ_under_cost	= mysql_str($t_under_cost);
					$univ_grad_cost 	= mysql_str($t_grad_cost);
					/*tab1*/
					$univ_about   		= "";
					$univ_calendar 		= $t_calendar;
					$univ_scale			= $t_locale_type . "*" . $t_size;
					$univ_buildtime     = "";
					$univ_schools		= mysql_str($t_school_detail);
					$univ_degree		= mysql_str($t_degrees);
					//rank
					$univ_ranking 		= mysql_str($t_ranking);
					$univ_country_rank	= "";
					$univ_shanghairank  = "";
					$univ_qsrank		= "";
					$univ_therank		= "";
					//facts
					$univ_stu_count     = $t_total_count;
					$univ_u_vs_g 		= $t_under_count / ($t_total_count - $t_under_count);
					$univ_internation	= mysql_str($t_international);
					$univ_ethnicity		= mysql_str($t_ethnicity);
					$univ_gender		= mysql_str($t_gender);
					//contacts
					$univ_country 		= "United States";
					$univ_region        = $t_region;
					$univ_address       = $t_location;
					$univ_zipcode		= "";
					$univ_tel  			= $t_tel;
					$univ_fax			= "";
					$univ_website       = $t_website;
					$univ_email			= "";

					//tab2
					$univ_enroll_count 	= mysql_str($t_total_enroll);
					$univ_apply_count 	= mysql_str($t_apply_count);
					$univ_accept_count	= mysql_str($t_accept_count);
					$univ_accept_rate_total = $t_accept_rate_total;
					$univ_accept_rate  	= mysql_str($t_apply_rate);
					$univ_apply_url		= $t_apply_url;


					$sql = <<<EOD
INSERT INTO univ (id,year,univ_chs_name,univ_eng_name,univ_type,univ_rating,user_rating,univ_apply_url,univ_under_fees,univ_grad_fees,univ_under_cost,univ_grad_cost,univ_degree,univ_logo,univ_thumb,univ_topimg,univ_calendar,univ_scale,univ_buildtime,univ_schools,univ_country_rank,univ_shanghairank,univ_qsrank,univ_therank,univ_stu_count,univ_gender,univ_u_vs_g,univ_internation,univ_country,univ_region,univ_address,univ_zipcode,univ_tel,univ_fax,univ_website,univ_email,univ_enroll_count,univ_apply_count,univ_accept_count,univ_accept_rate_total,univ_accept_rate,univ_character,univ_about,apply_flow,univ_ethnicity,created)
Values (null,2014,"{$univ_chs_name}","{$univ_eng_name}","{$univ_type}","{$univ_rating}","0","{$univ_apply_url}","{$univ_under_fees}","{$univ_grad_fees}","{$univ_under_cost}","{$univ_grad_cost}","{$univ_degree}","{$univ_logo}","","","{$univ_calendar}","{$univ_scale}","{$univ_buildtime}","{$univ_schools}","{$univ_country_rank}","{$univ_shanghairank}","{$univ_qsrank}","{$univ_therank}","{$univ_stu_count}","{$univ_gender}","{$univ_u_vs_g}","{$univ_internation}","{$univ_country}","{$univ_region}","{$univ_address}","{$univ_zipcode}","{$univ_tel}","{$univ_fax}","{$univ_website}","{$univ_email}","{$univ_enroll_count}","{$univ_apply_count}","{$univ_accept_count}","{$univ_accept_rate_total}","{$univ_accept_rate}","{$univ_character}","{$univ_about}","{$apply_flow}","{$univ_ethnicity}",now());
EOD;
			 		if ($mysqli->query($sql)) {
			 			echo "insert {$univ_eng_name} done!\n";
						free_mysqli();
					}else{
						print_r($mysqli->error);
						exit(1);
					}

					$html->clear();
					
				}
			}

			$result->close();

			break;
		}

	}

	
	function cleanup_database($db_name)
	{
		global $mysqli;
		$sql ="DROP TABLE IF EXISTS `fu`.`{$db_name}`;";
		if ($mysqli->multi_query($sql)) {
			free_mysqli();
		}
	}


	function free_mysqli()
	{
		global $mysqli;
		while($mysqli->more_results())
		{
		    $mysqli->next_result();
		    if($res = $mysqli->store_result()) // added closing bracket
		    {
		        $res->free(); 
		    }
		}
	}


	function getFilename($value='')
	{
 		$filename = str_replace(" ", "_", trim($value));
 		$filename = strtolower("ftb_" . $filename);
 		return $filename;
	}

	function generateApplicationArray($value='')
	{
		$arr = $value->find('td');

		return array("men"=>trim($arr[1]->plaintext),
					 "women"=>trim($arr[2]->plaintext),
					 "total"=>trim($arr[3]->plaintext));
	}

	function getLastText($value='')
	{
		$tmp = preg_split("/<\/span>/",$value);
		return mysql_str($tmp[1]);
	}

	function getParentText($value='')
	{
		$tmp = explode("<",$value);
		return mysql_str($tmp[0]);
	}

	function mysql_str($value='')
	{
		if (is_array($value)) {
			$value = serialize($value);
		}
		return mysql_escape_string(trim($value));
	}
	function createTableSQL()
	{
		global $mysqli;
		$sql = <<<EOD
create table IF NOT EXISTS `fu`.`univ`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`year` int(10),

	`univ_chs_name`   varchar(64) comment "common",
	`univ_eng_name`   varchar(128),
	`univ_type` 	  varchar(128),
	`univ_rating`     varchar(16),
	`user_rating`	  varchar(16),

	`univ_apply_url`  varchar(255),
	`univ_under_fees` varchar(128),
	`univ_grad_fees` varchar(128),
	`univ_under_cost` varchar(128),
	`univ_grad_cost` varchar(128),
	`univ_degree`    text,
	`univ_logo` 	  varchar(255) comment "images",
	`univ_thumb`      varchar(255),
	`univ_topimg`	  varchar(255),

	`univ_calendar`   varchar(128) comment "overview",
	`univ_scale` 	  varchar(64),
	`univ_buildtime`  varchar(64),
	`univ_schools` 	  varchar(255),

	`univ_country_rank`  varchar(64) comment "ranks",
	`univ_shanghairank`  varchar(64),
	`univ_qsrank` 		 varchar(64),
	`univ_therank` 		 varchar(64),
	`univ_stu_count`     varchar(255),
	`univ_gender`	     varchar(255),
	`univ_u_vs_g`        varchar(255),
	`univ_internation`   varchar(255),

	`univ_country` 		varchar(255) comment "contact",
	`univ_region`       varchar(255),
	`univ_address` 		varchar(255),
	`univ_zipcode` 		varchar(64),
	`univ_tel` 			varchar(255),
	`univ_fax` 			varchar(255),
	`univ_website` 		varchar(255),
	`univ_email` 		varchar(255),


	`univ_enroll_count` varchar(255) comment "apply",
	`univ_apply_count`  varchar(255),
	`univ_accept_count` varchar(255),
	`univ_accept_rate_total` varchar(255),
	`univ_accept_rate`  varchar(255),
	
	`univ_character`  text comment "common",
	`univ_about` 	  text,
	`apply_flow`      text,
	`univ_ethnicity`	text comment "backup",
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;
		if ($mysqli->query($sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}
		
	}



	function createSchool()
	{
		global $mysqli;
		$sql = <<<EOD
create table IF NOT EXISTS `fu`.`univ_school`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`univ_id` int(11) unsigned,
	`type`  varchar(255),
	`name` varchar(255),
	`ba` varchar(255),
	`years` varchar(255),
	`accept_rate` varchar(255),
	`fees` varchar(255),
	`salary` varchar(255),
	`apply_url` varchar(255),
	`url` varchar(255),
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;
		if ($mysqli->query($sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}
	}
?>