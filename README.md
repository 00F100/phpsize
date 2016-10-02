PHPsize
========================================

[![Build Status](https://travis-ci.org/00F100/phpsize.svg?branch=master)](https://travis-ci.org/00F100/phpsize) [![Code Climate](https://codeclimate.com/github/00F100/phpsize/badges/gpa.svg)](https://codeclimate.com/github/00F100/phpsize)

## Show this information about your project:

[![Count Files](https://00f100.github.io/phpsize/countFiles.svg)](https://github.com/00F100/phpsize)
[![Count Lines](https://00f100.github.io/phpsize/countLines.svg)](https://github.com/00F100/phpsize)
[![Count Digits](https://00f100.github.io/phpsize/countDigits.svg)](https://github.com/00F100/phpsize)
[![Count Logic Lines](https://00f100.github.io/phpsize/countLogicLines.svg)](https://github.com/00F100/phpsize)
[![Count Logic Digits](https://00f100.github.io/phpsize/countLogicDigits.svg)](https://github.com/00F100/phpsize)

Measure the size of project in number of files, lines, etc.

Easy configuration and secure result!

Installation
--------------------

```
$ wget https://raw.githubusercontent.com/00F100/phpsize/master/dist/phpsize.phar
```
or
[Download Phar file](https://raw.githubusercontent.com/00F100/phpsize/master/dist/phpsize.phar)

Usage
--------------------

```
PHPsize version 0.1.0
   Usage:
         Return JSON:
         php phpsize.phar --dir <path dir> --extension <valid extension> [--recursive]

         Create badges SVG:
         php phpsize.phar --dir <path dir> --extension <valid extension> --generate-svg <path dir> [--recursive]

   Options:
         -d,  --dir           Directory to load files
         -e,  --extension     Extension of files to load
         -g,  --generate-svg  Directory to save SVG files
         -r,  --recursive     Include subdirectory
         -h,  --help          Show this dialog
```