<?php

namespace Kabas;

use Illuminate\Container\Container;

class App extends Container
{
    /**
    * The current Kabas version
    * @var string
    */
    const VERSION = '0.1.5';

    /**
     * The driver used to read data
     * @var \Kabas\Drivers
     */
    public $driver;

    /**
     * Turns on or off any output
     * @var bool
     */
    public static $muted = false;

    /**
     * The instance of the app
     * @var Kabas
     */
    protected static $instance;

    /**
     * The Illuminate Translator instance
     * @var Illuminate\Translation\Translator
     */
    protected static $translator;

    /**
     * The classes to instantiate for the application to work
     * @var array
     */
    protected static $bootingSingletons = [
        'config' => \Kabas\Config\Container::class,
        'exceptions' => \Kabas\Exceptions\Handler::class,
        'session' => \Kabas\Session\Manager::class,
        'themes' => \Kabas\Themes\Container::class,
        'fields' => \Kabas\Fields\Container::class,
        'router' => \Kabas\Http\Router::class,
        'content' => \Kabas\Content\Container::class,
        'uploads' => \Kabas\Objects\Uploads\Container::class,
        'request' => \Kabas\Http\Request::class,
        'response' => \Kabas\Http\Response::class
    ];

    public function __construct($publicPath)
    {
        self::$instance = $this;
        $this->registerPaths($publicPath);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function __callStatic($name, $arguments)
    {
        if(!method_exists(self::$instance, $name)) {
            return self::$instance->$name;
        }
    }

    /**
     * Initiates the required singletons 
     * in order to get the app rolling.
     * @return void
     */
    public function boot(array $singletons = null)
    {
        if(is_null($singletons)) $singletons = static::$bootingSingletons;
        $this->registerBindings($singletons);
    }

    /**
     * Start up the application
     * @return void
     */
    public function handle()
    {
        $this->loadAliases();
        $this->themes->loadCurrent();
        $this->router->capture()->load()->setCurrent();
        $this->content->parse();
        $this->page = $this->router->getCurrent()->page;
        $this->response->init($this->page);
        $this->session->save();
    }

    /**
     * Load config/app's specified aliases
     * @return void
     */
    public function loadAliases()
    {
        foreach($this->config->get('app.aliases') as $alias => $class) {
            class_alias($class, $alias);
        }
    }

    /**
     * Returns app instance
     * @return Kabas
     */
    static function getInstance()
    {
        return self::$instance;
    }

    /**
     * Sets the driver for the app;
     * @param Driver $driver
     */
    static function setDriver($driver)
    {
        self::$instance->driver = $driver;
    }

    /**
    * Get the version number of this Kabas website.
    * @return string
    */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Defines path constants
     * @return void
     */
    protected function registerPaths($publicPath)
    {
        $this->define('DS', DIRECTORY_SEPARATOR);
        $this->define('CORE_PATH', __DIR__);
        $this->define('PUBLIC_PATH', $publicPath);
        $this->define('ROOT_PATH', realpath(PUBLIC_PATH . DS . '..'));
        $this->define('CONTENT_PATH', ROOT_PATH . DS . 'content');
        $this->define('STORAGE_PATH', ROOT_PATH . DS . 'storage');
        $this->define('SHARED_DIR', 'shared');
        $this->define('CONFIG_PATH', ROOT_PATH . DS . 'config');
        $this->define('THEMES_PATH', ROOT_PATH . DS . 'themes');
    }

    /**
     * Sets a new constant if it does not exist yet
     * @param string $name 
     * @param string $value 
     * @return void
     */
    protected function define($name, $value)
    {
        if(!defined($name)) define($name, $value);
    }

    /**
     * Defines given app singletons
     * @param array $singletons
     * @return void
     */
    protected function registerBindings(array $singletons)
    {
        foreach ($singletons as $name => $class) {
            $this->singleton($name, $class);
            if(method_exists($this[$name], 'boot')) $this[$name]->boot();
        }
    }

    static function preventFurtherOutput()
    {
        self::$muted = true;
    }

}
