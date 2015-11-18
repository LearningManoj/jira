<?php

namespace Technodelight\Jira\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Technodelight\Jira\Console\Command\AbstractCommand;
use Technodelight\Jira\Template\IssueRenderer;

class ListWorkInProgressCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('in-progress')
            ->setDescription('List tickets picked up by you')
            ->addArgument(
                'project',
                InputArgument::OPTIONAL,
                'Project name if differing from repo configuration'
            )
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Shows other team member\'s progress'
            )
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        // ensure we display every information for in progress issues
        if ($output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE && !$input->getOption('all')) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = $this->projectArgument($input);
        $issues = $this->getService('technodelight.jira.api')->inprogressIssues($project, $input->getOption('all'));

        if (count($issues) == 0) {
            $output->writeln('You don\'t have any in-progress issues currently.');
            return;
        }

        $output->writeln(
            sprintf(
                'You have %d in progress %s' . PHP_EOL,
                count($issues),
                $this->getService('technodelight.jira.pluralize_helper')->pluralize('issue', count($issues))
            )
        );

        $renderer = $this->getService('technodelight.jira.issue_renderer');
        $renderer->setOutput($output);
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $renderer->addWorklogs(
                $this->retrieveWorklogs($issues, $output->getVerbosity() === OutputInterface::VERBOSITY_DEBUG ? null : 10)
            );
        }
        $renderer->renderIssues($issues);
    }

    private function retrieveWorklogs($issues, $limit)
    {
        return $this->getService('technodelight.jira.api')->retrieveIssuesWorklogs(
            $this->issueKeys($issues), $limit
        );
    }

    private function issueKeys($issues)
    {
        $issueKeys = [];
        foreach ($issues as $issue) {
            $issueKeys[] = $issue->issueKey();
        }
        return $issueKeys;
    }
}
