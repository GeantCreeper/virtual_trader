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
$requete_user_id = $connexion->prepare("SELECT user_id FROM user WHERE username = ?");
$requete_user_id->bind_param("s", $user);
$requete_user_id->execute();
$result_user_id = $requete_user_id->get_result();
if ($result_user_id->num_rows === 0) {
    echo "<p class='error'>Utilisateur introuvable.</p>";
    header("Refresh: 2; url=../index.php?p=actions");
    exit();
}
$user_id = $result_user_id->fetch_assoc()['user_id'];
$requete_user_id->close();

// Vérifie si l'utilisateur possède suffisamment d'actions
$requete_wallet = $connexion->prepare("SELECT quantity FROM wallet WHERE user_id = ? AND action_id = ?");
$requete_wallet->bind_param("ii", $user_id, $action_id);
$requete_wallet->execute();
$result_wallet = $requete_wallet->get_result();
if ($result_wallet->num_rows === 0) {
    echo "<p class='error'>Vous ne possédez pas cette action.</p>";
    header("Refresh: 2; url=../index.php?p=actions");
    exit();
}
$wallet_quantity = $result_wallet->fetch_assoc()['quantity'];
$requete_wallet->close();

if ($wallet_quantity < $quantity) {
    echo "<p class='error'>Vous ne possédez pas suffisamment d'actions pour cette vente.</p>";
    header("Refresh: 2; url=../index.php?p=actions");
    exit();
}

// Récupère le prix de l'action
$requete_action_price = $connexion->prepare("SELECT price FROM actions WHERE action_id = ?");
$requete_action_price->bind_param("i", $action_id);
$requete_action_price->execute();
$result_action_price = $requete_action_price->get_result();
if ($result_action_price->num_rows === 0) {
    echo "<p class='error'>Action introuvable.</p>";
    header("Refresh: 2; url=../index.php?p=actions");
    exit();
}
$action_price = $result_action_price->fetch_assoc()['price'];
$requete_action_price->close();

// Calcule le montant total de la vente
$total_value = $quantity * $action_price;

// Met à jour le portefeuille de l'utilisateur
$new_quantity = $wallet_quantity - $quantity;
if ($new_quantity > 0) {
    $requete_update_wallet = $connexion->prepare("UPDATE wallet SET quantity = ? WHERE user_id = ? AND action_id = ?");
    $requete_update_wallet->bind_param("iii", $new_quantity, $user_id, $action_id);
    $requete_update_wallet->execute();
    $requete_update_wallet->close();
} else {
    $requete_delete_wallet = $connexion->prepare("DELETE FROM wallet WHERE user_id = ? AND action_id = ?");
    $requete_delete_wallet->bind_param("ii", $user_id, $action_id);
    $requete_delete_wallet->execute();
    $requete_delete_wallet->close();
}

// Insère une transaction de vente
$requete_transaction = $connexion->prepare("
    INSERT INTO transactions (user_id, action_id, value, quantity, transaction_type, transaction_date)
    VALUES (?, ?, ?, ?, 'sell', NOW())
");
if (!$requete_transaction) {
    die("Erreur dans la requête SQL : " . $connexion->error);
}
$requete_transaction->bind_param("iidi", $user_id, $action_id, $action_price, $quantity);
$requete_transaction->execute();
$requete_transaction->close();

// Met à jour l'argent de l'utilisateur
$requete_update_money = $connexion->prepare("UPDATE user SET money = money + ? WHERE user_id = ?");
if (!$requete_update_money) {
    die("Erreur dans la requête SQL : " . $connexion->error);
}
$requete_update_money->bind_param("di", $total_value, $user_id);
$requete_update_money->execute();
$requete_update_money->close();

// Vérifie si la valeur totale du portefeuille est inférieure à 1000€
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

echo "<p class='success'>Vente réalisée avec succès !</p>";

$connexion->close();
header("Refresh: 2; url=../index.php?p=actions");
exit();
?>