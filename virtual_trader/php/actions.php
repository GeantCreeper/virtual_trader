<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <title>Liste des actions</title>
</head>
<body>
    <form action="index.php?p=portfolio" method="post" style="margin-bottom: 20px;">
        <button type="submit">Retour au Portfolio</button>
    </form>
    <div class="titre">
        <h1>Liste des actions disponibles</h1>
    </div>

    <div class="formulaire_filtrage">
        <form method="post" action="">
            <label for="search_name">Nom de l'action :</label>
            <input type="text" name="search_name" placeholder="Rechercher par nom" />
            <br />

            <label for="min_price">Prix minimum :</label>
            <input type="number" name="min_price" step="0.01" placeholder="Prix minimum" />
            <br />

            <label for="max_price">Prix maximum :</label>
            <input type="number" name="max_price" step="0.01" placeholder="Prix maximum" />
            <br />

            <label for="progression">Progression :</label>
            <select name="progression">
                <option value="">-- Choisir une période --</option>
                <option value="1_month">1 mois</option>
                <option value="1_year">1 an</option>
            </select>
            <br />

            <button type="submit" name="filter">Filtrer</button>
        </form>
    </div>

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

    // Construction de la requête SQL en fonction des filtres
    $sql = "SELECT action_id, name, price FROM actions";
    $conditions = [];
    $params = [];
    $types = "";

    if (!empty($_POST['search_name'])) {
        $conditions[] = "name LIKE CONCAT('%', ?, '%')";
        $params[] = $_POST['search_name'];
        $types .= "s";
    }

    if (!empty($_POST['min_price'])) {
        $conditions[] = "price >= ?";
        $params[] = $_POST['min_price'];
        $types .= "d";
    }

    if (!empty($_POST['max_price'])) {
        $conditions[] = "price <= ?";
        $params[] = $_POST['max_price'];
        $types .= "d";
    }

    if (!empty($_POST['progression'])) {
        if ($_POST['progression'] == "1_month") {
            $conditions[] = "price >= (SELECT price FROM action_history WHERE action_id = actions.action_id AND date = DATE_SUB((SELECT actual_date FROM game_state), INTERVAL 1 MONTH))";
        } elseif ($_POST['progression'] == "1_year") {
            $conditions[] = "price >= (SELECT price FROM action_history WHERE action_id = actions.action_id AND date = DATE_SUB((SELECT actual_date FROM game_state), INTERVAL 1 YEAR))";
        }
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $stmt = $connexion->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>Nom de l'action</th>
                    <th>Prix actuel (€)</th>
                    <th>Acheter</th>
                    <th>Vendre</th>
                    <th>Voir l'évolution</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            $id = $row['action_id'];
            $nom = htmlspecialchars($row['name']);
            $prix = number_format($row['price'], 2, '.', ' ');

            echo "<tr>
                    <td>$nom</td>
                    <td>$prix</td>
                    <td>
                        <form method='post' action='php/buy.php'>
                            <input type='hidden' name='action_id' value='$id'>
                            <label for='quantity'>Quantité :</label>
                            <input type='number' name='quantity' min='1' required>
                            <button type='submit'>Acheter</button>
                        </form>
                    </td>
                    <td>
                        <form method='post' action='php/sell.php'>
                            <input type='hidden' name='action_id' value='$id'>
                            <label for='quantity'>Quantité :</label>
                            <input type='number' name='quantity' min='1' required>
                            <button type='submit'>Vendre</button>
                        </form>
                    </td>
                    <td>
                        <form method='post' action=''>
                            <input type='hidden' name='action_id' value='$id'>
                            <button type='submit' name='view_evolution' value='$id'>Voir l'évolution</button>
                        </form>
                    </td>
                  </tr>";

            // Afficher l'évolution si demandée
            if (isset($_POST['view_evolution']) && $_POST['view_evolution'] == $id) {
                $stmt_evolution = $connexion->prepare("
                    SELECT DATE_FORMAT(date, '%Y-%m') AS month, price
                    FROM action_history
                    WHERE action_id = ?
                    ORDER BY date DESC
                    LIMIT 12
                ");
                if (!$stmt_evolution) {
                    die("Erreur dans la requête SQL : " . $connexion->error);
                }

                $stmt_evolution->bind_param("i", $id);
                $stmt_evolution->execute();
                $result_evolution = $stmt_evolution->get_result();

                if ($result_evolution->num_rows > 0) {
                    echo "<tr><td colspan='5'>
                            <h3>Évolution du prix sur 12 mois pour $nom</h3>
                            <table border='1' style='width: 100%; margin: 10px 0;'>
                                <tr>
                                    <th>Mois</th>
                                    <th>Prix (€)</th>
                                </tr>";
                    while ($evolution_row = $result_evolution->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($evolution_row['month']) . "</td>
                                <td>" . htmlspecialchars(number_format($evolution_row['price'], 2)) . "</td>
                              </tr>";
                    }
                    echo "</table>
                          </td></tr>";
                } else {
                    echo "<tr><td colspan='5'><p>Aucune donnée d'évolution disponible pour cette action.</p></td></tr>";
                }

                $stmt_evolution->close();
            }
        }
        echo "</table>";
    } else {
        echo "<p>Aucune action trouvée.</p>";
    }

    $stmt->close();
    $connexion->close();
    ?>
    <script>
        let lastUpdate = null;

        function checkForUpdate() {
            fetch('/virtual_trader/php/javascript/update_flag.txt', { cache: 'no-store' })
                .then(response => response.text())
                .then(timestamp => {
                    if (lastUpdate === null) {
                        lastUpdate = timestamp; // Initialisation
                    } else if (lastUpdate !== timestamp) {
                        // Si le timestamp a changé, recharge la page
                        location.reload();
                    }
                })
                .catch(error => console.error('Erreur lors de la vérification de mise à jour:', error));
        }

        // Vérifie toutes les 10 secondes
        setInterval(checkForUpdate, 5000);
    </script>
</body>
</html>
