<?php

/*
 * The shop's public details, shown in the header and footer.
 * Contact lines are only shown when set.
 */
return [
    'name' => env('STUDIO_NAME', 'Cake Studio'),

    'tagline' => env('STUDIO_TAGLINE', 'Custom cakes, designed by you and baked to order.'),

    'phone' => env('STUDIO_PHONE'),

    'email' => env('STUDIO_EMAIL'),

];
