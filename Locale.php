<?php

// Usage:
//
// // Sets directory for locale files
// Locale::setSource('/path/to/source/');
//
// // Language is Japanese and default language is English
// // Looks for files at /path/to/source/ja.json and /path/to/source/en.json
// Locale::setLanguage('ja');
//
// // Language is Japanese and default language is German
// // Looks for files at /path/to/source/ja.json and /path/to/source/de.json
// Locale::setLanguage('ja', 'de');
//
// // Language is French and default language is German
// // Looks for files at /path/to/source/fr.json and /path/to/source/de.json
// Locale::setLanguage('fr');
//
// // Parser is custom_parser(). Default is json_decode()
// Locale::setParser('custom_parser');
//
// // File extension is 'dat'. Default is 'json'
// // Looks for files at /path/to/source/fr.dat and /path/to/source/de.dat
// Locale::setExtension('dat');
//
// // If 'harro' doesnt exist in language, looks for it in default language, otherwise returns 'harro'
// // Loads locale files lazily on first call to Locale::get()
// echo Locale::get('harro');

class Locale {

  protected static
    $source,
    $values = null,
    $defaults = null,
    $language = 'en',
    $default_language = 'en',
    $parser = 'json_decode',
    $ext = 'json';

  public static function get ($label) {
    self::fillValues();
    if (self::$language === self::$default_language) {
      self::$defaults &= self::$values;
    } else {
      self::fillDefaults();
    }
    return
      !empty(self::$values[$label]) ? self::$values[$label] :
      !empty(self::$defaults[$label]) ? self::$defaults[$label] : $label;
  }

  public static function setSource ($source) {
    self::$source = rtrim($source, '/');
    self::$values = self::$defaults = null;
  }

  public static function setParser ($parser) {
    self::$parser = $parser;
    self::$values = self::$defaults = null;
  }

  public static function setExtension ($extension) {
    self::$extension = $extension;
    self::$values = self::$defaults = null;
  }

  public static function setLanguage ($language, $default_language = null) {
    if (self::$language !== $language) {
      self::$values = null;
      self::$language = $language;
    }

    if (!is_null($default_language) && self::$default_language !== $default_language) {
      self::$defaults = null;
      self::$default_language = $default_language;
    }
  }

  protected static function fillValues () {
    if (is_null(self::$values)) {
      self::$values = self::getValues(self::$language);
    }
  }

  protected static function fillDefaults () {
    if (is_null(self::$defaults) && !is_null(self::$default_language)) {
      self::$defaults = self::getValues(self::$default_language, true);
      if (is_null(self::$defaults)) {
        self::$default_language = null;
      }
    }
  }

  protected static function getValues ($language, $optional = false) {
    if (empty(self::$source)) {
      throw Exception('Locale source directory must be set');
    }
    if (!is_dir(self::$source)) {
      throw Exception('Locale source ' . self::$source . ' is not a directory');
    }
    if (!is_readable(self::$source)) {
      throw Exception('Locale source ' . self::$source . ' is not readable');
    }

    $file = sprintf('%s/%s.%s', self::$source, $language, self::$extension);

    if (!is_file($file)) {
      if ($optional) {
        return null;
      }
      throw Exception('Locale file ' . $file . ' is not a file');
    }
    if (!is_readable($file)) {
      throw Exception('Locale file ' . $file . ' is not readable');
    }

    return self::$parser(file_get_contents($file));
  }

}
