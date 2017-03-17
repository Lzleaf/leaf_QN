<?php

define('QINIUROOT', str_replace('Qiniu', '', __DIR__));

spl_autoload_register(function($class) {
    include QINIUROOT . str_replace("\\",'/',$class) . '.php';
});

include 'functions.php';