<?php 
    session_start();
    if (!isset($_SESSION['user'])) {
        // Redirige vers le formulaire de connexion si aucune session utilisateur n'est active
        header('Location: ./login/form/login_form.php');
        exit();
}
?>
<!DOCTYPE html>
<html>
    <?php 
        $page=isset($_GET['p'])?$_GET['p']:'dashboard';
        //permet de donner le titre de la page d'après son nom
        if($page=='dashboard')
            {$titlename="Tableau de bord";}
        else if($page=='portfolio')
            {$titlename="Portefeuille";}
        else if($page=='actions')
            {$titlename="Actions";}
        else if($page=='buy')
            {$titlename="Buy";}
        else if($page=='sell')
            {$titlename="Vendre des actions";}
        else if($page=='ranking')
            {$titlename="Classement des joueurs";}
        else if($page=='notfound')
            {$titlename="Error 404";}
        else
            {$titlename="index";}
    ?>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="css/menu.css">
        <!-- applique le code précédent -->
        <title>
            <?php echo $titlename ?>
        </title>
    </head>

    <body>
        <?php
        //d'après le paramètre en début de code permet d'inclure des pages dans index.php
        include 'php/header.php';
        if($page=='actions')
            {include 'php/actions.php';}
        else if($page=='dashboard')
            {include 'php/dashboard.php';}
        else if($page=='buy')
            {include 'php/buy.php';}
        else if($page=='portfolio')
            {include 'php/portfolio.php';}
        else if($page=='ranking')
            {include 'php/ranking.php';}
        else if($page=='sell')
            {include 'php/sell.php';}
        else
            {include 'php/notfound.php';} 
        include 'php/footer.php'; 
        ?>   
    </body>    
</html>