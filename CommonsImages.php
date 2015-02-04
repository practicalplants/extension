<?php
/*ini_set("display_errors", 1);
ini_set("error_reporting", E_ALL ^ E_WARNING);
set_time_limit(0);

$time_start = microtime(true);
$ctr = 1;

$result = fopen("result.txt", "w");
getDirectoryListing($result, null, "Дискретная математика");
fclose($result);

$time_end = microtime(true);
$time = $time_end - $time_start;

echo "<br>Finished in $time seconds";*/

class PracticalPlants_CommonsImages{
	function __construct(){
	
	}
	
	function addImageToArticle($name){
		$title = Title::newFromText( $name );
		echo $title->getText()."\n";
		$article = new Article( $title, 0 );
		if ( !$article ) {
			echo 'replaceText: Article not found.'."\n";
			return false;
		}
		$content = $article->fetchContent();
		
	}
	
	function getCommonsImages($title){

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2");
		curl_setopt($ch, CURLOPT_URL, "http://commons.wikimedia.org/w/api.php?action=query&prop=revisions&rvprop=content&format=json&titles=".$title);
		$data = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_exec($ch);
		curl_close ($ch);
		if($data)
			$data = json_decode($data);
		$images = array();
		if(isset($data['query']) && isset($data['query']['pages']) && count($data['query']['pages']) > 0){
			foreach($data['query']['pages'] as $page){
				if(preg_match_all('~File:([\w\s[:punct:]]+(?:\.(?:jpg|jpeg)))~ui',$data['query']['pages'][0], $matches, PREG_PATTERN_ORDER)){
					
					foreach($matches[1] as $image){
						$images[] = $image;
					}
				}
			}
			return $images;
		}
		return false;
	}
	
	function getWikiSpeciesImage($title){
	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2");
			curl_setopt($ch, CURLOPT_URL, "http://species.wikimedia.org/w/api.php?action=query&prop=revisions&rvprop=content&format=json&titles=".urlencode($title) );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($ch);
			curl_close ($ch);
			if($data)
				$data = json_decode($data,true);
			//print_r($data);
			if(isset($data['query']) && isset($data['query']['pages']) && count($data['query']['pages']) > 0){
				$first = array_shift($data['query']['pages']);
				if( isset($first['missing']) || !isset($first['revisions']) ){
					return false; //invalid page title
				}
				$text = $first['revisions'][0]['*'];
				//echo $text;
				//echo preg_match('~\[\[(?:File|Image):(.+(?:\.(?:jpg|jpeg)))~u',$text,$matches);
				//print_r($matches);
				//exit;
				if(preg_match('~{{image\|(.+(?:\.(?:jpg|jpeg)))~ui',$text, $matches)){
					$image = $matches[1];
				}else if(preg_match('~\[\[(?:File|Image):(.+(?:\.(?:jpg|jpeg)))~ui',$text,$matches)){
					$image = $matches[1];
				}
			}else{
				//echo 'no pages returned';
			}
			if(isset($image))
				return $image;
			return false;
		}
}
?>