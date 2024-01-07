<?php

function connecter()
{
    try {

        $dns="mysql:host=mysql.info.unicaen.fr;port=3306;dbname=22004796_dev;charset=utf8";
        $utilisateur="22004796";
        $motDePasse="jahTiih0engeFail";
        $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                        );
        $connection = new PDO( $dns, $utilisateur, $motDePasse, $options );
        return($connection);
    
    
    } catch ( Exception $e ) {
        echo "Connection à MySQL impossible : ", $e->getMessage();
        die();
    }
}

function controlerDate($valeur) {
    if (preg_match("/^(\d\d\d\d)-(\d{1,2})-(\d{1,2})$/", $valeur, $regs)) {
        $an = $regs[1]; 
        $mois = ($regs[2] < 10) ? "0".$regs[2] : $regs[2]; 
        $jour = ($regs[3] < 10) ? "0".$regs[3] : $regs[3]; 
        if (checkdate($mois, $jour, $an)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function neContientPasDeChiffre($chaine) {
    return preg_match('/^[^0-9]*$/', $chaine);
}

function estNombre($chaine) {
    $regex = '/^[0-9]+$/'; // la regex accepte uniquement des chiffres de 0 à 9
    return preg_match($regex, $chaine);
}  

?>