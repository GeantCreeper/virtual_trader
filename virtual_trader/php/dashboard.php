<!-- Ce fichier permet de présenter le site -->
<link rel="stylesheet" type="text/css" href="css/style.css">
<?php
if (!isset($_SESSION['user'])) {
    echo "<p style='color: red;'>Vous devez être connecté pour accéder au tableau de bord.</p>";
    exit();
}

$user = $_SESSION['user']; // Récupère l'utilisateur connecté

// Connexion à la base de données
$serveur = "localhost:3307";
$utilisateur = "root";
$motdepasse = "";
$base_de_donnees = "virtual_trader";

$connexion = new mysqli($serveur, $utilisateur, $motdepasse, $base_de_donnees);
if ($connexion->connect_error) {
    die("Erreur de connexion : " . $connexion->connect_error);
}

// Récupère l'ID de l'utilisateur et son argent
$stmt_user = $connexion->prepare("SELECT user_id, money FROM user WHERE username = ?");
$stmt_user->bind_param("s", $user);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
if ($result_user->num_rows === 0) {
    echo "<p style='color: red;'>Utilisateur introuvable.</p>";
    exit();
}
$user_data = $result_user->fetch_assoc();
$user_id = $user_data['user_id'];
$user_money = $user_data['money'];
$stmt_user->close();
?>

<div class="titre">
    <h1>Bienvenue sur le tableau de bord, <?php echo htmlspecialchars($user); ?> !</h1>
</div>

<div class="corpsmenu">
    <h2>État du jeu</h2>
    <p><strong>Argent possédé :</strong> <?php echo number_format($user_money, 2); ?> €</p>

    <h2>Liste des actions possédées</h2>
    <?php
    // Récupère les actions possédées
    $stmt_wallet = $connexion->prepare("
        SELECT a.name AS action_name, w.quantity, a.price AS current_price, (w.quantity * a.price) AS total_value
        FROM wallet w
        INNER JOIN actions a ON w.action_id = a.action_id
        WHERE w.user_id = ?
    ");
    $stmt_wallet->bind_param("i", $user_id);
    $stmt_wallet->execute();
    $result_wallet = $stmt_wallet->get_result();

    if ($result_wallet->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>Nom de l'action</th>
                    <th>Quantité</th>
                    <th>Prix actuel (€)</th>
                    <th>Valeur totale (€)</th>
                </tr>";
        while ($row = $result_wallet->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['action_name']) . "</td>
                    <td>" . htmlspecialchars($row['quantity']) . "</td>
                    <td>" . htmlspecialchars(number_format($row['current_price'], 2)) . "</td>
                    <td>" . htmlspecialchars(number_format($row['total_value'], 2)) . "</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Vous ne possédez aucune action.</p>";
    }
    $stmt_wallet->close();
    ?>

    <h2>Évolution de la valeur du portefeuille (12 derniers mois)</h2>
    <?php
    // Récupère l'évolution de la valeur du portefeuille sur les 12 derniers mois
    $stmt_portfolio = $connexion->prepare("
        SELECT DATE_FORMAT(t.transaction_date, '%Y-%m') AS month, 
               SUM(CASE WHEN t.transaction_type = 'buy' THEN t.quantity * t.value 
                        WHEN t.transaction_type = 'sell' THEN -t.quantity * t.value 
                        ELSE 0 END) AS monthly_value
        FROM transactions t
        WHERE t.user_id = ?
        GROUP BY DATE_FORMAT(t.transaction_date, '%Y-%m')
        ORDER BY DATE_FORMAT(t.transaction_date, '%Y-%m') DESC
        LIMIT 12
    ");
    $stmt_portfolio->bind_param("i", $user_id);
    $stmt_portfolio->execute();
    $result_portfolio = $stmt_portfolio->get_result();

    if ($result_portfolio->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>Mois</th>
                    <th>Valeur du portefeuille (€)</th>
                </tr>";
        while ($row = $result_portfolio->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['month']) . "</td>
                    <td>" . htmlspecialchars(number_format($row['monthly_value'], 2)) . "</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Aucune donnée disponible pour les 12 derniers mois.</p>";
    }
    $stmt_portfolio->close();

    $connexion->close();
    ?>
</div>