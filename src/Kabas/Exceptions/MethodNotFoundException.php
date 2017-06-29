<?php

namespace Kabas\Exceptions;

use \Exception;

class MethodNotFoundException extends Exception
{
    public $hint;
    public $path;

    public function __construct($name, $code = 0, Exception $previous = null)
    {
        $this->hint = 'Method ' . $name . ' does not exist, please refer to the documentation.';
        $message = 'Error: Method "' . $name . '" does not exist.';
        parent::__construct($message, $code, $previous);
    }

}