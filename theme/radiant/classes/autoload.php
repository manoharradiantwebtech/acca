<?php

spl_autoload_register(function ($class) {
    global $CFG;
    $theme = $CFG->theme;
    $themepath = $CFG->dirroot . '/theme/' . $theme;
    $classfile = str_replace('\\', '/', $class) . '.php';
    $filepath = $themepath . '/classes/' . $classfile;
    if (file_exists($filepath)) {
        require_once($filepath);
    }
});
