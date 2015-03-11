<?php

/**
 * Class Tx_Piwikintegration_Controller_PiwikController
 *
 * is the backend controller
 */
class Tx_Piwikintegration_Controller_PiwikController extends Tx_Extbase_MVC_Controller_ActionController {
	/**
	 * @var tx_piwikintegration_div
	 */
	protected $piwikHelper = NULL;

	/**
	 * @var int
	 */
	protected $id = 0;

	/**
	 * @return void
	 */
	public function initializeAction() {
		$this->id = (int)t3lib_div::_GP('id');
		$this->piwikHelper = t3lib_div::makeInstance('tx_piwikintegration_div');
	}

	/**
	 * @throws Exception
	 * @return void
	 */
	public function indexAction() {
		if ($this->checkPiwikEnvironment()) {
			$piwikSiteId   = $this->piwikHelper->getPiwikSiteIdForPid($this->id);
			$this->view->assign('piwikSiteId', $piwikSiteId);
			$this->piwikHelper->correctUserRightsForSiteId($piwikSiteId);
			$this->piwikHelper->correctTitle($this->id, $piwikSiteId, $this->piwikHelper->getPiwikConfigArray($this->id));
		}
	}

	/**
	 * shows the api code
	 * @return void
	 */
	public function apiCodeAction() {
		$this->view->assign('piwikApiCode', $GLOBALS['BE_USER']->user['tx_piwikintegration_api_code']);
		$this->view->assign('piwikBaseUri', tx_piwikintegration_install::getInstaller()->getBaseUrl());
		$tracker = new tx_piwikintegration_tracking();
		$this->view->assign('piwikTrackingCode', $tracker->getPiwikJavaScriptCodeForPid($this->id));
	}

	/**
	 * checks the piwik environment
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function checkPiwikEnvironment() {
		// check if piwik is installed
		if (!tx_piwikintegration_install::getInstaller()->checkInstallation()) {
			tx_piwikintegration_install::getInstaller()->installPiwik();
			if (tx_piwikintegration_install::getInstaller()->checkInstallation()) {
				$flashMessage = t3lib_div::makeInstance(
					't3lib_FlashMessage',
					'Piwik installed',
					'Piwik is now installed / upgraded, wait a moment, reload the page ;) <meta http-equiv="refresh" content="2; URL=mod.php?M=web_txpiwikintegrationM1&uid=' . $this->id  . '#reload">',
					t3lib_FlashMessage::OK
				);
				t3lib_FlashMessageQueue::addMessage($flashMessage);
			}
			return FALSE;
		}
		// check whether a page is selected
		if (!$this->id) {
			$flashMessage = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				'Please select a page in the pagetree',
				'',
				t3lib_FlashMessage::NOTICE
			);
			t3lib_FlashMessageQueue::addMessage($flashMessage);
			return FALSE;
		}
		$t = $this->piwikHelper->getPiwikConfigArray($this->id);
		// check whether a configured page is selected
		if (!isset($t['piwik_idsite']) || !$this->piwikHelper->getPiwikSiteIdForPid($this->id)) {
			$flashMessage = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				'Page is not configured. Did you include the Typoscript template?',
				'',
				t3lib_FlashMessage::NOTICE
			);
			t3lib_FlashMessageQueue::addMessage($flashMessage);
			return FALSE;
		}
		// check whether piwik_host is correct
		if (($t['piwik_host'] !== 'typo3conf/piwik/piwik/') && ($t['piwik_host'] !== '/typo3conf/piwik/piwik/')) {
			$flashMessage = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				'Piwik host is not set correctly',
				'',
				t3lib_FlashMessage::ERROR
			);
			t3lib_FlashMessageQueue::addMessage($flashMessage);
			return FALSE;
		}
		unset($t);
		// check if patch level is correct
		if (!tx_piwikintegration_install::getInstaller()->checkPiwikPatched()) {
			//prevent lost configuration and so the forced repair.
			$exclude = array(
				'config/config.ini.php',
			);
			tx_piwikintegration_install::getInstaller()->patchPiwik($exclude);
		}
		return TRUE;
	}
}