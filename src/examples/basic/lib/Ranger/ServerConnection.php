<?php

namespace ExampleRangerEnabledApp\Ranger;

use Grizzlyware\Ranger\Client\Context;
use Grizzlyware\Ranger\Client\Exceptions\RemoteServerFailureException;
use Grizzlyware\Ranger\Client\License;

final class ServerConnection extends \Grizzlyware\Ranger\Client\ServerConnection
{
	public function validateLicense(License $license, Context $context)
	{
		// We're going to now speak to the server... over whichever transport layer we want...

		// Package the call up..
		$packedPayload = $this->packPayload($license, $context);

		try
		{
			$client = new \GuzzleHttp\Client();

			// This can be any page in WHMCS. There is a hook in the WHMCS module to detect the `Ranger-Client` header on every request and handle it
			$response = $client->request('POST', 'http://grz-whmcs-dev.test/whmcs/',
			[
				'headers' =>
				[
					'Ranger-Client' => 'Example-Basic-ExampleRangerEnabledApp',
					'Content-Type' => 'application/json'
				],

				'json' => $packedPayload
			]);
		}
		catch(\Exception $e)
		{
			throw new RemoteServerFailureException($e);
		}

		// Unpack the servers response
		$unpackedServersResponse = $this->unpackPayload(json_decode((string)$response->getBody()));

		return $unpackedServersResponse->validation_result;
	}
}


