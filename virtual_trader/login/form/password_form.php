<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../css/style.css">
    <title>Changer de mot de passe</title>
</head>
<body>
    <h1>Changer de mot de passe</h1>
    <div class="formulaire_connexion">
        <form method="post" action="../password_change.php">
            <label for="user">Identifiant :</label><br>
            <input type="text" id="user" name="user" placeholder="Entrez votre identifiant" required><br>
            
            <label for="old_password">Mot de passe actuel :</label><br>
            <input type="password" id="old_password" name="old_password" placeholder="Entrez votre ancien mot de passe" required><br>
            
            <label for="new_password">Nouveau mot de passe :</label><br>
            <input type="password" id="new_password" name="new_password" placeholder="Entrez votre nouveau mot de passe" required><br>
            
            <label for="password_confirmation">Confirmation du nouveau mot de passe :</label><br>
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirmez votre nouveau mot de passe" required><br>
            
            <input type="submit" name="mdp" value="Changer de mot de passe">
            <a href="login_form.php">Annuler</a>
        </form>
    </div>
    <?php
    session_start();
    if (isset($_SESSION['message'])) {
        echo "<p>" . htmlspecialchars($_SESSION['message']) . "</p>";
        unset($_SESSION['message']);
    }
    ?>
</body>
</html>