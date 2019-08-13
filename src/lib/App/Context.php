<?php

namespace WHMCS\Module\Server\Ranger\App;

class Context extends \Grizzlyware\Ranger\Client\Context
{
	public function getIpAddress()
	{
		$ipAddress = parent::getIpAddress();
		$ipAddress = $ipAddress ? $ipAddress : $_SERVER['REMOTE_ADDR'];
		$ipAddress = explode("/", $ipAddress, 2)[0];
		return $ipAddress;
	}
}

