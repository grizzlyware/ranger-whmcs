<?php

namespace WHMCS\Module\Server\Ranger;

use Grizzlyware\Salmon\WHMCS\Service\Service;
use Illuminate\Support\Str;

class ModuleParams
{
	protected $rawParams;
	protected $configuration;

	public function __construct(array $params)
	{
		$this->rawParams = $params;

		$this->configuration = [];

		foreach(self::getWhmcsServerConfiguration() as $option)
		{
			$this->configuration[$option['camelName']] = $option;
		}
	}

	public static function getWhmcsServerConfiguration()
	{
		$options =
		[
			'Prefix' =>
			[
				'Type' => 'text',
				'Size' => '25',
				'Default' => ''
			],

			'Suffix' =>
			[
				'Type' => 'text',
				'Size' => '25',
				'Default' => ''
			],

			'Length' =>
			[
				'Type' => 'text',
				'Size' => '25',
				'Default' => '20'
			],

			'Client can reissue' =>
			[
				'Type' => 'yesno',
				'Description' => 'Allow the client to reissue the license themselves'
			],

			'Application key' =>
			[
				'Type' => 'text',
				'Description' => '<br />This prevents licenses from being used for other applications. It keys a license to an application'
			]
		];

		$optionIndex = 1;

		foreach($options as $optionKey => $option)
		{
			$options[$optionKey]['optionIndex'] = $optionIndex;
			$options[$optionKey]['camelName'] = Str::camel($optionKey);
			$optionIndex++;
		}

		return $options;
	}

	public function getService()
	{
		return Service::findOrFail($this->serviceid);
	}

	public function __get($name)
	{
		if(isset($this->configuration[$name]))
		{
			$value = $this->rawParams['configoption' . $this->configuration[$name]['optionIndex']];

			switch($this->configuration[$name]['Type'])
			{
				case 'yesno':
					return !!$value;

				default:
					return $value;
			}
		}

		return isset($this->rawParams[$name]) ? $this->rawParams[$name] : null;
	}
}


