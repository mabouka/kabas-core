<?php

namespace Kabas\Objects\Image;

use Intervention\Image\ImageManager as Intervention;
use Kabas\App;

class Editor
{
    public $intervention;

    protected $dirname;

    protected $filename;

    protected $extension;

    protected $file;

    protected $history = [];

    function __construct($dir, $file, $extension)
    {
        $this->dirname = $dir;
        $this->filename = $file;
        $this->extension = $extension;
    }

    public function __call($name, $args)
    {
        $this->history[] = $this->getAction($name, $args, $this->getSlug($name, $args));
    }

    protected function getSlug($name, $args)
    {
        return $name . $this->serialize($args);
    }

    protected function serialize($args)
    {
        foreach($args as $key => $arg) {
            if(is_callable($arg)) $args[$key] = 'closure';
        }
        return md5(serialize($args));
    }

    public function hasChanges()
    {
        if (count($this->history)) return true;
        return false;
    }

    public function save()
    {
        $this->setFile();
        if(!file_exists($this->dirname . DS . $this->file)) {
            $this->executeActions();
            $this->intervention->save($this->dirname . DS . $this->file);
        }
        $this->history = [];
        return $this->file;
    }
    
    protected function setFile()
    {
        $this->file = $this->filename . $this->getHistoryString() . '.' . $this->extension;
    }

    protected function getHistoryString()
    {
        $string = '';
        foreach ($this->history as $o) {
            $string .= '-' . str_replace('.', 'dot', $o->slug);
        }
        return $string;
    }

    protected function getAction($name, $args, $slug = null)
    {
        $edit = new \stdClass();
        $edit->action = $name;
        $edit->args = $args;
        $edit->slug = $slug ?? $name;
        return $edit;
    }

    protected function executeActions()
    {
        $this->prepareIntervention();
        if(!isset($this->intervention->filename)) {
            // I don't think this can ever happen
            // @codeCoverageIgnoreStart
            $this->intervention->filename = $this->filename;
            $this->intervention->extension = $this->extension;
            $this->intervention->dirname = $this->dirname;
            // @codeCoverageIgnoreStop
        }
        foreach ($this->history as $o) {
            $this->intervention = call_user_func_array([$this->intervention, $o->action], $o->args);
        }
    }

    public function prepareIntervention()
    {
        if(!$this->intervention){
            $this->intervention = new Intervention(['driver' => App::config()->get('app.imageDriver')]);
            $this->intervention = $this->intervention->make($this->dirname . DS . $this->filename . '.' . $this->extension);
        }
    }
}
