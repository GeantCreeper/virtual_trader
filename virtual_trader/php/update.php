<?php
// Connexion à la base de données
$serveur = "localhost";
$utilisateur = "root";
$motdepasse = "";
$base_de_donnees = "virtual_trader";

$connexion = new mysqli($serveur, $utilisateur, $motdepasse, $base_de_donnees);
if ($connexion->connect_error) {
    die("Erreur de connexion : " . $connexion->connect_error);
}

// Récupère la date fictive actuelle
$requete_date = $connexion->prepare("SELECT actual_date FROM game_state");
$requete_date->execute();
$result_date = $requete_date->get_result();
if ($result_date->num_rows > 0) {
    $fictive_date = $result_date->fetch_assoc()['actual_date'];
} else {
    die("Erreur : Impossible de récupérer la date fictive.");
}
$requete_date->close();

// Étape 1 : Augmenter la date du jeu d'un mois
$connexion->query("UPDATE game_state SET actual_date = DATE_ADD(actual_date, INTERVAL 1 MONTH)");

// Étape 2 : Verser les dividendes aux joueurs
$requete_dividendes = $connexion->prepare("
    SELECT w.user_id, w.action_id, w.quantity, a.annual_dividend, a.price
    FROM wallet w
    INNER JOIN actions a ON w.action_id = a.action_id
    WHERE MONTH(?) = a.dividend_date
");
$requete_dividendes->bind_param("s", $fictive_date);
$requete_dividendes->execute();
$result_dividendes = $requete_dividendes->get_result();

while ($row = $result_dividendes->fetch_assoc()) {
    $user_id = $row['user_id'];
    $dividende_total = $row['quantity'] * $row['annual_dividend'] * $row['price'];

    // Ajoute les dividendes au solde de l'utilisateur
    $requete_update_money = $connexion->prepare("UPDATE user SET money = money + ? WHERE user_id = ?");
    $requete_update_money->bind_param("di", $dividende_total, $user_id);
    $requete_update_money->execute();
    $requete_update_money->close();
}
$requete_dividendes->close();

// Étape 3 : Mettre à jour les prix des actions
// Récupère les prix actuels des actions
$requete_prix = $connexion->prepare("SELECT action_id, price FROM actions");
$requete_prix->execute();
$result_prix = $requete_prix->get_result();

while ($row = $result_prix->fetch_assoc()) {
    $action_id = $row['action_id'];
    $prix_actuel = $row['price'];

    // Calcule l'évolution aléatoire du prix
    $evolution_min = max(-10, -3); // Minimum : -10% ou -3 points
    $evolution_max = min(10, 3);  // Maximum : +10% ou +3 points
    $evolution = rand($evolution_min * 100, $evolution_max * 100) / 10000; // Génère un pourcentage aléatoire

    $nouveau_prix = $prix_actuel * (1 + $evolution);

    // Applique les bornes : minimum 1€ par action
    $nouveau_prix = max(1, $nouveau_prix);

    // Met à jour le prix de l'action
    $requete_update_price = $connexion->prepare("UPDATE actions SET price = ? WHERE action_id = ?");
    $requete_update_price->bind_param("id", $nouveau_prix, $action_id);
    $requete_update_price->execute();
    $requete_update_price->close();

    // Enregistre l'historique du prix
    $requete_insert_history = $connexion->prepare("INSERT INTO action_history (action_id, date, price) VALUES (?, ?, ?)");
    $requete_insert_history->bind_param("isd", $action_id, $fictive_date, $nouveau_prix);
    $requete_insert_history->execute();
    $requete_insert_history->close();
}
$requete_prix->close();

// Étape 4 : Enregistrer la valeur totale du portefeuille dans portfolio_history
$requete_users = $connexion->prepare("SELECT user_id FROM user");
$requete_users->execute();
$result_users = $requete_users->get_result();

while ($user = $result_users->fetch_assoc()) {
    $user_id = $user['user_id'];

    // Calcule la valeur totale du portefeuille de l'utilisateur
    $requete_portfolio_value = $connexion->prepare("
        SELECT COALESCE(SUM(w.quantity * a.price), 0) AS total_value
        FROM wallet w
        INNER JOIN actions a ON w.action_id = a.action_id
        WHERE w.user_id = ?
    ");
    $requete_portfolio_value->bind_param("i", $user_id);
    $requete_portfolio_value->execute();
    $result_portfolio_value = $requete_portfolio_value->get_result();
    $portfolio_value = $result_portfolio_value->fetch_assoc()['total_value'];
    $requete_portfolio_value->close();

    // Vérifie si une entrée existe déjà pour cet utilisateur et cette date
    $requete_check = $connexion->prepare("
        SELECT COUNT(*) AS count
        FROM portfolio_history
        WHERE user_id = ? AND date = ?
    ");
    $requete_check->bind_param("is", $user_id, $fictive_date);
    $requete_check->execute();
    $result_check = $requete_check->get_result();
    $row_check = $result_check->fetch_assoc();
    $requete_check->close();

    // Si aucune entrée n'existe, insère une nouvelle ligne
    if ($row_check['count'] == 0) {
        $requete_insert_history = $connexion->prepare("INSERT INTO portfolio_history (user_id, value, date) VALUES (?, ?, ?)");
        if (!$requete_insert_history) {
            die("Erreur dans la requête SQL : " . $connexion->error);
        }
        $requete_insert_history->bind_param("ids", $user_id, $portfolio_value, $fictive_date);
        $requete_insert_history->execute();
        $requete_insert_history->close();
    }
}
$requete_users->close();

// Vérifie si la valeur totale du portefeuille de chaque utilisateur est inférieure à 1000€
$requete_users = $connexion->prepare("SELECT user_id FROM user");
$requete_users->execute();
$result_users = $requete_users->get_result();

while ($user = $result_users->fetch_assoc()) {
    $user_id = $user['user_id'];

    $requete_portfolio_value = $connexion->prepare("
        SELECT COALESCE(SUM(w.quantity * a.price), 0) + u.money AS total_value
        FROM wallet w
        INNER JOIN actions a ON w.action_id = a.action_id
        INNER JOIN user u ON w.user_id = u.user_id
        WHERE u.user_id = ?
    ");
    $requete_portfolio_value->bind_param("i", $user_id);
    $requete_portfolio_value->execute();
    $result_portfolio_value = $requete_portfolio_value->get_result();
    $total_value = $result_portfolio_value->fetch_assoc()['total_value'];
    $requete_portfolio_value->close();

    if ($total_value < 1000) {
        // Supprime toutes les données associées à l'utilisateur
        $requete_delete_transactions = $connexion->prepare("DELETE FROM transactions WHERE user_id = ?");
        $requete_delete_transactions->bind_param("i", $user_id);
        $requete_delete_transactions->execute();
        $requete_delete_transactions->close();

        $requete_delete_wallet = $connexion->prepare("DELETE FROM wallet WHERE user_id = ?");
        $requete_delete_wallet->bind_param("i", $user_id);
        $requete_delete_wallet->execute();
        $requete_delete_wallet->close();

        $requete_delete_portfolio_history = $connexion->prepare("DELETE FROM portfolio_history WHERE user_id = ?");
        $requete_delete_portfolio_history->bind_param("i", $user_id);
        $requete_delete_portfolio_history->execute();
        $requete_delete_portfolio_history->close();

        // Supprime l'utilisateur
        $requete_delete_user = $connexion->prepare("DELETE FROM user WHERE user_id = ?");
        $requete_delete_user->bind_param("i", $user_id);
        $requete_delete_user->execute();
        $requete_delete_user->close();

        // Enregistre un message dans player_lost_flag.txt
        file_put_contents(__DIR__ . '/javascript/player_lost_flag.txt', 'playerLost');

        // Ajoute un script pour envoyer un message au BroadcastChannel
        echo "<script>
            const bc = new BroadcastChannel('update_channel');
            bc.postMessage('playerLost');
        </script>";
    }
}

// Réinitialise le fichier player_lost_flag.txt après avoir traité les pertes
file_put_contents(__DIR__ . '/javascript/player_lost_flag.txt', '');

$requete_users->close();

// Indique qu'une mise à jour a été effectuée dans update_flag.txt
file_put_contents(__DIR__ . '/javascript/update_flag.txt', time());

echo "Mise à jour du jeu effectuée avec succès.";
$connexion->close();
?>