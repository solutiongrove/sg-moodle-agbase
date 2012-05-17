<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot.'/local/sg_oauth/OAuth.php');

class local_agbase_form extends moodleform {
    protected function definition() {
        global $OUTPUT;
        $mform = $this->_form;

        $mform->addElement('html', $OUTPUT->heading(get_string('settings', 'local_agbase'), 3, 'main'));
        $mform->addElement('text', 'agserverurl', get_string('agserverurl', 'local_agbase'), 'maxlength="255" size="40"');
        $mform->addRule('agserverurl', get_string('missingurl', 'local_agbase'), 'required', null, 'client');
        $mform->setDefault('agserverurl', $this->_customdata['url']);
        $mform->addHelpButton('agserverurl', 'agserverurl', 'local_agbase');

        $mform->addElement('text', 'agservermoodleid', get_string('agservermoodleid', 'local_agbase'), 'maxlength="255" size="40"');
        $mform->addRule('agservermoodleid', get_string('missingid', 'local_agbase'), 'required', null, 'client');
        $mform->setDefault('agservermoodleid', $this->_customdata['moodleid']);
        $mform->addHelpButton('agservermoodleid', 'agservermoodleid', 'local_agbase');

        $mform->addElement('text', 'agoauthkey', get_string('agoauthkey', 'local_agbase'), 'maxlength="255" size="40"');
        $mform->addRule('agoauthkey', get_string('missingkey', 'local_agbase'), 'required', null, 'client');
        $mform->setDefault('agoauthkey', $this->_customdata['oauthkey']);
        $mform->addHelpButton('agoauthkey', 'agoauthkey', 'local_agbase');

        $mform->addElement('text', 'agoauthsecret', get_string('agoauthsecret', 'local_agbase'), 'maxlength="255" size="40"');
        $mform->addRule('agoauthsecret', get_string('missingsecret', 'local_agbase'), 'required', null, 'client');
        $mform->setDefault('agoauthsecret', $this->_customdata['oauthsecret']);
        $mform->addHelpButton('agoauthsecret', 'agoauthsecret', 'local_agbase');

        $mform->addElement('submit', 'submitbutton', get_string('save', 'admin'));
    }
}


class local_agbase_rest {

    private $consumer_url;
    private $consumer_key;
    private $consumer_secret;
    private $consumer;
    private $token;
    private $signature_method;
    public $http_status;
    public $last_api_call;
    public $curl_errno;
    public $curl_error;

    function __construct() {
        $this->consumer_url = get_config('local_agbase', 'agserverurl');
        $this->consumer_key = get_config('local_agbase', 'agoauthkey');
        $this->consumer_secret = get_config('local_agbase', 'agoauthsecret');
        $this->consumer = new OAuthConsumer($this->consumer_key, $this->consumer_secret);
        $this->token = new OAuthToken('','');
        $this->signature_method = new OAuthSignatureMethod_HMAC_SHA1();
     }

    function http($url, $post_data = null) {/*{{{*/
        $fp = curl_init();
        if (defined("CURL_CA_BUNDLE_PATH")) curl_setopt($fp, CURLOPT_CAINFO, CURL_CA_BUNDLE_PATH);

        $useragent = null;
        $headers2 = array('Expect:');
        $timeout = 30;
        curl_setopt($fp, CURLOPT_URL, $url);
        curl_setopt($fp, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($fp, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($fp, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($fp, CURLOPT_REFERER, $url);
        curl_setopt($fp, CURLOPT_USERAGENT, $useragent);
        curl_setopt($fp, CURLOPT_HTTPHEADER, $headers2);
        curl_setopt($fp, CURLOPT_SSL_VERIFYPEER, 0);
        if (isset($post_data)) {
            curl_setopt($fp, CURLOPT_POST, 1);
            curl_setopt($fp, CURLOPT_POSTFIELDS, $post_data);
        }
        $response_data = curl_exec($fp);
        if (curl_errno($fp) === 23 || curl_errno($fp) === 61) {
            curl_setopt($fp, CURLOPT_ENCODING, 'none');
            $response_data = curl_exec($fp);
        }
        $this->http_status = curl_getinfo($fp, CURLINFO_HTTP_CODE);
        $this->last_api_call = $url;
        if (curl_errno($fp)) {
            $this->curl_errno = curl_errno($fp);
            $this->curl_error = curl_error($fp);
        }
        curl_close ($fp);
        return $response_data;
    }

    function call($method="POST", $service_name="", $params=array()) {
        $params['rest_service'] = $service_name;
        $oauth_request = OAuthRequest::from_consumer_and_token(
                                                               $this->consumer,
                                                               $this->token,
                                                               $method,
                                                               $this->consumer_url.'/moodleapi/v1',
                                                               $params
                                                               );

        $oauth_request->sign_request($this->signature_method, $this->consumer, $this->token);
        $response = $this->http($this->consumer_url.'/moodleapi/v1', $oauth_request->to_postdata());
        return $response;
    }
}