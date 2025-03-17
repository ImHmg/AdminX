<?php
class ErrorMessage
{
    private static $instance = null;
    public $messages = array();

    public function __construct()
    {
        
    }


    public static function get()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add($message)
    {
        $this->messages[] = $message;
    }

}
?>