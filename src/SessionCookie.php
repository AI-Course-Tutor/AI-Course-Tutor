<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

class SessionCookie {

    public static function set_params_and_start_session(): void
    {
         session_set_cookie_params([
            'secure' => true, // Must be true if SameSite=None
            'httponly' => true, // Helps mitigate XSS attacks
            'samesite' => 'Lax' // Enforce SameSite=lax
        ]);

        session_start();
    }

}
?>
