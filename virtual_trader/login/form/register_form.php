<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../css/style.css">
    <title>Créer un compte</title>
</head>
<body>
    <h1>Créer un compte</h1>
    <div class="formulaire_connexion">
        <form method="post" action="../register.php">
            <label for="user">Identifiant :</label><br>
            <input type="text" id="user" name="user" placeholder="Entrez votre identifiant" required><br>
            
            <label for="password">Mot de passe :</label><br>
            <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required><br>
            
            <input type="submit" name="création" value="Créer un compte">
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