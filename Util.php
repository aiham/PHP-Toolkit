<?php

class Util {

  static function isPost () {
    return strtolower($_SERVER['REQUEST_METHOD']) === 'post';
  }

  static function h ($str, $q = ENT_QUOTES, $e = 'UTF-8') {
    return htmlspecialchars($str, $q, $e);
  }

  // Redirect status codes
  // 300 Multiple Choices
  // 301 Moved Permanently
  // 302 Found
  // 303 See Other (since HTTP/1.1)
  // 304 Not Modified
  // 305 Use Proxy (since HTTP/1.1)
  // 306 Switch Proxy
  // 307 Temporary Redirect (since HTTP/1.1)
  // 308 Resume Incomplete
  static function redirect ($url, $status = 302) {
    // Prevent header injection in old PHP
    $url = str_ireplace(array("\r", "\n", "%0a", "%0d"), '', $url);

    if (headers_sent()) {
      // Print URL to screen if headers already sent
      printf('Redirecting <a href="%1$s">%1$s</a>', Util::h($url));
    } else {
      header('Location: ' . $url, $status);
    }

    exit;
  }

}
