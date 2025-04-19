<!DOCTYPE html>
<div class="titre">
    <h1> Liste de vos actions </h1>
</div>

<?php
// Connexion à la base de données
$serveur="localhost:3307"; // nom du serveur
$utilisateur="root"; // nom de l'utilisateur
$motdepasse=""; // mot de passe
$base_de_donnees="virtual_trader"; // base de données
// Connexion à la base de données
$connexion = new mysqli($serveur, $utilisateur, $motdepasse, $base_de_donnees);
if ($connexion->connect_error) {
    die("Erreur de connexion : " . $connexion->connect_error);
}

// Récupère les actions
$sql = "SELECT action_code, description, price FROM action"; // adapte le nom de la table si besoin
$result = $connexion->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['action_code'];
        $nom = htmlspecialchars($row['description']);
        $prix = number_format($row['price'], 2, '.', ' ');

        echo '
        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 10px;">
            <span>' . $nom . '</span>
            <span>Prix : ' . $prix . ' €</span>

            <form method="post" action="traitement.php" style="margin: 0;">
                <input type="hidden" name="action" value="achat">
                <input type="hidden" name="objet_id" value="' . $id . '">
                <button type="submit">Achat</button>
            </form>

            <form method="post" action="traitement.php" style="margin: 0;">
                <input type="hidden" name="action" value="vente">
                <input type="hidden" name="objet_id" value="' . $id . '">
                <button type="submit">Vente</button>
            </form>
        </div>
        ';
    }
} else {
    echo "Aucune action trouvée.";
}

$connexion->close();
?>
