<?php

namespace Kabas\View;

use \RecursiveDirectoryIterator as RDI;
use \RecursiveIteratorIterator as RII;
use Kabas\Utils\Assets;
use Kabas\App;

class View
{
    private static $isFirst;

    public function __construct($_view, $_data, $_directory)
    {
        if(self::isFirstView($_view)) ob_start();

        if(count($_data)) extract($_data);
        include($this->getTemplateFile($_view, $_directory));

        if(self::isFirstView($_view)) $this->showPage();
    }

    /**
     * Check if view is the root one or not.
     * @param  string  $view
     * @return boolean
     */
    static function isFirstView($view)
    {
        if(!isset(self::$isFirst)) {
            self::$isFirst = $view;
            return true;
        }
        else return self::$isFirst === $view;
    }

    /**
     * Includes the template
     * @param  string $view
     * @param  object $data
     * @return void
     */
    static function make($view, $data, $type = '')
    {
        return new self($view,$data,$type);
    }

    /**
     * Looks for the template file within its direcotry.
     * @param  string $view
     * @return string
     */
    protected function getTemplateFile($view, $directory)
    {
        $directory = THEME_PATH . DS . 'views' . DS . $directory;
        $view = $this->checkViewExtension($view);
        return $directory . DS . $view;
    }

    /**
     * Checks if view string contains .php extension
     * and adds it if needed.
     * @param  string $view
     * @return string
     */
    protected function checkViewExtension($view)
    {
        if(strpos($view, '.php') !== false) return $view;
        return $view . '.php';
    }

    protected function showPage()
    {
        if(App::$muted) ob_end_clean();
        $page = ob_get_clean();
        $page = Assets::load($page);
        echo $page;
    }

}
