<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="../css/connexion.css">
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
                echo "<p>Aucun joueur trouvé.</p>";
            }

            $requete->close();
        } elseif (!empty($_POST['search_user'])) {
            // Recherche d'un utilisateur spécifique
            $search_user = $_POST['search_user'];

            $requete = $connexion->prepare("SELECT username, money FROM user WHERE username = ?");
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
            } else {
                echo "<p>Aucun utilisateur trouvé avec cet identifiant.</p>";
            }

            $requete->close();
        } else {
            echo "<p>Veuillez entrer un identifiant.</p>";
        }

        $connexion->close();
        ?>
    </body>
</html>