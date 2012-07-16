<?php

class Profiler {

  protected static $stack = array(), $results = array(), $label_length = 0;

  public static function start ($label = 'No label') {

    self::$label_length = max(self::$label_length, mb_strlen($label));

    $item = array(

      'label' => $label,
      'start' => microtime(true)

    );

    array_push(self::$stack, $item);

  } // start

  public static function end () {

    $item = array_pop(self::$stack);

    $item['end'] = microtime(true);

    array_push(self::$results, $item);

  } // end

  public static function reset () {

    self::$stack = array();
    self::$results = array();
    self::$label_length = 0;

  } // reset

  public static function printResults ($method = 'cli') {

    if ($method === 'html') {

      echo '<br><pre>';

    } // if

    $label_length = self::$label_length + 5;
    $digit_length = mb_strlen(strval(count(self::$results)));
    $line_length = $label_length + $digit_length + 10;
    $line = str_repeat('-', $line_length);

    self::printLine($line, $method);
    self::printLine('Profiler Results:', $method);

    $i = 1;

    foreach (self::$results as $item) {

      self::printLine(sprintf(

        "%0" . $digit_length . "d. %-" . $label_length . "s %07.4f",
        $i,
        $item['label'],
        ($item['end'] - $item['start'])

      ), $method);

      $i++;

    } //foreach

    self::printLine($line, $method);

    if ($method === 'html') {

      echo '</pre><br>';

    } // if

  } // printResults

  protected static function printLine ($value, $method) {

    switch ($method) {

      case 'error':
        error_log($value);
        break;

      case 'cli':
      case 'html':
        echo $value . "\n";
        break;

      default:
        throw new Exception('Invalid print method: ' . $value);

    } // switch

  } // printLine

} // Profiler
