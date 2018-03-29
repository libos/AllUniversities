<?php

	baidu("哈佛大学",false);

	function baidu($name,$inChina)
	{
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

			$__tmp = $itempage->find('#baseInfoWrapDom',0)->find('.biItem');
			foreach ($__tmp as $_tmp) {
				$_title = str_replace("&nbsp;","",$_tmp->find('.biTitle',0)->plaintext);
				$_content = $_tmp->find('.biContent',0)->plaintext;
				$_base_info_table[$_title] = $_content;
			}

			$_card_img = $itempage->find('#card-img',0)->find('img',0)->getAttribute('src');

			$_img_ret = getImage($_card_img,'./baiduimg', $d . '.jpg');

		}else{
			/*
			* 国内大学
			*/
			
			$__tmp = $itempage->find('.desc',0)->find('.para');
			foreach ($__tmp as $_tmp) {
				$_base_desc = $_base_desc .  $_tmp->plaintext . "\n";
			}
			
			$__tmp = $itempage->find('#baseInfoWrapDom',0)->find('.biItem');
			foreach ($__tmp as $_tmp) {
				$_title = str_replace("&nbsp;","",$_tmp->find('.biTitle',0)->plaintext);
				$_content = $_tmp->find('.biContent',0)->plaintext;
				$_base_info_table[$_title] = $_content;
			}

			$_badge = $itempage->find('img.badge',0)->getAttribute('src');

			$_img_ret = getImage($_badge,'./baiduimg', $d . '.jpg');
		}



		echo $_base_desc;
		print_r($_base_info_table);
		print_r($_img_ret);

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
		curl_exec($ch);
		curl_close($ch);
		fclose($fp2);
	    list($width, $height, $type, $attr) = getimagesize($save_dir.$filename);
	    if (isset($type) && in_array($type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF)))
	    {
	        return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
            }
            else
	    {
	        return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>6);
            }
	}

?>

