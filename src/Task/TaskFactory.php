<?php

namespace ShineUnited\ComposerBuildPlugin\Shell\Task;

use ShineUnited\ComposerBuild\Capability\TaskFactory as TaskFactoryCapability;


class TaskFactory implements TaskFactoryCapability {

	public function handlesType($type) {
		$types = array(
			'shell',
			'exec'
		);

		return in_array($type, $types);
	}

	public function createTask($type, $name, array $config = array()) {
		switch($type) {
			case 'shell':
			case 'exec':
				return new ShellTask($name, $config);
			default:
				return false;
		}
	}
}
