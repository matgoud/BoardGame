<!doctype html>
<html lang="fr">
<head>
  <title>Board game</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="form.css"  type="text/css" >
</head>
<body>

  <ul class="navbar">
    <li><a href="index.php?">Accueil</a></li>
    <li><a href="index.php?action=new_boardGame">Ajouter un jeu</a></li>
    <li><a href="index.php?action=boardGame_lists">Liste des jeux</a></li>
    <li><a href="index.php?action=infos">A propos</a></li>
  </ul>

  <hr class="hr-color">
  <?php  echo $zonePrincipale; ?>
  <hr class="hr-color">
  

</body>
</html>
