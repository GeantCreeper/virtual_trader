<!-- filepath: c:\wamp64\www\virtual_trader\php\update.php -->
<?php
// Connexion à la base de données
$serveur = "localhost:3307";
$utilisateur = "root";
$motdepasse = "";
$base_de_donnees = "virtual_trader";

$connexion = new mysqli($serveur, $utilisateur, $motdepasse, $base_de_donnees);
if ($connexion->connect_error) {
    die("Erreur de connexion : " . $connexion->connect_error);
}

// Étape 1 : Augmenter la date du jeu d'un mois
$connexion->query("UPDATE game_state SET actual_date = DATE_ADD(actual_date, INTERVAL 1 MONTH)");

// Étape 2 : Verser les dividendes aux joueurs
// Récupère les actions avec des dividendes à verser ce mois-ci
$requete_dividendes = $connexion->prepare("
    SELECT w.user_id, w.action_id, w.quantity, a.annual_dividend
    FROM wallet w
    INNER JOIN actions a ON w.action_id = a.action_id
    INNER JOIN game_state g ON MONTH(g.actual_date) = a.dividend_date
");
$requete_dividendes->execute();
$result_dividendes = $requete_dividendes->get_result();

while ($row = $result_dividendes->fetch_assoc()) {
    $user_id = $row['user_id'];
    $dividende_total = $row['quantity'] * $row['annual_dividend'];

    // Ajoute les dividendes au solde de l'utilisateur
    $stmt_update_money = $connexion->prepare("UPDATE user SET money = money + ? WHERE user_id = ?");
    $stmt_update_money->bind_param("di", $dividende_total, $user_id);
    $stmt_update_money->execute();
    $stmt_update_money->close();
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
    $stmt_update_price = $connexion->prepare("UPDATE actions SET price = ? WHERE action_id = ?");
    $stmt_update_price->bind_param("di", $nouveau_prix, $action_id);
    $stmt_update_price->execute();
    $stmt_update_price->close();

    // Enregistre l'historique du prix
    $stmt_insert_history = $connexion->prepare("INSERT INTO action_history (action_id, date, price) VALUES (?, CURDATE(), ?)");
    $stmt_insert_history->bind_param("id", $action_id, $nouveau_prix);
    $stmt_insert_history->execute();
    $stmt_insert_history->close();
}
$requete_prix->close();

echo "Mise à jour du jeu effectuée avec succès.";
$connexion->close();
?>