<?php 
 	date_default_timezone_set('Asia/Shanghai');	
	$__root 			=	'http://www.shanghairanking.cn/';	
	$academic_500_2013 = 'http://www.shanghairanking.cn/ARWU2013.html';
	
	$sci_200_2013 = 'http://www.shanghairanking.cn/FieldSCI2013.html';
	$ei_200_2013 = 'http://www.shanghairanking.cn/FieldENG2013.html';
	$life_200_2013 = 'http://www.shanghairanking.cn/FieldLIFE2013.html';
	$med_200_2013 = 'http://www.shanghairanking.cn/FieldMED2013.html';
	$soc_200_2013 = 'http://www.shanghairanking.cn/FieldSOC2013.html';

	$math_200_2013 = 'http://www.shanghairanking.cn/SubjectMathematics2013.html';
	$phy_200_2013 = 'http://www.shanghairanking.cn/SubjectPhysics2013.html';
	$chem_200_2013 = 'http://www.shanghairanking.cn/SubjectChemistry2013.html';
	$computer_200_2013 = 'http://www.shanghairanking.cn/SubjectCS2013.html';
	$eb_200_2013	= 'http://www.shanghairanking.cn/SubjectEcoBus2013.html';

	$all_url = array($academic_500_2013,$sci_200_2013,$ei_200_2013,$life_200_2013,$med_200_2013,$soc_200_2013,$math_200_2013,$phy_200_2013,$chem_200_2013,$computer_200_2013,$eb_200_2013);
	$url_type = array('academic','sci','ei','life','med','soc','math','phy','chem','computer','eb');
	$_school_url_arr =	array();
	$_country_url 	=	array();
	$mysqli = new mysqli('localhost', 'root', '1028', 'fu');
	if ($mysqli->connect_error) {
	    die('Connect Error (' . $mysqli->connect_errno . ') '  . $mysqli->connect_error);
	}
	$mysqli->query("SET NAMES 'utf8'");
	$mysqli->autocommit(TRUE);
	cleanup_database();
	$query = getTables();
	if ($mysqli->multi_query($query)) {
		free_mysqli();
	}else{
		exit(1);
	}

	$index =0;
	foreach ($all_url as $url) {
		try {
			defaultHandler($url,$index);
 		} catch (Exception $e) {
		    echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}
	
	$mysqli->close();

	function defaultHandler($_main_url,$global_index)
	{
		global $mysqli,$__root,$all_url,$url_type,$_school_url_arr,$_country_url;

		include_once('../simple_html_dom.php');

		//_main_url
		$html = file_get_html($_main_url);

		$table = $html->find('#UniversityRanking',0);

		$index = 0;
		//世界排名	学校*	国家/地区	国家 排名	总分
		$_world_rank	=	'';
		$_school_name	=	'';
		$_school_engname=	'';
		$_school_url	=	'';
		$_country		=	'';
		$_country_rank	=	'';
		$_sum_score		=	'';
		$_alumni_score	=	'';
		$_award_score	=	'';
		$_hici_score	=	'';
		$_ns_score		=	'';
		$_pub_score		=	'';
		$_pcb_score		=	'';

		foreach($table->find('tr') as $tr)
		{
			// if($index >10)
			// {
			// 	break;
			// }
			if ($index == 0) {
				$index ++;
				continue;
			}
			$index ++;
			// foreach($tr->find('td') as $td)
			// {
			// 	echo1($td->plaintext);
			// }
			// echo "\n";
			$tds = $tr->find('td');
			if (count($tds) <4) {
				continue;
			}

			$_world_rank	=	$tds[0]->plaintext;
			$_school_name	=	$tds[1]->plaintext;
			$_school_url	=	$tds[1]->find('a',0)->href;
			$_school_engname =  getEngName($_school_url);

			$__country_link	=	$tds[2]->find('a',0);
			$_country		=	$__country_link->getAttribute('title');
			array_push($_country_url,$__root . $__country_link->href);
			$_country_rank	=	$tds[3]->plaintext;
			$_sum_score		=	$tds[4]->plaintext;
			$_alumni_score	=	$tds[5]->plaintext;
			$_award_score	=	$tds[6]->plaintext;
			$_hici_score	=	$tds[7]->plaintext;
			$_ns_score		=	$tds[8]->plaintext;
			$_pub_score		=	$tds[9]->plaintext;
			$_pcb_score		=	$tds[10]->plaintext;

			// echo "\n";echo "\n";
			echo1($_world_rank);
			echo1($_school_name);
			echo1($_school_engname);
			echo1($_school_url);
			echo1($_country	);
			// // print_r($_country_url);
			echo1($_country_rank);
			echo1($_sum_score	);
			echo1($_alumni_score);
			echo1($_award_score);
			echo1($_hici_score);
			echo1($_ns_score	);
			echo1($_pub_score	);
			echo1($_pcb_score	);
			$sch_ret = array();
 
			if (!isset($_school_url_arr[$_school_name])) {
				$_school_url_arr[$_school_name] = $_school_url;
				$sch_ret = school_detail($__root . $_school_url);
			}else
				{
					$sql = "select id,country_id,country_name from `school` where name='" . $__school_name . "';";
					if ($result = $mysqli->query($sql)) {
						 while($obj = $result->fetch_object()){
				            $sch_ret = array('sch_id'=>$obj->id,'country_id'=>$obj->country_id,'country_name'=>$obj->country_name);
				            break;
				        }
				        free_mysqli();
					}else
					{
						print_r($mysqli->error);
						exit(1);
					}
				}
			echo "\n";
 
			$_country_name = $sch_ret['country_name'];
			$query = <<<EOD
	INSERT INTO `shanghairanking` (id,type,year,world_rank,school_name,school_engname,school_id,country_name,country_id,country_rank,sum_score,alumni_score,award_score,hici_score,ns_score,pub_score,pcb_score,created) 
	VALUES (NULL,"{$url_type[$global_index]}","2013","$_world_rank","{$_school_name}","{$_school_engname}","{$sch_ret['sch_id']}","{$_country_name}","{$sch_ret['country_id']}","{$_country_rank}","{$_sum_score}","{$_alumni_score}","{$_award_score}","{$_hici_score}","{$_ns_score}","{$_pub_score}","{$_pcb_score}",now());
EOD;
			if ($mysqli->query($query)) {
				echo "insert done;\n";	
				free_mysqli();
			}else{
				print_r($mysqli->error);
				exit(1);
			}

		}
		
		$_country_url = array_unique($_country_url);
		$_school_url_arr = array_unique($_school_url_arr);

		// print_r($_country_url);

		/*
		*  国家
		*/
		$__country_rank = '';
		$__school_name	= '';
		$__school_url	= '';
		$__world_rank	= '';
		foreach ($_country_url as $url) {
			$chtml = file_get_html($url);
			$__country_name = $chtml->find('#countryflag',0)->plaintext;
			echo "\n=============={$__country_name}==================\n";	
			$ctable = $chtml->find('table#UniversityRanking',0);
			$cindex = 0;
			foreach ($ctable->find('tr') as $tr) {
				if ($cindex == 0) {
					$cindex ++ ;
					continue;
				}

				$cindex ++ ;
				$tds = $tr->find('td');
				if (count($tds) < 3) {
					continue;
				}
				$__country_rank  = $tds[0]->plaintext;
				$__school_name	 = $tds[1]->plaintext;
				$__school_engname= getEngName($tds[1]->find('a',0)->href);
				$__school_url	 = substr($tds[1]->find('a',0)->href,2);
				$__world_rank    = $tds[2]->plaintext;

				$__school_id 	 =  '';
				$__country_id  	 =  '';

				if (!isset($_school_url_arr[$__school_name])) {
					$_school_url_arr[$__school_name] = $__school_url;
					$sch_ret = school_detail($__root . $__school_url);
					$__school_id = $sch_ret["sch_id"];
					$__country_id = $sch_ret["country_id"];
				}else
				{
					$sql = "select id,country_id from `school` where name='" . $__school_name . "';";
					if ($result = $mysqli->query($sql)) {
						 while($obj = $result->fetch_object()){
				            $__school_id = $obj->id;
				            $__country_id = $obj->country_id;
				            break;
				         }
				         free_mysqli();
					}else{
						print_r($mysqli->error);
						$__school_id = 0;
						$__country_id = 0;
						// not allow
						exit(1);
					}
				}

				echo1($__country_rank);
				echo1($__school_name);
				echo1($__world_rank);

				echo "\n";

				$query = <<<EOD
	INSERT INTO `countryranking` (id,country_name,country_id,countryrank,school_name,school_engname,school_id,world_rank,created)
	VALUES (NULL,"{$__country_name}","{$__country_id}","{$__country_rank}","{$__school_name}","{$__school_engname}","{$__school_id}","{$__world_rank}",now());
EOD;
				if ($mysqli->query($query)) {
					free_mysqli();
				}else{
					print_r($mysqli->error);
					exit(1);
				}
			}
		}
	}
	


	function echo1($value='')
	{
		echo $value . "  ";
	}

	function imgsrc($value='')
	{
		$root = 'http://www.shanghairanking.cn';
		return $root . substr($value,2);
	}
	function getEngName($url)
	{
		$ret = basename($url);
		$ret = basename($ret,'.html');
		$ret = str_replace("-", " ", $ret);
		return $ret;
	}

	function school_detail($value='')
	{
		global $mysqli,$__root,$all_url,$url_type,$_school_url_arr;
		include_once('./TesseractOCR.php');

		$dhtml = file_get_html($value);

		$tab1table = $dhtml->find('#tab1 table',0);
		$tab2img = $dhtml->find('#tab2 img',0);
		$tab3img = $dhtml->find('#tab3 img',0);
		$tab4img = $dhtml->find('#tab4 img',0);
		$tab5img = $dhtml->find('#tab5 img',0);
		$tab6img = $dhtml->find('#tab6 img',0);

		$index = 0;
		$_school_engname = getEngName($value);
		$_school_name  = '';
		$_region	   = '';
		$_country      = '';
		$_foundation   = '';
		$_eng_addr	   = '';
		$_sch_urlx	   = '';
		foreach ($tab1table->find('tr') as $tr) {
			$tds = $tr->find('td');

			$td = $tds[1]->plaintext;

			switch ($index) {
				case 0:					//校名
					$_school_name = $td;
					break;
				case 1:					//区域
					$_region = $td;
					break;
				case 2:					//国家
					$_country = $td;
					break;
				case 3:					//成立年份
					$_foundation = $td;
					break;
				case 4:					//地址
					$_eng_addr = $td;
					break;
				default:				//网址
					$_sch_urlx = $td;
					break;
			}
			$index ++;
		}

		$qingkuang = imgsrc($tab3img->src);
		$yuanxi = imgsrc($tab4img->src);
		$zhuanye = imgsrc($tab5img->src);
		if (strpos($qingkuang,'加州大学-') != false) {
			$qingkuang = str_replace("加州大学-","加州大学",$qingkuang);
			$qingkuang = str_replace("学生.","分校学生.",$qingkuang);
			$yuanxi    = str_replace("加州大学-","加州大学",$yuanxi);
			$yuanxi    = str_replace("院系.","分校院系.",$yuanxi);
			$zhuanye   = str_replace("加州大学-","加州大学",$zhuanye);
			$zhuanye   = str_replace("专业","分校专业.",$zhuanye);
		}


		$d = date('mdGis');
		$ret_qingkuang = getImage($qingkuang,'./tmpimg', $d . '1'  . '.jpg');
		$ret_yuanxi   = getImage($yuanxi,'./tmpimg', $d . '2' . '.jpg');
		$ret_zhuanye   = getImage($zhuanye,'./tmpimg', $d . '3' . '.jpg');

		$_qingkuang_path = $ret_qingkuang['save_path'];
		$_yuanxi_path = $ret_yuanxi['save_path'];
		$_zhuanye_path = $ret_zhuanye['save_path'];
	
		if($ret_qingkuang['error'] == 0)
		{			
      		imagethresh($_qingkuang_path);
		}
		
		if($ret_yuanxi['error'] == 0)
		{
		    imagethresh($_yuanxi_path);
                }
		if($ret_zhuanye['error'] == 0)
		{
		    imagethresh($_zhuanye_path);
		}
		$_xuefei_path = '';
		if(isset($tab6img)  ){
			$xuefei = imgsrc($tab6img->src);
			if (strpos($xuefei,'加州大学-') != false) {
				$xuefei = str_replace("加州大学-","加州大学",$xuefei);
				$xuefei = str_replace("学费.","分校学费.",$xuefei);
			}
			$ret_xuefei   = getImage($xuefei,'./tmpimg', $d . '4' .  '.jpg');
			$_xuefei_path = $ret_xuefei['save_path'];
			imagethresh($_xuefei_path);
		}
		$img_list = array($_qingkuang_path,$_yuanxi_path,$_zhuanye_path,$_xuefei_path);
		$img_err  = array($ret_qingkuang['error'],$ret_yuanxi['error'],$ret_zhuanye['error'],$ret_xuefei['error']);
		$namelist = array('xuesheng','yuanxi','zhuanye','xuefei');
		$rec_ret = array();
		$i = 0;
		foreach ($img_list as $_filepath) {
			if($img_err[$i] != 0)
			{
			   $rec_ret[$namelist[$i]] = "";
			   $i ++ ;
			   continue;
		 	}
			$tesseract = new TesseractOCR($_filepath);
			$tesseract->setTempDir('./tmpimg/');
			if ($i == 0 || $i ==3) {
				$tesseract->setLanguage('chi_sim+' . $namelist[$i]);
				$rec = $tesseract->recognize();
			}else
			{
				$rec = $tesseract->recognize();
			}
			$rec_ret[$namelist[$i]] = $rec;
			$i++;
			echo "\n";
		}
		// print_r($rec_ret);
 		
 		$_inChina = false;
 		if ($_country =="中国") {
 			$_inChina = true;
 		}
		$baidu_ret = baidu($_school_name,$_inChina);

		$_stu_num_json =  mysql_str( array('imgsrc'=>$_qingkuang_path,'rec'=>$rec_ret['xuesheng'],'err'=>$img_err[0]) ) ;
		$_sch_depart_json = mysql_str( array('imgsrc'=>$_yuanxi_path,'rec'=>$rec_ret['yuanxi'],'err'=>$img_err[1]) );
		$_sch_major_json = mysql_str ( array('imgsrc'=>$_zhuanye_path,'rec'=>$rec_ret['zhuanye'],'err'=>$img_err[2]) );
		$_sch_xuefei_json = mysql_str ( array('imgsrc'=>$_xuefei_path,'rec'=>$rec_ret['xuefei'],'err'=>$img_err[3]) );

		$_basic_desc = mysql_escape_string($baidu_ret['base_desc']);
		$_baidu_info = mysql_str($baidu_ret['info_table']);
		$_nick_name =  mysql_escape_string(isset($_baidu_info['简称']) ? $_baidu_info['简称'] : "");
		$_motto     =  mysql_escape_string(isset($_baidu_info['校训']) ? $_baidu_info['校训'] : "");
		$_tmp_badge_img_ret = $baidu_ret['img_ret'];
		$_badgeicon =  mysql_str( array('imgsrc' => $_tmp_badge_img_ret['save_path'], 'err'=>$_tmp_badge_img_ret['error']) );
		
		$sql = "select id from `country` where name='" . $_country . "';";
		if ($result = $mysqli->query($sql)) {
			$row_cnt = $result->num_rows;

			if ($row_cnt == 0) {
				free_mysqli();
				$sql = <<<EOD
					INSERT INTO `country` (id,name,region,flag,created)
					VALUES (NULL,"{$_country}","{$_region}","",now());
EOD;
				if ($mysqli->query($sql)) {
					free_mysqli();
				}else{
					print_r($mysqli->error);
					exit(1);
				}
				$_country_id = $mysqli->insert_id;
			}else{
				$obj = $result->fetch_object();
				$_country_id = $obj->id;
				free_mysqli();
			}
		}else{
			print_r($mysqli->error);
			exit(1);
		}
 
		// school
		$sql = <<<EOD
		INSERT INTO `school` (id,uuid,name,eng_name,country_id,country_region,country_name,buid_at,addr,addr_eng,website,stu_num,depart,major,xuefei,basic_desc,baidu_info,nick_name,motto,badgeicon,created)
		VALUES (NULL,NULL,"{$_school_name}","{$_school_engname}","{$_country_id}","{$_region}","{$_country}","{$_foundation}","","{$_eng_addr}","{$_sch_urlx}","{$_stu_num_json}","{$_sch_depart_json}","{$_sch_major_json}","{$_sch_xuefei_json}","{$_basic_desc}","{$_baidu_info}","{$_nick_name}","{$_motto}","{$_badgeicon}",now());
EOD;
		if ($mysqli->query($sql)) {
			free_mysqli();
		}else{
			print_r($mysqli->error);
			exit(1);
		}
		$_sch_id = $mysqli->insert_id;

		return array('sch_id' => $_sch_id,'country_id' => $_country_id,'country_name'=>$_country);
	}

	function baidu($name,$inChina)
	{
		global $mysqli,$__root,$all_url,$url_type,$_school_url_arr;
		include_once('../simple_html_dom.php');
		$search_url = "http://baike.baidu.com/search/none?word=" . $name;
		$shtml = file_get_html($search_url);
		$itemlink = $shtml->find('.mod-list table',0)->find('h2 a',0)->href;
		$itempage = file_get_html($itemlink);

		$_base_desc = '';
		$_base_info_table = array();
		$_badge = '';
		$_card_img = '';
		$_img_ret = array();
		
		$d = date('mdGis');

		if (!$inChina) {
			/*
			* 国外大学
			*/

			$__tmp = $itempage->find('#card-container',0)->find('.para');
			foreach ($__tmp as $_tmp) {
				$_base_desc = $_base_desc .  $_tmp->plaintext . "\n";
			}

			$__tmp = $itempage->find('#baseInfoWrapDom',0);
			if (is_object($__tmp)) {
				$__tmp = $__tmp->find('.biItem');
				foreach ($__tmp as $_tmp) {
					$_title = str_replace("&nbsp;","",$_tmp->find('.biTitle',0)->plaintext);
					$_content = $_tmp->find('.biContent',0)->plaintext;
					$_base_info_table[$_title] = $_content;
				}
			}
			
			$__tmp = $itempage->find('#card-img',0);
			if (is_object($__tmp)) {
				$_card_img = $__tmp->find('img',0)->getAttribute('src');
			} 
			$ext=strrchr($_card_img,'.');
			$_img_ret = getImage($_card_img,'./baiduimg', $d . $ext);

		}else{
			/*
			* 国内大学
			*/
			
			$__tmp = $itempage->find('.desc',0)->find('.para');
			foreach ($__tmp as $_tmp) {
				$_base_desc = $_base_desc .  $_tmp->plaintext . "\n";
			}
			
			$__tmp = $itempage->find('#baseInfoWrapDom',0);
			if (is_object($__tmp)) {
				$__tmp = $__tmp->find('.biItem');
				foreach ($__tmp as $_tmp) {
					$_title = str_replace("&nbsp;","",$_tmp->find('.biTitle',0)->plaintext);
					$_content = $_tmp->find('.biContent',0)->plaintext;
					$_base_info_table[$_title] = $_content;
				}
			}

			$__tmp = $itempage->find('img.badge',0);
			if (is_object($__tmp)) {
				$_badge = $__tmp->getAttribute('src');
			}
			$ext=strrchr($_badge,'.');
			$_img_ret = getImage($_badge,'./baiduimg', $d . $ext);
		}

		// echo $_base_desc;
		// print_r($_base_info_table);
		// print_r($_img_ret);

		return array('base_desc'=>$_base_desc,
					 'info_table'=>$_base_info_table,
					 'img_ret'=>$_img_ret);
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

	function mysql_str($value='')
	{
		return mysql_escape_string( json_encode( $value ));
	}

	function getImage($url,$save_dir='',$filename=''){
	    if(trim($url)==''){
			return array('file_name'=>'','save_path'=>'','error'=>1);
	    }
	    if(trim($save_dir)==''){
			$save_dir='./';
	    }
	    if(trim($filename)==''){//保存文件名
	        $ext=strrchr($url,'.');
	        if($ext!='.gif'&&$ext!='.jpg'){
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

		if (strpos($save_dir,'tmpimg') != false) {
			$ext=strrchr($url,'.');
			$src_filename = basename($filename,$ext) . "_src" . $ext;
			@copy($save_dir . $filename,$save_dir . $src_filename);
		}
	    list($width, $height, $type, $attr) = getimagesize($save_dir.$filename);
	    if (isset($type) && in_array($type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF)))
	        return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
        else
	        return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>6);
	}


	function imagethresh($file='')
	{
		$font_color = array('r' => 160, 'g' => 160, 'b' => 160);

		$im = imagecreatefromjpeg($file);
		$w = imagesx($im);
		$h = imagesy($im);

		//生成黑白图片
		$im2 = imagecreatetruecolor($w, $h);
		$black = imagecolorallocate($im2, 0, 0, 0);
		$white = imagecolorallocate($im2, 255, 255, 255);
		for ($i=0; $i<$w; $i++)
		{		  
		    for ($j=0; $j<$h; $j++)
		    {
				$index = imagecolorat($im, $i, $j);
			        $rgb = imagecolorsforindex($im, $index);
			        
				if ($rgb['red'] < $font_color['r'] && $rgb['green'] < $font_color['g'] && $rgb['blue'] < $font_color['b'])
			        {
			            imagesetpixel($im2, $i, $j, $black);
			        } else {
			            imagesetpixel($im2, $i, $j, $white);
			        }
			    }
		}
		$_size = array('top'=>0,'left'=>$w,'bottom'=>$h,'right'=>0);
		for ($i=0; $i<$w; $i++)
		{		  
		    for ($j=0; $j<$h; $j++)
		    {
		    	$color_id = imagecolorat($im2, $i, $j);
		    	if ($color_id == $black ) {
		    		$_size['left'] = $i-5 < 0 ? 0 : $i-5;
		    		break 2;
		    	}
		   	}
		}
		for ($i=$w-1; $i>0; $i--)
		{		  
		    for ($j=0; $j<$h; $j++)
		    {
		    	$color_id = imagecolorat($im2, $i, $j);
		    	if ($color_id == $black){
		    		$_size['right'] = $i + 5 > $w ? $w : $i+5;
		    		break 2;
		    	}
		    }
		}
		for ($i=$h-1; $i>0; $i--)
		{		  
		    for ($j=0; $j<$w; $j++)
		    {
		    	$color_id = imagecolorat($im2, $j, $i);
		    	if ($color_id == $black){
		    		$_size['bottom'] = $i + 5 > $h ? $h : $i+5 ;
		    		break 2;
		    	}
		    }
		}
		for ($i=0; $i<$h; $i++)
		{		  
		    for ($j=0; $j<$w; $j++)
		    {
		    	$color_id = imagecolorat($im2, $j, $i);
		    	if ($color_id == $black){
		    		$_size['top'] = $i - 5 < 0 ? 0 : $i-5;
		    		break 2;
		    	}
		    }
		}
		$w = $_size['right']-$_size['left'];
		$h = $_size['bottom']-$_size['top'];
		$im3 = imagecreatetruecolor($w,$h);
		imagecopy($im3,$im2,0,0,$_size['left'],$_size['top'],$w,$h);
		imagejpeg($im3,$file,100);
		imagedestroy($im2);
		imagedestroy($im3);
		return array('w'=>$w,'h'=>$h);
	}
	function cleanup_database()
	{
		global $mysqli;
		$sql =<<<HERE_DOC
DROP TABLE IF EXISTS `fu`.`country`;
DROP TABLE IF EXISTS `fu`.`shanghairanking`;
DROP TABLE IF EXISTS `fu`.`countryranking`;
DROP TABLE IF EXISTS `fu`.`school`;
HERE_DOC;
		if ($mysqli->multi_query($sql)) {
			free_mysqli();
		}
	}
	function getTables()
	{

		$sql =<<<HERE_DOC
create table IF NOT EXISTS `fu`.`country`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`name` varchar(64) NOT NULL,
	`region` varchar(8) NOT NULL,
	`flag` varchar(255) NOT NULL,
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
create table IF NOT EXISTS `fu`.`shanghairanking`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`type` enum('academic','sci','ei','life','med','soc','math','phy','chem','computer','eb'),
	`year` int(10),
	`world_rank` varchar(16),
	`school_name` varchar(64),
	`school_engname` varchar(128),
	`school_id` int(11) unsigned,
	`country_name` varchar(64),
	`country_id` int(11) unsigned,
	`country_rank` varchar(16),
	`sum_score` decimal(4,2),
	`alumni_score` decimal(4,2),
	`award_score` decimal(4,2),
	`hici_score` decimal(4,2),
	`ns_score` decimal(4,2),
	`pub_score` decimal(4,2),
	`pcb_score` decimal(4,2),
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
create table IF NOT EXISTS `fu`.`countryranking`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`country_name` varchar(16),
	`country_id` int(11) unsigned,
	`countryrank` varchar(16),
	`school_name` varchar(64),
	`school_engname` varchar(128),
	`school_id` int(11) unsigned,
	`world_rank` varchar(16),
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
create table IF NOT EXISTS `fu`.`school`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`uuid` varchar(64),
	`name` varchar(64),
	`eng_name` varchar(128),
	`country_id` int(11) unsigned,
	`country_region` varchar(8),
	`country_name` varchar(64),
	`buid_at` varchar(8),
	`addr` varchar(255),
	`addr_eng` varchar(255),
	`website` varchar(255),
	`stu_num` varchar(255),
	`depart` text,
	`major` text,
	`xuefei` varchar(255),
	`basic_desc` text,
	`baidu_info` text,
	`nick_name` varchar(64),
	`motto` varchar(255),
	`badgeicon` varchar(255),
	`created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
HERE_DOC;
		
		return $sql;
	}

 ?>
