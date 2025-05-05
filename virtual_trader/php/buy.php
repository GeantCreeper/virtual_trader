<link rel="stylesheet" type="text/css" href="../css/style.css">
<?php
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    echo "<p class='error'>Vous devez être connecté pour acheter des actions.</p>";
    header("Refresh: 2; url=../index.php?p=actions");
    exit();
}

$user = $_SESSION['user']; // Récupère l'utilisateur connecté

// Connexion à la base de données
$serveur = "localhost";
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
$stmt_user_id = $connexion->prepare("SELECT user_id, money FROM user WHERE username = ?");
$stmt_user_id->bind_param("s", $user);
$stmt_user_id->execute();
$result_user_id = $stmt_user_id->get_result();
if ($result_user_id->num_rows === 0) {
    echo "<p class='error'>Utilisateur introuvable.</p>";
    header("Refresh: 2; url=../index.php?p=actions");
    exit();
}
$user_data = $result_user_id->fetch_assoc();
$user_id = $user_data['user_id'];
$user_money = $user_data['money'];
$stmt_user_id->close();

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

// Calcule le coût total
$total_cost = $quantity * $action_price;
if ($user_money < $total_cost) {
    echo "<p class='error'>Fonds insuffisants pour effectuer cet achat.</p>";
    header("Refresh: 2; url=../index.php?p=actions");
    exit();
}

// Met à jour le portefeuille de l'utilisateur
$stmt_wallet = $connexion->prepare("
    INSERT INTO wallet (user_id, action_id, quantity)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
");
if (!$stmt_wallet) {
    die("Erreur dans la requête SQL : " . $connexion->error);
}
$stmt_wallet->bind_param("iii", $user_id, $action_id, $quantity);
$stmt_wallet->execute();
$stmt_wallet->close();

// Insère une transaction d'achat
$stmt_transaction = $connexion->prepare("
    INSERT INTO transactions (user_id, action_id, value, quantity, transaction_type, transaction_date)
    VALUES (?, ?, ?, ?, 'buy', NOW())
");
if (!$stmt_transaction) {
    die("Erreur dans la requête SQL : " . $connexion->error);
}
$stmt_transaction->bind_param("iidi", $user_id, $action_id, $action_price, $quantity);
$stmt_transaction->execute();
$stmt_transaction->close();

// Met à jour l'argent de l'utilisateur
$new_money = $user_money - $total_cost;
$stmt_update_money = $connexion->prepare("UPDATE user SET money = ? WHERE user_id = ?");
if (!$stmt_update_money) {
    die("Erreur dans la requête SQL : " . $connexion->error);
}
$stmt_update_money->bind_param("di", $new_money, $user_id);
$stmt_update_money->execute();
$stmt_update_money->close();

// Vérifie si la valeur totale du portefeuille est inférieure à 1000€
$stmt_portfolio_value = $connexion->prepare("
    SELECT COALESCE(SUM(w.quantity * a.price), 0) + u.money AS total_value
    FROM wallet w
    INNER JOIN actions a ON w.action_id = a.action_id
    INNER JOIN user u ON w.user_id = u.user_id
    WHERE u.user_id = ?
");
$stmt_portfolio_value->bind_param("i", $user_id);
$stmt_portfolio_value->execute();
$result_portfolio_value = $stmt_portfolio_value->get_result();
$total_value = $result_portfolio_value->fetch_assoc()['total_value'];
$stmt_portfolio_value->close();

if ($total_value < 1000) {
    // Supprime toutes les données associées à l'utilisateur
    $stmt_delete_transactions = $connexion->prepare("DELETE FROM transactions WHERE user_id = ?");
    $stmt_delete_transactions->bind_param("i", $user_id);
    $stmt_delete_transactions->execute();
    $stmt_delete_transactions->close();

    $stmt_delete_wallet = $connexion->prepare("DELETE FROM wallet WHERE user_id = ?");
    $stmt_delete_wallet->bind_param("i", $user_id);
    $stmt_delete_wallet->execute();
    $stmt_delete_wallet->close();

    $stmt_delete_portfolio_history = $connexion->prepare("DELETE FROM portfolio_history WHERE user_id = ?");
    $stmt_delete_portfolio_history->bind_param("i", $user_id);
    $stmt_delete_portfolio_history->execute();
    $stmt_delete_portfolio_history->close();

    // Supprime l'utilisateur
    $stmt_delete_user = $connexion->prepare("DELETE FROM user WHERE user_id = ?");
    $stmt_delete_user->bind_param("i", $user_id);
    $stmt_delete_user->execute();
    $stmt_delete_user->close();

    // Envoie un message au BroadcastChannel
    echo "<script>
        const bc = new BroadcastChannel('update_channel');
        bc.postMessage('playerLost');
    </script>";

    // Réinitialise le fichier player_lost_flag.txt
    file_put_contents(__DIR__ . '/../javascript/player_lost_flag.txt', '');

    session_destroy();
    echo "<p class='error'>Vous avez perdu le jeu. Votre portefeuille est descendu en dessous de 1000€.</p>";
    header("Refresh: 3; url=../login/form/login_form.php");
    exit();
}

echo "<p class='success'>Achat effectué avec succès !</p>";

$connexion->close();
header("Refresh: 2; url=../index.php?p=actions");
exit();
?>