<?php

require_once __DIR__ . '/vendor/autoload.php';

/*
 * Quickstart
 * ----------
 *
 * You will need to edit ServerConnection to ensure the connection can be made back to your license server (WHMCS in this case).
 * There is a hook in the WHMCS module to detect the `Ranger-Client` header on every request and handle it.
 *
 * This example will handle storing the fingerprint (required to prevent constant remote license checks) as a file.
 * You can implement your own fingerprint storage by modifying License::fetchFingerprint() and storeFingerprint()
 * You can also adjust how long the fingerprint will be valid for using getSoftFingerprintTtl() and getHardFingerprintTtl()
 * The fingerprint is a signed JWT string, signed using the value returned from getFingerprintSecret(). This secret is not shared by the client and server.
 *
 * There is no need for a shared secret between the client and the server in this example, as if it was used in production, the HTTP connection would be over SSL, with host verification enabled.
 * You can implement a shared secret if you desire, likely best to be placed inside the Context
 *
 * */

try
{
	$context = ExampleRangerEnabledApp\Ranger\Context::create();
	$serverConnection = new ExampleRangerEnabledApp\Ranger\ServerConnection();
	$client = \Grizzlyware\Ranger\Ranger::client($context, $serverConnection);
	$license = ExampleRangerEnabledApp\Ranger\License::formWithString("AppOne-3fy96a46b5-Owned");

	$validationResult = $client->validateLicense($license);

	if($validationResult->isValid())
	{
		echo "The license is valid\n";
	}
	else
	{
		echo "The license is NOT valid\n";
	}
}
catch(\Exception $e)
{
	echo "Error: {$e->getMessage()}";
}



