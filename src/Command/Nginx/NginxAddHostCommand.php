<?php

/**
 * @file
 * Contains \Docker\Drupal\Command\DemoCommand.
 */

namespace Docker\Drupal\Command\Nginx;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Docker\Drupal\Style\DockerDrupalStyle;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;


/**
 * Class NginxAddHostCommand
 * @package Docker\Drupal\Command\Nginx
 */
class NginxAddHostCommand extends Command
{
  protected function configure()
  {
      $this
          ->setName('nginx:addhost')
          ->setDescription('Add nginx host to DD and host OS')
          ->setHelp("This command will add a host server_name & reload NGINX config.")
      ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $application = $this->getApplication();
    $io = new DockerDrupalStyle($input, $output);

    $io->section("Nginx ::: add host");

    if($config = $application->getAppConfig($io)) {
      $appname = $config['appname'];
      $apphost = $config['host'];
    }

    if(!isset($apphost)) {
      $apphost = 'docker.dev';
    }

    $currenthost = $apphost;
    $helper = $this->getHelper('question');

    $question = new Question('Please enter new hostname : [' . $currenthost . '] ', $currenthost);
    $newhost = $helper->ask($input, $output, $question);

    if(file_exists('.config.yml')){
      $config = Yaml::parse(file_get_contents('.config.yml'));
      $config['host'] = $newhost;
      $yaml = Yaml::dump($config);
      file_put_contents('.config.yml', $yaml);
    }else{
      $io->error('You\'re not currently in an APP directory. APP .config.yml not found.');
      exit;
    }

    $application->setNginxHost($io);
    $application->addHostConfig($io);

    if($application->checkForAppContainers($appname, $io)){

      $command = $application->getComposePath($appname, $io).'exec -T nginx nginx -s reload 2>&1';
			$application->runcommand($command, $io);
    }
  }
}