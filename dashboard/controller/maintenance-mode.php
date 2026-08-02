<?php
defined('ENTRY_GUARD') or die;

$flag = DIR . '/.maintenance';

$maintenance = URL::GET('maintenance');

$state = ''; $switch = '';

if( $maintenance === 'ON' ) {
    Ensure::writeLock( $flag, date('Y-m-d H:i:s') );
    
    alert( 'warning', 'Modo manutenção ATIVADO' );

    $state = 'Desativar modo manutenção';
    $switch = 'OFF';
} 
else if( $maintenance === 'OFF' ) {
    alert( 'warning', 'Modo manutenção DESATIVADO' );

    $state = 'Ativar modo manutenção';
    $switch = 'ON';

    if( file_exists($flag) ) {
        unlink( $flag );
    }
}
else {
    if( file_exists($flag) ) {
        $state = 'Desativar modo manutenção';
        $switch = 'ON';
    }
    else {
        $state = 'Ativar modo manutenção';
        $switch = 'OFF';
    }
}