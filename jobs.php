<?php

# Define exception types

# for 401s
class MwsAuthenticationException extends Exception {};
# for 403s
class MwsAuthorizationException extends Exception {};
# for unauthorized users
class UserAuthorizationException extends Exception {};

# Define configuration constants
if (!defined('MWS_SCHEME')) define('MWS_SCHEME', 'https');
if (!defined('MWS_HOST')) define('MWS_HOST', 'localhost');
if (!defined('MWS_PORT')) define('MWS_PORT', 8443);
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
  # TODO - this is improper from a security perspective, since
  # we essentially trust any certificate and, therefore, identity
  # of the MWS server is not guaranteed
  #
  # However, since we can rely on DNS being configured properly
  # at this point, it's not 100% necessary to fix right now.
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  # These are stubs for the correct (secure) settings. Thanks to 
  # http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
  #curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
  #curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  #curl_setopt($ch, CURLOPT_CAINFO, "/path/to/certificate/authority");
  return $ch;
}


function get_job($job_id, $username) {
  
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
