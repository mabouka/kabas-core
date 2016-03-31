<?php

namespace Kabas\Model;

use Kabas\App;
use Kabas\Utils\Text;

class Model
{
      protected $driver;
      protected $fields;
      protected static $table;
      protected static $instance;

      public function __construct()
      {
            $this->makeModel();
      }

      public function __get($val)
      {
            return $this->fields->$val;
      }

      // We don't need this for now

      // public function __call($name, $args)
      // {
      //       $resp = call_user_func_array([$this->model, $name], $args);
      //       $instance->parse($resp);
      //       return $resp;
      // }

      public static function __callStatic($name, $args)
      {
            $instance = new static;
            $resp = call_user_func_array([$instance->model, $name], $args);
            $instance->parse($resp);
            return $resp;
      }

      private function parse($response)
      {
            // var_dump($response);
      }

      private function makeModel()
      {
            $this->checkDriver();
            $class = Text::toNamespace($this->driver);
            $modelInfo = new \stdClass();
            $modelInfo->table = static::$table;
            $modelInfo->name = 'news';
            $this->model = App::getInstance()->make('Kabas\Drivers\\' . $class, [[], $modelInfo]);
      }

      private function checkDriver()
      {
            if(!isset($this->driver)) $this->setDefaultDriver();
      }

      private function setDefaultDriver()
      {
            $this->driver = 'json';
      }
}
