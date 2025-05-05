<?php
session_start();
if (isset($_POST['user']) && isset($_POST['old_password']) && isset($_POST['new_password']) && isset($_POST['password_confirmation']))  // si le compte et le mot de passe sont mis en place
    {
        $user = $_POST['user']; // récupère le nom d'utilisateur
        $old_password = $_POST['old_password']; // récupère le mot de passe actuel
        $new_password = $_POST['new_password'];
        $password_confirmation = $_POST['password_confirmation'];

        if ($new_password != $password_confirmation) {
            $_SESSION['message'] = "Le mot de passe et la confirmation ne sont pas identiques.";
            header('location: form/password_form.php');
            exit();
        }

        $serveur = "localhost"; // nom du serveur
        $utilisateur = "root"; // nom de l'utilisateur
        $motdepasse = ""; // mot de passe
        $base_de_donnees = "virtual_trader"; // base de données
        $connexion = new mysqli($serveur, $utilisateur, $motdepasse, $base_de_donnees); // se connecte d'après les paramètres

        if ($connexion->connect_error) // si n'y parvient pas quitte le programme
            {
                die("Erreur de connexion à la base de données : " . $connexion->connect_error);
            }

        $requete = $connexion->prepare("SELECT password FROM user WHERE username = ?"); // prépare la requête en cherchant dans la base
        if (!$requete) // si ne s'effectue pas quitte le programme
            {
                die("Erreur de préparation de la requête : " . $connexion->error);
            }

        $requete->bind_param("s", $user); // prépare la requête d'après user
        $requete->execute(); // lance la requête
        $resultat = $requete->get_result();

        if ($resultat->num_rows > 0) { // si l'utilisateur est trouvé
            $row = $resultat->fetch_assoc(); // récupère le mot de passe hashé
            $hashed_password = $row['password'];

            if (password_verify($old_password, $hashed_password)) { // vérifie si le mot de passe actuel est correct
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // hash le nouveau mot de passe
                $update = $connexion->prepare("UPDATE user SET password = ? WHERE username = ?"); // prépare la requête de mise à jour

                if (!$update) { // si ne s'effectue pas quitte le programme
                    die("Erreur de préparation de la requête : " . $connexion->error);
                }

                $update->bind_param("ss", $new_hashed_password, $user); // prépare la requête d'après user et le nouveau mot de passe
                $update->execute(); // lance la requête

                $_SESSION['message'] = "Le mot de passe a été mis à jour avec succès.";
                header('location: form/login_form.php');
                exit();
            } else {
                $_SESSION['message'] = "Le mot de passe actuel est incorrect.";
                header('location: form/password_form.php');
                exit();
            }
        } else {
            $_SESSION['message'] = "Utilisateur non trouvé.";
            header('location: form/password_form.php');
            exit();
        }

        $requete->close(); // ferme la requête
    } 
else 
    {
        $_SESSION['message'] = "Les variables du formulaire ne sont pas déclarées.";
        header('location: form/password_form.php');
        exit();
    }
?>