<?php

/*
|--------------------------------------------------------------------------
| Security Lab configuration (intentionally insecure)
|--------------------------------------------------------------------------
| Shared values for the deliberately vulnerable profile API used in the demo.
*/

return [
    // Breadcrumb handed to logged-in attackers in branch 2; becomes the
    // encryption key material for the branch 3 challenge.
    'salt' => env('LAB_SALT', 'Y0uM16htN33dM3'),
];
