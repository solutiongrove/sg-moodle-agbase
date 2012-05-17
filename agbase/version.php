<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version      = 2012041900;       // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires     = 2011112900;       // Requires this Moodle version
$plugin->component    = 'local_agbase';   // Full name of the plugin (used for diagnostics)
$plugin->cron         = 0;
$plugin->dependencies = array('local_sg_oauth' => 2012032700);