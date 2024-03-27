<?php

namespace APP\plugins\blocks\twitterBlock;

use APP\core\Application;
use APP\plugins\blocks\twitterBlock\classes\SettingsForm;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\BlockPlugin;

class TwitterBlockPlugin extends BlockPlugin
{
    /**
     * @copydoc Plugin::isSitePlugin()
     */
    public function isSitePlugin()
    {
        return !Application::get()->getRequest()->getContext();
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName(): string
    {
        return __('plugins.blocks.twitter.title');
    }


    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDescription(): string
    {
        return __('plugins.blocks.twitter.desc');
    }

    /**
     * @copydoc BlockPlugin::getContents()
     */
    public function getContents($templateMgr, $request = null): string
    {
        $contextId = Application::get()->getRequest()->getContext()?->getId() ?: Application::CONTEXT_SITE;
        foreach (['tweetTitle', 'tweetUrl', 'tweetColor', 'tweetOptions', 'tweetHeight', 'tweetDataLimit'] as $setting) {
            $templateMgr->assign($setting, $this->getSetting($contextId, $setting));
        }

        return parent::getContents($templateMgr, $request);
    }

    /**
     * @copydoc Plugin::getActions()
     */
    public function getActions($request, $actionArgs): array
    {
        $actions = parent::getActions($request, $actionArgs);
        if (!$this->getEnabled()) {
            return $actions;
        }

        $router = $request->getRouter();
        $url = $router->url($request, null, null, 'manage', null, ['verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'blocks']);
        array_unshift($actions, new LinkAction('settings', new AjaxModal($url, $this->getDisplayName()), __('manager.plugins.settings')));
        return $actions;
    }

    /**
     * @copydoc Plugin::manage()
     */
    public function manage($args, $request): JSONMessage
    {
        if ($request->getUserVar('verb') !== 'settings') {
            return parent::manage($args, $request);
        }

        $form = new SettingsForm($this);
        if (!$request->getUserVar('save')) {
            $form->initData();
            return new JSONMessage(true, $form->fetch($request));
        }

        $form->readInputData();
        if (!$form->validate()) {
            return new JSONMessage(true, $form->fetch($request));
        }

        $form->execute();
        return new JSONMessage(true);
    }
}
