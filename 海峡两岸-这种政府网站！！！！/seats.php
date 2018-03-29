<?php
	include_once('config.php');
	include_once('simple_html_dom.php');
//序号	区域	县市	学校名称	招生名额	招生系组数
foreach($seats_url as $url)
{

	$html = file_get_html($url);
	
	$table = $html->find('table',0);

	$_num = '';
	$_region 	= '';
	$_city 		= '';
	$_univ_name = '';
	
	$_seats = '';
	$_row_count = '';

	foreach($table->find('tr') as $tr)
	{
		if($tr->getAttribute('class') == 'zsjhTitle')
			continue;
		$tds = $tr->find('td');
		if(count($tds) < 5)
		{
			print_r($tr->plaintext . "\n");
			continue;
		}
		$_num 		= $tds[0]->plaintext;
		$_region 	= $tds[1]->plaintext;
		$_city		= $tds[2]->plaintext;
		$_univ_name     = $tds[3]->plaintext;
		
		$_seats	= $tds[4]->plaintext;
		$_row_count = $tds[5]->plaintext;

		echo $_univ_name . "    " . $_seats . "\n";	
	
	}

}



?>
