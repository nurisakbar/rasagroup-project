<?php

return [
    /*
    | Penegakan hak akses modul panel admin di sisi server. Bila false, hanya menu yang disaring
    | (tidak disarankan). Atur lewat ADMIN_RBAC_ENFORCE di .env.
    */
    'enforce' => env('ADMIN_RBAC_ENFORCE', true),
];
