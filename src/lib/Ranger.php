<?php

namespace WHMCS\Module\Server\Ranger;

use Grizzlyware\Salmon\WHMCS\Billing\Invoice\Item;
use Grizzlyware\Salmon\WHMCS\Service\Service;
use Illuminate\Support\Str;
use WHMCS\Module\Server\Ranger\App\ClientConnection;
use WHMCS\Module\Server\Ranger\App\License;
use WHMCS\Module\Server\Ranger\App\LicenseStore;
use Grizzlyware\Salmon\WHMCS\Mail\Template as MailTemplate;

class Ranger
{
	public static function addLicenseKeyToInvoice($vars)
	{
		self::init();

		// Find the invoice items for this invoice which are services
		foreach(Item::where('type', 'Hosting')->where('invoiceid', $vars['invoiceid'])->get() as $item)
		{
			try
			{
				// Init service and license
				$serviceObj = \Grizzlyware\Salmon\WHMCS\Service\Service::findOrFail($item->relid);
				if($serviceObj->product->servertype != 'ranger') continue;
				$license = \WHMCS\Module\Server\Ranger\App\License::formWithWhmcsService($serviceObj);

				// Add the license key to the invoice line
				if($license->ranger_key)
				{
					$item->description = "{$license->ranger_key} - {$item->description}\\rNew line here";
					$item->save();
				}
			}
			catch(\Exception $e)
			{
				//
			}
		}
	}

	public static function adaptAdminAreaServicesList($vars)
	{
		self::init();

		$returnVars = ['productsummary' => $vars['productsummary']];

		foreach($returnVars['productsummary'] as $serviceIndex => $service)
		{
			try
			{
				// Init service and license
				$serviceObj = \Grizzlyware\Salmon\WHMCS\Service\Service::findOrFail($service['id']);
				if($serviceObj->product->servertype != 'ranger') continue;
				$license = \WHMCS\Module\Server\Ranger\App\License::formWithWhmcsService($serviceObj);

				$service['ranger'] =
				[
					'license' =>
						[
							'key' => $license->ranger_key
						]
				];

				$service['domain'] = ' '. $service['ranger']['license']['key'];

				// Set it
				$returnVars['productsummary'][$serviceIndex] = $service;
			}
			catch(\Exception $e)
			{
				//
			}
		}

		return $returnVars;
	}

	public static function injectEmailMergeFields($vars)
	{
		self::init();

		// Check it's a service email...
		if(MailTemplate::type('product')->name($vars['messagename'])->count() < 1) return [];

		// Get the service and license objects
		$service = Service::findOrFail($vars['relid']);
		$license = License::formWithWhmcsService($service);

		return
		[
			'ranger_license_key' => $license->ranger_key
		];
	}

	public function suggestEmailMergeFields($vars)
	{
		if($vars['type'] != 'product') return [];

		return
		[
			'ranger_license_key' => 'Ranger license key'
		];
	}

	public static function detectClientLicenseCallback()
	{
		if(!isset(getallheaders()["Ranger-Client"])) return;

		self::init();
		self::handleLicenseCallback();
	}

	public static function injectIntelligentSearchResults($vars)
	{
		// Wrap the whole thing in a try/catch, WHMCS may not handle errors gracefully
		try
		{
			self::init();

			// Empty results ready to send back
			$results = [];

			// Closure to generate a search result
			$generateSearchResult = function($service)
			{
				$license = License::formWithWhmcsService($service);

				return
				[
					'title' => "{$service->product->name} &ndash; {$license->ranger_key}",
					'href' => "clientsservices.php?userid={$service->userid}&id={$service->id}",
					'subTitle' => $service->client->label . " #{$service->client->id}",
					'icon' => 'fas fa-key'
				];
			};

			// Try and find an exact match for the license using the indexed column in the Salmon DataStore
			$service = LicenseStore::searchForLicenseInSalmonDataStore(trim(Str::lower($vars['searchTerm'])));

			// If we've found a service, return it.
			if($service) return [$generateSearchResult($service)];

			foreach(LicenseStore::searchForLicenseInSalmonDataStore($vars['searchTerm'], true) as $service)
			{
				try
				{
					$results[] = $generateSearchResult($service);
				}
				catch(\Exception $e)
				{
					// Nothing we can do...
				}
			}

			return $results;
		}
		catch(\Exception $e)
		{
			return [];
		}
	}

	public static function adaptClientServicesList($vars)
	{
		self::init();

		$returnVars = ['services' => $vars['services']];

		foreach($returnVars['services'] as $serviceIndex => $service)
		{
			try
			{
				if($service['module'] != 'ranger') continue;

				// Init service and license
				$serviceObj = \Grizzlyware\Salmon\WHMCS\Service\Service::findOrFail($service['id']);
				$license = \WHMCS\Module\Server\Ranger\App\License::formWithWhmcsService($serviceObj);

				$service['ranger'] =
					[
						'license' =>
							[
								'key' => $license->ranger_key
							]
					];

				$service['domain'] = ' '. $service['ranger']['license']['key'];

				// Set it
				$returnVars['services'][$serviceIndex] = $service;
			}
			catch(\Exception $e)
			{

			}
		}

		return $returnVars;
	}

	//

	public static function init()
	{
		require_once __DIR__ . "/../vendor/autoload.php";
	}

	protected static function handleLicenseCallback()
	{
		$serverClientConnection = new ClientConnection();
		$requestJson = json_decode(file_get_contents('php://input'));
		$serversResponse = $serverClientConnection->handleRequest($requestJson);

		// Package the servers response up and send it back to the client...
		$packedServerResponse = $serverClientConnection->packPayload($serversResponse);

		header('Content-Type: application/json');
		echo json_encode($packedServerResponse);
		exit;
	}
}


