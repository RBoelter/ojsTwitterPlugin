<?php

namespace APP\plugins\blocks\twitterBlock;

use APP\core\Application;
use APP\notification\Notification;
use APP\notification\NotificationManager;
use APP\template\TemplateManager;
use PKP\form\Form;
use PKP\form\validation\FormValidatorCSRF;
use PKP\form\validation\FormValidatorPost;

class SettingsForm extends Form
{
    public $plugin;

    public function __construct($plugin)
    {
        parent::__construct($plugin->getTemplateResource('settings.tpl'));
        $this->plugin = $plugin;
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    /**
     * Load settings already saved in the database
     *
     * Settings are stored by context, so that each journal or press
     * can have different settings.
     */
    public function initData()
    {
        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $contextId = ($context && $context->getId()) ? $context->getId() : Application::CONTEXT_SITE;
        $this->setData('tweetTitle', $this->plugin->getSetting($contextId, 'tweetTitle'));
        $this->setData('tweetUrl', $this->plugin->getSetting($contextId, 'tweetUrl'));
        $this->setData('tweetColor', $this->plugin->getSetting($contextId, 'tweetColor'));
        $this->setData('tweetOptions', $this->plugin->getSetting($contextId, 'tweetOptions'));
        $this->setData('tweetHeight', $this->plugin->getSetting($contextId, 'tweetHeight'));
        $this->setData('tweetDataLimit', $this->plugin->getSetting($contextId, 'tweetDataLimit'));
        parent::initData();
    }

    /**
     * Load data that was submitted with the form
     */
    public function readInputData()
    {
        $this->readUserVars(['tweetTitle', 'tweetUrl', 'tweetColor', 'tweetOptions', 'tweetHeight', 'tweetDataLimit']);
        parent::readInputData();
    }

    /**
     * Fetch any additional data needed for your form.
     *
     * Data assigned to the form using $this->setData() during the
     * initData() or readInputData() methods will be passed to the
     * template.
     *
     * @param null $template
     * @param bool $display
     *
     * @return string|null
     */
    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pluginName', $this->plugin->getName());
        return parent::fetch($request, $template, $display);
    }

    /**
     * Save the settings
     *
     * @return mixed|null
     */
    public function execute(...$functionArgs)
    {
        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $contextId = ($context && $context->getId()) ? $context->getId() : Application::CONTEXT_SITE;
        $this->plugin->updateSetting($contextId, 'tweetTitle', $this->getData('tweetTitle'));
        $this->plugin->updateSetting($contextId, 'tweetUrl', $this->getData('tweetUrl'));
        $this->plugin->updateSetting($contextId, 'tweetColor', $this->getData('tweetColor'));
        $this->plugin->updateSetting($contextId, 'tweetOptions', $this->getData('tweetOptions'));
        $this->plugin->updateSetting($contextId, 'tweetHeight', $this->getData('tweetHeight'));
        $this->plugin->updateSetting($contextId, 'tweetDataLimit', $this->getData('tweetDataLimit'));
        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification(
            $request->getUser()->getId(),
            Notification::NOTIFICATION_TYPE_SUCCESS,
            ['contents' => __('common.changesSaved')]
        );
        return parent::execute();
    }
}
