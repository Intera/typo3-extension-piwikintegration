<?php

/**
 * Class Tx_Piwikintegration_Controller_PiwikInstallationController
 *
 * controller to run the installation of piwik in several seperated steps to avoid timeouts
 */
class Tx_Piwikintegration_Controller_PiwikInstallationController extends Tx_Extbase_MVC_Controller_ActionController {
	/**
	 * @var int
	 */
	protected $id = 0;

	/**
	 * @return void
	 */
	public function initializeAction() {
		$this->id = (int)t3lib_div::_GP('id');
	}

	/**
	 * @return void
	 */
	public function indexAction() {
		$this->showAndRedirect('download', 'Downloaded');
	}

	/**
	 * @return void
	 */
	public function downloadAction() {
		$this->showAndRedirect('patch', 'Patched');
	}

	/**
	 * @return void
	 */
	public function patchAction() {
		$this->showAndRedirect('configure', 'Configured');
	}

	/**
	 * @throws Tx_Extbase_MVC_Exception_UnsupportedRequestType
	 * @return void
	 */
	public function configureAction() {
		$this->redirect('apiCode', 'Piwik');
	}

	/**
	 * @param $action
	 * @param $message
	 * @throws Tx_Extbase_MVC_Exception_UnsupportedRequestType
	 * @return void
	 */
	protected function showAndRedirect($action, $message) {
		$this->flashMessageContainer->add(
			$message
		);
		$this->redirect(
			$action,
			NULL,
			NULL,
			NULL,
			$this->id,
			10
		);
	}
}