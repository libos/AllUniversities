<?php
	date_default_timezone_set('Asia/Shanghai');	
	include_once('../simple_html_dom.php');

	$url_arr = array("http://rankings.betteredu.net/THE/2013-2014/top-400.html",
				 	 "http://rankings.betteredu.net/THE/2013-2014/europe.html",
					 "http://rankings.betteredu.net/THE/2013-2014/asia.html",
					 "http://rankings.betteredu.net/THE/2013-2014/north-america.html",
					 "http://rankings.betteredu.net/THE/2013-2014/south-america.html",
					 "http://rankings.betteredu.net/THE/2013-2014/oceania.html",
					 "http://rankings.betteredu.net/THE/2013-2014/africa.html",
					 "http://rankings.betteredu.net/THE/2013-2014/engineering-and-IT.html",
					 "http://rankings.betteredu.net/THE/2013-2014/clinical-health.html",
					 "http://rankings.betteredu.net/THE/2013-2014/social-sciences.html",
					 "http://rankings.betteredu.net/THE/2013-2014/life-sciences.html",
					 "http://rankings.betteredu.net/THE/2013-2014/physical-sciences.html",
					 "http://rankings.betteredu.net/THE/2013-2014/arts-and-humanities.html");


	$mysqli = new mysqli('localhost', 'root', '1028', 'fu');
	if ($mysqli->connect_error) {
	    die('Connect Error (' . $mysqli->connect_errno . ') '  . $mysqli->connect_error);
	}
	$mysqli->query("SET NAMES 'utf8'");
	$mysqli->autocommit(TRUE);
	cleanup_database();


	$create_sql = <<<EOD
create table IF NOT EXISTS `fu`.`theranking`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`type` enum('top400','europe','asia','north-america','south-america','oceania','africa','engineering-and-IT','clinical-health','social-sciences','life-sciences','physical-sciences','arts-and-humanities') comment "top400,europe,asia,north-america,south-america,oceania,africa,engineering-and-IT,clinical-health,social-sciences,life-sciences,physical-sciences,arts-and-humanities",
	`year` int(10),
	`rank` varchar(16),
	`institution` int(11) unsigned,
	`location` varchar(16),
	`overall_score` decimal(4,2) default 0,
	`teaching_score` decimal(4,2) default 0,
	`outlook_score` decimal(4,2) default 0,
	`incoming_score` decimal(4,2) default 0,
	`reseach_score` decimal(4,2) default 0,
	`citation_score` decimal(4,2) default 0,
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;

	if ($mysqli->query($create_sql)) {
		free_mysqli();
	}else{
		print_r($mysqli->error);
		exit(1);
	}



	$url_type = array('top400','europe','asia','north-america','south-america','oceania','africa','engineering-and-IT','clinical-health','social-sciences','life-sciences','physical-sciences','arts-and-humanities');
	// Rank	Institution	Location	
	// Overall score	Teaching	
	// International outlook	Industry income	Research	Citations
	$_rank = '';
	$_institution = '';
	$_loc = '';
	$_overall_score = '';
	$_teaching  = '';
	$_outlook = '';
	$_industry = '';
	$_research = '';
	$_cita = '';
	$_index = 0;
	foreach ($url_arr as $url) {
		$_type = $url_type[$_index];
		echo "{$url}\n";
		$html = file_get_html($url);

		$table = $html->find('.the_pmside table',0);
		$xxxx = 0;
		foreach ($table->find('tr') as $tr) {
			$tds = $tr->find('td');
			if ($xxxx == 0) {
				$xxxx +=1;
				continue;
			}
			$_rank 			= 	$tds[0]->plaintext;
			$_institution 	= 	$tds[1]->plaintext;
			$_loc 			= 	$tds[2]->plaintext;
			$_overall_score = 	score_trim($tds[3]->plaintext);
			if (isset($tds[4])) {
				$_teaching  	= 	score_trim($tds[4]->plaintext);
				$_outlook 		= 	score_trim($tds[5]->plaintext);
				$_industry 		= 	score_trim($tds[6]->plaintext);
				$_research 		= 	score_trim($tds[7]->plaintext);
				$_cita 			= 	score_trim($tds[8]->plaintext);	
			}
			else
			{
				$_teaching  	= 	0;
				$_outlook 		= 	0;
				$_industry 		= 	0;
				$_research 		= 	0;
				$_cita 			= 	0;	
			}
			

			$sql = <<<EOD
			INSERT INTO theranking (id,type,year,rank,institution,location,overall_score,teaching_score,outlook_score,incoming_score,reseach_score,citation_score,created)
			VALUES (NULL,"{$_type}",2013,"{$_rank}","{$_institution}","{$_loc}",{$_overall_score},{$_teaching},{$_outlook},{$_industry},{$_research},{$_cita},now());

EOD;
			if ($mysqli->query($sql)) {
				echo "insert done\n";	
			}else{
				print_r($mysqli->error);
				exit(1);
			}
		}


		$_index ++;
	}


	$mysqli->close();

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
		$sql =<<<HERE_DOC
DROP TABLE IF EXISTS `fu`.`theranking`;
HERE_DOC;
		if ($mysqli->query($sql)) {
			free_mysqli();
		}
	}
?>