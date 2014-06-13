<?php

namespace Src\Lib;

use Src\Lib\Application;
use Src\Lib\FileUploadHelperHandler;

class FileUploadHelper extends Application
{
	private $uploader;
	public $allowedExtensions;
	public $sizeLimit;
	public $inputName;
	public $rootFolder;
	public $chunksFolder;
	public $defaultFolder;

	function __construct()
	{
		$this->uploader = new FileUploadHelperHandler();

		$this->allowedExtensions = array(); // all (empty)
		$this->sizeLimit = (10 * 1024 * 1024); // 10 MB
		$this->inputName = 'tempfile';

		$this->rootFolder = $this->app('path.info')['root'];
		$this->chunksFolder = $this->app('path.info')['chunks'];
		$this->defaultFolder = $this->app('path.info')['temp'];
	}

	function handleUpload($uploadFolder = null, $fileName = null)
	{
		if(empty($uploadFolder))
		{
			$uploadFolder = ($this->rootFolder . $this->defaultFolder);
		}
		else
		{
			$uploadFolder = $this->rootFolder . str_replace($this->rootFolder, '', $uploadFolder);
		}

		$this->uploader->allowedExtensions = $this->allowedExtensions;
		$this->uploader->sizeLimit = $this->sizeLimit;
		$this->uploader->inputName = $this->inputName;
		$this->uploader->chunksFolder = ($this->rootFolder . $this->chunksFolder);

		$result = $this->uploader->handleUpload($uploadFolder, $fileName);
		$result['preventRetry'] = true;

		if(! empty($result['error']))
		{
			return $result;
		}

		$compressed_ext = array('bz2','gz','lz','z','7z','cab','rar','zip','sfx','tgz','uha','zip','jpg','jpeg');

		if(! empty($result['size']) && $result['size'] >= (1024 * 256) // Mayor 256 KB
			&& ! in_array(pathinfo($result['uploadName'], PATHINFO_EXTENSION), $compressed_ext))
		{
			$oldPath = $uploadFolder . DIRECTORY_SEPARATOR . $result['uploadName'];
			$zipPath = $oldPath . '_TEMP_ZIP';

			if($this->create_zip(array($oldPath => $result['fileName']), $zipPath))
			{
				$newSize = filesize($zipPath);
				$percentaje_gained = (($newSize * 100) / $result['size']);

				if($percentaje_gained <= 80.0)
				{
					rename($zipPath, $oldPath);

					$result['size'] = $newSize;
					$result['fileName'] .= '.zip';
				}
				else
				{
					unlink($zipPath);
				}
			}
		}

		return $result;
	}

	function create_zip($files = array(), $destination = '', $overwrite = false)
	{
		if(file_exists($destination) && !$overwrite)
		{
			return false;
		}

		$valid_files = array();

		if(! empty($files) && is_array($files))
		{
			foreach($files as $file => $fileName)
			{
				if(file_exists($file))
				{
					$valid_files[] = array('file' => $file, 'name' => ($fileName ?: $file));
				}
			}
		}

		if(count($valid_files))
		{
			if(!extension_loaded('zip'))
			{
				return false;
			}

			$zip = new \ZipArchive();

			if($zip->open($destination, $overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE) !== true)
			{
				return false;
			}

			foreach($valid_files as $source)
			{
				$sourcePath = $source['file'];
				$sourceName = $source['name'];

				//$zip->addFile($sourcePath, $sourceName);

				if(!file_exists($sourcePath))
				{
					continue;
				}

				$sourcePath = str_replace('\\', '/', realpath($sourcePath));

				if(is_dir($sourcePath) === true)
				{
					$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourcePath), RecursiveIteratorIterator::SELF_FIRST);

					foreach($files as $file)
					{
						if(substr($file, -1) == '.')
						{
							continue;
						}

						$file = str_replace('\\', '/', realpath($file));

						if(is_dir($file) === true)
						{
							// IBM850 = Codificación usada por PHP/"ZipArchive" (extensión) en Windows
							$zip->addEmptyDir(iconv('UTF-8', 'IBM850//TRANSLIT', str_replace($sourceName . '/', '', $file . '/')));
							//$zip->addEmptyDir(mb_convert_encoding(str_replace($sourceName . '/', '', $file . '/'), 'IBM850', 'auto'));
						}
						else if(is_file($file) === true)
						{
							// IBM850 = Codificación usada por PHP/"ZipArchive" (extensión) en Windows
							$zip->addFromString(iconv('UTF-8', 'IBM850//TRANSLIT', str_replace($sourceName . '/', '', $file)), file_get_contents($file));
							//$zip->addFromString(mb_convert_encoding(str_replace($sourceName . '/', '', $file), 'IBM850', 'auto'), file_get_contents($file));
						}
					}
				}
				else if(is_file($sourcePath) === true)
				{
					// IBM850 = Codificación usada por PHP/"ZipArchive" (extensión) en Windows
					$zip->addFromString(iconv('UTF-8', 'IBM850//TRANSLIT', basename($sourceName)), file_get_contents($sourcePath));
					//$zip->addFromString(mb_convert_encoding(basename($sourceName), 'IBM850', 'auto'), file_get_contents($sourcePath));
				}
			}

			return ($zip->close() && file_exists($destination));
		}
		else
		{
			return false;
		}
	}
}
