<?php 

    function loginPage() {
        $template = "#FSTRINCLUDE assets/login.html";
        $template = str_replace('__THEME_CSS__', '<style>' . getThemeCss() . '</style>', $template);
        return $template;
    }

?>