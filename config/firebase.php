<?php

return [
    'project_id' => env(strtoupper(config('app.name')) . '_FIREBASE_PROJECT_ID'),
    'private_key' => env(strtoupper(config('app.name')) . '_FIREBASE_PRIVATE_KEY'),
    'client_email' => env(strtoupper(config('app.name')) . '_FIREBASE_CLIENT_EMAIL'),
];
