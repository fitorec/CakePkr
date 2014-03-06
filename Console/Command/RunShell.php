<?php
/*
 * ¡A very fast and simple packer whit git options!
 * Please read:
 *  https://github.com/fitorec/CakePkr
 */

App::uses('AppShell', 'Console/Command');

class RunShell extends AppShell {
  public $tasks = array();

  public function main() {
    if(isset($this->args[0])) {
       $this->asignerFilePkr($this->args[0]);
    } else {
      $this->checkGitSystem();
    }
  }

  /**
   * Descripción de la función
   *
   * @param tipo $parametro1 descripción del párametro 1.
   * @return tipo descripcion de lo que regresa
   * @access publico/privado
   * @link [URL de mayor infor]
  */
    function checkGitSystem() {
      //Revisar si recibe como argumento mysqldump exporta las bases
      $cmd = 'git status --porcelain | grep "^[A| M]" | cut -c 4-';
      $modificados = explode("\n", shell_exec($cmd));
      foreach ($modificados as $fileName) {
        echo $fileName."\n";
        $this->asignerFilePkr($fileName);
      }
    }//end function

    /**
     * Descripción de la función
     *
     * @param tipo $parametro1 descripción del párametro 1.
     * @return tipo descripcion de lo que regresa
     * @access publico/privado
     * @link [URL de mayor infor]
     */
    function asignerFilePkr($fileFullPath) {
        if(!file_exists($fileFullPath)) {
          return false;
        }
        //Soport .ctp
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
    }//end function

    /**
     * Genera archivos CSS en formato compacto!
     *
     */
    function checkLess($fileName) {
      if (!class_exists('lessc')) {
        $this->loadLessCompiler();
      }
      //Generamos el nuevo nombre del archivo y el contendio
      $newFile = preg_replace('/\.less$/', '.min.css', $fileName);
      $less = new lessc;
      try {
        $newContent = $less->compile(file_get_contents($fileName));
        //Finalmente con esta información generamos el archivo
        if($this->write($newFile, $newContent)){
          $this->showExport($fileName, $newFile);
        }
      } catch (exception $e) {
        throw new Exception($e->getMessage());
      }
    }

    /**
     * Genera archivos CSS en formato compacto!
     *
     */
    function checkCss($fileName) {
      //Si el archivo modificado ya es compacto no hacer nada
      if(preg_match('/\.min\.css$/', $fileName)) {
        return;
      }
      if (!class_exists('CssMin')) {
        $this->loadCssMin();
      }
      //Generamos el nuevo nombre del archivo y el contendio
      $newFile = preg_replace('/\.css$/', '.min.css', $fileName);
      $newContent = CssMin::minify(file_get_contents($fileName));
      //Finalmente con esta información generamos el archivo
      if($this->write($newFile, $newContent)){
        $this->showExport($fileName, $newFile);
      }
    }

    /**
     * Genera archivos CSS en formato compacto!
     *
     */
    function checkJs($fileName) {
      //Si el archivo modificado ya es compacto no hacer nada
      if(preg_match('/\.min\.js$/', $fileName)) {
        return;
      }
      if (!class_exists('JSMin')) {
        $this->loadJsMin();
      }
      //Generamos el nuevo nombre del archivo y el contendio
      $newFile = preg_replace('/\.js$/', '.min.js', $fileName);
      $newContent = JSMin::minify(file_get_contents($fileName));
      //Finalmente con esta información generamos el archivo
      if($this->write($newFile, $newContent)){
        $this->showExport($fileName, $newFile);
      }
    }

/**
 * Escribe el contenido $content en el archivo ($filename)
 *
 * @param string $filename El archivo a escribir
 * @param string $contents El contenido a escribi
 * @throws RuntimeException
 */
 public function write($filename, $content) {
    $path = realpath(dirname($filename));

		if (!is_writable($path)) {
			throw new RuntimeException('The path: ' . $path . ' not is write');
		}
    exec("git add '{$filename}'");
		return file_put_contents($filename, $content) !== false;
	}


/**
 * Load the less compiler
 *
 */
  public function loadLessCompiler() {
    App::import('Vendor', 'cssmin', array('file' => 'lessphp/lessc.inc.php'));
    if (!class_exists('lessc')) {
      throw new Exception(sprintf('Cannot not load filter class "%s".', 'lessc'));
    }
  }

/**
 * Carga la libreria CSSMin en caso de usarse :¬)
 *
 */
  public function loadCssMin() {
    App::import('Vendor', 'cssmin', array('file' => 'cssmin/CssMin.php'));
    if (!class_exists('CssMin')) {
      throw new Exception(sprintf('Cannot not load filter class "%s".', 'CssMin'));
    }
  }

  /**
   * Carga la libreria JSMin en caso de usarse :¬)
   */
  public function loadJsMin() {
    App::import('Vendor', 'jsmin', array('file' => 'jsmin/jsmin.php'));
    if (!class_exists('JSMin')) {
      throw new Exception(sprintf('Cannot not load filter class "%s".', 'CssMin'));
    }
  }

    
  /**
   * Muestra la generación de una nueva exportación
   */
  function showExport($file_org, $file_dst) {
    $this->out($file_org, 0);
    $this->out('<warning> -> </warning>', 0);
    $this->out("<question>{$file_dst}</question>");
  }//end function
}
