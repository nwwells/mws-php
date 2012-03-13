<?php

require 'jobs.php';

try { 
  $job = get_job('Moab.10', 'adaptive');
  if (gettype($job) == 'string') {
    echo $job;
  } else {
    echo var_dump($job);
  }
} catch (Exception $e) {
  echo $e->getMessage();
}
?>
