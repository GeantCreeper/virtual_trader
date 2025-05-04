<?php
session_start();
if (isset($_POST['user']) && isset($_POST['password'])) { // Vérifie si les champs sont remplis
    $serveur = "localhost:3307"; // Nom du serveur
    $utilisateur = "root"; // Nom de l'utilisateur
    $motdepasse = ""; // Mot de passe
    $base_de_donnees = "virtual_trader"; // Base de données

    $connexion = new mysqli($serveur, $utilisateur, $motdepasse, $base_de_donnees); // Connexion à la base de données
    if ($connexion->connect_error) { // Vérifie la connexion
        die("Erreur de connexion à la base de données : " . $connexion->connect_error);
    }

    $user = $_POST['user']; // Récupère le nom d'utilisateur
    $password = $_POST['password']; // Récupère le mot de passe

    // Vérifie si l'utilisateur existe déjà
    $requete = $connexion->prepare("SELECT * FROM user WHERE username = ?");
    if (!$requete) {
        die("Erreur de préparation de la requête : " . $connexion->error);
    }
    $requete->bind_param("s", $user);
    $requete->execute();
    $resultat = $requete->get_result();

    if ($resultat->num_rows > 0) { // Si l'utilisateur existe déjà
        $_SESSION['message'] = "Le nom d'utilisateur existe déjà.";
        header('location: form/register_form.php');
        exit();
    } else {
        if (!empty($user) && !empty($password)) { // Vérifie que les champs ne sont pas vides
            // Hachage du mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prépare l'insertion dans la base de données
            $requete = $connexion->prepare("INSERT INTO user (username, password, money) VALUES (?, ?, ?)");
            if (!$requete) {
                die("Erreur de préparation de la requête : " . $connexion->error);
            }
            $money = 10000.00; // Montant initial
            $requete->bind_param("ssd", $user, $hashed_password, $money); // Ajoute les paramètres

            if ($requete->execute()) { // Si l'insertion réussit
                $requete->close();
                $connexion->close();
                $_SESSION['message'] = "Votre inscription a bien été validée.";
                header('location: form/login_form.php');
                exit();
            } else {
                echo "Erreur lors de l'insertion : " . $requete->error;
                $requete->close();
            }
        } else {
            $_SESSION['message'] = "Les champs ne doivent pas être vides.";
            header('location: form/register_form.php');
            exit();
        }
    }
} else {
    $_SESSION['message'] = "Les variables du formulaire ne sont pas déclarées.";
    header('location: form/register_form.php');
    exit();
}
?>