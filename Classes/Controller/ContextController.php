<?php
namespace ThomasWoehlke\Gtd\Controller;

/***
 *
 * This file is part of the "Getting Things Done" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2016 Thomas Woehlke <thomas@woehlke.org>, faktura gGmbH
 *
 ***/

/**
 * Class ContextController
 *
 * @package ThomasWoehlke\Gtd\Controller
 */
class ContextController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * contextRepository
     *
     * @var \ThomasWoehlke\Gtd\Domain\Repository\ContextRepository
     * @inject
     */
    protected $contextRepository = null;

    /**
     * contextService
     *
     * @var \ThomasWoehlke\Gtd\Service\ContextService
     * @inject
     */
    protected $contextService = null;

    /**
     * projectRepository
     *
     * @var \ThomasWoehlke\Gtd\Domain\Repository\ProjectRepository
     * @inject
     */
    protected $projectRepository = null;

    /**
     * userAccountRepository
     *
     * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $userAccountRepository = null;

    private $extName = 'gtd';

    /**
     * action switchContext
     *
     * @return void
     */
    public function switchContextAction(\ThomasWoehlke\Gtd\Domain\Model\Context $context)
    {
        $sessionData = $GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_gtd_fesessiondata');
        $sessionData['contextUid'] = $context->getUid();
        $GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_gtd_fesessiondata', $sessionData);
        $GLOBALS['TSFE']->fe_user->storeSessionData();
        $this->myRedirect('inbox',array(),"Task");
    }

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $contexts = $this->contextRepository->findAll();
        $this->view->assign('contexts', $contexts);
        $this->view->assign('langKey',$this->getLanguageId());
    }

    /**
     * action show
     *
     * @param \ThomasWoehlke\Gtd\Domain\Model\Context $context
     * @return void
     */
    public function showAction(\ThomasWoehlke\Gtd\Domain\Model\Context $context)
    {
        $this->view->assign('context', $context);
        $this->view->assign('langKey',$this->getLanguageId());
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {
        $ctx = $this->contextService->getCurrentContext();
        $this->view->assign('contextList',$this->contextService->getContextList());
        $this->view->assign('currentContext',$ctx);
        $this->view->assign('rootProjects',$this->projectRepository->getRootProjects($ctx));
        $this->view->assign('langKey',$this->getLanguageId());
    }

    /**
     * action create
     *
     * @param \ThomasWoehlke\Gtd\Domain\Model\Context $newContext
     * @return void
     */
    public function createAction(\ThomasWoehlke\Gtd\Domain\Model\Context $newContext)
    {
        $userObject = $this->userAccountRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
        $newContext->setUserAccount($userObject);
        $this->contextRepository->add($newContext);
        //$this->addFlashMessage('The object was created.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
        $msg = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_gtd_flash.context.created', $this->extName, null);
        $this->addFlashMessage($msg, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
        $this->myRedirect('show',array(),'UserConfig');
    }

    /**
     * action edit
     *
     * @param \ThomasWoehlke\Gtd\Domain\Model\Context $context
     * @ignorevalidation $context
     * @return void
     */
    public function editAction(\ThomasWoehlke\Gtd\Domain\Model\Context $context)
    {
        $ctx = $this->contextService->getCurrentContext();
        $this->view->assign('mycontext', $context);
        $this->view->assign('contextList',$this->contextService->getContextList());
        $this->view->assign('currentContext',$ctx);
        $this->view->assign('rootProjects',$this->projectRepository->getRootProjects($ctx));
        $this->view->assign('langKey',$this->getLanguageId());
    }

    /**
     * action update
     *
     * @param \ThomasWoehlke\Gtd\Domain\Model\Context $context
     * @return void
     */
    public function updateAction(\ThomasWoehlke\Gtd\Domain\Model\Context $ctx)
    {
        $this->contextRepository->update($ctx);
        //$this->addFlashMessage('The object was updated.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
        $msg = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_gtd_flash.context.updated', $this->extName, null);
        $this->addFlashMessage($msg, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
        $this->myRedirect('show',array(),'UserConfig');
    }

    /**
     * action delete
     *
     * @param \ThomasWoehlke\Gtd\Domain\Model\Context $context
     * @return void
     */
    public function deleteAction(\ThomasWoehlke\Gtd\Domain\Model\Context $context)
    {
        $this->contextRepository->remove($context);
        //$this->addFlashMessage('The object was deleted.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $msg = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_gtd_flash.context.deleted', $this->extName, null);
        $this->addFlashMessage($msg, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->myRedirect('show',array(),'UserConfig');
    }

    /**
     * @return string
     */
    private function getLanguage(){

        $settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );

        return $settings['config.']['language'];
    }

    private function getLanguageId(){

        $settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );

        return $settings['config.']['sys_language_uid'];
    }

    /**
     * @param string $actionName
     * @param array $controllerArguments
     * @param string $controllerName
     */
    private function myRedirect($actionName='inbox',$controllerArguments=array(),$controllerName = 'Context'){
        $langId=$this->getLanguageId();
        $pid = $this->uriBuilder->getTargetPageUid();
        $this->uriBuilder->reset()->setArguments(array('L' => $langId))->setTargetPageUid($pid);
        $uri = $this->uriBuilder->uriFor($actionName, $controllerArguments,$controllerName);
        $this->redirectToUri($uri);
    }
}
