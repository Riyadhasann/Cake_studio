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

    /*
     * Icing colors a customer can pick for the message written on the cake (name => hex). The first is the default.
     */
    'message_colors' => [
        'Chocolate' => '#4a2a1a',
        'White' => '#ffffff',
        'Pink' => '#e0608a',
        'Red' => '#c0262d',
        'Blue' => '#2c6db3',
        'Green' => '#2e7d4f',
        'Gold' => '#b8902b',
    ],

];
