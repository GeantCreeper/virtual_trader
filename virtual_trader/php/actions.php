<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <title>Liste des actions</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    $serveur = "localhost";
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

    $requete = $connexion->prepare($sql);
    if ($types) {
        $requete->bind_param($types, ...$params);
    }
    $requete->execute();
    $result = $requete->get_result();

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
                $requete_evolution = $connexion->prepare("
                    SELECT DATE_FORMAT(date, '%Y-%m') AS month, price
                    FROM action_history
                    WHERE action_id = ?
                    ORDER BY date DESC
                    LIMIT 12
                ");
                if (!$requete_evolution) {
                    die("Erreur dans la requête SQL : " . $connexion->error);
                }

                $requete_evolution->bind_param("i", $id);
                $requete_evolution->execute();
                $result_evolution = $requete_evolution->get_result();

                if ($result_evolution->num_rows > 0) {
                    $months = [];
                    $prices = [];
                    while ($evolution_row = $result_evolution->fetch_assoc()) {
                        $months[] = htmlspecialchars($evolution_row['month']);
                        $prices[] = htmlspecialchars(number_format($evolution_row['price'], 2, '.', ''));
                    }

                    // Inverser les données pour afficher de gauche à droite
                    $months = array_reverse($months);
                    $prices = array_reverse($prices);

                    // Convertir les données en JSON pour JavaScript
                    $months_json = json_encode($months);
                    $prices_json = json_encode($prices);

                    echo "<tr><td colspan='5'>
                            <h3>Évolution du prix sur 12 mois pour $nom</h3>
                            <canvas id='chart_$id' width='400' height='200'></canvas>
                            <script>
                                const ctx_$id = document.getElementById('chart_$id').getContext('2d');
                                new Chart(ctx_$id, {
                                    type: 'line',
                                    data: {
                                        labels: $months_json,
                                        datasets: [{
                                            label: 'Prix (€)',
                                            data: $prices_json,
                                            borderColor: 'rgba(75, 192, 192, 1)',
                                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                            borderWidth: 2
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'top'
                                            }
                                        },
                                        scales: {
                                            x: {
                                                title: {
                                                    display: true,
                                                    text: 'Mois'
                                                }
                                            },
                                            y: {
                                                title: {
                                                    display: true,
                                                    text: 'Prix (€)'
                                                },
                                                beginAtZero: false
                                            }
                                        }
                                    }
                                });
                            </script>
                          </td></tr>";
                } else {
                    echo "<tr><td colspan='5'><p>Aucune donnée d'évolution disponible pour cette action.</p></td></tr>";
                }

                $requete_evolution->close();
            }
        }
        echo "</table>";
    } else {
        echo "<p>Aucune action trouvée.</p>";
    }

    $requete->close();
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

        // Vérifie toutes les 5 secondes
        setInterval(checkForUpdate, 5000);
    </script>
</body>
</html>
