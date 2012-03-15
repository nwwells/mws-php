<?php

require 'jobs.php';

try { 
  var_dump(get_jobs('adaptive'));
} catch (Exception $e) {
  echo $e->getMessage();
}
?>
