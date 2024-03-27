<?php

namespace APP\plugins\blocks\twitterBlock\classes;

use APP\core\Application;
use APP\notification\Notification;
use APP\notification\NotificationManager;
use APP\plugins\blocks\twitterBlock\TwitterBlockPlugin;
use APP\template\TemplateManager;
use PKP\form\Form;
use PKP\form\validation\FormValidatorCSRF;
use PKP\form\validation\FormValidatorPost;

class SettingsForm extends Form
{
    /**
     * Constructor
     */
    public function __construct(public TwitterBlockPlugin $plugin)
    {
        parent::__construct($plugin->getTemplateResource('settings.tpl'));
        $this->plugin = $plugin;
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    /**
     * @copydoc Form::initData()
     */
    public function initData(): void
    {
        $contextId = Application::get()->getRequest()->getContext()?->getId() ?: Application::CONTEXT_SITE;
        foreach (['tweetTitle', 'tweetUrl', 'tweetColor', 'tweetOptions', 'tweetHeight', 'tweetDataLimit'] as $setting) {
            $this->setData($setting, $this->plugin->getSetting($contextId, $setting));
        }
        parent::initData();
    }

    /**
     * @copydoc Form::readInputData()
     */
    public function readInputData(): void
    {
        $this->readUserVars(['tweetTitle', 'tweetUrl', 'tweetColor', 'tweetOptions', 'tweetHeight', 'tweetDataLimit']);
        parent::readInputData();
    }

    /**
     * @copydoc Form::fetch()
     */
    public function fetch($request, $template = null, $display = false): string
    {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pluginName', $this->plugin->getName());
        return parent::fetch($request, $template, $display);
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        $request = Application::get()->getRequest();
        $contextId = $request->getContext()?->getId() ?: Application::CONTEXT_SITE;
        foreach (['tweetTitle', 'tweetUrl', 'tweetColor', 'tweetOptions', 'tweetHeight', 'tweetDataLimit'] as $setting) {
            $this->plugin->updateSetting($contextId, $setting, $this->getData($setting));
        }

        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification(
            $request->getUser()->getId(),
            Notification::NOTIFICATION_TYPE_SUCCESS,
            ['contents' => __('common.changesSaved')]
        );
        return parent::execute();
    }
}
