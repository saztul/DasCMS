<?php
class RSent extends BRequest 
{
    //read data from server path
    //merge data with $_POST

    private static $data = null;
    
    private function __construct(){}
    
    private static function init()
    {
        global $_POST;
        if(self::$data == null)
        {
            self::$data = $_POST;
            if(get_magic_quotes_gpc())
            {
                foreach ($_POST as $key => $value) 
                {
                    self::$data[$key] = stripslashes($value);
                }
            }
        }
    }
    
    public static function get($key, $encoding = "ISO-8859-15")
    {
        self::init();
        $ret = '';
        if(array_key_exists($key, self::$data))
        {
            $ret = self::$data[$key];
        }
        return mb_convert_encoding($ret, $encoding, 'UTF-8');
    }
    
    public static function has($key)
    {
        self::init();
        return array_key_exists($key, self::$data);
    }
    
    public static function hasValue($key)
    {
        self::init();
        return (array_key_exists($key, self::$data) && !empty(self::$data[$key]));
    }
    
    public static function alter($key, $value, $encoding = "ISO-8859-15")
    {
        self::init();
        self::$data[$key] = mb_convert_encoding($value, 'UTF-8', $encoding);
    }
    
    public static function data($encoding = "ISO-8859-15")
    {
        self::init();
        $data = self::$data;
        if(strtoupper($encoding) != 'UTF-8')
        {
            $data = array();
            foreach (self::$data as $k => $v) 
            {
                $data[mb_convert_encoding($k, $encoding, 'UTF-8')] = mb_convert_encoding($v, $encoding, 'UTF-8');
            }
            
        }
        return self::$data;
    }
}
?>