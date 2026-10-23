<?php

/*
|--------------------------------------------------------------------------
| Security Lab configuration (intentionally insecure)
|--------------------------------------------------------------------------
| Shared values for the deliberately vulnerable profile API used in the demo.
*/

return [
    // The salt leaked in branch 2. In branch 3 it is the key material used to
    // encrypt/decrypt the user id in the URL.
    'salt' => env('LAB_SALT', 'Y0uM16htN33dM3'),
];
