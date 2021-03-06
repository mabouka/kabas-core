<?php

namespace Kabas\Database;

use Kabas\Utils\Text;
use Kabas\Utils\Lang;
use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class Model extends EloquentModel
{
    use Concerns\HasFields;

    /**
    * The current model's singular name
    * @var string
    */
    static protected $object;

    /**
    * The current model's table or directory name
    * @var string
    */
    static protected $repository;

    /**
    * The current model's structure file
    * @var string
    */
    static protected $structure;

    /**
    * Indicates if the model is translatable
    * @var bool
    */
    static protected $translated = true;

    /**
     * The "booting" method of the model.
     * @return void
     */
    protected static function boot()
    {
        static::constructObjectName();
        static::constructRepositoryName();
        static::constructStructureFileName();
        parent::boot();
    }

    /**
     * Returns the current model's object name
     * @return string
     */
    public function getObjectName() : string
    {
        return static::$object;
    }

    /**
     * Returns the current model's repository name
     * @return string
     */
    public function getRepositoryName() : string
    {
        return static::$repository;
    }

    /**
     * Returns the current model's repository path for given locale
     * @param string $locale
     * @return string
     */
    public function getRepositoryPath($locale = null) : string
    {
        if(is_null($locale)) $locale = SHARED_DIR;
        return realpath(CONTENT_PATH) . DS . $locale . DS . $this->getRepositoryName();
    }

    /**
     * Returns the all the paths for the current model's content directories
     * @return array
     */
    public function getRepositories() : array
    {
        $paths = [SHARED_DIR => $this->getRepositoryPath()];
        if(!$this->isTranslatable()) return $paths;
        foreach (Lang::getAll() as $locale) {
            $paths[$locale->original] = $this->getRepositoryPath($locale->original);
        }
        return $paths; 
    }

    /**
     * Returns the current model's full path to its JSON structure file
     * @return string
     */
    public function getStructurePath() : string
    {
        $path = realpath(THEME_PATH . DS . 'structures' . DS . 'models' . DS . static::$structure);
        if(!$path) throw new \Kabas\Exceptions\FileNotFoundException($path);
        return $path;
    }

    /**
     * Indicates if the model has translatable fields
     * @return bool
     */
    public function isTranslatable() : bool
    {
        return static::$translated;
    }

    /**
     * Initializes the object name
     * @return void
     */
    protected static function constructObjectName()
    {
        if(strlen(static::$object)) return;
        static::$object = lcfirst(Text::removeNamespace(static::class));
    }

    /**
     * Initializes the repository and attribute on this model
     * @return void
     */
    protected static function constructRepositoryName()
    {
        static::$repository = static::$repository ?? static::$object . 's';
    }

    /**
     * Initializes the structure attributes on this model
     * @return void
     */
    protected static function constructStructureFileName()
    {
        static::$structure = static::$structure ?? static::$object . '.json';
    }

    /**
     * Fill the model with an array of attributes.
     * @param  array  $attributes
     * @return $this
     */
    public function fill(array $attributes)
    {
        parent::fill($attributes)->makeFieldsFromRawAttributes($attributes);
        return $this;
    }
    
    /**
     * Create a new model instance that is existing.
     * @param  array  $attributes
     * @param  string|null  $connection
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = parent::newFromBuilder($attributes, $connection);
        $model->updateFieldsFromRawAttributes((array) $attributes);
        return $model;
    }

    /**
     * Set a given attribute on the model.
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        return parent::setAttribute($key, $value)
                ->setField($key, $this->getAttributeValue($key));
    }

    /**
     * Alias of getRepository
     * @return string
     */
    public function getTable()
    {
        return $this->getRepositoryName();
    }

    /**
     * Dynamically retrieve attributes on the model.
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getField($key) ?? $this->getAttribute($key);
    }
}
