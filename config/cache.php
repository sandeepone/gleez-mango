<?php defined('SYSPATH') OR die('No direct script access.');

return array
(
  'mango' => array(               // Driver group
    'driver' => 'mango',          // Using Mango driver
    'collection' => 'Cache',      // Cahce collection
    'default_expire' => 3600,     // Default expire
  )
);