#!/usr/bin/env php
<?php

/* main check */
if(isset($argv[1]) && isset($argv[2])) {

	$comicLink = $argv[1];
	$comicFolder = $argv[2];

	/* some preparations */
	if(((strpos($comicLink, "http://") === FALSE ) && (strpos($comicLink, "https://") === FALSE))) {
		show("You should add http:// or https:// to comic address! Aborting.");
		die();
	}
	if(strpos($comicFolder, "/") === 0) {
		$folder = $comicFolder;
	} else {
		$folder = dirname(__FILE__) . "/" . $comicFolder;
	}
	if(!is_dir($folder)) {
		if(is_file($folder)) {
			show("The target directory is actually a file! I would not delete it. Aborting.");
			die();
		} else {
			mkdir($folder);
		}
	}

	/* main action */
	$folder .= "/";
	$a = file_get_contents($comicLink);
	preg_match("/currentUrl \: \'http\:\/\/tapastic\.com\/series\/(.*?)\'\,/", $a, $b);
	if(isset($b[1])) {
		$c = file_get_contents("http://tapastic.com/series/episodes/".$b[1]."/1/desc/0");
		$d = json_decode($c);
		if($d->code == "200") {
			$e = $d->data->pager->totalPage;
			$f = $d->data->episodeList;
			$h = [];
			for($i = 1; $i <= $e; $i++) {
				$l = file_get_contents("http://tapastic.com/series/episodes/".$b[1]."/".$i."/desc/0");
				$n = json_decode($l);
				$g = trim(preg_replace('/\s\s+/', ' ', $n->data->episodeList));
				preg_match_all("/data\-href\=\"\/episode\/(.*?)\"/", $g, $m2);
				preg_match_all("/\<p class\=\"title \"\> (.*?) \<\/p\>/", $g, $m3);
				for($i2 = 0; $i2 < count($m2[1]); $i2++) {
					$h[$i2 + (($i - 1) * 10)] = [
						'id' => $m2[1][$i2],
						'title' => $m3[1][$i2],
					];
				}
			}
			foreach($h as $item) {
				$j = file_get_contents("http://tapastic.com/episode/" . $item['id']);
				preg_match_all("/\<img class\=\"art-image\" src\=\"(.*?)\"/", $j, $m4);
				if(isset($m4[1])) {
					if(count($m4[1]) == 1) {
						$item2 = $m4[1][0];
						$k = file_get_contents($item2);
						if(strpos($item2, "jpg")) {
							$ext = "jpg";
						}
						if(strpos($item2, "gif")) {
							$ext = "gif";
						}
						$name = str_replace("/", "-", $item['title']);
						$file = fopen($folder . $name . "." . $ext, "w");
						fwrite($file,$k);
						fclose($file);
					} else {
						mkdir($folder . $item['title']);
						foreach($m4[1] as $key=>$item2) {
							$k = file_get_contents($item2);
							if(strpos($item2, "jpg")) {
								$ext = "jpg";
							}
							if(strpos($item2, "gif")) {
								$ext = "gif";
							}
							$name = str_replace("/", "-", $item['title']);
							$file = fopen($folder . $name . '/' . $key . "." . $ext, "w");
							fwrite($file,$k);
							fclose($file);
						}
					}
				}
			}
		}
	}
} else {
	show("You forgot to set link or folder! Aborting.");
	die();
}

function show($string) {
	echo $string . "\r\n";
}
?>
