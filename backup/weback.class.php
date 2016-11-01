<?php
/*
数据库连接，
备份还原数据库操作，还原数据库会先删掉数据库再重建
备份还原网站操作，还原网站会先清空网站再还原，当前存放备份文件的目录不会被清空
*/

class weback
{
	public function __construct(){
		$this->mysql = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
		if(!$this->mysql){
			exit("数据库连接错误".mysql_error());
		}
		if(!@mysql_select_db(DB_NAME,$this->mysql)){
			exit("数据库".DB_NAME."不存在");
		}
		@mysql_unbuffered_query("set names ".DB_CHARSET);
	}

	function query($sql){
		if(!$res = @mysql_query($sql,$this->mysql)){
			exit("操作数据库失败".mysql_error()."<br>sql:{$sql}");
		}
		return $res;
	}

	function fetch_asc($sql){
		$result = $this->query($sql);
		$arr=array();
		while($rows = mysql_fetch_assoc($result)){
			$arr[]=$rows;
		}
		mysql_free_result($result);
		return $arr;
	}

	//备份数据库
	function db_back(){
		$rel2 = $this->fetch_asc('SHOW TABLE STATUS FROM '.DB_NAME);
		$db=array();
		foreach($rel2 as $key=>$value){
			if(substr($value['Name'],0,strlen(DB_PRE))==DB_PRE){
				$db[]=$value['Name'];
			}
		}
		$sql = "";
		foreach($db as $k=>$v){
			$rel = $this->fetch_asc('SHOW CREATE TABLE '.$v);
			$sql .= "DROP TABLE IF EXISTS `".$v."`;\n";
			$sql .= $rel[0]['Create Table'].";\n";
			$record = $this->fetch_asc("select * from ".$v);
			if(!empty($record)){
				$insert=array();
				foreach($record as $key=>$value){
					foreach($value as $r_k=>$r_v){
						$insert[$r_k]="'".mysql_real_escape_string($r_v)."'";
					}
					$sql.="INSERT INTO `".$v."` VALUES(".implode(',',$insert).");\n";
				}
			}
		}
		if(!@file_put_contents(DB_BACK,$sql)){
			exit('备份失败,请检查文件夹是否有足够的权限');
		}
	}
	//还原数据库
	function db_import(){
		mysql_query("drop database ".DB_NAME);
		mysql_query("create database ".DB_NAME);
		mysql_select_db(DB_NAME,$this->mysql);
		$data = @file_get_contents(DB_BACK);
		$data = explode(";\n",trim($data));
		if(!empty($data)){
			foreach($data as $k=>$v){
				$this->query($v);
			}
		}
		//echo "数据还原成功";
	}

	//zip打包压缩网站
	function web_back(){
		$dir="../";
		$zip = new ZipArchive();
		$filename = date('Ymdhis').'.zip';
		if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
			exit("无法创建 <$filename>n");
		}
		$files = $this->list_dir($dir);
		foreach($files as $path){
			$zip->addFile($path,str_replace("./","",str_replace("\\","/",$path)));
		}
		$zip->addFile($dir.WEB_BACK.'/'.DB_BACK);
		$zip->close();
		echo "网站已备份为：$filename <br/>";
		unlink(DB_BACK);
	}

	function list_dir($dir='.') {
		$files = array();
		if (is_dir($dir)) {
			$fh = opendir($dir);
			while (($file = readdir($fh)) !== false) {
				if (strcmp($file, '.')==0 || strcmp($file, '..')==0 || strcmp($file, WEB_BACK)==0){ continue; }
				$filepath = $dir . '/' . $file;
				if ( is_dir($filepath) ){
					$files = array_merge($files, $this->list_dir($filepath));
				}else{
					array_push($files, $filepath);
				}
			}
			closedir($fh);
		} else {
			$files = false;
		}
		return $files;
	}
	//解压还原网站
	function web_import($file){
		$zip = new ZipArchive() ;
		if ($zip->open($file) !== TRUE) {
			exit("不存在备份文件:$file");
		}
		$zip->extractTo('..');
		$zip->close();
	}

	//删除网站
	function del_dir($dir) {
		$dh = opendir($dir);
		while ($file=readdir($dh)) {
			if (strcmp($file, '.')==0 || strcmp($file, '..')==0 || strcmp($file, WEB_BACK)==0){ continue; }
			$fullpath=$dir."/".$file;
			if(!is_dir($fullpath)) {
				unlink($fullpath);
			} else {
				$this->del_dir($fullpath);
			}
		}
		closedir($dh);
	}
}