<?php

return [
  'gcm' => [
      'priority' => 'normal',
      'dry_run' => false,
      'apiKey' => 'My_ApiKey',
  ],
  'fcm' => [
        'priority' => 'normal',
        'dry_run' => false,
        'apiKey' => 'My_ApiKey',
  ],
  'apn' => [
      'certificate' => app_path().'/apns/user/foodieexpressdev.pem',
      'passPhrase' => env('IOS_PUSH_PASS', 'appoets123$'),
      'passFile' => app_path().'/apns/provider/Certificates_APNS_DEV.pem',
      'dry_run' => true
  ]
];