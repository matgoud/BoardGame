<?php

class BoardGame{
    
    private $gameId;
    private $gameName;
    private $editor;
    private $style;
    private $releaseDate;
    private $numberPlayers;
    private $durationGame;

    public function __construct($gameId,$gameName,$editor,$style,$releaseDate,$numberPlayers,$durationGame)
    {   
        $this->gameId=$gameId;
        $this->gameName=$gameName;
        $this->editor=$editor;
        $this->style=$style;
        $this->releaseDate=$releaseDate;
        $this->numberPlayers=$numberPlayers;
        $this->durationGame=$durationGame;

    }

    public function __toString(){
        $ligneT= "(<u><b>".$this->gameName."</b></u>, ".$this->editor.", ". $this->style.", ". $this->releaseDate.", ". $this->numberPlayers.", ". $this->durationGame." )<br>";
        return $ligneT;
    }

    public function afficherSimple(){//Cette fonction sert pour la page affichant la liste des jeux. Elle permet d'afficher simplement le nom du jeu et de créer un lien dessus pour renvoyer sur sa page dédiée
        $ligneT = '<a href="index.php?action=selectedGame&gameId='.$this->gameId.'">'.$this->gameName.'</a>';
        return $ligneT;
    }

    public function afficherComplet(){//Cette fonction va nous permetre d'afficher les caractéristiques du jeu

        $ligneT = '<h1>'.$this->gameName.'</h1>';
        $ligneT .=  '<ul class="game">';
        $ligneT .= '<li> Editeur : '.$this->editor.'</li>';
        $ligneT .= '<li> Style de jeu : '.$this->style.'</li>';
        $ligneT .= '<li> Date de sortie : '.$this->releaseDate.'</li>';
        $ligneT .= '<li> Nombre de joueurs max : '.$this->numberPlayers.'</li>';
        $ligneT .= '<li> Durée moyenne d\'une partie : '.$this->durationGame.'min</li>';
        $ligneT .= '</ul>';
        return $ligneT;

    }


}

$gameId=null;$gameName=null;$editor = null;$style = null;$releaseDate = null;$numberPlayers =  null;$durationGame = null;			
$erreur=array("gameName"=>null,"editor"=>null,"style"=>null,"releaseDate"=>null,"numberPlayers"=>null,"durationGame"=>null);
$tab_boardGame=array();

?>