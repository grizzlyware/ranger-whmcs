<?php

namespace ExampleRangerEnabledApp\Ranger;

use Grizzlyware\Ranger\Client\Context;

final class License extends \Grizzlyware\Ranger\Client\License
{
	protected $licenseKey;
	static $fingerprintPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . "fingerprint";

	private function getFingerprintPath()
	{
		return self::$fingerprintPath . '-' . md5($this->licenseKey);
	}

	protected function getFingerprintSecret()
	{
		return "MyFingerPrintSecret";
	}

	protected function getSoftFingerprintTtl()
	{
		// 7 days in seconds
		return 604800;
	}

	protected function getHardFingerprintTtl()
	{
		// 10 days in seconds
		return 864000;
	}

	public function fetchFingerprint()
	{
		if(!file_exists($this->getFingerprintPath())) return null;
		return file_get_contents($this->getFingerprintPath());
	}

	public function storeFingerprint($fingerprintString)
	{
		file_put_contents($this->getFingerprintPath(), $fingerprintString);
	}

	public function validateForContext(Context $context)
	{
		throw new \Exception("Validation not performed on the client");
	}

	public function setKey($licenseKey)
	{
		$this->licenseKey = $licenseKey;
	}

	public static function formWithString($licenseKey)
	{
		$license = new self();
		$license->setKey($licenseKey);
		return $license;
	}

	public function pack()
	{
		return (object)['licenseKey' => $this->licenseKey];
	}

	public static function unpack($body)
	{
		// Reconstruct it
		$license = new self();

		// Set the props
		$license->setKey($body->licenseKey);

		return $license;
	}
}



