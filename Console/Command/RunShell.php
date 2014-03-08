<?php
/**
 * A very fast and simple Packer whit git options!
 *
 * PHP versions 4 and 5
 *
 * @author    @fitorec
 * @copyright 2014-2015 Miguel Angel Marcial Martinez
 * @version   0.1
 * @link      https://github.com/fitorec/CakePkr
 * @since     0.1
 *
 * More information please read:
 *
 *    https://github.com/fitorec/CakePkr
 */

App::uses('AppShell', 'Console/Command');

class RunShell extends AppShell {
  public $tasks = array();

  public function main() {
    if(isset($this->args[0])) {
       $this->dispatchPkr($this->args[0]);
    } else {
      $this->checkGitSystem();
    }
  }//end main

/**
 * Check our file system
 *
 */
  function checkGitSystem() {
    $cmd = 'git status --porcelain | grep "^[A| M]" | cut -c 4-';
    $modificados = explode("\n", shell_exec($cmd));
    foreach ($modificados as $fileName) {
      $this->dispatchPkr($fileName);
    }
  }//end checkGitSystem

/**
 * Dispatcher fileFullPath
 *
 * @param String $fileFullPath the file.
 * @return success
 * @access public
 */
  function dispatchPkr($fileFullPath) {
    if(!file_exists($fileFullPath)) {
      return false;
    }
    //Sopport .ctp
    if(preg_match('/\.ctp$/', $fileFullPath)) { // .ctp case
      //$this->out($fileFullPath);
    }
    //Support .css
    if(preg_match('/\.css$/', $fileFullPath)) {
      return $this->checkCss($fileFullPath);
    }
    //Support .less
    if(preg_match('/\.less$/', $fileFullPath)) {
      return $this->checkLess($fileFullPath);
    }
    //Support .js
    if(preg_match('/\.js$/', $fileFullPath)) {
      return $this->checkJs($fileFullPath);
    }
  }//end dispatchPkr

/**
 * Apply lessc compiler to $fileName
 *
 * @return success
 */
  function checkLess($fileName) {
    if (!class_exists('lessc') and !$this->loadLessCompiler()) {
      return false;
    }
    if (!class_exists('CssMin') and !$this->loadCssMin()) {
      return false;
    }
    $newFile = preg_replace('/\.less$/', '.min.css', $fileName);

    $less = new lessc($fileName);
    try {
      $newContent = CssMin::minify($less->parse());
      if($this->write($newFile, $newContent)){
        $this->showExport($fileName, $newFile);
        return true;
      }
    } catch (exception $e) {
      throw new Exception($e->getMessage());
    }
    return false;
  }//end checkLess

/**
 * Apply CssMin to $fileName
 *
 * @return success
 */
  function checkCss($fileName) {
    if(preg_match('/\.min\.css$/', $fileName)) {
      return false;
    }
    if (!class_exists('CssMin') and !$this->loadCssMin()) {
      return false;
    }
    $newFile = preg_replace('/\.css$/', '.min.css', $fileName);
    $newContent = CssMin::minify(file_get_contents($fileName));
    if($this->write($newFile, $newContent)){
      $this->showExport($fileName, $newFile);
      return true;
    }
  }//end checkCss

/**
 * Apply JsMin to $fileName.
 *
 * @return success
 */
  function checkJs($fileName) {
    if(preg_match('/\.min\.js$/', $fileName)) {
      return false;
    }
    if (!class_exists('JSMin') and !$this->loadJsMin()) {
      return false;
    }
    $newFile = preg_replace('/\.js$/', '.min.js', $fileName);
    $newContent = JSMin::minify(file_get_contents($fileName));
    if($this->write($newFile, $newContent)){
      $this->showExport($fileName, $newFile);
      return true;
    }
    return false;
  }//end checkJs

/**
 * Writes compiled assets to the filesystem
 *
 * @param string $filename The filename to write.
 * @param string $contents The contents to write.
 * @throws RuntimeException
 */
  public function write($filename, $content) {
    $path = realpath(dirname($filename));
    if (!is_writable($path)) {
      throw new RuntimeException('The path: ' . $path . ' not is write');
      return false;
    }
    exec("git add '{$filename}'");
    return file_put_contents($filename, $content) !== false;
  }//end write

/**
 * Load the less compiler
 */
  public function loadLessCompiler() {
    App::import('Vendor', 'lessc', array('file' => 'lessphp/lessc.inc.php'));
    if (!class_exists('lessc')) {
      throw new Exception(sprintf('Cannot not load class "%s".', 'lessc'));
      return false;
    }
    return true;
  }//end loadLessCompiler

/**
 * Load cssmin library
 */
  public function loadCssMin() {
    App::import('Vendor', 'cssmin', array('file' => 'cssmin/CssMin.php'));
    if (!class_exists('CssMin')) {
      throw new Exception(sprintf('Cannot not load class "%s".', 'CssMin'));
      return false;
    }
    return true;
  }//end loadCssMin

/**
 * Load jsmin library
 */
  public function loadJsMin() {
    App::import('Vendor', 'jsmin', array('file' => 'jsmin/jsmin.php'));
    if (!class_exists('JSMin')) {
      throw new Exception(sprintf('Cannot not load class "%s".', 'JSMin'));
      return false;
    }
    return true;
  }//end loadJsMin

/**
 * Show fancy information the how $file_org generate $file_dst
 *
 * Example out:      $file_org -> $file_dst
 */
  function showExport($file_org, $file_dst) {
    $this->out($file_org, 0);
    $this->out('<warning> -> </warning>', 0);
    $this->out("<question>{$file_dst}</question>");
  }//end showExport

}//end RunShell Class
