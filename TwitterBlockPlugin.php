<?php

namespace APP\plugins\blocks\twitterBlock\classes;

use APP\core\Application;
use APP\plugins\blocks\twitterBlock\SettingsForm;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\BlockPlugin;

class TwitterBlockPlugin extends BlockPlugin
{
    // Enables plugin site-wide or in specific context; this must return true only if the user is in the site-wide context
    public function isSitePlugin()
    {
        return !Application::get()->getRequest()->getContext();
    }

    public function getDisplayName()
    {
        return __('plugins.blocks.twitter.title');
    }


    public function getDescription()
    {
        return __('plugins.blocks.twitter.desc');
    }

    public function getContents($templateMgr, $request = null)
    {
        $context = Application::get()->getRequest()->getContext();
        $contextId = ($context && $context->getId()) ? $context->getId() : Application::CONTEXT_SITE;
        $templateMgr->assign('tweetTitle', $this->getSetting($contextId, 'tweetTitle'));
        $templateMgr->assign('tweetUrl', $this->getSetting($contextId, 'tweetUrl'));
        $templateMgr->assign('tweetColor', $this->getSetting($contextId, 'tweetColor'));
        $templateMgr->assign('tweetHeight', $this->getSetting($contextId, 'tweetHeight'));
        $templateMgr->assign('tweetOptions', $this->getSetting($contextId, 'tweetOptions'));
        $templateMgr->assign('tweetDataLimit', $this->getSetting($contextId, 'tweetDataLimit'));
        return parent::getContents($templateMgr, $request);
    }

    public function getActions($request, $actionArgs)
    {
        $actions = parent::getActions($request, $actionArgs);
        if (!$this->getEnabled()) {
            return $actions;
        }
        $router = $request->getRouter();
        $linkAction = new LinkAction(
            'settings',
            new AjaxModal(
                $router->url(
                    $request,
                    null,
                    null,
                    'manage',
                    null,
                    [
                        'verb' => 'settings',
                        'plugin' => $this->getName(),
                        'category' => 'blocks'
                    ]
                ),
                $this->getDisplayName()
            ),
            __('manager.plugins.settings'),
            null
        );
        array_unshift($actions, $linkAction);
        return $actions;
    }

    public function manage($args, $request)
    {
        switch ($request->getUserVar('verb')) {
            case 'settings':
                $form = new SettingsForm($this);
                if (!$request->getUserVar('save')) {
                    $form->initData();
                    return new JSONMessage(true, $form->fetch($request));
                }
                $form->readInputData();
                if ($form->validate()) {
                    $form->execute();
                    return new JSONMessage(true);
                }
        }
        return parent::manage($args, $request);
    }
}
