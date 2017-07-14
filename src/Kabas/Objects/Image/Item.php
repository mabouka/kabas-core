<?php

namespace Kabas\Objects\Image;

use Kabas\Objects\Image\Editor;
use Kabas\Utils\Image;
use Kabas\Utils\Url;

class Item
{
    public $error = false;
    public $path;
    public $dirname;
    public $filename;
    public $extension;
    public $src;
    protected $renamed;
    protected $editor;

    public function __construct($content)
    {
        if(is_array($content)) $content = (object) $content;
        if(!is_object($content)) return $this->error = true;
        $content = $this->mergeWithBase($content);
        $this->setFile($content->path);
        $this->setAlt($content->alt);
    }

    public function __toString()
    {
        return $this->apply()->src();
    }

    public function __call($name, $args)
    {
        $this->makeEditor(false);
        if(!method_exists($this->editor, $name)) return $this->forwardToIntervention($name, $args);
        call_user_func_array([$this->editor, $name], $args);
        return $this;
    }

    protected function forwardToIntervention($name, $args)
    {
        $this->editor->prepareIntervention();
        return call_user_func_array([$this->editor->intervention, $name], $args);
    }

    public function apply()
    {
        if($this->editor && $this->editor->hasChanges()) $this->renamed = $this->editor->save();
        return $this;
    }

    public function show($echo = true)
    {
        $s = '<img src="' . $this->__toString() . '" alt="' . $this->alt() . '" />';
        if($echo) echo($s);
        return $s;
    }

    public function alt()
    {
        return $this->alt;
    }

    public function src()
    {
        return $this->src . '/' . $this->fullname();
    }

    public function fullname()
    {
        if($this->renamed) return $this->renamed;
        return $this->filename . '.' . $this->extension;
    }

    protected function mergeWithBase($content)
    {
        $base = new \stdClass;
        $base->path = false;
        $base->alt = false;
        foreach($content as $key => $value) {
            $base->$key = $value;
        }
        return $base;
    }

    protected function makeEditor($prepareIntervention = true)
    {
        if(!$this->error) {
            if(!$this->editor) {
                $this->editor = new Editor($this->dirname, $this->filename, $this->extension);
            }
            if($prepareIntervention) $this->editor->prepareIntervention();
        }
    }

    protected function setFile($path)
    {
        if(!$path) {
            $this->error = true;
            return false;
        }
        $this->path = realpath(ROOT_PATH . DS . trim($path, '\\/'));
        if(!$this->path){
            $this->error = true;
            return false;
        }
        else{
            $file = pathinfo($this->path);
            $this->dirname = isset($file['dirname']) ? $file['dirname'] : null;
            $this->filename = isset($file['filename']) ? $file['filename'] : null;
            $this->extension = isset($file['extension']) ? $file['extension'] : null;
            $this->src = Url::fromPath($this->dirname);
        }
    }

    protected function setAlt($string)
    {
        $this->alt = $this->getAlt($string);
    }

    protected function getAlt($string = null)
    {
        if(is_string($string)) return $string;
        return $this->filename;
    }
}
