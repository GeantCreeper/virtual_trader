<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <title>Classement des joueurs</title>
    </head>
    <body>
        <h1>Classement des joueurs</h1>
        <h2>Chercher un joueur</h2>
        <div class="formulaire_connexion">
            <form method="post" action="">
                Identifiant : <br><input type="text" name="search_user" placeholder="Entrez l'identifiant du joueur" /><br />
                <input type="submit" name="user_search" value="Chercher un utilisateur" />
                <input type="submit" name="reset" value="Retour au classement général" />
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
            die("Erreur de connexion à la base de données : " . $connexion->connect_error);
        }

        // Si le bouton "Retour au classement général" est cliqué ou si aucune recherche n'est effectuée
        if (isset($_POST['reset']) || !isset($_POST['user_search'])) {
            // Affiche le classement général
            echo "<h2>Classement général</h2>";
            $requete = $connexion->prepare("SELECT username, money FROM user ORDER BY money DESC");
            if (!$requete) {
                die("Erreur de préparation de la requête : " . $connexion->error);
            }

            $requete->execute();
            $resultat = $requete->get_result();

            if ($resultat->num_rows > 0) {
                echo "<table border='1'>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Argent (€)</th>
                        </tr>";
                while ($row = $resultat->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['username']) . "</td>
                            <td>" . htmlspecialchars($row['money']) . "</td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='error'>Aucun joueur trouvé.</p>";
            }

            $requete->close();
        } elseif (!empty($_POST['search_user'])) {
            // Recherche d'un utilisateur spécifique
            $search_user = $_POST['search_user'];

            // Récupère les informations de l'utilisateur
            $requete = $connexion->prepare("SELECT username, money FROM user WHERE username LIKE CONCAT('%', ?, '%')");
            if (!$requete) {
                die("Erreur de préparation de la requête : " . $connexion->error);
            }

            $requete->bind_param("s", $search_user);
            $requete->execute();
            $resultat = $requete->get_result();

            if ($resultat->num_rows > 0) {
                // Affiche les résultats de la recherche
                echo "<h3>Résultat de la recherche :</h3>";
                echo "<table border='1'>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Argent (€)</th>
                        </tr>";
                while ($row = $resultat->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['username']) . "</td>
                            <td>" . htmlspecialchars($row['money']) . "</td>
                          </tr>";
                }
                echo "</table>";

                // Récupère les transactions de l'utilisateur
                $requete_transactions = $connexion->prepare("
                    SELECT t.transaction_type, t.quantity, (t.quantity * t.value) AS total_value, t.transaction_date, a.name AS action_name
                    FROM transactions t
                    INNER JOIN actions a ON t.action_id = a.action_id
                    INNER JOIN user u ON t.user_id = u.user_id
                    WHERE u.username LIKE CONCAT('%', ?, '%')
                    AND t.transaction_date <= (SELECT actual_date FROM game_state)
                    ORDER BY t.transaction_date DESC
                    LIMIT 10
                ");
                if (!$requete_transactions) {
                    die("Erreur de préparation de la requête : " . $connexion->error);
                }

                $requete_transactions->bind_param("s", $search_user);
                $requete_transactions->execute();
                $resultat_transactions = $requete_transactions->get_result();

                if ($resultat_transactions->num_rows > 0) {
                    echo "<h3>Dernières transactions :</h3>";
                    echo "<table border='1'>
                            <tr>
                                <th>Type</th>
                                <th>Action</th>
                                <th>Quantité</th>
                                <th>Valeur (€)</th>
                                <th>Date</th>
                            </tr>";
                    while ($transaction = $resultat_transactions->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($transaction['transaction_type']) . "</td>
                                <td>" . htmlspecialchars($transaction['action_name']) . "</td>
                                <td>" . htmlspecialchars($transaction['quantity']) . "</td>
                                <td>" . htmlspecialchars($transaction['total_value']) . "</td>
                                <td>" . htmlspecialchars($transaction['transaction_date']) . "</td>
                              </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p class='error'>Aucune transaction trouvée pour cet utilisateur.</p>";
                }

                $requete_transactions->close();
            } else {
                echo "<p class='error'>Aucun utilisateur trouvé avec cet identifiant.</p>";
            }

            $requete->close();
        } else {
            echo "<p>Veuillez entrer un identifiant.</p>";
        }

        $connexion->close();
        ?>
    </body>
</html>