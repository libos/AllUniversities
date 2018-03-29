<?php 

	include_once('config.php');
	include_once('simple_html_dom.php');

	//序号	区域	县市	学校名称	系所名称	系所介绍

	$html = file_get_html($sci_graduate_url);
//	$html = file_get_html($master_url);
	//$html = file_get_html($doctor_url);

	$table = $html->find('table.zsjhTable',0);
	
	$_num 		= '';
	$_region 	= '';
	$_city 		= '';
	$_univ_name = '';
	$_sch_name	= '';

	$_row_count = 0;
	$_detail_url= '';
	$index = 0;

	foreach ($table->find('tr') as $tr) {
		if($tr->getAttribute('class') == 'zsjhTitle')
			continue;
		$tds = $tr->find('td');
		$_num 		= $tds[0]->plaintext;
		$_region 	= $tds[1]->plaintext;
		$_city		= $tds[2]->plaintext;
		$_univ_name = $tds[3]->plaintext;
		$_sch_name  = $tds[4]->plaintext;

		if(isset($tds[5]))
		{
			$_row_count 	= intval($tds[5]->rowspan);
			$_detail_url 	= $tds[5]->find('a',0)->href;
			echo "\n\n" . $_univ_name . "           "  . $_detail_url . "\n";
			parse_detail($_detail_url,$_univ_name);
		}
		if($_row_count >= 0)
		{
			echo $_sch_name . "\n";
			$_row_count --;
		}
		$index++;

	}





	function parse_detail($detail_url,$univ_name)
	{	
		$root_url = 'http://hxla.gatzs.com.cn';
		$url = $root_url . $detail_url;
		$dhtml = file_get_html($url);
		$index = -1;
		$_base_info = array();
		$_rec_desc	= '';
		$_detail 	= array();

		$tables = $dhtml->find('table');
		$tables_count = count($tables);
		if ($tables_count > 1) {
			$index = 0;	
		}
		foreach ($tables as $table) {
			foreach ($table->find('tr') as $tr) {
				$tds = $tr->find('td');
				if (count($tds) == 0) {
					continue;
				}
				$first_td = $tds[0]->plaintext;
				if ($tr->getAttribute('bgcolor') == '#dddddd' || $first_td == "招生说明" || $first_td == "学校名称：" . $univ_name) {
					if ($tables_count == 1) {
						$index ++;	
					}
					continue;
				}

				if ($index == 0 && $tables_count  == 2) {
					if (!isset($tds[1])) {
						$index ++ ;
					}
				}
				if ($index == 0) {
					array_push($_base_info,$tds[1]->plaintext);
				}
				if ($index == 1) {
					$_rec_desc = $tds[0]->plaintext;
				}
				if ($index == 2) {
					array_push($_detail,array($tds[0]->plaintext,$tds[1]->plaintext,$tds[2]->plaintext));
//					array_push($_detail,array($tds[0]->plaintext,$tds[1]->plaintext,$tds[2]->plaintext,$tds[3]->plaintext));
				}
			}
			if ($tables_count > 1) {
				$index ++;
			}
		}
		print_r($_base_info);
		print_r($_rec_desc);
		print_r($_detail);

	}
 ?>
