<?php

namespace WHMCS\Module\Server\Ranger\App;

use Grizzlyware\Ranger\Client\Context;
use Grizzlyware\Ranger\Server\License\ValidationResult;
use Grizzlyware\Salmon\WHMCS\Helpers\DataStore;
use Grizzlyware\Salmon\WHMCS\Service\Service;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils;

class License extends \Grizzlyware\Ranger\Client\License
{
	protected $licenseKey;
	protected $whmcsService;

	public function fetchFingerprint()
	{
		throw new \Exception("fetchFingerprint method not supported on the server");
	}

	public function storeFingerprint($fingerprintString)
	{
		throw new \Exception("storeFingerprint method not supported on the server");
	}

	public function validateForContext(Context $context)
	{
		$result = new ValidationResult();
		$licenseStatus = $this->getStatus();

		$checkAttributeInArray = function($needle, $haystack)
		{
			$needle = trim(Str::lower($needle));
			$haystack = array_map('trim', $haystack);
			$haystack = array_map([Str::class, 'lower'], $haystack);
			return in_array($needle, $haystack);
		};

		if($licenseStatus->status == 'reissued')
		{
			// Update the locked context
			$this->validIps = $context->getIpAddress() ? [$context->getIpAddress()] : null;
			$this->validDirectories = [$context->getDirectory()];
			$this->validHostnames = [$context->getDomain()];
			$this->status = 'locked';
			$result->valid = true;
		}
		elseif($licenseStatus->status == 'locked')
		{
			if($this->validIps && count($this->validIps) > 0 && !IpUtils::checkIp($context->getIpAddress(), $this->validIps))
			{
				$result->valid = false;
			}
			elseif($this->validDirectories && count($this->validDirectories) > 0 && !$checkAttributeInArray($context->getDirectory(), $this->validDirectories))
			{
				$result->valid = false;
			}
			elseif($this->validHostnames && count($this->validHostnames) > 0 && !$checkAttributeInArray($context->getDomain(), $this->validHostnames))
			{
				$result->valid = false;
			}
			else
			{
				$result->valid = true;
			}
		}
		else
		{
			$result->valid = false;
		}

		return $result;
	}

	public static function formWithString($licenseKey)
	{
		throw new \Exception("formWithString method not supported on the server");
	}

	public function setLicenseKey()
	{
		$this->licenseKey = $this->ranger_key;
	}

	public function setWhmcsService(Service $service)
	{
		$this->whmcsService = $service;
	}

	public function pack()
	{
		return (object)['licenseKey' => $this->licenseKey];
	}

	public static function formWithWhmcsService(Service $service)
	{
		$license = new License();
		$license->setWhmcsService($service);
		return $license;
	}

	public function getStatus()
	{
		if($this->whmcsService->domainstatus != "Active")
		{
			return (object)['status' => 'inactive', 'label' => 'Inactive', 'description' => 'The WHMCS service is not active, the license will not validate'];
		}

		if(!$this->ranger_key)
		{
			return (object)['status' => 'pending', 'label' => 'Pending', 'description' => 'The license key has not been generated yet'];
		}

		switch($this->status)
		{
			case 'reissued':
				return (object)['status' => 'reissued', 'label' => 'Reissued', 'description' => 'The valid IP, directory and hostname will be locked on the next license check'];
				break;

			case 'locked':
				return (object)['status' => 'locked', 'label' => 'Locked', 'description' => 'The license is locked to IPs, directories and hostnames'];
				break;

			default:
				// Set the status and return ourself
				$this->status = 'reissued';
				return $this->getStatus();
		}
	}

	public function __get($name)
	{
		$value = DataStore::get('service', $this->whmcsService->id, Str::snake($name));

		switch($name)
		{
			case 'validIps':
			case 'validDirectories':
			case 'validHostnames':
				if(!$value) return [];
				return $value;

			default:
				return $value;
		}
	}

	public function __set($name, $value)
	{
		$valueIndex = DataStore::EMPTY_VALUE_INDEX;
		if($name == 'ranger_key') $valueIndex = trim(Str::lower($value));
		DataStore::set('service', $this->whmcsService->id, Str::snake($name), $value, $valueIndex);
	}
}


