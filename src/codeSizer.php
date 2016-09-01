<?php

namespace PHPsize
{
	class codeSizer
	{
		private $_directory = false;
		private $_extensions = false;
		private $_recursive = false;

		public function init()
		{
			$sucess = false;
			$args = func_get_args();

			while($value = current($args)){
				switch($value){
					case '--dir':
					case '-d':
						$directory = next($args);
						$this->setDirectory($directory);
						break;
					case '--ext':
					case '-e':
						$extension = next($args);
						$this->setExtension($extension);
						break;
					case '--recursive':
					case '-r':
						$this->setRecursive(true);
						break;
				}
				next($args);
			}
			if($this->getDirectory() && $this->getExtension()){
				$files = scandir($this->getDirectory());
				if($this->getRecursive()){
					debug($this->scan($files, true));
				}
				debug($this->scan($files, false));
			}
		}

		public function scan(array $files, $recursive = true, $path = false)
		{
			$return = array();
			$counLines = 0;
			$countDigits = 0;
			$countLogic = 0;
			foreach($files as $file){
				if($file != '.' && $file != '..'){
					if($path){
						$filePath = $this->getDirectory() . $path . $file;
					}else{
						$filePath = $this->getDirectory() . $file;
					}
					if($recursive && is_dir($filePath)){
						$files = scandir($this->getDirectory() . $filePath . '/', $recursive);
						$return = array_merge($return, $this->scan($files, $recursive, ($path ? $path : '') . $file . '/'));
					}else{
						if(is_file($filePath)){
							$contentFile = file_get_contents($filePath);
							$lines = explode("\n", $contentFile);
							if(count($files) > 0){
								foreach($lines as $line){
									if(strlen(trim($line)) > 5){
										$counLines++;
										$countDigits = $countDigits + str_replace(' ','', trim(strlen($line)));
									}
								}
							}
						}
					}
				}
			}
			debug(compact('counLines', 'countDigits'));
		}




		public function setDirectory($directory)
		{
			$this->_directory = $directory;
		}

		public function getDirectory()
		{
			return $this->_directory;
		}

		public function setExtension($extensions)
		{
			$this->_extensions = $extensions;
		}

		public function getExtension()
		{
			return $this->_extensions;
		}

		public function setRecursive($recursive)
		{
			$this->_recursive = $recursive;
		}

		public function getRecursive()
		{
			return $this->_recursive;
		}
	}
}