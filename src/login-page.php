<?php 

    function loginPage() {

        function getValue($key) {
            if (isset($_GET[$key])) {
                return $_GET[$key];
            } elseif (isset($_POST[$key])) {
                return $_POST[$key];
            } elseif (isset($_COOKIE[$key])) {
                return $_COOKIE[$key];
            }
            return '';
        }

        $template = "#FSTRINCLUDE assets/login.html";
        $template = str_replace('__THEME_CSS__', '<style>' . getThemeCss() . '</style>', $template);
        $template = str_replace('__INPUT_SERVER__', getValue('server'), $template);
        $template = str_replace('__INPUT_PORT__', getValue('port'), $template);
        $template = str_replace('__INPUT_USER__', getValue('user'), $template);
        $template = str_replace('__INPUT_SCHEMA__', getValue('schema'), $template);

        if(ErrorMessage::get()->hasErrors()) {
            $template = str_replace('__ERROR__', '<div class="alert alert-danger login-alert mt-3" role="alert">' . ErrorMessage::get()->toString() .  '</div>', $template);
        } else {
            $template = str_replace('__ERROR__', '', $template);
        }

        return $template;
    }

?>