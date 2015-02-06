<?php
use SMW\MediaWiki\Api\Ask;

class PracticalPlants_Api extends Ask{

	protected function addQueryResult( SMWQueryResult $queryResult ) {
		$serialized = $queryResult->serializeToArray();
		$result = $this->getResult();

		$result->setIndexedTagName( $serialized['results'], 'result' );
		$result->setIndexedTagName( $serialized['printrequests'], 'printrequest' );
		
		foreach ( $serialized['results'] as $subjectName => $subject ) {
			if ( is_array( $subject ) && array_key_exists( 'printouts', $subject ) ) {
				foreach ( $subject['printouts'] as $property => $values ) {
					if ( is_array( $values ) ) {
						$result->setIndexedTagName( $serialized['results'][$subjectName]['printouts'][$property], 'value' );
					}
				}
			}
		}
				
		$output = array();
		header("Content-type: Application/JSON");
		foreach($serialized['results'] as $r){
			$common = (count($r['printouts']['Has common name'])>0) ? $r['printouts']['Has common name'][0]['fulltext'] : '';
			$taxo = (count($r['printouts']['Is taxonomy type'])>0) ? $r['printouts']['Is taxonomy type'][0]['fulltext'] : '';
			$output[$r['fulltext']] = array('taxonomy'=>$taxo,'common'=>$common);
		}
		echo json_encode($output);
		exit;
		
	}
}