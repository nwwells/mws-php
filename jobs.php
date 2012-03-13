<?php

# Define exception types

# for 401s
class MwsAuthenticationException extends Exception {};
# for 403s
class MwsAuthorizationException extends Exception {};
# for unauthorized users
class UserAuthorizationException extends Exception {};

# Define configuration constants
if (!defined('MWS_SCHEME')) define('MWS_SCHEME', 'http');
if (!defined('MWS_HOST')) define('MWS_HOST', 'localhost');
if (!defined('MWS_PORT')) define('MWS_PORT', 8080);
if (!defined('MWS_BASE')) define('MWS_BASE', '/mws/rest/');
if (!defined('MWS_USER')) define('MWS_USER', 'admin');
if (!defined('MWS_PASS')) define('MWS_PASS', 'adminpw');

# utility function to get a CURL configured for MWS
function get_curl($resource) {
  $url = MWS_SCHEME . '://' . MWS_HOST . ':' . MWS_PORT . MWS_BASE . $resource;
  $userpwd = MWS_USER . ':' . MWS_PASS;

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
  return $ch;
}


function get_job($job_id, $username, $decode = true) {
  
  #Get job object from MWS
  $ch = get_curl("jobs/" . $job_id);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $resp = curl_exec($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);

  if($info['http_code'] == 401) {
    throw new MwsAuthenticationException('MWS has refused access to the configured username/password');
  }

  $job = json_decode($resp);
  if($job->user != $username) {
    throw new UserAuthorizationException("User '".$username."' is not authorized to see job '".$job_id."'");
  } else {
    return $job;
  }
}
?>
