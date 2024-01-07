<?php

require_once("lib.php");
require_once("BoardGame.php");
$action = key_exists('action', $_GET)? trim($_GET['action']): null;

switch($action){
    
    case "new_boardGame"://Formulaire permettant d'ajouter un nouveau je de société dans la bd

        $cible='new_boardGame';

		if (!isset($_POST["gameName"])	&& !isset($_POST["editor"]) && !isset($_POST["style"]) && !isset($_POST["releaseDate"]) && !isset($_POST["numberPlayers"]) && !isset($_POST["durationGame"]) ){
			include("formulaire_board_game.html");
		}else{

            $gameName = key_exists('gameName', $_POST)? trim($_POST['gameName']): null;
			$editor = key_exists('editor', $_POST)? trim($_POST['editor']): null;
			$style = key_exists('style', $_POST)? trim($_POST['style']): null;
			$releaseDate = key_exists('releaseDate', $_POST)? trim($_POST['releaseDate']): null;
			$numberPlayers = key_exists('numberPlayers', $_POST)? trim($_POST['numberPlayers']): null;
			$durationGame = key_exists('durationGame', $_POST)? trim($_POST['durationGame']): null;

            if ($gameName==""){
				$erreur["gameName"] = "Vous n'avez pas indiqué le nom du jeu !";//Si le champ est vide on renvoit une erreur
			}
			
			if ($editor==""){
				$erreur["editor"] = "Vous n'avez pas indiqué qui est l'éditeur du jeu !";//Si le champ est vide on renvoit une erreur	
			}

			if ($style==""){
				$erreur["style"] = "Vous n'avez pas indiqué le style du jeu !";//Si le champ est vide on renvoit une erreur
			}else if(!neContientPasDeChiffre($style)){
                $erreur["style"] = "Le style du jeu ne pas contenir de chiffre !";//On renvoit une erreur si l'utilisateur a saisit un chiffre
            }
			
			if ($releaseDate==""){
                $erreur["releaseDate"] = "Vous n'avez pas indiqué la date de sortie !";//Si le champ est vide on renvoit une erreur
            }else if(!controlerDate($releaseDate)){
                $erreur["releaseDate"] = "la date rentrée n'est pas correct ou le format n'est pas le bon !";
            }

            if($numberPlayers == NULL){
                $erreur["numberPlayers"] = "Veuillez entrer un nombre de joueurs !";//Si le champ est vide on renvoit une erreur
            }else if($numberPlayers <= 0 && estNombre($numberPlayers)){
                $erreur["numberPlayers"] = "Le nombre de joueurs max ne peut pas être de 0 ou moins !";
            }else if(!estNombre($numberPlayers)){
                $erreur["numberPlayers"] = "Vous ne pouvez pas rentrer de caractère !";//On renvoit une erreur si l'utilisateur a saisit un caractère
            }

            if($durationGame == NULL){
                $erreur["durationGame"] = "Veuillez entrer une durée !";//Si le champ est vide on renvoit une erreur
            }else if($durationGame <= 0 && estNombre($durationGame)){
                $erreur["durationGame"] = "Une partie ne peut pas durer 0 min ou moins !";
            }else if(!estNombre($durationGame)){
                $erreur["durationGame"] = "Vous ne pouvez pas rentrer de caractère !";//On renvoit une erreur si l'utilisateur a saisit un caractère
            }

            $compteur_erreur=count($erreur);
            foreach ($erreur as $cle=>$valeur){
				if ($valeur==null){
                    $compteur_erreur=$compteur_erreur-1;
                }
			}

            if ($compteur_erreur == 0){
                //Si il n'y a pas d'erreur on va pouvoir ajouter le nouveau jeu dans la bd
                $connection =connecter();
                $corps = "<h1>Votre jeu a été ajouté avec succès</h1><br>";

                //Requête préparée permettant d'ajouter les données dur formulaire dans la bd et éviter les injections sql
                $requete = "INSERT INTO `BoardGame` (`gameName`,`editor`,`style`,`releaseDate`,`numberPlayers`,`durationGame`) VALUES (:gameName,:editor,:style,:releaseDate,:numberPlayers,:durationGame);";
                $stmt = $connection->prepare($requete);
                $data = array(
                    ':gameName' => $gameName,
                    ':editor' => $editor,
                    ':style' => $style,
                    ':releaseDate' => $releaseDate,
                    ':numberPlayers' => $numberPlayers,
                    ':durationGame' => $durationGame,
                );
                $stmt->execute($data);//On exécute la requête
                $boardGame = new BoardGame($gameId,$gameName,$editor,$style,$releaseDate,$numberPlayers,$durationGame);
                $corps .= "Ajout dans la base de données de : ". $boardGame;
                $zonePrincipale = $corps ;
                $connection = null;
                
            }else{
                include("formulaire_board_game.html");
            }

        }

    break;

    case "boardGame_lists"://On affiche la listes de tous les jeux présents dans la bd

        $corps='<h1>Liste des jeux</h1>';
        $corps.='<a class=edit-bouton href="index.php?action=order&how=asc">trier de a-z</a>';
        $corps.='<a class=edit-bouton href="index.php?action=order&how=desc">trier de z-a</a>';
		$connection =connecter();
		$requete="SELECT * FROM BoardGame";//Requête permettant d'aller chercher toutes les informations contenu dans la bd
		
		$query  = $connection->query($requete);

		$query->setFetchMode(PDO::FETCH_OBJ);
		
        $corps .='<ul class="game-list">';
		while( $enregistrement = $query->fetch() ){   

            //On va chercher chaque caractéristiques du jeu
            $gameId = $enregistrement->gameId;
            $gameName = $enregistrement->gameName;
            $editor = $enregistrement->editor;
            $style = $enregistrement->style;
            $releaseDate = $enregistrement->releaseDate;
            $numberPlayers = $enregistrement->numberPlayers;
            $durationGame = $enregistrement->durationGame;

            //On instancie un new BoardGame avec les infos de la bd
            $boardGame = new BoardGame($gameId,$gameName,$editor,$style,$releaseDate,$numberPlayers,$durationGame);
            $corps .= "<li>".$boardGame->afficherSimple()."</li>";//On ajoute chaque nom de jeu dans la liste
  
		}
        $corps .= "</ul>";
		$zonePrincipale=$corps ;
		$query = null;
		$connection = null;

    break;
    
    case "selectedGame"://Page dédiée pour chaque jeu ou l'on affiche toutes les caractéristiques du jeu sélectionnée

        $corps ="";

        if(key_exists('gameId',$_GET)){
            $gameId = key_exists('gameId', $_GET)? trim($_GET['gameId']): null;

            $connection = connecter();
            $requete="SELECT * FROM BoardGame WHERE gameId=:gameId";//On va chercher les infos d'un jeu spécifique grâce à son gameId
            $stmt = $connection->prepare($requete);
            $data = array(':gameId'=>$_GET["gameId"]);
            $stmt->execute($data);
            $stmt->setFetchMode(PDO::FETCH_OBJ);
            $enregistrement = $stmt->fetch();
    
            $gameId = $enregistrement->gameId;
            $gameName = $enregistrement->gameName;
            $editor = $enregistrement->editor;
            $style = $enregistrement->style;
            $releaseDate = $enregistrement->releaseDate;
            $numberPlayers = $enregistrement->numberPlayers;
            $durationGame = $enregistrement->durationGame;
    
            $boardGame = new BoardGame($gameId,$gameName,$editor,$style,$releaseDate,$numberPlayers,$durationGame);
            $corps .= $boardGame->afficherComplet();//On affiche tous les détails du jeu
    
            $corps .= '<a class="deletebouton" href="index.php?action=confirmation&gameId='.$gameId.'">Supprimer</a>';
            $corps .= '<a class="edit-bouton" href="index.php?action=editgame&gameId='.$gameId.'">Modifier</a>';
    
            $zonePrincipale=$corps;
            $query = null;
            $connection = null;
        }else{
            $zonePrincipale = "<h1>Erreur</h1>";
        }

    break;

    case "confirmation"://Page qui permet de demander la confirmation de la suppresion à l'utilisateur

        if(key_exists('gameId',$_GET)){
            $gameId = $_GET['gameId'];
            $zonePrincipale='<form action="index.php?action=deletegame&gameId='.$gameId.'" method="post">
            <h2>Voulez vous vraiment supprimer le jeu ?</h2>
            <p>
                <input type="submit" value="Enregistrer" class="submit-delete">
                <a href="index.php?action=boardGame_lists" class="edit-bouton">Annuler</a>
            </p>
            </form>';
        }else{
            $zonePrincipale = "<h1>Erreur</h1>";
        }

	break;

    case "deletegame"://Page qui va supprmier le jeu de la bd si l'utilisateur a confirmé

        if(key_exists('gameId',$_GET)){
            $gameId = $_GET['gameId'];
            $sql = "DELETE FROM BoardGame WHERE gameId=:gameId";
            $connection = connecter();
            $data = array(':gameId' => $gameId);
            $req=$connection->prepare($sql);
            $req->execute($data);		
            $connection = null;
            $zonePrincipale="<h1>Le jeu a été supprimer avec succès</h1>";	
        }else{
            $zonePrincipale = "<h1>Erreur</h1>";
        }

    break;

    case "editgame"://Page ouvrant le formulaire contenant les informations du jeu que l'on veut modifier

		if(key_exists('gameId',$_GET)){
			$connection = connecter();
			$gameId = $_GET['gameId'];
			$requete = "Select * From BoardGame where gameId = :gameId";//On va récupérer les informations du jeu que l'on souhaite modifier
			$stmt = $connection->prepare($requete);
			$data = array(':gameId'=>$_GET["gameId"]);
			$stmt->execute($data);
			$stmt->setFetchMode(PDO::FETCH_OBJ);
			$res = $stmt->fetch();
			$cible="";
			if($res!=null){
				$cible = "editgame";
				$gameName = $res->gameName;
				$editor = $res->editor;
				$style = $res->style;
				$releaseDate = $res->releaseDate;
				$numberPlayers = $res->numberPlayers;
                $durationGame = $res->durationGame;			
			}
			if (!isset($_POST["gameName"])	&& !isset($_POST["editor"]) && !isset($_POST["style"]) && !isset($_POST["releaseDate"]) && !isset($_POST["numberPlayers"]) && !isset($_POST["durationGame"])){
				include("formulaire_board_game.html");
			}
			else{

                $gameName = key_exists('gameName', $_POST)? trim($_POST['gameName']): null;
                $editor = key_exists('editor', $_POST)? trim($_POST['editor']): null;
                $style = key_exists('style', $_POST)? trim($_POST['style']): null;
                $releaseDate = key_exists('releaseDate', $_POST)? trim($_POST['releaseDate']): null;
                $numberPlayers = key_exists('numberPlayers', $_POST)? trim($_POST['numberPlayers']): null;
                $durationGame = key_exists('durationGame', $_POST)? trim($_POST['durationGame']): null;
			
                if ($gameName==""){
                    $erreur["gameName"] = "Vous n'avez pas indiqué le nom du jeu";	
                }
                
                if ($editor==""){
                    $erreur["editor"] = "Vous n'avez pas indiqué qui est l'éditeur du jeu";	
                }
    
                if ($style==""){
                    $erreur["style"] = "Vous n'avez pas indiqué le style du jeu !";
                }else if(!neContientPasDeChiffre($style)){
                    $erreur["style"] = "Le style du jeu ne pas contenir de chiffre !";
                }
                
                if ($releaseDate==""){
                    $erreur["releaseDate"] = "Vous n'avez pas indiqué la date de sortie !";
                }else if(!controlerDate($releaseDate)){
                    $erreur["releaseDate"] = "la date rentrée n'est pas correct ou le format n'est pas le bon !";
                }
    
                if($numberPlayers == NULL){
                    $erreur["numberPlayers"] = "Veuillez entrer un nombre de joueurs !";
                }else if($numberPlayers <= 0 && estNombre($numberPlayers)){
                    $erreur["numberPlayers"] = "Le nombre de joueurs max ne peut pas être de 0 ou moins !";
                }else if(!estNombre($numberPlayers)){
                    $erreur["numberPlayers"] = "Vous ne pouvez pas rentrer de caractère !";
                }
    
                if($durationGame == NULL){
                    $erreur["durationGame"] = "Veuillez entrer une durée !";
                }else if($durationGame <= 0 && estNombre($durationGame)){
                    $erreur["durationGame"] = "Une partie ne peut pas durer 0 min ou moins !";
                }else if(!estNombre($durationGame)){
                    $erreur["durationGame"] = "Vous ne pouvez pas rentrer de caractère !";
                }
				
				$compteur_erreur=count($erreur);
                foreach ($erreur as $cle=>$valeur){
			    	if ($valeur==null){
                        $compteur_erreur=$compteur_erreur-1;
                    }
			    }
				
				if ($compteur_erreur == 0) {//Si il n'y a pas d'erreur alors on va pouvoir modifier les caractéristiques du jeu
					$zonePrincipale="" ;
					$sql = "Update BoardGame set gameName =:gameName, editor =:editor, style =:style, releaseDate =:releaseDate, numberPlayers =:numberPlayers, durationGame =:durationGame where gameId = :gameId";
                    $data = array(':gameName'=>$gameName,':editor'=>$editor,':style' => $style,':releaseDate' => $releaseDate,':numberPlayers' => $numberPlayers,':durationGame'=>$durationGame,':gameId' => $gameId);
                    $req=$connection->prepare($sql);
                    $req->execute($data);	
                    $zonePrincipale="<h1>Le jeu a été mis à jour avec succès</h1>";		
                    $connection = null;
				}
				else {
					include("formulaire_board_game.html");
				}	
					
			}
		}else{
            $zonePrincipale = "<h1>Erreur</h1>";
        }

    break;

    case "order"://Point complémentaire permet de trier la listes des jeux de a-z ou de z-a

        if(key_exists('how',$_GET)){

            $how = $_GET['how'];

        if($how == 'asc'){//Si how = asc on trie de a-z
                $requete="SELECT * FROM BoardGame ORDER BY gameName ASC";//Requête renvoyant chaque jeux trié par gameName de a-z

            }else{//Sinon de z-a
                $requete="SELECT * FROM BoardGame ORDER BY gameName DESC";//Requête renvoyant chaque jeux trié par gameName de z-a
            }

            $corps='<h1>Liste des jeux</h1>';
            $corps.='<a class=edit-bouton href="index.php?action=order&how=asc">trier de a-z</a>';
            $corps.='<a class=edit-bouton href="index.php?action=order&how=desc">trier de z-a</a>';

            $connection =connecter();

            $query  = $connection->query($requete);

            $query->setFetchMode(PDO::FETCH_OBJ);
            
            $corps .='<ul class="game-list">';
            while( $enregistrement = $query->fetch() ){   
                $gameId = $enregistrement->gameId;
                $gameName = $enregistrement->gameName;
                $editor = $enregistrement->editor;
                $style = $enregistrement->style;
                $releaseDate = $enregistrement->releaseDate;
                $numberPlayers = $enregistrement->numberPlayers;
                $durationGame = $enregistrement->durationGame;

                $boardGame = new BoardGame($gameId,$gameName,$editor,$style,$releaseDate,$numberPlayers,$durationGame);
                $corps .= "<li>".$boardGame->afficherSimple()."</li>";
    
            }
            $corps .= "</ul>";
            $zonePrincipale=$corps ;
            $query = null;
            $connection = null;
        }
    
    break;

    case "infos"://Page d'information sur moi et les fonctionnalités de mon site 

        $corps = "<h2><strong>A propos</strong></h2>";
        $corps .= '
        <p class="infoEtu">Goudal Mathieu 22004796 Grp 3A<br>
        Ce site comporte plusieurs fonctionnalités telles que :</p>
        <ul class="infos">
            <li>Ajout d\'un jeu dans la base de données</li>
            <li>Listes de tous les jeux présents dans la base de données</li>
            <li>Page dédiée pour chaque jeu de la liste</li>
            <li>Suppression/Modification des jeux de la base de données</li>
            <li>Possibilité d\'afficher la liste des jeux par ordre alphabétique</li>
        </ul>
        <p class="infoEtu">Ps : Je n\'étais pas en L1 info l\'année dernière je n\'ai donc pas assisté au cours de TW1/2.<br>
        J\'ai pu rattraper la majeure partie des notions mais pas tout, je vous serais donc reconnaissant de le prendre en compte dans votre notation (pour les notions de TW1/2).<p>
        ';
        $zonePrincipale = $corps;
    
    break;

    default://Page d'accueil de mon site expliquant le but du site
        $zonePrincipale="<h2>Bienvenue sur un site sur les jeux de société</h2>" ;
    break;

}

include("squelette.php");

?>