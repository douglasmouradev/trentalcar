<?php

declare(strict_types=1);

final class Config
{
    /** @var array{name:string,url:string,base:string,env:string,debug:bool,default_lang:string,session_lifetime:int,session_secure:bool,max_upload:int,upload_path:string,per_page:int,privacy:array<string,string>}|null */
    private static ?array $app = null;

    /** @var array{host:string,port:int,database:string,username:string,password:string,charset:string}|null */
    private static ?array $database = null;

    /** @return array{name:string,url:string,base:string,env:string,debug:bool,default_lang:string,session_lifetime:int,session_secure:bool,max_upload:int,upload_path:string,per_page:int,privacy:array<string,string>} */
    public static function app(): array
    {
        if (self::$app !== null) {
            return self::$app;
        }

        /** @var array{name:string,url:string,base:string,env:string,debug:bool,default_lang:string,session_lifetime:int,session_secure:bool,max_upload:int,upload_path:string,per_page:int,privacy:array<string,string>} $cfg */
        $cfg = require BASE_PATH . '/config/app.php';
        self::$app = $cfg;
        return self::$app;
    }

    /** @return array{host:string,port:int,database:string,username:string,password:string,charset:string} */
    public static function database(): array
    {
        if (self::$database !== null) {
            return self::$database;
        }

        /** @var array{host:string,port:int,database:string,username:string,password:string,charset:string} $cfg */
        $cfg = require BASE_PATH . '/config/database.php';
        self::$database = $cfg;
        return self::$database;
    }

    public static function clearCache(): void
    {
        self::$app = null;
        self::$database = null;
    }
}

