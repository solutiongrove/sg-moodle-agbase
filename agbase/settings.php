<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs this condition or there is error on login page
    $ADMIN->add('localplugins', new admin_externalpage('local_agbase',
            get_string('pluginname', 'local_agbase'),
            new moodle_url('/local/agbase/index.php')));
}