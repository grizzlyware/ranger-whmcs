<?php

namespace WHMCS\Module\Server\Ranger\App;

use Grizzlyware\Ranger\Server\License\ValidationResult;

class ClientConnection extends \Grizzlyware\Ranger\Server\ClientConnection
{
	public function initialiseDataStore()
	{
		$this->store = new LicenseStore();
	}

	protected function getRegisteredPackClasses()
	{
		return
		[
			License::class,
			Context::class,
			ValidationResult::class
		];
	}
}





