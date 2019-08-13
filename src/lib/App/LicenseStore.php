<?php

namespace WHMCS\Module\Server\Ranger\App;

use Grizzlyware\Ranger\Server\Exceptions\LicenseNotFoundException;
use Grizzlyware\Ranger\Server\License\Store;
use Grizzlyware\Salmon\WHMCS\Helpers\DataStore;
use Grizzlyware\Salmon\WHMCS\Service\Service;
use WHMCS\Module\Server\Ranger\ModuleParams;

class LicenseStore extends Store
{
	protected $whmcsParams;

	public function setWhmcsModuleParams(ModuleParams $params)
	{
		$this->whmcsParams = $params;

		$this->keyLength = $this->whmcsParams->length;
		$this->keyPrefix = $this->whmcsParams->prefix;
		$this->keySuffix = $this->whmcsParams->suffix;
	}

	public function findLicenseByKey($licenseKey)
	{
		$localService = self::searchForLicenseInSalmonDataStore($licenseKey);
		if(!$localService) throw new LicenseNotFoundException();

		return License::formWithWhmcsService($localService);
	}

	protected function rangerServicesScope()
	{
		return Service::whereHas('product', function($productQuery)
		{
			$productQuery->where('servertype', 'ranger');
		});
	}

	public function isLicenseKeyAvailable($licenseKey)
	{
		return !self::searchForLicenseInSalmonDataStore($licenseKey);
	}

	public function findLicenseByPayload($payload)
	{
		return $this->findLicenseByKey($payload->licenseKey);
	}

	public static function searchForLicenseInSalmonDataStore($licenseKey, $loose = false)
	{
		// Init the Salmon DataStore, as we're querying it directly with Eloquent.
		DataStore::getModel('void', 1, 'void');

		// Normalise the key
		$licenseKey = trim(strtolower($licenseKey));

		// A loose search will return multiple results. It may be slower, but it will match with only part of the license key provided.
		if($loose)
		{
			$services = collect();
			$dataStoreItems = DataStore\Item::relType('service')->key('ranger_key')->where('value', 'LIKE', "%{$licenseKey}%")->get();

			foreach($dataStoreItems as $dataStoreItem)
			{
				$service = Service::find($dataStoreItem->rel_id);
				if($service) $services->push($service);
			}

			return $services;
		}
		else
		{
			$dataStoreItem = DataStore\Item::relType('service')->key('ranger_key')->valueIndex($licenseKey)->first();
			if(!$dataStoreItem) return null;
			return Service::find($dataStoreItem->rel_id);
		}
	}
}



