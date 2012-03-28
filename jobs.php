<?php

# Define exception types

# for 401s
class MwsAuthenticationException extends Exception {};
# for 403s
class MwsAuthorizationException extends Exception {};
# for unauthorized users
class UserAuthorizationException extends Exception {};
# general MWS Error code exception
class MwsErrorCodeException extends Exception {};

# Define configuration constants
if (!defined('MWS_SCHEME')) define('MWS_SCHEME', 'http');
if (!defined('MWS_HOST')) define('MWS_HOST', 'localhost');
if (!defined('MWS_PORT')) define('MWS_PORT', 8080);
if (!defined('MWS_BASE')) define('MWS_BASE', '/mws/rest/');
if (!defined('MWS_USER')) define('MWS_USER', 'admin');
if (!defined('MWS_PASS')) define('MWS_PASS', 'adminpw');

# utility function to get a CURL configured for MWS
function run_curl($resource, $config_closure=null, $info_store=null) {
  $url = MWS_SCHEME . '://' . MWS_HOST . ':' . MWS_PORT . MWS_BASE . $resource;
  $userpwd = MWS_USER . ':' . MWS_PASS;

  $ch = curl_init($url);

  # do call specific configuration
  if(isset($config_closure)) call_user_func($config_closure, $ch);

  # These options should override input.
  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

  # TODO - this is improper from a security perspective, since
  # we essentially trust any certificate and, therefore, identity
  # of the MWS server is not guaranteed
  #
  # However, since we can rely on DNS being configured properly
  # at this point, it's not 100% necessary to fix right now.
  #curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  # These are stubs for the correct (secure) settings. Thanks to 
  # http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
  #curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
  #curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  #curl_setopt($ch, CURLOPT_CAINFO, "/path/to/certificate/authority");

  $resp = curl_exec($ch);
  $info_store = curl_getinfo($ch);
  curl_close($ch);

  if($info_store['http_code'] == 401) {
    throw new MwsAuthenticationException('MWS has refused access to the configured username/password');
  } else if ($info_store['http_code'] >= 400) {
    error_log('error when calling MWS!');
    error_log('response: '.$resp);
    error_log('info: '.json_encode($info_store));
    throw new MwsErrorCodeException('Call to MWS failed with error '.$info_store['http_code']);
  }
  return json_decode($resp);
}


function get_job($job_id, $username) {
  #Get job object from MWS
  $job = run_curl("jobs/" . $job_id);

  if (check_user($username, $job)) {
    return $job;
  }
}

function get_jobs($username) {

  #Get job objects from MWS
  $jobs_resp = run_curl("jobs");

  $user_jobs = array();
  foreach ($jobs_resp->results as $job) {
	 if (check_user($username, $job, true)) {
	   array_push($user_jobs, $job);
	 }
  }
  $jobs_resp->results = $user_jobs;
  $jobs_resp->resultCount = count($user_jobs);
  # will need to change this when we support max and offset
  $jobs_resp->totalCount = $jobs_resp->resultCount;

  return $jobs_resp;
}

function submit_job($job) {
  if (!is_string($job))
    $job = json_encode($job);

  return run_curl("jobs", function($ch) use ($job) {
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $job);
  });
}

function check_user($username, $job, $silent=false) {
  if($job->user == $username) {
    return true;
  } else {
    if ($silent) {
      return false;
    } else {
      throw new UserAuthorizationException("User '".$username."' is not authorized to see job '".$job_id."'");
    }
  }
}

?>
