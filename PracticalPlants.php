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


$wgHooks['DoEditSectionLink'][] = 'PracticalPlants::doEditSectionLink';
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
