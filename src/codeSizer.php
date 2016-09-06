<?php

namespace PHPsize
{
	use Phar;
	use FilesystemIterator;

	class CodeSizer
	{
		private $version = '0.2.0';
		private $directory = false;
		private $extensions = array();
		private $recursive = false;
		private $dirSaveSvg = false;
		private $exclude = array(
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

			$this->outputText("PHPsize version " . $this->version . "\n");

			$this->configureArgs($args);
			
			if($json = $this->process($args)){
				return 'SVG files were generated in: ' . $this->getDirSaveSvg();
			}
			return $this->help();
		}

		private function process($args)
		{
			if($this->getDirectory() && $this->getExtension()){
				$directory = str_replace($args[0], '', $this->getPathDir()) . $this->getDirectory();
				$files = scandir($directory);
				$scan = $this->scan($directory, $files, $this->getRecursive());
				if(is_array($scan) && count($scan) > 0){
					if($this->getDirSaveSvg()){
						return $this->makeSvg($scan);
					}
					return json_encode($scan);
				}
			}
		}

		private function configureArgs($args)
		{
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
		}

		public function makeSvg($scan)
		{
			$this->makeSvgCountLines(number_format($scan['countLines'], 0, ',', '.'));
			$this->makeSvgCountDigits(number_format($scan['countDigits'], 0, ',', '.'));
			$this->makeSvgCountFiles(number_format($scan['countFiles'], 0, ',', '.'));
			$this->makeSvgCountLogicLines(number_format($scan['countLogicLines'], 0, ',', '.'));
			$this->makeSvgCountLogicDigits(number_format($scan['countLogicDigits'], 0, ',', '.'));
			return true;
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
					$filePath = $directory . $file;
					if($path){
						$filePath = $directory . $path . $file;
					}
					if($recursive && is_dir($filePath)){
						$files = scandir($filePath . '/', $recursive);
						$return[] = $this->scan($directory, $files, $recursive, ($path ? $path : '') . $file . '/', false);

					}
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

									foreach($this->exclude as $exclude){
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
			$result = compact('countLines', 'countDigits', 'countFiles', 'countLogic', 'countLogicLines', 'countLogicDigits');
			if(is_array($return) && count($return) > 0){
				$local = compact('countLines', 'countDigits', 'countFiles', 'countLogic', 'countLogicLines', 'countLogicDigits');
				foreach($return as $item){
					foreach($item as $index => $value){
						if(array_key_exists($index, $local)){
							$local[$index] = $local[$index] + $value;
						}
					}
				}
				$result = $local;
			}
			return $result;
		}

		public function setDirectory($directory)
		{
			$this->directory = $directory;
		}

		public function getDirectory()
		{
			return $this->directory;
		}

		public function setExtension($extensions)
		{
			$extensions = explode(',', $extensions);
			$this->extensions = array_merge($this->extensions, $extensions);
		}

		public function getExtension()
		{
			return $this->extensions;
		}

		public function setRecursive($recursive)
		{
			$this->recursive = $recursive;
		}

		public function getRecursive()
		{
			return $this->recursive;
		}

		public function setDirSaveSvg($dirSaveSvg)
		{
			$this->dirSaveSvg = $dirSaveSvg;
		}

		public function getDirSaveSvg()
		{
			return $this->dirSaveSvg;
		}

		public function help()
		{
			$this->outputText("   Usage:\n");
			$this->outputText("         Return JSON: \n");
			$this->outputText("         php phpsize.phar --dir <path dir> --extension <valid extension> [--recursive] \n\n");
			$this->outputText("         Create badges SVG: \n");
			$this->outputText("         php phpsize.phar --dir <path dir> --extension <valid extension> --generate-svg <path dir> [--recursive] \n\n");
			$this->outputText("   Options:\n");
			$this->outputText("         -d,  --dir           Directory to load files\n");
			$this->outputText("         -e,  --extension     Extension of files to load\n");
			$this->outputText("         -g,  --generate-svg  Directory to save SVG files\n");
			$this->outputText("         -r,  --recursive     Include subdirectory\n");
			$this->outputText("         -h,  --help          Show this dialog\n");
		}

		private function outputText($var)
		{
			echo $var;
		}

		private function getPathDir()
		{
			$phar= new Phar('./phpsize.phar', FilesystemIterator::CURRENT_AS_FILEINFO, 'phpsize.phar');
			return $phar->running(false);
		}
	}
}