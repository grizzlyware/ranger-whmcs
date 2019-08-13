<?php

require __DIR__ . "/../vendor/autoload.php";

$rootPath = realpath(__DIR__ . "/../");
$sourcePath = realpath(__DIR__ . "/../src/");

// Zip it up! (Source: https://stackoverflow.com/a/4914807/1002843)
$destinationDir = realpath(__DIR__ . "/../dist/");

// Initialize archive object
$zip = new \ZipArchive();
$zip->open($destinationDir . '/ranger-whmcs.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

// Create recursive directory iterator
$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sourcePath), \RecursiveIteratorIterator::LEAVES_ONLY);

// To put it in an understandable structure
$modulePath = 'ranger-whmcs/whmcs/modules/servers/ranger/';

foreach($files as $name => $file)
{
	// Skip directories
	if(!$file->isDir())
	{
		// Get real and relative path for current file
		$filePath = $file->getRealPath();
		$relativePath = substr($filePath, strlen($sourcePath) + 1);

		// Skip some files
		if(strpos($file->getFilename(), 'fingerprint-') === 0) continue;

		// Add current file to archive
		$zip->addFile($filePath, $modulePath . $relativePath);
	}
}

// We're done!
echo "Success! WHMCS module packaged successfully.\n";


