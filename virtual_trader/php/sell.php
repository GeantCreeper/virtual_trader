<link rel="stylesheet" type="text/css" href="../css/style.css">
<?php
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    echo "<p class='error'>Vous devez être connecté pour vendre des actions.</p>";
    header("Refresh: 2; url=../index.php?p=actions");
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

// Récupère les données du formulaire
$action_id = $_POST['action_id'];
$quantity = intval($_POST['quantity']); // Récupère la quantité saisie
if ($quantity <= 0) {
    echo "<p class='error'>La quantité doit être supérieure à 0.</p>";
    header("Refresh: 2; url=../index.php?p=actions");
    exit();
}

// Récupère l'ID de l'utilisateur connecté
$stmt_user_id = $connexion->prepare("SELECT user_id FROM user WHERE username = ?");
$stmt_user_id->bind_param("s", $user);
$stmt_user_id->execute();
$result_user_id = $stmt_user_id->get_result();
if ($result_user_id->num_rows === 0) {
    echo "<p class='error'>Utilisateur introuvable.</p>";
    header("Refresh: 2; url=../index.php?p=actions");
    exit();
}
$user_id = $result_user_id->fetch_assoc()['user_id'];
$stmt_user_id->close();

// Vérifie si l'utilisateur possède suffisamment d'actions
$stmt_wallet = $connexion->prepare("SELECT quantity FROM wallet WHERE user_id = ? AND action_id = ?");
$stmt_wallet->bind_param("ii", $user_id, $action_id);
$stmt_wallet->execute();
$result_wallet = $stmt_wallet->get_result();
if ($result_wallet->num_rows === 0) {
    echo "<p class='error'>Vous ne possédez pas cette action.</p>";
    header("Refresh: 2; url=../index.php?p=actions");
    exit();
}
$wallet_quantity = $result_wallet->fetch_assoc()['quantity'];
$stmt_wallet->close();

if ($wallet_quantity < $quantity) {
    echo "<p class='error'>Vous ne possédez pas suffisamment d'actions pour cette vente.</p>";
    header("Refresh: 2; url=../index.php?p=actions");
    exit();
}

// Récupère le prix de l'action
$stmt_action_price = $connexion->prepare("SELECT price FROM actions WHERE action_id = ?");
$stmt_action_price->bind_param("i", $action_id);
$stmt_action_price->execute();
$result_action_price = $stmt_action_price->get_result();
if ($result_action_price->num_rows === 0) {
    echo "<p class='error'>Action introuvable.</p>";
    header("Refresh: 2; url=../index.php?p=actions");
    exit();
}
$action_price = $result_action_price->fetch_assoc()['price'];
$stmt_action_price->close();

// Calcule le montant total de la vente
$total_value = $quantity * $action_price;

// Met à jour le portefeuille de l'utilisateur
$new_quantity = $wallet_quantity - $quantity;
if ($new_quantity > 0) {
    $stmt_update_wallet = $connexion->prepare("UPDATE wallet SET quantity = ? WHERE user_id = ? AND action_id = ?");
    $stmt_update_wallet->bind_param("iii", $new_quantity, $user_id, $action_id);
    $stmt_update_wallet->execute();
    $stmt_update_wallet->close();
} else {
    $stmt_delete_wallet = $connexion->prepare("DELETE FROM wallet WHERE user_id = ? AND action_id = ?");
    $stmt_delete_wallet->bind_param("ii", $user_id, $action_id);
    $stmt_delete_wallet->execute();
    $stmt_delete_wallet->close();
}

// Insère une transaction de vente
$stmt_transaction = $connexion->prepare("
    INSERT INTO transactions (user_id, action_id, value, quantity, transaction_type, transaction_date)
    VALUES (?, ?, ?, ?, 'sell', NOW())
");
if (!$stmt_transaction) {
    die("Erreur dans la requête SQL : " . $connexion->error);
}
$stmt_transaction->bind_param("iidi", $user_id, $action_id, $action_price, $quantity);
$stmt_transaction->execute();
$stmt_transaction->close();

// Met à jour l'argent de l'utilisateur
$stmt_update_money = $connexion->prepare("UPDATE user SET money = money + ? WHERE user_id = ?");
if (!$stmt_update_money) {
    die("Erreur dans la requête SQL : " . $connexion->error);
}
$stmt_update_money->bind_param("di", $total_value, $user_id);
$stmt_update_money->execute();
$stmt_update_money->close();

echo "<p class='success'>Vente réalisée avec succès !</p>";

$connexion->close();
header("Refresh: 2; url=../index.php?p=actions");
exit();
?>