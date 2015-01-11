<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$console->register('demo:test')
	->setDefinition(array())
	->setDescription('Demo')
	->setCode(function (InputInterface $input, OutputInterface $output) use ($app)
	{
		$output->writeln('-------------------------------------------------------------------------------');

		$output->writeln('Demo');

		$output->writeln('-------------------------------------------------------------------------------');
	});
