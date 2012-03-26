<?php

// Name: URLCache.php
// Author: Aiham Hammami
// Date: 27 January 2011
//
// Get content from a URL and cache results.
// Cache file path, extension and expiration time are customisable.

class URLCache {

  # Properties

  protected static
    $dir = './',
    $ext = '.tmp',
    $use_locks = false,
    $expiration = 0;

  protected
    $url = null,
    $filename = null,
    $contents = null,
    $contentsFrom = null;

  # Constructor

  public function __construct ($url, $dir = null, $ext = null) {
    if (!empty($dir)) {
      $this->setDir($dir);
    }
    if (!empty($ext)) {
      $this->setExt($ext);
    }
    $this->setURL($url);
  }

  # Static Methods

  // Cache directory path
  public static function setDir ($dir) {
    $dir = str_replace('\\', '/', $dir);
    $dir = rtrim($dir, '/') . '/';
    self::$dir = $dir;
  }

  // Cache file extension
  public static function setExt ($ext) {
    if (strpos($ext, '.') === false) {
      $ext = '.' . $ext;
    }
    self::$ext = $ext;
  }

  // Whether to get an exclusive lock when saving a file or not
  public static function useLocks ($use_locks) {
    if (is_bool($use_locks) ||
       ($use_locks === 1 && $use_locks === 0)) {
      self::$use_locks = $use_locks;
    }
  }

  // $expiration in seconds
  public static function setExpiration ($expiration) {
    if (is_int($expiration)) {
      self::$expiration = $expiration;
    }
  }

  // Delete all files in cache directory with cache file extension
  public static function clearAllCache () {
    $cache_files = glob(self::$dir . '*' . self::$ext);
    if ($cache_files === false) {
      return false;
    }

    foreach ($cache_files as $file) {
      if (is_file($file)) {
        @unlink($file);
      }
    }

    return true;
  }

  # Public Methods

  public function setURL ($url) {
    if (is_string($url) && $url !== '') {
      $this->url = $url;
      $this->filename = null;
    }
  }

  public function URL () {
    return $this->url;
  }

  public function getContentsFrom () {
    return $this->contentsFrom;
  }

  public function filename () {
    if (is_null($this->URL())) {
      return null;
    }

    if (is_null($this->filename)) {
      $this->filename = self::$dir . md5($this->URL()) . self::$ext;
    }
    return $this->filename;
  }

  // Set $from_cache to true to get directly from URL
  public function getContents ($from_cache = true) {
    if (!$from_cache) {
      $this->contentsFrom = 'url';
      return $this->getURLContents();
    }

    if ($this->isCacheValid()) {
      $cache_contents = $this->getCacheContents();
      if (!is_null($cache_contents)) {
        $this->contentsFrom = 'cache';
        return $cache_contents;
      }
    }

    $url_contents = $this->getURLContents();

    if (!is_null($url_contents)) {
      $this->saveCacheContents($url_contents);
    }

    $this->contentsFrom = 'url';
    return $url_contents;
  }

  public function clearCache () {
    $this->contents = null;

    if ($this->cacheExists()) {
      return @unlink($this->filename());
    }
    return true;
  }

  # Protected Methods

  protected function cacheExists () {
    if (is_null($this->filename())) {
      return false;
    }

    return @file_exists($this->filename());
  }

  protected function isCacheReadable () {
    if (is_null($this->filename())) {
      return false;
    }

    return @is_readable($this->filename());
  }

  protected function isCacheValid () {
    if (!$this->cacheExists() || !$this->isCacheReadable()) {
      return false;
    }

    $now = time();
    $modified = @filemtime($this->filename());
    $file_life = $now - intval($modified);

    if ($modified === false || (self::$expiration > 0 && $file_life >= self::$expiration)) {
      return false;
    }

    return true;
  }

  protected function getCacheContents () {
    if (!is_null($this->contents)) {
      return $this->contents;
    }

    if (!$this->cacheExists()) {
      return null;
    }

    $contents = @file_get_contents($this->filename());

    if ($contents !== false) {
      $this->contents = $contents;
    }

    return $contents === false ? null : $contents;
  }

  protected function getURLContents () {
    if (is_null($this->URL())) {
      return null;
    }

    $contents = @file_get_contents($this->URL());

    return $contents === false ? null : $contents;
  }

  protected function saveCacheContents ($contents) {
    if (is_null($this->filename())) {
      return false;
    }

    $this->contents = strval($contents);

    if (self::$use_locks) {
      $success = @file_put_contents($this->filename(), $this->contents, LOCK_EX);
    } else {
      $success = @file_put_contents($this->filename(), $this->contents);
    }

    return $success === false ? false : true;
  }

}

