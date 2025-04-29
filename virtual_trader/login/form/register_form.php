<!-- Ce fichier permet de compléter un formulaire de création de compte -->
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="../../css/style.css">
        <title> Créer un compte </title>
    </head>
    <body>
        <h1>Créer un compte</h1>
        <!-- formulaire avec comme paramètres user - password et les envoie vers repcreate.php avec la méthode post -->
        <div class="formulaire_connexion">
            <form method="post" action="../register.php">
                Identifiant : <br><input type="text" name="user" placeholder="Entrez votre identifiant" /><br />
                Mot de passe : <br><input type="text" name="password" placeholder="Entrez votre mot de passe" /><br />
                <input type="submit" name="création" value="Créer un compte" />
                <a href="login_form.php">Annuler</a>
            </form>
        </div> 
    </body>
    </html>
<?php
session_start();
if (isset($_SESSION['message'])) {
    echo "<p>" . htmlspecialchars($_SESSION['message']) . "</p>";
    unset($_SESSION['message']); // Supprime le message après affichage
}
?>