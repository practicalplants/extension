<?php
class PracticalPlants{

	public static function init(){
		
	}

	public static function doEditSectionLink( $skin, $title, $section, $tooltip, $result, $lang = false ){
		$result = '';
		return true;
	}
		

	
	public static function loginToEdit($editpage){
	    global $wgUser;
	    if($wgUser->isAllowed( 'edit' )){
	        return true;
	    }
	    //echo '<pre>';print_r($page); exit;
	    //user doesn't have permission, so we redirect
	    PracticalPlants_SSO_Auth::getInstance()->redirectToLogin($editpage->getArticle());
	}
	
	public static function outputPageParserOutput($out,$parserout){
		//echo '<pre>';
		
		//print_r($out);//echo $parserout->mText;
		//exit;
	}
	public static function parserAfterTidy($parser,$text){
		/*if(preg_match('$<div id="article-summary">(.+)</div>$i', $text, $matches)){
			$text = preg_replace('$<div id="article-summary">(.+)</div>$i', '', $text);
			$article_summary = $matches[0];
			//$text = preg_replace('$(<h1 id="article-title">.+</h1>)$i','$1'+$article_summary,$text);
		}
		if(preg_match('$<div id="article-image">(.+)</div>$i', $text, $matches)){
			$text = preg_replace('$<div id="article-image">(.+)</div>$i', '', $text);
			$article_image = $matches[0];
		}
		if(preg_match('$<div id="article-state">(.+)</div>$i', $text, $matches)){
			$text = preg_replace('$<div id="article-state">(.+)</div>$i', '', $text);
			$article_state = $matches[0];
		}*/
		return true;
	}
	
	public static function parserFirstCallInit(&$parser){
		$parser->setFunctionHook('pask', 'PracticalPlants::pAsk');
		$parser->setFunctionHook('striptags', 'PracticalPlants::striptags');
		$parser->setFunctionHook('plant name', 'PracticalPlants::plantName');
		$parser->setFunctionHook('group items', 'PracticalPlants::groupItems');
		$parser->setFunctionHook('template escape', 'PracticalPlants::escapeForTemplateArgument');
		$parser->setFunctionHook('case', 'PracticalPlants::changeCase');
		
		//$parser->setHook('group items','PracticalPlants::groupItemsTag');
		return true;
	}
	
	public static function languageGetMagic(&$magicWords){
		$magicWords['pask'] = array(0,'pask');
		$magicWords['plant name'] = array(0,'plant name');
		$magicWords['group items'] = array(0,'group items');
		$magicWords['template escape'] = array(0,'template escape');
		$magicWords['case'] = array(0,'case');
		$magicWords['striptags'] = array(0,'striptags');
		return true;
	}
	
	
	public static function pAsk($parser,$property_name){
		$args = func_get_args();
		$parser = array_shift($args);
		$property_name = array_shift($args);
		$substring = null;
		$format = 'list';
		$default = 'None listed.';
		$class = '';
		$link = true;
		foreach($args as $arg){
			$arg = explode('=',$arg);
			if(is_array($arg)){
				switch($arg[0]){
					case 'format':
						if(in_array($arg[1], array('ul','list')) ){
							$format = $arg[1];
						}
						break;
					case 'link':
						$link = ($arg[1]=='false' || $arg[1]=='no' || $arg[1]==false) ? false : true;
						break;
					case 'substring':
						$substring = $arg[1];
						break;
					case 'default':
						$default = $arg[1];
						break;
					case 'class':
						$class = $arg[1];
						break;
				}
			}
		}
		
		$properties = self::getAllValuesForProperty($property_name,$substring); //SF_AutocompleteAPI::getAllValuesForProperty($property_name,$substring)
		
		foreach($properties as &$property){
			if($link === true){
				$property = '[['.$property.']]';
			}
		}
		if(count($properties) > 0){
			switch($format){
				case 'ul':
					$return = '<ul class="'.$class.'"><li>'.implode('</li><li>',$properties).'</li></ul>';
				break;
				default:
				case 'list':
					$return = implode(', ',$properties);
			}
		}else{
			$return = $default;
		}
		return array( $return, 'noparse' => false );
	}
	
	private static function getAllValuesForProperty( $property_name, $substring, $base_property_name = null, $base_value = null ) {
	
		$values = array();
		$db = wfGetDB( DB_SLAVE );
		$sql_options = array();
		$sql_options['LIMIT'] = 500;//$limit;

		$property = SMWPropertyValue::makeUserProperty( $property_name );
		$is_relation = ( $property->getPropertyTypeID() == '_wpg' );
		$property_name = str_replace( ' ', '_', $property_name );
		$conditions = array( 'p_ids.smw_title' => $property_name );

		if ( $is_relation ) {
			$value_field = 'o_ids.smw_title';
			$from_clause = $db->tableName( 'smw_rels2' ) . " r JOIN " . $db->tableName( 'smw_ids' ) . " p_ids ON r.p_id = p_ids.smw_id JOIN " . $db->tableName( 'smw_ids' ) . " o_ids ON r.o_id = o_ids.smw_id";
		} else {
			$value_field = 'a.value_xsd';
			$from_clause = $db->tableName( 'smw_atts2' ) . " a JOIN " . $db->tableName( 'smw_ids' ) . " p_ids ON a.p_id = p_ids.smw_id";
		}

		if ( !is_null( $base_property_name ) ) {
			$base_property = SMWPropertyValue::makeUserProperty( $base_property_name );
			$base_is_relation = ( $base_property->getPropertyTypeID() == '_wpg' );

			$base_property_name = str_replace( ' ', '_', $base_property_name );
			$conditions['base_p_ids.smw_title'] = $base_property_name;
			$main_prop_alias = ( $is_relation ) ? 'r' : 'a';
			if ( $base_is_relation ) {
				$from_clause .= " JOIN " . $db->tableName( 'smw_rels2' ) . " r_base ON $main_prop_alias.s_id = r_base.s_id";
				$from_clause .= " JOIN " . $db->tableName( 'smw_ids' ) . " base_p_ids ON r_base.p_id = base_p_ids.smw_id JOIN " . $db->tableName( 'smw_ids' ) . " base_o_ids ON r_base.o_id = base_o_ids.smw_id";
				$base_value = str_replace( ' ', '_', $base_value );
				$conditions['base_o_ids.smw_title'] = $base_value;
			} else {
				$from_clause .= " JOIN " . $db->tableName( 'smw_atts2' ) . " a_base ON $main_prop_alias.s_id = a_base.s_id";
				$from_clause .= " JOIN " . $db->tableName( 'smw_ids' ) . " base_p_ids ON a_base.p_id = base_p_ids.smw_id";
				$conditions['a_base.value_xsd'] = $base_value;
			}
		}

		if ( !is_null( $substring ) ) {
			$conditions[] = SFUtils::getSQLConditionForAutocompleteInColumn( $value_field, $substring );
		}

		$sql_options['ORDER BY'] = $value_field;
		$res = $db->select( $from_clause, "DISTINCT $value_field",
			$conditions, __METHOD__, $sql_options );

		while ( $row = $db->fetchRow( $res ) ) {
			$values[] = str_replace( '_', ' ', $row[0] );
		}
		$db->freeResult( $res );

		return $values;
	}
	
	public static function striptags($parser,$html){
		//echo preg_replace('~\[\[[^|]+(?:|(.+))\]\]~uis','$2', strip_tags($html));
		//return $html;
		//echo $html; 
		$replace = array('\[\[SWM::on\]\]'=>'','\[\[SMW::off\]\]'=>'');
		if(preg_match_all('~\[\[([^|\]]+(?:\|([^\]]+))?)\]\]~uis',$html,$matches)){
			//print_r($matches); //return true;
			foreach($matches[0] as $i => $m){
				$pattern = '\[\['.str_replace('|','\|',$matches[1][$i]).'\]\]';
				if(!strpos($matches[1][$i],'SWM')){
					$replace[$pattern] = !empty($matches[2][$i]) ? $matches[2][$i] : $matches[1][$i];
				}
			}
			foreach($replace as $pattern => $replacement){
				//echo 'Replacing '.$pattern.' with '.$replacement."\n";
				$html = preg_replace('~'.$pattern.'~ui',$replacement,$html);
			}
		}
		return strip_tags( $html );
		//return '--STRIPTAGS--'.$html.'--/STRIPTAGS--';
	}
	function striptags_parser(){
	
	}
	
	/* {{#plant name:}} parserfunction to format a plant species name automatically */
	public static function plantName($parser,$name){
		$arguments = func_get_args();
		$arguments = array_slice($arguments, 2);
		$options = array();
		foreach($arguments as $arg){
			$o = explode('=',$arg);
			if(!empty($o) && count($o)===2){
				$options[str_replace(' ','_',trim($o[0]))] = trim($o[1]);
			}
		}
		
		return self::formatSpeciesName($name,$options);
	}
	
	/* Format a plant name */
	public static function formatSpeciesName($name,$options=array()){
		$settings = array(
			'abbreviate_binomial' => false
		);
		$options = array_merge($settings, $options);
		
		/* Check to see if there's a standard binomial to begin with, and if so apply formatting */
		if(preg_match('~^([\w]+ (?:x )?[\w-]+)~', $name, $matches)){
			if($options['abbreviate_binomial']==true){
				$binom_parts = explode(' ',$matches[1]);
				if(count($binom_parts)>1){
					$binom = substr($binom_parts[0], 0, 1) .'. '. implode(' ',array_slice($binom_parts,1));
				}else{
					$binom = $matches[1];
				}
			}else{
				$binom = $matches[1];
			}
			
			$binomial = '<em class="binomial">'.$binom.'</em>';
		}else{
			return $name;
		}
		$name = html_entity_decode($name, ENT_QUOTES);
		/*echo $name."<br>\n"; 
		preg_match("~^(?:[\w]+ (?:x )?[\w]+)(?: \(?[[:upper:]]{1}[\w\s]+ Group\)?)?(?: ?('[\w\s]+')?)?$~", $name, $matches);
		print_r($matches); exit;*/
		
		if(preg_match('~^([\w]+ (?:x )?[\w-]+)$~', $name, $matches)){
			$name = $binomial;
			
		/*match binomial name with cultivar and/or cultivar group. Eg. 
		* Brassica oleracea Capitata Group 
		* Brassica oleracea 'January King'
		* Brassica oleracea (Capitata Group) 'January King'
		* Brassica oleracea Capitata Group 'January King'
		*/
		}else if(preg_match("~^(?:[\w]+ (?:x )?[\w-]+)(?: \(?[[:upper:]]{1}[\w\s]+ Group\)?)?(?: ?('[\w\s]+')?)?$~", $name, $matches)){
			
			array_shift($matches);
			if(count($matches)===3){
				$name = $binomial;
				$name .= ' <span class="cultivar-group">'.$matches[2].'</span>';
				$name .= ' <span class="cultivar">'.$matches[3].'</span>';
			}else{
				//match binomial name and cultivar group
				if(preg_match("~^([\w]+ (?:x )?[\w-]+) (\(?[[:upper:]]{1}[\w\s]+ Group\)?)$~", $name, $matches)){
					$name = $binomial;
					$name .= ' <span class="cultivar-group">'.$matches[2].'</span>';
				
				//match binomial name and cultivar
				}else if(preg_match("~^([\w]+ (?:x )?[\w]+) ('[\w\s]+')$~", $name, $matches)){
					$name = $binomial;
					$name.= ' <span class="cultivar">'.$matches[2].'</span>';
				}
			}
			
		/* Match binomial name and variety. Eg.
		* Malus domestica var. varietyname
		* Malus domestica var varietyname		
		*/
		}else if(preg_match("~^(?:[\w]+ (?:x )?[\w-]+) var\.? ([\w]+)$~", $name, $matches)){
			$name = $binomial;
			$name.= ' <span class="variety-var">var.</span>';
			$name.= ' <em class="variety">'.$matches[1].'</em>';
			

		/* Match binomial name and subspecies. Eg.
		* Malus domestica ssp. varietyname
		* Malus domestica subsp. varietyname		
		*/
		}else if(preg_match("~^(?:[\w]+ (?:x )?[\w-]+) (?:subsp\.?|ssp\.?) ([\w]+)$~", $name, $matches)){
			$name = $binomial;
			$name.= ' <span class="subspecies-ssp"> ssp. </span>';
			$name.= ' <em class="subspecies">'.$matches[1].'</em>';
			
		}else{
			$name = $name;
		}
		
		return '<span class="plant-name">'.$name.'</span>';
	}
	
	public static function groupItems($parser,$group_tpl,$item_tpl,$input){	
		global $wgHooks;
				
		$args = array_slice(func_get_args(),3);
		//echo '<pre>'; print_r($args); echo '</pre><br><br>'; 
		$groups = array();
		$output = '';

		$args = explode(':|:',$input);
		foreach($args as $i => &$a){
			$a = trim($a);
			if(empty($a))
				unset($args[$i]);
		}
		
		if(count($args)>0){
			//echo '<pre>'; echo $group_tpl; echo $item_tpl; print_r($args);
			//exit;
			//iterate through item arguments separated by semi-colons eg: what;whaaat;omgz
			foreach($args as $item){
				if(empty($item))
					continue;
				$item = explode(':;:',$item);
				if(is_array($item) && count($item) > 1)			
					$groups[$item[0]][] = $item;
			}
			if(empty($groups))
				return '';
					
			foreach($groups as $title => $group){
				$items = array();
				foreach($group as $item){
					$items[] = '{{'.$item_tpl.'|'.implode('|',$item).'}}';
				}
				$group_render = '{{'.$group_tpl.'|title='.$title.'|items='.implode('',$items).' }}';
				$output.= $group_render."\n";
			}
			//echo "<pre>"; print_r($output); echo "</pre>\n\n\n<br><br><br>";
			//echo $output; exit;
			return array($output,'noparse'=>false);//,'isHTML' => true);
		}else{
			return true;
		}
	}
	
	public static function parseGroup($group_template,$item_template,$groups){
	
	}
	
	/* Replaces all instances of = and | with a strip marker to allow them to be passed in arguments to a template without being mistaken for mediawiki control characters */
	public static function escapeForTemplateArgument($parser){
		$args = func_get_args();
		array_shift($args);
		$i = implode('|', $args); 
		//echo $i."<br><br>\n\n";
		while ( $pos = strpos($i,'=')){
			$i = substr_replace($i, $parser->insertStripItem( '=', $parser->mStripState ), $pos, 1);
		}
		//while ( $pos = strpos($i,'|')){
		//	$i = substr_replace($i, $parser->insertStripItem( '|', $parser->mStripState ), $pos, 1);
		//}
		//echo $i."<br><br><hr><br><br>\n\n\n\n\n\n";
		return $i;
	}
	
	/*function groupItemsTag($content, $args, $parser){
		//group template
		//item template
		//items
		$group_template = false; 
		$item_template = false; 
		$items = false;
		if(preg_match('~<group template>(.*)</group template>~si',$content,$matches))
			$group_template = $matches[1];
		if(preg_match('~<item template>(.*)</item template>~si',$content,$matches))
			$item_template = $matches[1];
		if(preg_match('~<items>(.*)</items>~si',$content,$matches))
			$items = $matches[1];
		if(!$group_template || !$item_template)
			return 'groupitems error: Group or item template missing.';
		if(!$items)
			return 'groupitems error: No items supplied.';
		$items = $parser->recursiveTagParse($items);
		echo $group_template."\n\n\n".$item_template."\n\n\n\n".$items;
		exit;
		
	}*/
/*
	{{#group items:
	format=HHHH$1 ($2)HHHH
	$3
	part used=what;part used for=whaaat;part use details=omgz|
	part used=what;part used for=whaaat;part use details=omgz|
	}}
	*/
	
	
	public static function changeCase($parser,$type,$string){
		if(empty($type) || empty($string))
			return '';
		switch($type){
			case 'upper first':
				return ucfirst($string);
			case 'upper words':
				return ucwords($string);
			case 'upper':
				return strtoupper($string);
			case 'lower':
				return strtolower($string);
		}
		return $string;
	}
	
	public static function onEditPage($editPage){
		global $wgParser;
		if(!$editPage->getTitle()->exists() && $editPage->getTitle()->getNamespace()===0){
		
			$editPage->editFormTextTop .= '<div class="tabify tabify-style-boxed">';
			$list_title = Title::newFromText('Edit new article');
			$id = $list_title->getArticleID();
			if($id){
				$article = Article::newFromId($id);
				$text = $article->getRawText();
				$parseroutput = $article->getParserOutput();
				
		
				$text = $wgParser->parse($text,$editPage->getTitle(),new ParserOptions)->getText();
				$editPage->editFormTextTop .= '<div class="tabify-tab">';
				$editPage->editFormTextTop .= $text;//$parseroutput->getText();
				$editPage->editFormTextTop .= '</div>';
			}
			/*$editPage->editFormPageTop .= '<h1>Create a new article using a form</h1>
			<p>This is the recommended way to create a new article. You need to decide which form best describes the article from the list below.</p>';
			$editPage->editFormTextTop .= $text;//$parseroutput->getText();
			$editPage->editFormTextBeforeContent .= '<h1>Create a new article using free-text</h1><p><em>If you really know what you\'re doing</em>, you can use the free-text box below to create your article. You will need an intimate knowledge of the templates and parser functions Practical Plants uses to create it\'s layouts. There isn\'t currently any documentation on this but you can poke around in the source of other articles for examples and ask any questions on the Forums.</p><br>';
			$editPage->editFormTextBeforeContent .= $wgParser->parse('<headertabs />',$editPage->getTitle(),new ParserOptions)->getText();*/
					
			$list_title = Title::newFromText('Edit new article with wikitext');
			$id = $list_title->getArticleID();
			if($id){
				$article = Article::newFromId($id);
				$text = $article->getRawText();
				$parseroutput = $article->getParserOutput();
				
				//to get parser:
				/*$title = $editPage->getArticle();
				$wikipage = Title::factory($title);*/
				
				$text = $wgParser->parse($text,$editPage->getTitle(),new ParserOptions)->getText();
				$editPage->editFormTextTop .= '<div class="tabify-tab">';
				$editPage->editFormTextTop .= $text;//$parseroutput->getText();
				//$editPage->editFormTextBeforeContent = '3';
				//$editPage->editFormTextAfterWarn .= '4';
				//$editPage->editFormTextAfterTools .= '5';
			}
			
			
			$editPage->editFormTextBottom .= '</div></div>';
		
		}
		
		
		
		return true;
		
	}
	
	
	//preload data for cultivars/cultivar groups/varieties/subspecies, where a parent species is specified	
	public static function sfAddSpeciesChild(&$page_contents, $page_title, $form_title){
		$page_title = $form_title;
		$form_title = $page_title;
		//echo '<pre>'; print_r(func_get_args()); print_r($_GET); exit;
		if(isset($_GET['extend']))
			$extend = str_replace('_',' ',$_GET['extend']);
		if(isset($_GET['child_type'])){
			$child_type = strtolower($_GET['child_type']);
		}else if(isset($_GET['title'])){
			if( preg_match('~^Special:FormEdit/([\w]+)(/([\w]+))?$~',$_GET['title'],$matches) ){
				//$extend = 
				$child_type=strtolower($matches[1]);
			}
		}		
		if(!isset($extend) || !isset($child_type))
			return true;
		$list_title = Title::newFromText($extend);
		$id = $list_title->getArticleID();
		if(!$id)
			return true;
		$article = Article::newFromId($id);
		$text = $article->getRawText();
		
		$text = self::removeField($text,array('common','primary image'));
		$preload = '';
		//fields which, if present in the plant template, should be removed. These are fields which a small minority of pages will have which were used previously but now cause problems when extending
		$bad_fields = array('cultivar of','cultivar name','show cultivar group','cultivar group','is a variety','variety type','variety name','variety of','subspecies name','subspecies of');
		//$bad_fields=array();
		//echo $text; exit;
		switch($child_type){
			case 'cultivar_group':
				if(!strstr($text,"{{Plant\n|")) //make sure there's an instance of the plant template in the page we're extending
					return true;
				$text = self::removeField($text,$bad_fields);
				$preload = str_replace("{{Plant\n|", "{{Cultivar group\n|cultivar group of=".$extend.'|', $text);
				break;
			case 'cultivar':
				if(strstr($text,"{{Plant\n|")){
					$text = self::removeField($text,$bad_fields);
					$preload = str_replace("{{Plant\n|", "{{Cultivar\n|cultivar of=".$extend."\n|", $text);
				}elseif(strstr($text,"{{Cultivar group\n|")){
					$text = self::removeField($text,$bad_fields);
					$preload = str_replace("{{Cultivar group\n|", "{{Cultivar\n|cultivar of=".$extend."\n|", $text);
				}else{
					return true;
				}
				break;
			case 'variety':
				$preload = str_replace("{{Plant\n|", '{{Variety', $text);
			case 'subspecies':
				$preload = str_replace("{{Plant\n|", '{{Subspecies', $text);
				break;
		}
			
		if(isset($preload))
			$page_contents = $preload;
		//echo $preload; exit;
		
		return true;
	}
	
	public static function removeField($text,$f){
		if(is_array($f)){
			foreach($f as $field){
				$text = self::removeField($text,$field);
			}
			return $text;
		}elseif(is_string($f)){
			return preg_replace("~\|".$f."=.*\n?\|~",'|',$text);
		}else{
			return $text;
		}
	}
	
	public static function setSpeciesChildName(&$mTarget, $query ){
		if(strstr($query, 'Cultivar_group')){
			if($_GET['extend']){
				switch($_GET['child_type']){
					case 'cultivar_group':
						$mTarget='Cultivar group test';
						break;
					case 'cultivar':
					case 'variety':
					case 'subspecies':
						$mTarget='Other test yo';
						break;
				}
			}
			//echo '<pre>'; print_r($_GET); print_r(func_get_args()); 
			//exit;
		}
		//if($_GET()
		
		
		return true;
	}
	
	public static function linkBegin($dummy, $target, $options, &$html, $attribs, $ret){
		//$html['href'] = str_replace('%27',"'",$html['href']);
		//$attribs=array('href'=>'/Lol','title'=>'lol');
		//$html['href'] = '/lol';
		//$html['title']= 'Lol';
		//echo '<pre>'; print_r(func_get_args()); echo '</pre>'; 
		return true;
	}
	
	public static function linkEnd($dummy, $target, $options, $html, $attribs, $ret){
		$attribs['href'] = str_replace('%27',"'",$attribs['href']);
		//$html=array('href'=>'/Lol','title'=>'lol');
		//$html['href'] = '/lol';
		//$html['title']= 'Lol';
		//echo '<pre>'; print_r( $attribs ); echo '</pre>'; 
		return true;
	}


	public static function sfMultipleInstanceTemplateBeforeHTML(&$html, $template){
		if($template->template_name === 'Reference'){
			$html = '
			<div class="multipleTemplateWrapper">
			<table class="table">
			<thead>
				<th class="ref-type">Type</th>
				<th class="ref-id">Identifier</th>
				<th class="ref-author">Author</th>
				<th class="ref-title">Title</th>
				<th class="ref-source">Source</th>
				<th class="ref-url">URL/ISBN</th>
				<th class="ref-date">Date</th>
				<th class="ref-buttons"></th>
			</thead>
			<tbody class="multipleTemplateList">';
		}
		return true;
	}
	public static function sfMultipleInstanceTemplateAfterHTML(&$html, &$button, $template){
		if($template->template_name === 'Reference'){
			$html = '</tbody></table>';
			$html .= $button;
			$html .= '</div>';
		}
		return true;
	}
	public static function sfMultipleInstanceTemplateHTML(&$html, $content, $template){
		//$html = '<tr class="multipleTemplateInstance">'.$content.'</tr>';
		if($template->template_name === 'Reference'){
			$html = '<tr class="multipleTemplateInstance multipleTemplate">'.$content.'</tr>';
		}
		return true;
	}

	public static function sfMultipleInstanceTemplateAdderHTML(&$html, $content, $template){
		if($template->template_name === 'Reference'){
			$html = '<tr class="multipleTemplateStarter" style="display:none">'.$content.'</tr>';
		}
		return true;
	}

	public static function sfMultipleInstanceTemplateInnerHTML(&$html, $section, $template){
		global $sfgScriptPath;
		if($template->template_name === 'Reference'){
		$html =   str_replace('-!/td!-','</td>',str_replace('-!td!-', '<td>', $section)).'<td>'
				. '<span class="removeButton"><a class="btn btn-link remover"><i class="icon-remove"></i></a></span>'
				. '<span class="instanceRearranger"><img src="'.$sfgScriptPath.'/skins/rearranger.png" class="rearrangerImage"></span>'
				. '</td>';
		}
		return true;
	}

}

?>