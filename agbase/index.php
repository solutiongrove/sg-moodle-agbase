<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/agbase/locallib.php');

require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

admin_externalpage_setup('local_agbase', '');

$use_url = trim(get_config('local_agbase', 'agserverurl'));
if (empty($use_url)) {
  $use_url = 'http://ka.academygrove.com';
}
$use_moodleid = trim(get_config('local_agbase', 'agservermoodleid'));
$use_oauthkey = trim(get_config('local_agbase', 'agoauthkey'));
$use_oauthsecret = trim(get_config('local_agbase', 'agoauthsecret'));
$agbase_settings_form = new local_agbase_form(null,array('url'=>$use_url, 'moodleid'=>$use_moodleid, 'oauthkey'=>$use_oauthkey, 'oauthsecret'=>$use_oauthsecret));
$form_data = $agbase_settings_form->get_data();

$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname . ': ' . get_string('pluginname', 'local_agbase'));

echo $OUTPUT->header();

if (!empty($form_data) and confirm_sesskey()) {
  set_config('agserverurl', $form_data->agserverurl, 'local_agbase');
  set_config('agservermoodleid', $form_data->agservermoodleid, 'local_agbase');
  set_config('agoauthkey', $form_data->agoauthkey, 'local_agbase');
  set_config('agoauthsecret', $form_data->agoauthsecret, 'local_agbase');
}

$agbase_settings_form->display();


echo $OUTPUT->footer();