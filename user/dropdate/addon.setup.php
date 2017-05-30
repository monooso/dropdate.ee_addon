<?php

return array(
  'author'      => 'Stephen Lewis',
  'author_url'  => 'http://experienceinternet.co.uk/software/dropdate/',
  'name'        => 'Dropdate',
  'description' => 'Fieldtype enabling users to select a date using 3 or 5 drop-downs (day, month, year[, hour, minute]).',
  'version'     => '2.2.0',
  'namespace'   => 'Dropdate',
  'settings_exist' => TRUE,
  'fieldtypes' => array(
    'dropdate' => array(
      'name' => 'Dropdate',
      'compatibility' => 'date'
    )
  )
);