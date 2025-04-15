<!-- Ce fichier permet de présenter le site -->
<div class="titre">
    <h1> Bienvenue sur ce site </h1>
</div>
<div class="corpsmenu">   
    <!-- salue l'utilisateur connecté d'après son nom d'utilisateur enregistré -->
    <p><?php 
    if(isset($_SESSION['user'])) // si connecté personnalise sinon généralise
        {
            ?>
            <p><?php echo "Bienvenue sur ce site ".$_SESSION['user'].", vous pouvez trouver trois sections :" ?></p>
            <ul>
                <li>Tableau de bord</li>
                <li>Portefeuille</li>
                <li>Classment</li>
                <li>Déconnexion</li>
            </ul>
            <?php
        }
    else
        {
            ?>
            <p>Bienvenue sur ce site, vous pouvez trouver trois sections :</p>
            <ul>
                <li>Tableau de bord</li>
                <li>Portefeuille</li>
                <li>Classment</li>
                <li>Connexion</li>
            </ul>
            <?php
        }
    ?>
    <img src="images/welcome.png" alt="welcome" class="img_welcome">
</div>