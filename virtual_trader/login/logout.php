<!-- Ce fichier permet de se déconnecter -->
<?php
// On démarre la session
session_start();

// On stocke un message de déconnexion dans une variable temporaire
$message = "Vous avez été déconnecté avec succès.";

// On détruit les variables de session
session_unset();

// On détruit la session
session_destroy();

// On redémarre une session pour afficher le message
session_start();
$_SESSION['message'] = $message;

// On redirige le visiteur vers la page de connexion
header('location: form/login_form.php');
exit();
?>