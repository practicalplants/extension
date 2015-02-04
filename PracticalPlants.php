<?php
global $wgDebugLogGroups;
$ppDir = $IP.'/extensions/PracticalPlants';
$wgDebugLogGroups += array(
	'practicalplants'			=> $ppDir.'logs/practicalplants.log',
 	'practicalplants-sso'		=> $ppDir.'logs/sso.log'
 );

//include('SSO_Auth.php');
//$wgAuth = PracticalPlants_SSO_Auth::getInstance();

$wgAutoloadClasses['PracticalPlants'] = dirname(__FILE__) . '/PracticalPlants_Body.php';
$wgAutoloadClasses['PracticalPlants_API'] = dirname(__FILE__) . '/api.php';
$wgAutoloadClasses['PracticalPlants_CommonsImages'] = dirname(__FILE__) . '/CommonsImages.php';
$wgAutoloadClasses['PPFormEditAction'] = dirname(__FILE__) . '/FormEditAction.php';


$ppResourceTemplate = array(
	'localBasePath' => dirname( __FILE__ ).'/resources',
	'remoteExtPath' => 'PracticalPlants/resources',
	'group' => 'ext.practicalplants'
);

$wgResourceModules += array(
  'ext.practicalplants.css'=>$ppResourceTemplate + array(
          'styles' => array(
          	'css/main.css'=>array('media'=>'screen'),
          	'css/print.css'=>array('media'=>'print'),
          	'../../../../resources/css/global.css'=>array('media'=>'screen'),
          	'../../../../resources/css/masthead.css'=>array('media'=>'screen')
          ),
          'position'=>'top'
 ),
	'modernizr' => $ppResourceTemplate + array(
        'scripts' => array( 'js/modernizr-1.7.min.js' ),
        'dependencies' => array( 'jquery.ui.autocomplete' )
	),
	'augment' => $ppResourceTemplate + array(
	    'scripts' => array( 'js/augment.js' )
	),
	'jquery.ui.autocomplete.html' => $ppResourceTemplate + array(
		'scripts' => array('js/jquery.ui.autocomplete-html.js'),
		'dependencies' => array( 'jquery.ui.autocomplete' )
	),
	'ext.practicalplants.init.dom' => $ppResourceTemplate + array(
		'scripts' => array(
			'js/practicalplants.init.dom.js',
			'js/practicalplants.js'),
		'dependencies' => array( 'modernizr','augment' ),
		'position' => 'top'
	),
	'browserupdate' => $ppResourceTemplate + array(
		'scripts'=> array('js/browserupdate.js')
	),
	'ext.practicalplants.init' => $ppResourceTemplate + array(
		'scripts' => array(
			'js/practicalplants.init.mast-search.js',
			'js/practicalplants.init.forms.js',
			'js/practicalplants.init.article.js',
			'../../../../resources/js/login-menu.js'),
		'dependencies' => array( 'jquery.ui.autocomplete.html', 'jquery.collapse','jquery.ui.tabs','ext.discover.js','jquery.ui.accordion','jquery.scrollto', 'mediawiki.api', 'jquery.qtip','bootstrap.js')
	),
	'ext.practicalplants.page.main'=> $ppResourceTemplate + array(
		'scripts' => array('js/practicalplants.page.main-discover.js'),
		'dependencies' => array( 'jquery.cookie')
	),
	'ext.practicalplants.page.search'=> $ppResourceTemplate + array(
		'scripts' => array('js/practicalplants.page.search-discover.js'),
		'dependencies' => array( 'jquery.cookie','ext.practicalplants.init')
	),
	'jquery.collapse' => $ppResourceTemplate + array(
		'scripts' => array('js/jquery.collapse.js'),
		'dependencies' => array( 'jquery.cookie','ext.practicalplants.init')
	),
	'jquery.scrollto' => $ppResourceTemplate + array(
		'scripts' => array('js/jquery.scrollto.min.js'),
		'dependencies' => array( 'jquery')
	),
	'jquery.qtip' => $ppResourceTemplate + array(
		'scripts' => array('js/jquery.qtip-1.0.0-rc3.min.js'),
		'dependencies' => array( 'jquery')
	)
);


$wgHooks['DoEditSectionLink'][] = 'PracticalPlants::doEditSectionLink';
$wgHooks['BeforePageDisplay'][] = 'PracticalPlants::loadResources';
$wgHooks['AlternateEdit'][] = 'PracticalPlants::loginToEdit';
$wgActions['formedit'] = 'PPFormEditAction'; //override SF formedit action to provide forward to login

$wgHooks['ParserAfterTidy'][] = 'PracticalPlants::parserAfterTidy';
$wgHooks['ParserFirstCallInit'][] = 'PracticalPlants::parserFirstCallInit';
$wgHooks['LanguageGetMagic'][] = 'PracticalPlants::languageGetMagic';
//$wgHooks['OutputPageParserOutput'][] = 'PracticalPlants::outputPageParserOutput';
$wgHooks['EditPage::showEditForm:initial'][] = 'PracticalPlants::onEditPage';

$wgHooks['sfEditFormPreloadText'][] = 'PracticalPlants::sfAddSpeciesChild';
$wgHooks['sfMultipleInstanceTemplateBeforeHTML'][] = 'PracticalPlants::sfMultipleInstanceTemplateBeforeHTML';
$wgHooks['sfMultipleInstanceTemplateAfterHTML'][] = 'PracticalPlants::sfMultipleInstanceTemplateAfterHTML';
$wgHooks['sfMultipleInstanceTemplateHTML'][] = 'PracticalPlants::sfMultipleInstanceTemplateHTML';
$wgHooks['sfMultipleInstanceTemplateInnerHTML'][] = 'PracticalPlants::sfMultipleInstanceTemplateInnerHTML';
$wgHooks['sfMultipleInstanceTemplateAdderHTML'][] = 'PracticalPlants::sfMultipleInstanceTemplateAdderHTML';

//$wgHooks['sfSetTargetName'][] = 'PracticalPlants::setSpeciesChildName';

//$wgHooks['LinkBegin'][] = 'PracticalPlants::linkBegin';
$wgHooks['LinkEnd'][] = 'PracticalPlants::linkEnd';

/* Enable API */
$wgAPIModules['taxonomies'] = 'PracticalPlants_API';
