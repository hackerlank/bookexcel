# Bookexcel

Export excel to `txt,csv,json,xml` and generate parse code for `C#,Java,PHP...`.
Mostly use in game.

# Feture

* Works in Windows,Linux and Mac OX S
* Support `.xls .xlsx and .ods` format
* Batch works
* Filter by: `file name,sheet name,column,row`
* Easy to extend your own export format and parse code

# How to use

1. Install php and make sure php in `PATH`
2. Edit `bookexcel.php`, add base params, for example:

			$params['inputPath'] = 'excelDir';
			$params['outputPath'] = 'excelExports';
			$params['codeType'] = 'C#';
			$params['codeSavePath'] = 'src';
			$bk = new Bookexcel();
			$bk->parseExcels($params);

3. Run command:

			php bookexcel.php

	For Windows, you can also put php in `runtime` dir and run `bookexcel.bat`.

# Requirement

* php 5.4 or higher

# License

Bookexcel is licensed under the MIT License. See the [LICENSE](https://opensource.org/licenses/MIT) file for more information.