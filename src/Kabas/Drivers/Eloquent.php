<?php

namespace Kabas\Drivers;

use Kabas\App;
use \Illuminate\Database\Eloquent\Model as IlluminateEloquent;
use \Illuminate\Database\Capsule\Manager as Capsule;

class Eloquent extends IlluminateEloquent
{
    protected static $info;

    public function __construct(array $attributes = [], $info = null)
    {
        self::$info = $info;
        $this->fillable = $info->fillable;
        $this->guarded = $info->guarded;
        $this->table = $info->table;
        $capsule = new Capsule;
        $capsule->addConnection((array) App::config()->settings->database);
        $capsule->bootEloquent();
        App::config()->models->loadModel($info);
        parent::__construct($attributes);
    }

    /**
     * Call instanciateField on every field from the model
     * @param  object $item
     * @return object
     */
    protected function loopThroughFields($item)
    {
        foreach($item as $key => $value) {
            $item->$key = self::instanciateField($key, $value);
        }
        return $item;
    }

    /**
     * Instanciate a Kabas FieldType.
     * @param  string $key
     * @param  string $value
     * @return \Kabas\Fields\[type]
     */
    protected static function instanciateField($key, $value)
    {
        $field = App::config()->models->getField($key, static::$info->name);
        if(!$field) return $value;
        try {
            $class = App::fields()->getClass($field->type);
        } catch (\Kabas\Exceptions\TypeException $e) {
            $e->setFieldName($key, static::$info->name);
            echo $e->getMessage();
            die();
        };
        
        return new $class($key, call_user_func_array([$class,'format'], [$value]), $field);
    }




    /**
     *
     *  All of the code below is overridden Eloquent methods we had to tweak
     *  in order to keep a reference to the static::$info property
     *  everytime this class is instanciated. Best not to touch!
     *
     */


    public function setAttribute($key, $value)
    {
        if((is_object($value) && get_class($value) !== 'Carbon\Carbon') || is_array($value)) {
            $value = json_encode((array) $value);
        }
        parent::setAttribute($key, $value);
    }


     public static function all($columns = ['*'])
     {
        $columns = is_array($columns) ? $columns : func_get_args();
        $instance = new static([], static::$info);
        return $instance->newQuery()->get($columns);
     }

    public static function observe($class, $priority = 0)
    {
        $instance = new static([], static::$info);
        $className = is_string($class) ? $class : get_class($class);
        foreach ($instance->getObservableEvents() as $event) {
            if (method_exists($class, $event)) {
                static::registerModelEvent($event, $className.'@'.$event, $priority);
            }
        }
    }

    public function newInstance($attributes = [], $exists = false)
    {
        $model = new static((array) $attributes, static::$info);
        $model->exists = $exists;
        return $model;
    }


    public static function hydrate(array $items, $connection = null)
    {
        $instance = (new static([], static::$info))->setConnection($connection);
        $items = array_map(function ($item) use ($instance) {
            $item = $instance->loopThroughFields($item);
            return $instance->newFromBuilder($item);
        }, $items);
        return $instance->newCollection($items);
    }

    public static function hydrateRaw($query, $bindings = [], $connection = null)
    {
        $instance = (new static([], static::$info))->setConnection($connection);
        $items = $instance->getConnection()->select($query, $bindings);
        return static::hydrate($items, $connection);
    }

    public static function create(array $attributes = [])
    {
        $model = new static($attributes, static::$info);
        $model->save();
        return $model;
    }

    public static function forceCreate(array $attributes)
    {
        $model = new static([], static::$info);
        return static::unguarded(function () use ($model, $attributes) {
            return $model->create($attributes);
        });
    }

    public static function query()
    {
        return (new static([], static::$info))->newQuery();
    }

    public static function on($connection = null)
    {
        $instance = new static([], static::$info);
        $instance->setConnection($connection);
        return $instance->newQuery();
    }

    public static function onWriteConnection()
    {
        $instance = new static([], static::$info);
        return $instance->newQuery()->useWritePdo();
    }

    public static function with($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }
        $instance = new static([], static::$info);
        return $instance->newQuery()->with($relations);
    }

    public static function destroy($ids)
    {
        $count = 0;
        $ids = is_array($ids) ? $ids : func_get_args();
        $instance = new static([], static::$info);
        $key = $instance->getKeyName();
        foreach ($instance->whereIn($key, $ids)->get() as $model) {
            if ($model->delete()) {
                $count++;
            }
        }
        return $count;
    }

    public static function flushEventListeners()
    {
        if (! isset(static::$dispatcher)) {
        return;
        }
        $instance = new static([], static::$info);
        foreach ($instance->getObservableEvents() as $event) {
        static::$dispatcher->forget("eloquent.{$event}: ".static::class);
        }
    }

    public function replicate(array $except = null)
    {
        $except = $except ?: [
            $this->getKeyName(),
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
        ];
        $attributes = Arr::except($this->attributes, $except);
        $instance = new static([], static::$info);
        $instance->setRawAttributes($attributes);
        return $instance->setRelations($this->relations);
    }

    public static function __callStatic($method, $parameters)
    {
        $instance = new static([], static::$info);
        return call_user_func_array([$instance, $method], $parameters);
    }

}
