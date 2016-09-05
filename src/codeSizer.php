<?php

namespace PHPsize
{
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
		private $_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="150" height="20">
							<linearGradient id="b" x2="0" y2="100%%">
								<stop offset="0" stop-color="#bbb" stop-opacity=".1"/>
								<stop offset="1" stop-opacity=".1"/>
							</linearGradient>
							<mask id="a">
								<rect width="150" height="20" rx="3" fill="#fff"/>
							</mask>
							<g mask="url(#a)">
								<path fill="#555" d="M0 0h77v20H0z"/>
								<path fill="%s" d="M77 0h73v20H77z"/>
								<path fill="url(#b)" d="M0 0h150v20H0z"/>
							</g>
							<g fill="#fff" text-anchor="middle" font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="11">
								<text x="38.5" y="15" fill="#010101" fill-opacity=".3">%s</text>
								<text x="38.5" y="14">%s</text>
								<text x="112.5" y="15" fill="#010101" fill-opacity=".3">%s</text>
								<text x="112.5" y="14">%s</text>
							</g>
						</svg>';

		public function init()
		{
			$sucess = false;
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
				$files = scandir($this->getDirectory());
				if($this->getRecursive()){
					$scan = $this->scan($files, true);
				}else{
					$scan = $this->scan($files, false);
				}
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
			$this->writeSvg('countLines', sprintf($this->_svg, '#007ec6', 'lines', 'lines', $value, $value));
		}

		public function makeSvgCountDigits($value)
		{
			$this->writeSvg('countDigits', sprintf($this->_svg, '#007ec6', 'digits', 'digits', $value, $value));
		}

		public function makeSvgCountFiles($value)
		{
			$this->writeSvg('countFiles', sprintf($this->_svg, '#007ec6', 'files', 'files', $value, $value));
		}

		public function makeSvgCountLogicLines($value)
		{
			$this->writeSvg('countLogicLines', sprintf($this->_svg, '#007ec6', 'logic lines', 'logic lines', $value, $value));
		}

		public function makeSvgCountLogicDigits($value)
		{
			$this->writeSvg('countLogicDigits', sprintf($this->_svg, '#007ec6', 'logic digits', 'logic digits', $value, $value));
		}

		public function writeSvg($filename, $svg)
		{
			$fopen = fopen($this->getDirSaveSvg() . '/' . $filename . '.svg', 'w');
			fwrite($fopen, $svg);
			fclose($fopen);
		}

		public function scan(array $files, $recursive = true, $path = false)
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
						$filePath = $this->getDirectory() . $path . $file;
					}else{
						$filePath = $this->getDirectory() . $file;
					}
					if($recursive && is_dir($filePath)){
						$files = scandir($this->getDirectory() . $filePath . '/', $recursive);
						$return[] = $this->scan($files, $recursive, ($path ? $path : '') . $file . '/', false);
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
			die(0);
		}

		private function _echo($var)
		{
			echo $var;
		}
	}
}