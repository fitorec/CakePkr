<?php
/**
 * A very fast and simple Packer whit git options!
 *
 * PHP versions 4 and 5
 *
 * @author	@fitorec
 * @copyright 2014-2015 Miguel Angel Marcial Martinez
 * @version	 0.1
 * @link		https://github.com/fitorec/CakePkr
 * @since	 0.1
 *
 * More information please read:
 *
 *	https://github.com/fitorec/CakePkr
 */

App::uses('AppShell', 'Console/Command');

class CodeSninfferShell extends AppShell {

	public $tasks = array();

	public $errors = 0;

	public function main() {
		$this->checkGitSystem();
		exit($this->errors);
	}

/**
 * Check our file system
 *
 */
	public function checkGitSystem() {
		$cmd = 'git status --porcelain | grep "^[A| M]" | cut -c 4-';
		$modificados = explode("\n", shell_exec($cmd));
		foreach ($modificados as $fileName) {
			$this->__dispatchCdSninf($fileName);
		}
	}//end checkGitSystem

/**
 * Dispatcher fileFullPath
 *
 * @param String $fileFullPath the file.
 * @return success
 * @access public
 */
	private function __dispatchCdSninf($fileFullPath) {
		if (!file_exists($fileFullPath)) {
			return false;
		}
		//Sopport .ctp
		if (preg_match('/\.ctp$/', $fileFullPath)) { // .ctp case
			return $this->__check($fileFullPath);
		}
		//Support .css
		if (preg_match('/\.php$/', $fileFullPath)) {
			return $this->__check($fileFullPath);
		}
	}//end __dispatchCdSninf

/**
 * Apply CssMin to $fileName
 *
 * @return success
 */
	private function __check($fileName) {
		$path = realpath(dirname($fileName));
		$file = basename($fileName);
		$fullPath = $path . DS . $file;
		$cmd = sprintf('phpcs --standard=CakePHP %s', $fullPath);
		$this->out($cmd);
		$ret = exec($cmd, $out, $err);
		foreach ($out as $line) {
			$tag = 'question';
			if (strpos($line, 'F') === 0) {
				$tag = 'warning';
			} elseif (strpos($line, '---') === 0) {
				$tag = 'success';
			}
				$this->out("<{$tag}>{$line}</{$tag}>");
		}
		if ($err) {
			$this->errors++;
		}
	}//end __checkCss

}//end CodeSninfferShell Class
