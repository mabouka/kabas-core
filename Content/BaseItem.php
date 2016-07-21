<?php

namespace Kabas\Content;

use \Kabas\App;
use \Kabas\Utils\File;

class BaseItem
{
      public $id;

      public $template;

      public $data;

      public $options;

      public $fields;

      public $directory;

      protected $structure;

      public function __construct($data)
      {
            $this->id = isset($data->id) ? $data->id : false;
            $this->template = isset($data->template) ? $data->template : false;
            $this->data = isset($data->data) ? $data->data : new \stdClass();
            $this->options = isset($data->options) ? $data->options : new \stdClass();
            $this->setData($data);
      }

      public function build($data)
      {
            $this->mergeData($data);
            $this->loadFields();
      }

      public function loadFields()
      {
            $structure = $this->getStructure();
            $this->fields = isset($structure->fields) ? $structure->fields : new \stdClass();
            $this->updateData();
      }

      protected function setData($data)
      {
            return null;
      }

      protected function mergeData($data)
      {
            if($data){
                  foreach ($data as $key => $value) {
                        $this->data->$key = $value;
                  }
            }
      }

      protected function getStructure()
      {
            if(is_null($this->structure)) $this->structure = File::loadJson($this->getStructureFile());
            return $this->structure;
      }

      protected function getStructureFile()
      {
            $path = THEME_PATH . DS . 'structures' . DS;
            $path .= $this->directory . DS;
            $path .= $this->template . '.json';
            return $path;
      }

      protected function updateData()
      {
            foreach ($this->fields as $key => $field) {
                  if(!isset($this->data->$key)){
                        $this->data->$key = isset($field->default) ? $field->default : null;
                  }
                  try { 
                        $this->makeFieldData($key, $field);
                  }
                  catch (\Kabas\Exceptions\TypeException $e) {
                        $e->setFieldName($key, $this->id);
                        $e->showAvailableTypes();
                        echo $e->getMessage();
                        die();
                  }
            }
      }

      protected function makeFieldData($key, $field)
      {
            $type = isset($field->type) ? $field->type : 'text';
            if(!App::config()->fieldTypes->exists($type)){
                  $error = 'Type "' . $type . '" is not a supported field type.';
                  throw new \Kabas\Exceptions\TypeException($error);
            }
            $class = App::config()->fieldTypes->getClass($type);
            $multi = isset($field->multiple) ? $field->multiple : false;
            $this->data->$key = App::getInstance()->make($class, [$key, $this->data->$key, $multi]);
      }
}