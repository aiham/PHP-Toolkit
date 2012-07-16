<?php

require_once dirname(__FILE__) . '/Profiler.php';

header('Content-Type: text/html; charset=utf-8');

Profiler::start('App');

echo "Working...<br>\n";

// Can nest calls to Profiler::start()
Profiler::start('First');
sleep(1);
Profiler::end();

Profiler::start('Second');
sleep(2);
Profiler::end();

Profiler::start('Third');
sleep(1);
Profiler::end();

echo 'Done';

Profiler::end();

Profiler::printResults('html');
// or
// Profiler::printResults('error');
// Profiler::printResults('cli');
