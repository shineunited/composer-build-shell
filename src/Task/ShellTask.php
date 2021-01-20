<?php

namespace ShineUnited\ComposerBuildPlugin\Shell\Task;

use ShineUnited\ComposerBuild\Task\Task;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Composer\IO\IOInterface;


class ShellTask extends Task {

	public function configure() {
		$this->addArgument(
			'cmd', // name
			InputArgument::REQUIRED | InputArgument::IS_ARRAY, // mode
			'Command(s) to execute', // description
			null // default
		);

		$this->addOption(
			'env',
			null,
			InputOption::VALUE_REQUIRED,
			'Json or string encoded env variables',
			array()
		);
	}

	protected function parseEnv($input) {
		if(is_array($input)) {
			if(array_keys($input) !== range(0, count($input) - 1)) {
				// associative array, return directly
				return $input;
			}

			// non-associative array, assume key=value pairs
			$arrResult = array();
			foreach($input as $item) {
				$pair = $this->parseEnv($item);

				if(array_keys($pair) !== range(0, count($pair) - 1)) {
					foreach($pair as $key => $value) {
						$arrResult[$key] = $value;
					}
				}
			}

			return $arrResult;
		}

		if(is_string($input)) {
			$jsonResult = json_decode($input, true);
			if(json_last_error() == JSON_ERROR_NONE) {
				// no parse error, valid json
				if(is_array($jsonResult) && array_keys($jsonResult) !== range(0, count($jsonResult) - 1)) {
					// associative array, this is a result
					return $jsonResult;
				}
			}

			$strResult = array();
			parse_str($input, $strResult);
			return $strResult;
		}

		return array();
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		$io = $this->getIO();

		$cmds = $input->getArgument('cmd');

		if(!is_array($cmds)) {
			$cmds = array($cmds);
		}

		$env = $this->parseEnv($input->getOption('env'));

		foreach($cmds as $cmd) {
			$io->write('> ' . $cmd, true, IOInterface::VERBOSE);
			
			$process = new Process($cmd, null, $env);

			$result = $process->run(function($type, $buffer) use ($io) {
				if(Process::ERR == $type) {
					$io->writeError($buffer, false, IOInterface::NORMAL);
				} else {
					$io->write($buffer, false, IOInterface::NORMAL);
				}
			});

			if($result) {
				return $result;
			}
		}
	}
}
