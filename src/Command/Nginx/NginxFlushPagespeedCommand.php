<?php

/**
 * @file
 * Contains \Docker\Drupal\Command\DemoCommand.
 */

namespace Docker\Drupal\Command\Nginx;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Docker\Drupal\Style\DockerDrupalStyle;

/**
 * Class WatchCommand
 * @package Docker\Drupal\Command\Nginx
 */
class NginxFlushPagespeedCommand extends Command
{
  protected function configure()
  {
      $this
          ->setName('nginx:flush')
          ->setDescription('Flush nginx cache')
          ->setHelp("This command will flush NGINX pagespeed cache.")
      ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $application = $this->getApplication();
    $io = new DockerDrupalStyle($input, $output);

    $io->section("Nginx ::: flush");

    if($config = $application->getAppConfig($io)) {
      $appname = $config['appname'];
    }

    if($application->checkForAppContainers($appname, $io)){
      $command = $application->getComposePath($appname, $io).'exec -T nginx bash -c "rm -rf /var/ngx_pagespeed_cache/*" 2>&1';
			$application->runcommand($command, $io);
    }
  }
}