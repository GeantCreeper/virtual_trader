<!-- Ce fichier permet d'afficher le menu qui redirige vers d'autres pages -->
<link rel="stylesheet" type="text/css" href="css/style.css">
<header>
<h1>
    <span class="header1"><a href="index.php?p=dashboard"> Tableau de bord </a></span>
    <span class="header2"><a href="index.php?p=portfolio"> Portefeuille </a></span>
    <span class="header3"><a href="index.php?p=ranking"> Classement </a></span>
    <?php
    if(isset($_SESSION['user'])) // si l'utilisateur connecté prosose la déconnexion sinon propose la connexion
        {
            ?> 
            <span class="header4"><a href="login/logout.php"> Déconnexion </a></span>
            <?php
        }
    else
        {
            ?>
            <span class="header4"><a href="login/login_form.php"> Connexion </a></span>
            <?php
        }
    ?>
</h1>
</header>