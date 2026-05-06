<?php

declare(strict_types=1);

/**
 * Live chat configuration (Tawk.to).
 *
 * IMPORTANT:
 * - Do not put API secrets here.
 * - Tawk embed only needs property ID + widget ID.
 * - You can toggle chat on/off with "enabled".
 */
return [
    // Optional admin-style toggle for all pages.
    'enabled' => true,

    // Replace these with values from your Tawk.to dashboard embed snippet.
    // Example property_id format: 1234567890abcdef12345678
    'property_id' => '69fb449c7984481c34ee3670',

    // Usually "1" in the embed URL.
    'widget_id' => '1jnuo8192',
];
