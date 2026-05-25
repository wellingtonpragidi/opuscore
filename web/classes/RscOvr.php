<?php
/**
 * Resource Override 
 * Substituicao de Recursos
 *
class RscOvr {

    private static $features = [];

    public static function add( $feature ) {
        self::$features[$feature] = true;
    }

    public static function has( $feature ) {
        return isset( self::$features[$feature] ) && self::$features[$feature];
    }

}
*/