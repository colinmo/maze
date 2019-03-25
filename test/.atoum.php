<?php

use mageekguy\atoum;

$stdout = new atoum\writers\std\out;
$report = new atoum\reports\realtime\santa;
$script->addReport(
    $report->addWriter($stdout)
);
