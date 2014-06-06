<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

$console = new Application('Herramientas de gestion de Silex.', '1.0');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'Nombre del entorno.', 'dev'));
$console->setDispatcher($app['dispatcher']);

$console->register('show:routes')
	->setDefinition(array())
	->setDescription('Shows all routes')
	->setCode(function (InputInterface $input, OutputInterface $output) use ($app)
	{
		$output->writeln('-------------------------------------------------------------------------------');
		$output->writeln(' Silex Configured Routes:');
		$output->writeln('-------------------------------------------------------------------------------');

		$routes = $app['routes']->getIterator();

		foreach ($routes as $key => $route)
		{
			$requirements = $route->getRequirements();
			$requirements_str = '';

			foreach ($requirements as $key => $requirement)
			{
				$requirements_str .= " | $key: $requirement";
			}

			$output->writeln($route->getPath());
			$output->writeln(trim($requirements_str));
		}

		$output->writeln('-------------------------------------------------------------------------------');
	});

$console->register('clear:cache')
	->setDefinition(array())
	->setDescription('Clear cache (HTTP, Twig, WebProfiler, ...)')
	->setCode(function (InputInterface $input, OutputInterface $output) use ($app)
	{
		$output->writeln('-------------------------------------------------------------------------------');

		$cacheDir = __DIR__ . "/../var/cache/";

		$finder = new Finder();
		$finder->in($cacheDir)->notName('.htaccess');

		$fs = new Filesystem();
		$fs->remove($finder);

		$output->writeln(' Cache cleared');

		$output->writeln('-------------------------------------------------------------------------------');
	});

return $console;
