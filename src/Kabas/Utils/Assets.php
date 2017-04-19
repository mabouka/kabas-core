<?php

namespace Kabas\Utils;

use \Kabas\App;

class Assets
{
      /**
       * Default location tag
       * @var string
       */
      protected static $placeholder = '<meta name="kabas-assets-location" value="%s">';

      /**
       * Keeps track of all required assets
       * @var array
       */
      protected static $required = [];

      /**
       * Adds an assets dependency.
       * @param  string $src
       * @param  string $location
       * @return void
       */
      public static function add($src, $location)
      {
            if(!isset(self::$required[$location])) self::$required[$location] = [];
            if(is_string($src)) self::pushToLocation($location, $src);
            else if(is_array($src)) {
                  foreach ($src as $item) {
                        self::add($item, $location);
                  }
            }
      }

      /**
       * Add a marker to pinpoint the location where
       * the assets will be loaded.
       * @param  string $location
       * @param  string $src (optional)
       * @return void
       */
      public static function here($location, $src = null)
      {
            echo self::getLocationTag($location) . PHP_EOL;
            if($src) self::add($src, $location);
      }

      /**
       * Takes the page buffer and loads the assets at the marked positions.
       * @param  string $page
       * @return string
       */
      public static function load($page)
      {

            preg_match_all(self::getLocationPattern(), $page, $matches);
            foreach($matches[1] as $i => $location) {
                  $page = self::includeLocation($location, self::getLocationAssets($location), $matches[0][$i], $page);
            }
            return $page;
      }

      /**
       * returns the asset's HREF
       * @param  string $src
       * @return string
       */
      public static function src($src)
      {
            $s = Url::base();
            $s .= '/' . App::themes()->getCurrent('name') . '/';
            $s .= $src;
            return $s;
      }

      /**
       * Adds a dependency to location
       * @param  string $location
       * @param  string $src
       * @return void
       */
      protected static function pushToLocation($location, $src)
      {
            if(!in_array($src, self::$required[$location])){
                  self::$required[$location][] = $src;
            }
      }

      /**
       * Generate the proper tag for an asset.
       * @param  object $asset
       * @return string
       */
      protected static function getTag($asset)
      {
            switch($asset->type) {
                  case 'css':
                        return '<link rel="stylesheet" type="text/css" href="' . $asset->src . '" />';
                        break;
                  case 'js':
                        $tag = '<script type="text/javascript"';
                        foreach ($asset->attr as $attr) {
                              $tag .= ' ' . $attr;
                        }
                        return $tag .= ' src="' . $asset->src . '"></script>';
                        break;
                  default:
                        return '<link rel="icon" href="' . $asset->src . '" />';
                        break;
            }
      }

      /**
       * returns the asset's base information
       * @param  string $src
       * @return object
       */
      protected static function parseAsset($src)
      {
            $asset = new \stdClass();
            $asset->attr = [];
            $attributes = explode('-', explode(':',$src)[0]);
            $identifier = array_shift($attributes);
            switch ($identifier) {
                  case 'js':
                  case 'script':
                        $asset->type = 'js';
                        $asset->attr = $attributes;
                        break;
                  case 'css':
                  case 'style':
                        $asset->type = 'css';
                        break;
            }
            if(!isset($asset->type)){
                  $asset->type = strtolower(pathinfo($src, PATHINFO_EXTENSION));
                  $asset->path = $src;
            }
            else $asset->path = substr($src, (strpos($src, ':') + 1));
            return $asset;
      }

      /**
       * returns a location's placeholder tag
       * @param  string $location
       * @return string
       */
      protected static function getLocationTag($location)
      {
            return sprintf(self::$placeholder, $location);
      }

      /**
       * returns a generic regex pattern for placeholders
       * @return string
       */
      protected static function getLocationPattern()
      {
            $s = '/';
            $s .= sprintf(self::$placeholder, '(.+)?');
            return $s .= '/';
      }

      /**
       * returns all available assets for a location
       * @param  string $location
       * @return array
       */
      protected static function getLocationAssets($location)
      {
            $a = [];
            if(isset(self::$required[$location])){
                  foreach (self::$required[$location] as $src) {
                        if($src = self::getAsset($src)) array_push($a, $src);
                  }
            }
            return $a;
      }

      /**
       * returns an asset object if it exists
       * @param  string $src
       * @return object
       */
      protected static function getAsset($src)
      {
            $asset = self::parseAsset($src);
            if (strpos($asset->path,'http') === 0 || strpos($asset->path,'//') === 0) {
                  $asset->src = $asset->path;
            }
            else{
                  $asset->path = realpath(PUBLIC_PATH . DS . App::themes()->getCurrent('name') . DS . $src);
                  if(!$asset->path) return false;
                  $asset->src = self::src($src);
            }
            $asset->tag = self::getTag($asset);
            return $asset;
      }

      /**
       * Replaces a location's placeholder with its registered assets.
       * Returns updated page
       * @param  string $location
       * @param  array $assets
       * @param  string $placeholder
       * @param  string $page
       * @return string
       */
      protected static function includeLocation($location, $assets, $placeholder, $page)
      {
            $page = str_replace($placeholder, self::getTagsString($assets), $page);
            return $page;
      }

      /**
       * returns all tags from assets array in one string
       * @param  array $assets
       * @return string
       */
      protected static function getTagsString($assets)
      {
            $s = '';
            foreach ($assets as $asset) {
                  $s .= $asset->tag . PHP_EOL;
            }
            return $s;
      }
}