<?php
 	date_default_timezone_set('Asia/Shanghai');	
	$qs_faculty_url_arr = array("http://www.topuniversities.com/university-rankings/faculty-rankings/arts-and-humanities/2013",
								"http://www.topuniversities.com/university-rankings/faculty-rankings/engineering-and-technology/2013",
								"http://www.topuniversities.com/university-rankings/faculty-rankings/life-sciences-and-medicine/2013",
								"http://www.topuniversities.com/university-rankings/faculty-rankings/natural-sciences/2013",
								"http://www.topuniversities.com/university-rankings/faculty-rankings/social-sciences-and-management/2013",
								);

	//arts-and-humanities engineering-and-technology life-sciences-and-medicine natural-sciences social-sciences-and-management
	$city_ranking_url = "http://www.topuniversities.com/city-rankings/2013";

	$category = array(	"world" => array("world ranking","latin_american_university_rankings","brics_rankings","asian_university_rankings"),
						"Arts & Humanities"=> array("Philosophy","Modern Languages","History","Linguistics","English Language & Literature"),
						"Life Sciences & Medicine"=>array("Medicine","Biological Sciences","Psychology","Pharmacy & Pharmacology","Agriculture & Forestry"),
						"Social Sciences"=>array("Statistics & Operational Research","Sociology","Politics & International Studies","Law","Economics & Econometrics","Accounting & Finance","Communication & Media Studies","Education"),
						"Engineering & Technology"=>array("Computer Science & Information Systems","Chemical Engineering","CiviI & Structural Engineering","Electrical & Electronic Engineering","Mechanical, Aeronautical & Manufacturing Engineering"),
						"Natural Sciences"=>array("Physics & Astronomy","Mathematics","Environmental Sciences","Earth & Marine Sciences","Chemistry","Materials Sciences","Geography")
					);

	
	// $mysqli = new mysqli('localhost', 'root', '1028', 'fu');
	// if ($mysqli->connect_error) {
	//     die('Connect Error (' . $mysqli->connect_errno . ') '  . $mysqli->connect_error);
	// }
	// $mysqli->query("SET NAMES 'utf8'");
	// $mysqli->autocommit(TRUE);
	// cleanup_database();

	// // getFaculty();
	// // getCityRank();
	// $mysqli->close();


	parseTxt();


	function parseTxt()
	{
		global $category;
		foreach ($category as $big => $small) {
			foreach ($small as $cname) {		
				$filename = category2filename($cname);
				$json_data = file_get_contents("./tmptxt/{$filename}");
				$json = json_decode($json_data, true);
				$___i = 0;
				foreach ($json as $key => $value) {
					print_r($value);

					break 3;
				}
			}

		}


	}
	function getFaculty()
	{
		global $mysqli,$qs_faculty_url_arr;
		include_once('../simple_html_dom.php');

		// QS RANK	 	SCHOOL NAME		COUNTRY			QS STARS RATING 	OVERALL	
		// ACADEMIC REPUTATION
		// EMPLOYER REPUTATION
		// CITATIONS PER PAPER
		// H-INDEX CITATIONS

		$create_sql = <<<EOD
create table IF NOT EXISTS `fu`.`qs_faculty`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`type` enum("arts-and-humanities","engineering-and-technology","life-sciences-and-medicine","natural-sciences","social-sciences-and-management"),
	`year` int(10),
	`qs_rank` varchar(16),
	`school_name` varchar(128),
	`country` varchar(64),
	`qs_star` int(10),
	`academic_reputation` decimal(4,2) default 0,
	`employer_reputation` decimal(4,2) default 0,
	`citations_reputation` decimal(4,2) default 0,
	`hindex_reputation` decimal(4,2) default 0,
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;
		if ($mysqli->query($create_sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}

		
		$qs_faculty_type = array("arts-and-humanities","engineering-and-technology","life-sciences-and-medicine","natural-sciences","social-sciences-and-management");

		$_type = "";
		$_year = 2013;
		$_qs_rank = "";
		$_school_name = "";
		$_country = "";
		$_qs_star = 0;
		$_academic_reputation 	= 	0;
		$_employer_reputation 	= 	0;
		$_citations_reputation 	= 	0;
		$_hindex_reputation 	= 	0;
		$_i = 0;
		foreach ($qs_faculty_url_arr as $furl) {
			echo "$furl\n";
			$html = file_get_html($furl);

			$_type = $qs_faculty_type[$_i];

			$table = $html->find('table.views-table',0);
			$_________tmp = 0;
			foreach ($table->find('tr') as $tr) {
				
				if ($_________tmp == 0) {
					$_________tmp += 1;
					continue;
				}
				
				$tds = $tr->find('td');
				
				$_qs_rank = $tds[1]->plaintext;
				$_school_name = $tds[3]->plaintext;
				$_country = $tds[4]->plaintext;

				$___tmp = $tds[5]->find('span');
				if (is_object($___tmp)) {
					$_qs_star = str_replace("qs-stars-", "", $___tmp->getAttribute('class')) ;	
				}else
				{
					$_qs_star = 0;
				}
				
				$_academic_reputation 	= 	score_trim($tds[6]->plaintext);
				$_employer_reputation 	= 	score_trim($tds[7]->plaintext);
				$_citations_reputation 	= 	score_trim($tds[8]->plaintext);
				$_hindex_reputation 	= 	score_trim($tds[9]->plaintext);

				$sql = <<<EOD
				insert into qs_faculty (id,type,year,qs_rank,school_name,country,qs_star,academic_reputation,employer_reputation,citations_reputation,hindex_reputation,created)
				VALUES (NULL,"{$_type}",{$_year},"{$_qs_rank}","{$_school_name}","{$_country}",{$_qs_star},{$_academic_reputation},{$_employer_reputation},{$_citations_reputation},{$_hindex_reputation},now());
EOD;
				
				if ($mysqli->query($sql)) {
					echo "insert\t{$_school_name}\tdone!\n";
					free_mysqli();
				}else{
					print_r($mysqli->error);
					exit(1);
				}

			}
			$html->clear();
			$_i = $_i + 1;
		}

	}



	function getCityRank()
	{
		global $mysqli,$city_ranking_url;
		include_once('../simple_html_dom.php');

		// QS RANK	 	SCHOOL NAME		COUNTRY			QS STARS RATING 	OVERALL	
		// ACADEMIC REPUTATION
		// EMPLOYER REPUTATION
		// CITATIONS PER PAPER
		// H-INDEX CITATIONS

		$create_sql = <<<EOD
create table IF NOT EXISTS `fu`.`qs_cityranking`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`year` int(10),
	`qs_rank` varchar(16),
	`city_name` varchar(128),
	`rankings` int(10) comment "rankings is intended to take a read of the collective performance of a city’s universities in the qs world university rankings®. the indicators have been designed to take into account the magnetism of the large numbers universities found in large cities as well as lending recognition to the locations of the world\’s elite institutions. all indicators in this category carry equal weight.",
	`student_mix` int(10) comment "student mix is designed to look at the student make-up of the city, both overall and from an international perspective. cities with higher proportions of students are likely to be better equipped with the facilities students need. cities with high numbers of international students are more likely to have the facilities to welcome more.",
	`quality_living` int(10) default 0 comment "a score based on the results of the mercer quality of living survey 2011. since mercer only lists 50 world cities, those not listed are automatically assigned a minimum of half the available points in lieu of further data which has been requested.",
	`employer_activity` int(10) default 0 comment "domestic employer popularity a score based on the number of domestic employers who identified at least one institution in the city as producing excellent graduates international employer popularity [x2] a score based on the weighted count of international employers who identified at least one institution in the city as producing excellent graduates. since all our work is focused on international students and opportunities for mobility, this indicator carries twice the weight of the domestic alternative.",
	`affordability` int(10) default 0 comment "usually the most substantial outlay for a student, particularly for an international student, global trends suggest that tuition fees are likely to play an increasing role in shaping international student mobility trends over the next ten years. this carries twice the weight of the other two affordability indicators. big mac index a score based on the well-known index of retail pricing in cities worldwide, compiled and published by the economist intelligence unit. mercer cost of living index hong kong is a great example of why two third-party indices of affordability have been selected. in hong kong, property is at a premium but food is inexpensive. hong kong places as the world\’s 9th most expensive city in the mercer index but is the second cheapest country in the big mac index. the two working together form a more appropriate read for students.",
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;
		if ($mysqli->query($create_sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}


		$html = file_get_html($city_ranking_url);
		$table = $html->find('table.views-table',0);

		$_year = 2013;
		$_city_name 	= '';
		$_qs_rank 		= '';
		$_rankings 		= 0;
		$_student_mix 	=  0;
		$_quality_living =  0;
		$_employer_activity =  0;
		$_affordability 	=  0;


		$_________tmp = 0;
		foreach ($table->find('tr') as $tr) {
			if ($_________tmp == 0) {
				$_________tmp += 1;
				continue;
			}

			$tds = $tr->find('td');

			$_city_name 	= trim($tds[1]->plaintext);
			$_qs_rank 		= trim($tds[2]->plaintext);
			$_rankings 		= trim($tds[3]->plaintext);
			$_student_mix 	=  trim($tds[4]->plaintext);
			$_quality_living =  trim($tds[5]->plaintext);
			$_employer_activity =  trim($tds[6]->plaintext);
			$_affordability 	=  trim($tds[7]->plaintext);

			$sql = <<<EOD
			INSERT INTO qs_cityranking (id,year,qs_rank,city_name,rankings,student_mix,quality_living,employer_activity,affordability,created)
			VALUES (NULL,{$_year},"{$_qs_rank}","{$_city_name}","{$_rankings}",{$_student_mix},{$_quality_living},{$_employer_activity},{$_affordability},now());
EOD;


			if ($mysqli->query($sql)) {
				echo "insert\t{$_city_name}\tdone!\n";
				free_mysqli();
			}else{
				print_r($mysqli->error);
				exit(1);
			}

		}



		$html->clear();
	}
	//get JSON File

	$subject_type = array(	"Arts & Humanities"=> array("Philosophy","Modern Languages","History","Linguistics","English Language & Literature"),
							"Life Sciences & Medicine"=>array("Medicine","Biological Sciences","Psychology","Pharmacy & Pharmacology","Agriculture & Forestry"),
							"Social Sciences"=>array("Statistics & Operational Research","Sociology","Politics & International Studies","Law","Economics & Econometrics","Accounting & Finance","Communication & Media Studies","Education"),
							"Engineering & Technology"=>array("Computer Science & Information Systems","Chemical Engineering","CiviI & Structural Engineering","Electrical & Electronic Engineering","Mechanical, Aeronautical & Manufacturing Engineering"),
							"Natural Sciences"=>array("Physics & Astronomy","Mathematics","Environmental Sciences","Earth & Marine Sciences","Chemistry","Materials Sciences","Geography")
						);
	$subject_url = array(	"/university-rankings/university-subject-rankings/2014/philosophy",
							"/university-rankings/university-subject-rankings/2014/modern-languages",
							"/university-rankings/university-subject-rankings/2014/history-archaeology",
							"/university-rankings/university-subject-rankings/2014/linguistics",
							"/university-rankings/university-subject-rankings/2014/english-language-literature",
							"/university-rankings/university-subject-rankings/2014/medicine",
							"/university-rankings/university-subject-rankings/2014/biological-sciences",
							"/university-rankings/university-subject-rankings/2014/psychology",
							"/university-rankings/university-subject-rankings/2014/pharmacy",
							"/university-rankings/university-subject-rankings/2014/agriculture-forestry",
							"/university-rankings/university-subject-rankings/2014/statistics-operational-research",
							"/university-rankings/university-subject-rankings/2014/sociology",
							"/university-rankings/university-subject-rankings/2014/politics",
							"/university-rankings/university-subject-rankings/2014/law-legal-studies",
							"/university-rankings/university-subject-rankings/2014/economics-econometrics",
							"/university-rankings/university-subject-rankings/2014/accounting",
							"/university-rankings/university-subject-rankings/2014/communication-media-studies",
							"/university-rankings/university-subject-rankings/2014/education-training",
							"/university-rankings/university-subject-rankings/2014/computer-science-information-systems",
							"/university-rankings/university-subject-rankings/2014/engineering-chemical",
							"/university-rankings/university-subject-rankings/2014/engineering-civil-structural",
							"/university-rankings/university-subject-rankings/2014/engineering-electrical-electronic",
							"/university-rankings/university-subject-rankings/2014/engineering-mechanical",
							"/university-rankings/university-subject-rankings/2014/physics",
							"/university-rankings/university-subject-rankings/2014/mathematics",
							"/university-rankings/university-subject-rankings/2014/environmental-studies",
							"/university-rankings/university-subject-rankings/2014/earth-marine-sciences",
							"/university-rankings/university-subject-rankings/2014/chemistry",
							"/university-rankings/university-subject-rankings/2014/materials-sciences",
							"/university-rankings/university-subject-rankings/2014/geography"
							);



	// getJsonFile();

	function getJsonFile()
	{
		include_once('../simple_html_dom.php');
		global $subject_type,$subject_url;
		$ROOT = "http://www.topuniversities.com/";
		$index = 0;
	 	foreach ($subject_type as $key => $value) {
	 		foreach ($value as $name) {
	 			$html = file_get_html($ROOT . $subject_url[$index]);

	 			$bodyclass = $html->find('body',0)->getAttribute('class');
	 			echo $bodyclass . "\n";
	 			$num = findNum($bodyclass);

	 			$_txt_url = "http://www.topuniversities.com/sites/qs.topuni/files/custom-rankings/{$num}.txt";
	 			
	 			$filename = str_replace("&", "", $name);
 				$filename = str_replace(" ", "_", $filename);
 				$filename = strtolower("qs_" . $filename . ".txt");
 				echo $_txt_url . "\n";
 				echo $filename . "\n";
	 			getTxt($_txt_url,'./tmptxt',$filename);

	 			$index++;
	 		}
	 	}

	}

	function category2filename($value='')
	{
		$filename = str_replace("&", "", $value);
 		$filename = str_replace(" ", "_", $filename);
 		$filename = strtolower("qs_" . $filename . ".txt");
 		return $filename;
	}

	function findNum($str=''){
		$str=trim($str);
		if(empty($str)){return '';}
		$result='';
		for($i=0;$i<strlen($str);$i++){
			if(is_numeric($str[$i])){
				$result.=$str[$i];
			}
		}
		return $result;
	}


	function getTxt($url,$save_dir='',$filename='')
	{
	    if(trim($url)==''){
			return array('file_name'=>'','save_path'=>'','error'=>1);
	    }
	    if(trim($save_dir)==''){
			$save_dir='./';
	    }
	    if(trim($filename)==''){//保存文件名
	        $ext=strrchr($url,'.');
	        if($ext!='.txt'){
				return array('file_name'=>'','save_path'=>'','error'=>3);
			}
	        $filename=time().$ext;
	    }
	    if(0!==strrpos($save_dir,'/')){
			$save_dir.='/';
	    }
	    //创建保存目录
	    if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
			return array('file_name'=>'','save_path'=>'','error'=>5);
	    }

	    //获取远程文件所采用的方法 
	    $ch = curl_init($url);
		$fp2 = @fopen($save_dir . $filename, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp2);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:30.0) Gecko/20100101 Firefox/30.0');
		curl_exec($ch);
		curl_close($ch);
		fclose($fp2);

	    
	   return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
	}

	function score_trim($value='')
	{
		$ret = str_replace("&nbsp;", " ", $value);
		$ret = trim($ret);
		if ($ret == '') {
			$ret = 0;
		}
		return $ret;
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


	function cleanup_database()
	{
		global $mysqli;
		// DROP TABLE IF EXISTS `fu`.`qs_faculty`;
		$sql =<<<HERE_DOC
DROP TABLE IF EXISTS `fu`.`qs_cityranking`;
HERE_DOC;
		if ($mysqli->query($sql)) {
			free_mysqli();
		}
	}


?>
