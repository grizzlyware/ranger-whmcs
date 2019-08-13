<?php

/*
 * Ranger - WHMCS provisioning module
 * Used to generate and distribute license keys for software applications, sold via WHMCS
 *
 * Written by Grizzlyware Ltd - Available free of charge
 *
 * Versions
 * --------
 * V1.0 	- 	03/08/2019 - Initial Release
 * */

use \WHMCS\Module\Server\Ranger\ModuleParams;
use \WHMCS\Module\Server\Ranger\App\License;
use \WHMCS\Module\Server\Ranger\App\LicenseStore;

if(!defined("WHMCS")) die("This file cannot be accessed directly");

\WHMCS\Module\Server\Ranger\Ranger::init();

function ranger_MetaData()
{
	return
	[
		'DisplayName' => 'Ranger License Keys',
		'RequiresServer' => false
	];
}

function ranger_ConfigOptions()
{
	return ModuleParams::getWhmcsServerConfiguration();
}

function ranger_CreateAccount(array $params)
{
	try
	{
		// Build the params
		$params = new ModuleParams($params);

		// Get the WHMCS service
		$service = $params->getService();

		// Get a license
		$license = License::formWithWhmcsService($service);

		// Got a key?
		if($license->ranger_key) throw new \Exception('There is already a license key set for this service');

		// License store
		$licenseStore = new LicenseStore();
		$licenseStore->setWhmcsModuleParams($params);

		// Generate and set the key
		$generatedKey = $licenseStore->generator()->getKey();
		$license->ranger_key = $generatedKey;

		return "success";
	}
	catch(\Exception $e)
	{
		return $e->getMessage();
	}
}

function ranger_AdminServicesTabFields(array $params)
{
	try
	{
		// Build the params
		$params = new ModuleParams($params);

		// Get the license
		$license = License::formWithWhmcsService($params->getService());
		$licenseStatus = $license->getStatus();

		return
		[
			'License Key' => $license->ranger_key ? "<code style='padding: 10px; display: block; font-size: 16px;'>{$license->ranger_key}</code>" : 'Not generated',
			'License Status' => "<b>{$licenseStatus->label}</b> &mdash; {$licenseStatus->description}",
			'Valid IPs' => '<input name="ranger_valid_ips" class="form-control" type="text" value="' . htmlspecialchars(implode(', ', $license->validIps)) . '" /><small>IP ranges may be used (10.0.1.0/24)</small>',
			'Valid Hostnames' => '<input name="ranger_valid_hostnames" class="form-control" type="text" value="' . htmlspecialchars(implode(', ', $license->validHostnames)) . '" />',
			'Valid Directories' => '<input name="ranger_valid_directories" class="form-control" type="text" value="' . htmlspecialchars(implode(', ', $license->validDirectories)) . '" />',
			'' => '<small>Multiple environments can be added. Separate values with commas (1.1.1.1, 8.8.8.8). Remove a constraint to not validate against it. Constraints are not case sensitive.</small>'
		];
	}
	catch(\Exception $e)
	{
		return ['Error' => $e->getMessage()];
	}
}

function ranger_AdminServicesTabFieldsSave(array $params)
{
	try
	{
		// Build the params
		$params = new ModuleParams($params);

		// Get the license
		$license = License::formWithWhmcsService($params->getService());

		$formatRestriction = function($restriction)
		{
			return trim(\Illuminate\Support\Str::lower($restriction));
		};

		if(isset($_REQUEST['ranger_valid_ips']))
		{
			$formInputValue = $formatRestriction($_REQUEST['ranger_valid_ips']);
			$license->validIps = $formInputValue ? array_map($formatRestriction, explode(",", htmlspecialchars_decode($formInputValue))) : null;
		}

		if(isset($_REQUEST['ranger_valid_hostnames']))
		{
			$formInputValue = $formatRestriction($_REQUEST['ranger_valid_hostnames']);
			$license->validHostnames = $formInputValue ? array_map($formatRestriction, explode(",", htmlspecialchars_decode($formInputValue))) : null;
		}

		if(isset($_REQUEST['ranger_valid_directories']))
		{
			$formInputValue = $formatRestriction($_REQUEST['ranger_valid_directories']);
			$license->validDirectories = $formInputValue ? array_map($formatRestriction, explode(",", htmlspecialchars_decode($formInputValue))) : null;
		}
	}
	catch(\Exception $e)
	{
		//
	}
}

function ranger_Reissue(array $params)
{
	try
	{
		// Build the params
		$params = new ModuleParams($params);

		// Get the license
		$license = License::formWithWhmcsService($params->getService());

		// Set the state
		$license->status = 'reissued';

		return "success";
	}
	catch(\Exception $e)
	{
		return $e->getMessage();
	}
}

function ranger_SuspendAccount(array $params)
{
	return 'success';
}

function ranger_UnsuspendAccount(array $params)
{
	return 'success';
}

function ranger_TerminateAccount(array $params)
{
	return 'success';
}

function ranger_AdminCustomButtonArray()
{
	return
	[
		"Reissue" => "reissue"
	];
}

function ranger_ClientAreaCustomButtonArray(array $params)
{
	try
	{
		// Build the params
		$params = new ModuleParams($params);

		$buttons = [];

		if($params->clientCanReissue)
		{
			$buttons['Reissue'] = 'reissue';
		}

		return $buttons;
	}
	catch(\Exception $e)
	{
		return [];
	}
}

function ranger_ClientArea(array $params)
{
	try
	{
		// Build the params
		$params = new ModuleParams($params);

		// Get the license
		$license = License::formWithWhmcsService($params->getService());

		return
		[
			'tabOverviewModuleOutputTemplate' => 'overview.tpl',

			'templateVariables' =>
			[
				'ranger' =>
				[
					'license' =>
					[
						'key' => $license->ranger_key,
						'clientCanReissue' => $params->clientCanReissue,
						'status' => $licenseStatus = $license->getStatus(),
						'environment' =>
						[
							'ips' => $license->validIps,
							'hostnames' => $license->validHostnames,
							'directories' => $license->validDirectories,
						]
					]
				]
			],
		];
	}
	catch(\Exception $e)
	{
		return
		[
			'tabOverviewModuleOutputTemplate' => 'error.tpl',

			'templateVariables' =>
			[
				'ranger' =>
				[
					'error' => $e->getMessage()
				]
			],
		];
	}
}

