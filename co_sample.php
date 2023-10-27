<?php
class CSampleController extends CEntrataApp {

	protected $m_arrobjSamples;
	protected $m_arrPropertyPreferences;
	protected $m_arrobjSamplesForDisplay;
	
	public function initialize() {} 
	
	public function execute() {
		switch( $this->getRequestAction() ) {
			case 'view_sample_page':
				$this->handleViewSamplePage();
				break;
		}
	}
	
	/**
	* Handler functions
	**/
	
	public function handleViewSamplePage() {
		
		if( isset( $this->getRequestData( [ 'property_id' ] ) ) ) {
			$this->displayMessage( 'Invalid Request', 'Invalid property id. Please try again later.', '' );
			return;
		}
		
		if( isset( $this->getRequestData( [ 'customer_id' ] ) ) ) {
			$this->displayMessage( 'Invalid Request', 'Invalid customer id. Please try again later.', '' );
			return;
		}
		
		$intPropertyId = $this->getRequestData( [ 'property_id' ] );
		$intCustomerId = $this->getRequestData( [ 'customer_id' ] );
		
		$this->m_arrobjSamples = fetchSamplesByCustomerIdByPropertyIdByCid( $intCustomerId, $intPropertyId, $this->getCid(), $this->m_objDB );
		
		$this->m_arrPropertyPreferences = fetchPropertyPreferencesByKeysByPropertyIdByCid( [ 'DISPLAY_EXAMPLE_DETAILS', 'SHOW_IN_UI', 'SAMPLE_PRODUCT_PERMISSION' ], $intPropertyId, $this->getCid(), $this->m_objDB );
		
		$this->m_arrPropertyPreferences = rekey( 'key', $this->m_arrPropertyPreferences );
		
		$this->m_arrobjSamplesForDisplay = [];
		
		if( valArrKeyExists( $this->m_arrPropertyPreferences, 'SAMPLE_PRODUCT_PERMISSION', 1 ) ) {
			$this->m_arrobjSamplesForDisplay = fetchSampleForDisplay($this->m_arrobjSamples, $cid, $this->m_objAnotherDB, $this->m_arrPropertyPreferences, $this->m_arrobjSamplesForDisplay);
		}
		
		$this->displayViewSamplePage();
	}
	/**
	 * @param arrmix                                                        $arrObjSamples
	 * @param int                                                           $intCid
  	 * @param \CDatabase                                                    $ObjAnotherDB
    	 * @param array 							$ArrPropertyPreferences
	 * @param arrobj                                                        $ArrobjSamplesForDisplay
	 * @return array
	 */
	public function fetchSampleForDisplay($arrObjSamples, $intCid , $ObjAnotherDB, $ArrPropertyPreferences, $ArrobjSamplesForDisplay){
		$ArrobjSamplesForDisplay = [];
		foreach( $arrObjSamples as $objSample ) {
				$objExample = fetchExampleByIdByCid( $objSample->getExampleId(),$intCid, $ObjAnotherDB );
				
				if( valArrKeyExists( $ArrPropertyPreferences, 'DISPLAY_EXAMPLE_DETAILS', 1 ) ) {
					if( false == is_null( $objExample->getTitle() )){
					$objSample->setExampleTitle( $objExample->getTitle() );
					}
					if( false == is_null( $objExample->getDescription() )){
					$objSample->setExampleDescription( $objExample->getDescription() );
					}
				}
				
				if( valArrKeyExists( $ArrPropertyPreferences, 'SHOW_IN_UI', 1 ) && true == isset( $ArrobjSamplesForDisplay[$objSample->getId()] )) {
					
					$ArrobjSamplesForDisplay[$objSample->getId()] = $objSample;
				}
				
				if( $objSample->isDefaultSample() && true == isset( $ArrobjSamplesForDisplay[$objSample->getId()] ) ) {
					
					$ArrobjSamplesForDisplay[$objSample->getId()] = $objSample;
					
				}
			}
		return $ArrobjSamplesForDisplay;
	}
	
	/**
	* Other function
	**/

	
	/**
	* Display functions
	**/
	
	public function displayViewSamplePage() {
		$this->loadExitTags();

		$arrmixTemplateParameters['samples'] = $this->m_arrobjSamplesForDisplay;
	
		$this->setRenderTemplate( 'app_path/view_sample_page.tpl', $arrmixTemplateParameters );
	}
}
	
?>
