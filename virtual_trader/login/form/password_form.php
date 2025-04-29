<!-- Ce fichier permet de compléter un formulaire de création de compte -->
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="../../css/style.css">
        <title> Changer de mot de passe </title>
    </head>
    <body>
        <h1>Changer de mot de passe</h1>
        <!-- formulaire avec comme paramètres user - password et les envoie vers repcreate.php avec la méthode post -->
        <div class="formulaire_connexion">
            <form method="post" action="../password_change.php">
                Identifiant : <br><input type="text" name="user" placeholder="Entrez votre identifiant" /><br />
                Mot de passe actuel : <br><input type="text" name="old_password" placeholder="Entrez votre ancien mot de passe" /><br />
                Nouveau mot de passe : <br><input type="text" name="new_password" placeholder="Entrez votre nouveau mot de passe" /><br />
                Confirmation du nouveau mot de passe : <br><input type="text" name="password_confirmation" placeholder="Confirmez du nouveau mot de passe" /><br />
                <input type="submit" name="mdp" value="Changer de mot de passe" />
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