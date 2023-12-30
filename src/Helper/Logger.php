<?php
namespace Grpcphp\Helper;

class Logger 
{
    public function info($message)
    {
        echo "[".date(DATE_ATOM)."][info] " . $message . " \n";
    }
}

