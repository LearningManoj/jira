<?php

namespace Technodelight\Jira\Console;

use Symfony\Component\Console\Application as BaseApp;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Technodelight\Jira\Console\Command\TodoCommand;
use Technodelight\Jira\Console\Command\ListWorkInProgressCommand;

use Technodelight\Jira\Configuration\Configuration;
use Technodelight\Jira\Configuration\GlobalConfiguration;

use Technodelight\Jira\Helper\GitHelper;
use Technodelight\Jira\Helper\DateHelper;
use Technodelight\Jira\Helper\TemplateHelper;
use Technodelight\Jira\Helper\GitBranchnameGenerator;

use Technodelight\Jira\Api\Client as JiraClient;

class Application extends BaseApp
{
    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var GitHelper
     */
    protected $gitHelper;

    /**
     * @var DateHelper
     */
    protected $dateHelper;

    /**
     * @var GitBranchnameGenerator
     */
    protected $gitBranchnameGenerator;

    /**
     * @var TemplateHelper
     */
    protected $templateHelper;

    /**
     * @var JiraClient
     */
    protected $jira;

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new TodoCommand;
        $commands[] = new ListWorkInProgressCommand;
        return $commands;
    }

    /**
     * @return Configuration
     */
    public function config()
    {
        if (!isset($this->config)) {
            // init configuration
            $config = GlobalConfiguration::initFromDirectory(getenv('HOME'));
            $projectConfig = Configuration::initFromDirectory($this->git()->topLevelDirectory());
            $config->merge($projectConfig);
            $this->config = $config;
        }

        return $this->config;
    }

    /**
     * @return GitHelper
     */
    public function git()
    {
        if (!isset($this->gitHelper)) {
            $this->gitHelper = new GitHelper;
        }

        return $this->gitHelper;
    }

    /**
     * @return GitBranchnameGenerator
     */
    public function gitBranchnameGenerator()
    {
        if (!isset($this->gitBranchnameGenerator)) {
            $this->gitBranchnameGenerator = new GitBranchnameGenerator;
        }

        return $this->gitBranchnameGenerator;
    }

    /**
     * @return TemplateHelper
     */
    public function templateHelper()
    {
        if (!isset($this->templateHelper)) {
            $this->templateHelper = new TemplateHelper;
        }

        return $this->templateHelper;
    }

    /**
     * @return DateHelper
     */
    public function dateHelper()
    {
        if (!isset($this->dateHelper)) {
            $this->dateHelper = new DateHelper;
        }

        return $this->dateHelper;
    }

    /**
     * @return JiraClient
     */
    public function jira()
    {
        if (!isset($this->jira)) {
            $this->jira = new JiraClient($this->config());
        }

        return $this->jira;
    }
}