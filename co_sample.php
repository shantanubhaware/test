<?php
class CSampleController extends CEntrataApp {

	protected $m_arrmixAllInfo;

	public function initialize() {}

	/**
	 * @return bool
	 */
	public function execute() : bool {
		switch( $this->getRequestAction() ) {
			case 'view_sample_page':
				$this->handleViewSamplePage();
				break;

			default:
				trigger_error( __( 'Error: Failed to handle action.' ), E_USER_ERROR );
		}

		return true;
	}

	/**
	 * Handler functions
	 **/

	/**
	 * @return void
	 */
	public function handleViewSamplePage() : void {

		$boolIsValid = $this->validateViewSamplePage();

		if( false == $boolIsValid ) {
			$this->displayMessage( 'Invalid Request', 'Invalid customer id. Please try again later.', '' );
			return;
		}

		$this->m_arrmixAllInfo = $this->loadSamplePageInfo($this->getRequestData( [ 'customer_id' ] ), $intPropertyId, $this->getCid(), $this->m_objDB );

		$this->displayViewSamplePage();
	}

	/**
	 * Other function
	 **/

	/**
	 * @param int        $intCustomerId
	 * @param int        $intPropertyId
	 * @param int        $intClientId
	 * @param \CDatabase $objDatabase
	 * @return array
	 */
	public function loadSamplePageInfo( int $intCustomerId, int $intPropertyId, int $intClientId, CDatabase $objDatabase ) : array {

		$intPropertyId = $this->getRequestData( [ 'property_id' ] );

		$arrobjSamples = fetchSamplesByCustomerIdByPropertyIdByCid( $intCustomerId, $intPropertyId, $intClientId, $objDatabase );

		$arrstrPropertyPreferences = fetchPropertyPreferencesByKeysByPropertyIdByCid( [ 'DISPLAY_EXAMPLE_DETAILS', 'SHOW_IN_UI', 'SAMPLE_PRODUCT_PERMISSION' ], $intPropertyId, $intClientId, $objDatabase );

		$arrstrPropertyPreferences = rekey( 'key', $arrstrPropertyPreferences );

		$arrobjSamplesForDisplay = [];

		if( valArrKeyExists( $arrstrPropertyPreferences, 'SAMPLE_PRODUCT_PERMISSION', 1 ) ) {
			foreach( $arrobjSamples as $objSample ) {
				$objExample = fetchExampleByIdByCid( $objSample->getExampleId(), $intClientId, $this->m_objAnotherDB );

				if( valArrKeyExists( $arrstrPropertyPreferences, 'DISPLAY_EXAMPLE_DETAILS', 1 ) ) {
					$objSample->setExampleTitle( $objExample->getTitle() );
					$objSample->setExampleDescription( $objExample->getDescription() );
				}

				if( valArrKeyExists( $arrstrPropertyPreferences, 'SHOW_IN_UI', 1 ) ) {

					$arrobjSamplesForDisplay[$objSample->getId()] = $objSample;
				}

				if( $objSample->isDefaultSample() ) {
					$arrobjSamplesForDisplay['all_details'][$objSample->getId()] = $objSample;
				}
			}
		}

		return $arrobjSamplesForDisplay;

	}

	/**
	 * @return bool
	 */
	public function validateViewSamplePage() : bool {

		switch( NULL ) {
			default:
				$boolIsValid = false;

				if( !valId( $this->getRequestData( [ 'property_id' ] ) ) ) {
					break;
				}

				if( !valId( $this->getRequestData( [ 'customer_id' ] ) ) ) {
					break;
				}

				$boolIsValid = true;
		}

		return $boolIsValid;

	}


	/**
	 * Display functions
	 **/

	/**
	 * @return void
	 */
	public function displayViewSamplePage() : void {
		$this->loadExitTags();

		$arrmixTemplateParameters['samples'] = $this->m_arrmixAllInfo['all_details'] ?? [];

		$this->setRenderTemplate( 'app_path/view_sample_page.tpl', $arrmixTemplateParameters );
	}
}

?>
