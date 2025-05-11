<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/connexion.css">
    <link rel="stylesheet" type="text/css" href="../../css/style.css">
    <title>Page de connexion</title>
</head>
<body>
    <h1>Se connecter</h1>
    <div class="formulaire_connexion">
        <form method="post" action="../login.php">
            <label for="user">Identifiant :</label><br>
            <input type="text" id="user" name="user" placeholder="Entrez votre identifiant" required><br>
            
            <label for="password">Mot de passe :</label><br>
            <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required><br>
            
            <input type="submit" name="connexion" value="Se connecter">
            <a href="register_form.php">Créer un compte</a>
            <a href="password_form.php">Modifier le mot de passe</a>
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