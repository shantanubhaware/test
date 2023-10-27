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
			$arrObjExampleIds = [];
			foreach( $this->m_arrobjSamples as $objSample ) {
				$arrObjExampleIds = $objSample->getExampleId();
				if( valArrKeyExists( $this->m_arrPropertyPreferences, 'SHOW_IN_UI', 1 ) ) {
					$this->m_arrobjSamplesForDisplay[$objSample->getId()] = $objSample;
				}
				if( $objSample->isDefaultSample() ) {
					$this->m_arrobjSamplesForDisplay[$objSample->getId()] = $objSample;
				}
			}

			$arrobjExample = fetchExampleByIdsByCid( $arrObjExampleIds, $this->getCid(), $this->m_objAnotherDB );
			foreach( $arrobjExample as $objExample ) {
				if( valArrKeyExists( $this->m_arrPropertyPreferences, 'DISPLAY_EXAMPLE_DETAILS', 1 ) ) {
					//to do need to update $objSample here
				}
			}
			
		}
		
		$this->displayViewSamplePage();
	}
	
	/**
	* Other function
	**/
	public function fetchExampleByIdsByCid( $arrObjExampleIds, $this->getCid(), $this->m_objAnotherDB ){
		//to do
		return $arrobjExample
	}
	
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
