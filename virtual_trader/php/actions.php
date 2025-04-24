<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="../css/connexion.css">
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
        $serveur = "localhost:3307"; // nom du serveur
        $utilisateur = "root"; // nom de l'utilisateur
        $motdepasse = ""; // mot de passe
        $base_de_donnees = "virtual_trader"; // base de données

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
                $conditions[] = "price >= (SELECT price FROM action_history WHERE action_id = actions.action_id AND date = DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
            } elseif ($_POST['progression'] == "1_year") {
                $conditions[] = "price >= (SELECT price FROM action_history WHERE action_id = actions.action_id AND date = DATE_SUB(CURDATE(), INTERVAL 1 YEAR))";
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
                                <button type='submit' name='view_evolution'>Voir l'évolution</button>
                            </form>
                        </td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Aucune action trouvée.</p>";
        }

        // Affiche l'évolution du prix d'une action sur 12 mois
        if (isset($_POST['view_evolution']) && !empty($_POST['action_id'])) {
            $action_id = $_POST['action_id'];

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

            $stmt_evolution->bind_param("i", $action_id);
            $stmt_evolution->execute();
            $result_evolution = $stmt_evolution->get_result();

            if ($result_evolution->num_rows > 0) {
                echo "<h2>Évolution du prix sur 12 mois</h2>";
                echo "<table border='1'>
                        <tr>
                            <th>Mois</th>
                            <th>Prix (€)</th>
                        </tr>";
                while ($row = $result_evolution->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['month']) . "</td>
                            <td>" . htmlspecialchars(number_format($row['price'], 2)) . "</td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Aucune donnée d'évolution disponible pour cette action.</p>";
            }

            $stmt_evolution->close();
        }

        $stmt->close();
        $connexion->close();
        ?>
    </body>
</html>
