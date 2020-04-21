<?php

namespace nlib\Log\Traits;

use nlib\Path\Classes\Path;

trait LogTrait {

    #region Public Method

    public function log(array $values, string $file = 'log_') : string {
        
        $log = $this->ilog($file);
        $string = '';
        
        $date = date('Y-m-d H:i:s');
        if(!empty($values)) :
            if(reset($values) === "\n") $string = "\n";
            else foreach($values as $key => $value) $string .= '[' . $date . '] [' . $key . '] ' . (is_array($value) ? json_encode($value) : $value) . PHP_EOL;
        else : $string = '[' . $date . '] [Log] Empty log values.' . PHP_EOL; endif;

        file_put_contents($log, $string, FILE_APPEND);

        return $string;
    }

    public function vlog(...$parameters) : string {

        $log = $this->ilog('vdlog_', 3);
        $vardump = '';

        ob_start();
        echo "\n". date('Y-m-d H:i:s') . "\n";
        var_dump($parameters);
        echo  "\n";
        $vardump = ob_get_clean();

        file_put_contents($log, $vardump, FILE_APPEND);

        return $vardump;
    }

    public function dvlog(...$parameters) : void {
        $vardump = $this->vlog(...$parameters);
        $this->endlog('vdlog_');
        die($vardump);
    }

    public function endlog(string $file = 'log_') : void { $this->log(["\n"], $file); }

    public function dlog(array $values, string $file = 'log_') : void {
        $message = $this->log($values, $file);
        $this->endlog($file);
        die($message);
    }

    public function jlog(array $values, string $file = 'log_') : void {
        header('Content-type: application/json');
        echo json_encode($values);
        $this->log($values, $file);
        $this->endlog($file);
        die;
    }

    public function clog(int $day = 7) : void {

        $excludes = ['.', '..', 'index.php', '.gitkeep', '.gitignore'];

        if(empty($log = Path::i()->getLog())) die('Log cannot be empty');

        if($folder = opendir($log)) :

            while(false !== ($file = readdir($folder))) :
                
                if(!in_array($file, $excludes)) :

                    $time = explode('_', explode('.', $file)[0]);
                    if(strtotime(end($time)) < time() - $day * 24 * 3600) unlink($log . $file);

                endif;
            endwhile;

            closedir($folder);

        endif;
    }

    #endregion

    #region Private Method

    private function ilog(string $file, int $day = 7) : string {

        if(empty($log = Path::i()->getLog())) die('Log cannot be empty');
        date_default_timezone_set('Europe/Brussels');
        // if(empty(is_dir($log))) mkdir($log, 0777);

        $this->clog($day);
        $log .= DIRECTORY_SEPARATOR . $file . date('Y-m-d') .'.log';

        return $log;
    }
    
    #endregion
}