<!-- Ce fichier permet de compléter un formulaire de connexion -->
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="../css/connexion.css">
        <title> Page de connexion </title>
    </head>
    <body>
        <h1>Se connecter</h1>
        <!-- formulaire avec comme paramètres user - password et les envoie vers login.php avec la méthode post -->
        <div class="formulaire_connexion">
            <form method="post" action="../login.php">
                Identifiant : <br><input type="text" name="user" placeholder="Entrez votre identifiant" /><br />
                Mot de passe : <br><input type="text" name="password" placeholder="Entrez votre mot de passe" /><br />
                <input type="submit" name="connexion" value="Se connecter" />
                <a href="register_form.php">Créer un compte</a>
                <a href="password_form.php">Modifier le mot de passe</a>
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