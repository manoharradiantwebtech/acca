<?php

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_query';  // To check on upgrade, that module sits in correct place
$plugin->version   = 2019111702;        // The current module version (Date: YYYYMMDDXX)
$plugin->requires  = 2013040500;        // Requires Moodle version 2.5.
$plugin->release   = '1.3.0 (2019111700)';
$plugin->maturity  = MATURITY_STABLE;
$plugin->cron      = 0;
