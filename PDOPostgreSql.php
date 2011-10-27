<?php

class PDOPostgreSql {

  public
    $dbh = null,
    $last_stmt = null;

  protected
    $stmts = array();

  const
    RETURN_RESULTS = 1,
    RETURN_COUNT = 2,
    RETURN_AFFECTED = 4;

  public function __construct ($host, $user, $pass, $name = null) {
    $dsn = is_null($name) ?
      sprintf('pgsql:host=%s', $host) :
      sprintf('pgsql:dbname=%s;host=%s', $name, $host);

    $this->dbh = new PDO($dsn, $user, $pass);
    $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  public function select ($sql, $values = array(), $use_cached = true) {
    return $this->execute($sql, $values, $use_cached);
  }

  public function count ($sql, $values = array(), $use_cached = true) {
    return $this->execute($sql, $values, $use_cached, self::RETURN_COUNT);
  }

  public function update ($sql, $values = array(), $use_cached = true) {
    return $this->execute($sql, $values, $use_cached, self::RETURN_AFFECTED);
  }

  protected function execute ($sql, $values = array(), $use_cached = true, $return_type = self::RETURN_RESULTS) {
    $sql_id = md5($sql);

    if (!$use_cached || !array_key_exists($sql_id, $this->stmts)) {
      $this->stmts[$sql_id] = $this->dbh->prepare($sql);
    }

    if (!$this->stmts[$sql_id]) {
      throw new PDOException('Invalid syntax');
    }

    $stmt =& $this->stmts[$sql_id];
    $this->last_stmt =& $this->stmts[$sql_id];

    if (count($values) > 0) {
      $success = $stmt->execute($values);
    } else {
      $success = $stmt->execute();
    }

    if (!$success) {
      return false;
    }

    if ($return_type === self::RETURN_AFFECTED) {
      return $stmt->rowCount();
    }

    $results = $stmt->fetchAll();

    if ($result_type === self::RETURN_COUNT) {
      return count($results);
    }

    return $results;
  }

  public function clearCache () {
    $this->stmts = array();
    $this->last_stmt = null;
    if (function_exists('gc_collect_cycles')) {
      gc_collect_cycles();
    }
  }

}
