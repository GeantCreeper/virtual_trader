<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <header>
        <h1>
            <span class="header1"><a href="index.php?p=dashboard">Tableau de bord</a></span>
            <span class="header2"><a href="index.php?p=portfolio">Portefeuille</a></span>
            <span class="header3"><a href="index.php?p=ranking">Classement</a></span>
            <?php
            if (isset($_SESSION['user'])) { 
                // Si l'utilisateur est connecté, afficher le lien de déconnexion
                ?>
                <span class="header4"><a href="login/logout.php">Déconnexion</a></span>
                <?php
            } else {
                // Sinon, afficher le lien de connexion
                ?>
                <span class="header4"><a href="login/login_form.php">Connexion</a></span>
                <?php
            }
            ?>
        </h1>
    </header>
</body>
</html>