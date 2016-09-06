<?php

namespace PHPsize
{
	use Phar;

	class codeSizer
	{
		private $_version = '0.1.0';
		private $_directory = false;
		private $_extensions = false;
		private $_recursive = false;
		private $_dir_save_svg = false;
		private $_exclude = array(
			'<?php',
			'?>',
			'/**',
			'*/',
			'*',
			'namespace',
			'{',
			'//',
			'use',
			'class',
			'}'
		);

		public function init()
		{
			$args = func_get_args();

			$this->_echo("PHPsize version " . $this->_version . "\n");

			while($value = current($args)){
				switch($value){
					case '--dir':
					case '-d':
						$directory = next($args);
						$this->setDirectory($directory);
						break;
					case '--extension':
					case '-e':
						$extension = next($args);
						$this->setExtension($extension);
						break;
					case '--recursive':
					case '-r':
						$this->setRecursive(true);
						break;
					case '--generate-svg':
					case '-g':
						$dirSaveSvg = next($args);
						$this->setDirSaveSvg($dirSaveSvg);
						break;
					case '--help':
					case '-h':
						return $this->help();
						break;
				}
				next($args);
			}
			if($this->getDirectory() && $this->getExtension()){
				$directory = str_replace($argv[0], '', $this->getPathDir()) . $this->getDirectory();
				$files = scandir($directory);
				$scan = $this->scan($directory, $files, $this->getRecursive());
				if(is_array($scan) && count($scan) > 0){
					if($this->getDirSaveSvg()){
						return $this->makeSvg($scan);
					}else{
						return json_encode($scan);
					}
				}
			}
			return $this->help();
		}

		public function makeSvg($scan)
		{
			$this->makeSvgCountLines(number_format($scan['countLines'], 0, ',', '.'));
			$this->makeSvgCountDigits(number_format($scan['countDigits'], 0, ',', '.'));
			$this->makeSvgCountFiles(number_format($scan['countFiles'], 0, ',', '.'));
			$this->makeSvgCountLogicLines(number_format($scan['countLogicLines'], 0, ',', '.'));
			$this->makeSvgCountLogicDigits(number_format($scan['countLogicDigits'], 0, ',', '.'));
		}

		public function makeSvgCountLines($value)
		{
			$this->writeSvg('countLines', file_get_contents('https://img.shields.io/badge/lines-' . $value . '-blue.svg?style=plastic'));
		}

		public function makeSvgCountDigits($value)
		{
			$this->writeSvg('countDigits', file_get_contents('https://img.shields.io/badge/digits-' . $value . '-blue.svg?style=plastic'));
		}

		public function makeSvgCountFiles($value)
		{
			$this->writeSvg('countFiles', file_get_contents('https://img.shields.io/badge/files-' . $value . '-blue.svg?style=plastic'));
		}

		public function makeSvgCountLogicLines($value)
		{
			$this->writeSvg('countLogicLines', file_get_contents('https://img.shields.io/badge/logic lines-' . $value . '-blue.svg?style=plastic'));
		}

		public function makeSvgCountLogicDigits($value)
		{
			$this->writeSvg('countLogicDigits', file_get_contents('https://img.shields.io/badge/logic digits-' . $value . '-blue.svg?style=plastic'));
		}

		public function writeSvg($filename, $svg)
		{
			$fopen = fopen($this->getDirSaveSvg() . '/' . $filename . '.svg', 'w');
			fwrite($fopen, $svg);
			fclose($fopen);
		}

		public function scan($directory, array $files, $recursive, $path = null)
		{
			$return = array();
			$countLines = 0;
			$countDigits = 0;
			$countFiles = 0;
			$countLogicLines = 0;
			$countLogicDigits = 0;
			foreach($files as $file){
				if($file != '.' && $file != '..'){
					if($path){
						$filePath = $directory . $path . $file;
					}else{
						$filePath = $directory . $file;
						// $filePath = $this->getDirectory() . $file;
					}
					if($recursive && is_dir($filePath)){
						$files = scandir($filePath . '/', $recursive);
						$return[] = $this->scan($directory, $files, $recursive, ($path ? $path : '') . $file . '/', false);
					}else{
						if(is_file($filePath)){
							$extension = end(explode('.', $filePath));
							if(in_array($extension, $this->getExtension())){
								$contentFile = file_get_contents($filePath);
								$lines = explode("\n", $contentFile);
								$countFiles = $countFiles + 1;
								if(count($lines) > 0){
									foreach($lines as $line){

										$ignoreLine = false;
										$line = trim($line);

										foreach($this->_exclude as $exclude){
											$value = substr($line, 0, strlen($exclude));
											if($value == $exclude){
												$ignoreLine = true;
											}
										}
										if(!$ignoreLine){
											$countLogicLines++;
											$countLogicDigits = $countLogicDigits + strlen($line);
										}
										$countLines++;
										$countDigits = $countDigits + strlen($line);
									}
								}
							}
						}
					}
				}
			}
			if(is_array($return) && count($return) > 0){
				$local = compact('countLines', 'countDigits', 'countFiles', 'countLogic', 'countLogicLines', 'countLogicDigits');
				foreach($return as $item){
					foreach($item as $index => $value){
						if(array_key_exists($index, $local)){
							$local[$index] = $local[$index] + $value;
						}
					}
				}
				$return = $local;
			}else{
				$return = compact('countLines', 'countDigits', 'countFiles', 'countLogic', 'countLogicLines', 'countLogicDigits');
			}
			return $return;
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
			$this->_extensions = explode(',', $extensions);
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

		public function setDirSaveSvg($dirSaveSvg)
		{
			$this->_dir_save_svg = $dirSaveSvg;
		}

		public function getDirSaveSvg()
		{
			return $this->_dir_save_svg;
		}

		public function help()
		{
			$this->_echo("   Usage:\n");
			$this->_echo("         Return JSON: \n");
			$this->_echo("         php phpsize.phar --dir <path dir> --extension <valid extension> [--recursive] \n\n");
			$this->_echo("         Create badges SVG: \n");
			$this->_echo("         php phpsize.phar --dir <path dir> --extension <valid extension> --generate-svg <path dir> [--recursive] \n\n");
			$this->_echo("   Options:\n");
			$this->_echo("         -d,  --dir           Directory to load files\n");
			$this->_echo("         -e,  --extension     Extension of files to load\n");
			$this->_echo("         -g,  --generate-svg  Directory to save SVG files\n");
			$this->_echo("         -r,  --recursive     Include subdirectory\n");
			$this->_echo("         -h,  --help          Show this dialog\n");
		}

		private function _echo($var)
		{
			echo $var;
		}

		private function getPathDir()
		{
			return Phar::running(false);
		}
	}
}