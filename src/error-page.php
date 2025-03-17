<?php 

    function errorPage() {
        $template = "#FSTRINCLUDE assets/error-page.html";
        $msg = "";

        foreach(ErrorMessage::get()->messages as $m) {
            $msg .= $m ."</br>";
        }
        $template = str_replace("__ERROR_MESSAGE__",$msg, $template);
        return $template;
    }

?>