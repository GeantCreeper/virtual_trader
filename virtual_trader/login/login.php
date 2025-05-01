<?php
session_start();
if (isset($_POST['user']) && isset($_POST['password']))  // si le compte et le mot de passe sont mis en place
    {
        $serveur="localhost:3307"; // nom du serveur
        $utilisateur="root"; // nom de l'utilisateur
        $motdepasse=""; // mot de passe
        $base_de_donnees="virtual_trader"; // base de données
        $connexion=new mysqli($serveur, $utilisateur, $motdepasse, $base_de_donnees); // se connecte d'après les paramètres
        if ($connexion->connect_error) // si n'y parvient pas quitte le programme
            {
                die("Erreur de connexion à la base de données : ".$connexion->connect_error);
            }
        $user=$_POST['user']; // récupère le nom d'utilisateur
        $password=$_POST['password']; // récupère le mot de passe
        $requete=$connexion->prepare("SELECT password FROM user WHERE username = ?"); // prépare la requête en cherchant dans la base
        if (!$requete) // si ne s'effectue pas quitte le programme
            {
                die("Erreur de préparation de la requête : ".$connexion->error);
            }
        $requete->bind_param("s", $user); // prépare la requête d'après user
        $requete->execute(); // lance la requête
        $resultat=$requete->get_result();
        if ($resultat->num_rows > 0) // si l'utilisateur est trouvé
            {
                $row = $resultat->fetch_assoc(); // récupère le mot de passe hashé
                $hashed_password = $row['password'];
                if (password_verify($password, $hashed_password)) // vérifie le mot de passe
                    {
                        $_SESSION['user'] = $user;
                        $_SESSION['open_update'] = true;
                        // Redirige vers index.php
                        header('Location: ../index.php');
                        exit();
                    } 
                else 
                    {
                        $_SESSION['message'] = "Le mot de passe est incorrect.";
                        header('location: form/login_form.php');
                        exit();
                    }
            } 
        else 
            {
                $_SESSION['message'] = "Le nom d'utilisateur n'existe pas.";
                header('location: form/login_form.php');
                exit();
            }
        $requete->close(); // ferme la requête
    } 
else 
    {
        $_SESSION['message'] = "Les variables du formulaire ne sont pas déclarées.";
        header('location: form/login_form.php');
        exit();
    }
?>
