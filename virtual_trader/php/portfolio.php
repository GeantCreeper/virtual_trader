<?php
// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    echo "<p style='color: red;'>Vous devez être connecté pour voir votre portfolio.</p>";
    exit();
}

$user = $_SESSION['user']; // Récupère l'utilisateur connecté
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="../css/style.css">
        <title>Portfolio de <?php echo htmlspecialchars($user); ?></title>
    </head>
    <form action="index.php?p=actions" method="post">
    <button type="submit" class="return">Voir les actions disponibles</button>
    </form>
    <body>
        <h1>Portfolio de <?php echo htmlspecialchars($user); ?></h1>

        <?php
        // Connexion à la base de données
        $serveur = "localhost:3307";
        $utilisateur = "root";
        $motdepasse = "";
        $base_de_donnees = "virtual_trader";

        $connexion = new mysqli($serveur, $utilisateur, $motdepasse, $base_de_donnees);
        if ($connexion->connect_error) {
            die("Erreur de connexion à la base de données : " . $connexion->connect_error);
        }

        // Étape 1 : Récupérer l'ID de l'utilisateur connecté
        $stmt_user_id = $connexion->prepare("SELECT user_id FROM user WHERE username = ?");
        $stmt_user_id->bind_param("s", $user);
        $stmt_user_id->execute();
        $result_user_id = $stmt_user_id->get_result();
        if ($result_user_id->num_rows === 0) {
            echo "<p style='color: red;'>Utilisateur introuvable.</p>";
            exit();
        }
        $user_id = $result_user_id->fetch_assoc()['user_id'];
        $stmt_user_id->close();

        // Étape 2 : Préparer et exécuter la requête pour le portfolio
        $requete = $connexion->prepare("
            SELECT 
                a.name AS stock_name,
                w.quantity AS quantity,
                a.price AS value_per_stock,
                (w.quantity * a.price) AS total_value
            FROM 
                wallet w
            INNER JOIN 
                actions a ON w.action_id = a.action_id
            WHERE 
                w.user_id = ?
        ");

        if (!$requete) {
            die("Erreur de préparation de la requête : " . $connexion->error);
        }

        $requete->bind_param("i", $user_id);
        $requete->execute();
        $resultat = $requete->get_result();

        if ($resultat->num_rows > 0) {
            // Affiche le portfolio de l'utilisateur
            echo "<table border='1'>
                    <tr>
                        <th>Nom de l'action</th>
                        <th>Quantité</th>
                        <th>Valeur par action (€)</th>
                        <th>Valeur totale (€)</th>
                    </tr>";
            while ($row = $resultat->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['stock_name']) . "</td>
                        <td>" . htmlspecialchars($row['quantity']) . "</td>
                        <td>" . htmlspecialchars(number_format($row['value_per_stock'], 2)) . "</td>
                        <td>" . htmlspecialchars(number_format($row['total_value'], 2)) . "</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>Aucun portfolio trouvé pour cet utilisateur.</p>";
        }

        $requete->close();

        // Vérifie si un portefeuille existe déjà pour cet utilisateur, sinon le crée automatiquement
        $verif_wallet = $connexion->prepare("SELECT * FROM wallet WHERE user_id = ?");
        $verif_wallet->bind_param("i", $user_id);
        $verif_wallet->execute();
        $res_wallet = $verif_wallet->get_result();

        if ($res_wallet->num_rows === 0) {
            echo "<p style='color: red;'>Aucun portefeuille trouvé. Veuillez effectuer une transaction pour créer un portefeuille.</p>";
        }

        $verif_wallet->close();
        $connexion->close();
        ?>
    </body>
</html>
