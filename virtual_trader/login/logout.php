<!-- Ce fichier permet de se déconnecter -->
<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion</title>
    <script>
        const bc = new BroadcastChannel('update_channel');
        bc.postMessage('closeUpdate');
    </script>
</head>
<body>
    <p>Vous avez été déconnecté avec succès.</p>
    <script>
        window.location.href = '../login/form/login_form.php';
    </script>
</body>
</html>