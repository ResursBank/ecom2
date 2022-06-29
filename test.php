<?php

$a = 'asd';
$b = 'mhm';
$c = '';

switch ($a)
{
    case $a === 'asd':
        $c = 'm';
    case $a[0] === 'a':
        $c .= 'a';
        break;
    case $a[1] === 's':
        $c .= 's';
        break;
}


die(var_dump($c));