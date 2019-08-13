<?php

namespace ExampleRangerEnabledApp\Ranger;

final class Context extends \Grizzlyware\Ranger\Client\Context
{
	public function determineContextAttributes()
	{
		// IP of the server
		$serverIp = $_SERVER['SERVER_ADDR'] ? $_SERVER['SERVER_ADDR'] : null;

		// PHP 5 special.
		$serverHostname = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		$serverHostname = $serverHostname ? $serverHostname : gethostname();

		$this->setIpAddress($serverIp);
		$this->setDirectory(realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR)); // To put us back into the "basic" dir. Using __DIR__ on its own would also be fine.
		$this->setDomain($serverHostname);
	}
}



