# Virtual Trader

## Objectif du projet
L’objectif de ce projet est de concevoir une application de gestion de portefeuille d’actions virtuelles (en utilisant MySQL et PHP).

## Description du jeu
L’utilisateur démarre le jeu avec une certaine quantité d’argent (10 000€) pour acheter des actions virtuelles parmi une liste prédéfinie d’actions. Chaque action possède un code, une description, une valeur d’achat. Chaque année une action peut également rapporter un dividende (une action possède donc une date de distribution de dividende). Un joueur perd le jeu quand la valeur totale de son portefeuille (somme de la valeur des actions possédées et des liquidités) descend en dessous de 1000€.

## Déroulement d’un tour de jeu
Le jeu fonctionne en pseudo temps réel. Les joueurs peuvent effectuer les actions de leur choix et les conséquences de ces actions sont appliquées immédiatement. À pas de temps fixe (par exemple toutes les minutes), le jeu est mis à jour, c’est-à-dire que :
- La date du jeu augmente d’un mois
- Le joueur perçoit les dividendes de ses actions s’il y en a (une action ne verse qu’un seul dividende par an, à condition de posséder l’action à la date de distribution du dividende)
- Les valeurs des actions sont mises à jour (cela affecte le prix auquel vous pouvez acheter les actions, mais aussi le prix auquel vous pouvez les vendre) – pour mettre à jour le prix d’une action, vous utiliserez le principe suivant : l’évolution du prix est calculée à partir de l’évolution du prix du mois précédent auquel on ajoute aléatoirement une valeur comprise entre -3 et +3 points, sans pouvoir dépasser les bornes -10% et +10% et sans pouvoir descendre en dessous de 1€ par action. Par exemple, si le mois précédent l’action avait monté de +5%, pour le mois en cours le prix de l’action évoluera selon une valeur aléatoire comprise entre +2% et +8%.

## Fonctionnalité à implémenter

### Fonctionnalités générales
- Inscription (email, mot de passe)
- Connexion
- Modifier son mot de passe
- Rechercher un joueur et suivre un joueur (cela permet de voir ses derniers achats/ventes)

### Fonctionnalités propres au jeu
- Initialiser le jeu, reprendre le jeu
- Visualiser l’état du jeu (quantité d’argent possédé, liste des actions possédées, évolution de la valeur du portefeuille sur les 12 derniers mois)
- Rechercher et filtrer les actions disponibles (par nom, par prix, par pourcentage de progression sur 1 mois ou sur 1 an)
- Visualiser l’évolution du prix d’une action sur les 12 derniers mois
- Acheter/vendre une action
- Visualiser le classement des joueurs (en fonction de la valeur totale du portefeuille)
- Dérouler les tours de jeu

### Améliorations possibles
Vous pouvez apporter des améliorations au jeu pour le rendre plus riche. Du moment que les objectifs décrits ci-dessus sont remplis, vous pouvez faire preuve de créativité. Par exemple :
- Ajouter des emprunts
- Gérer des options d’achat

