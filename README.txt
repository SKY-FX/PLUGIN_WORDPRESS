Pour installer le plugin "parserPDF et que tout fonctionne correctement sous WordPress
1.Copier coller le répertoire /parserPDF dans /wp-content/plugins/ du site WordPress
1-bis.Activer le plugin ParserPDF dans wordpress
2.Installer le plugin de formulaire "WordPress File Upload" dans Wordpress Extension
3.Copier coller le ShortCode issue du plugin du formulaire à l'endroit où l'on souhaite visualiser le formulaire.

Le plugin "parserPDF",
Utilise le filtre "wfu_after_upload" du plugin "WordPress File Upload" pour lancer la simulation en PHP après que le fichier PDF a était uploadé coté FRONT
Il utilise la librairie PDF PARSER php pour extraire les medata du pdf
Il récupère les données utilisateurs concernant l'impression du PDF
Il calcul le prix du document imprimé. Remise faite sur les reliures (>50 : 20%; >10 : 10%)
Il calcul aussi le poids total du document (5g par feuille couleur, 5.61g par feuille N&B) et du format (A3 ou A4)
Des limitations sur la possibilité d'imprimer sont faites en fonction du nombre de feuilles et du type de reliure ainsi que le poids du document
Il créé un produit wooCommerce dans WordPress pour être compatible avec l'ajout au panier de Wordpress (prix, poids, classe d'expedition)
Affiche le boutton "ajouter au panier" et les données de la simulation quand tout est prêt (JAVASCRIPT)
Le bouton ajouter au panier ajoute le produit au panier avec sa quantité et renvoie vers la page de validation des commandes

--> Les PDF uploadé sont stockés sur le serveur dans /wp-content/uploads
--> Les Produits créés sont stockés dans PRODUITS de wordpress
Ces deux derniers ne sont jamais supprimés via ce PLUGIN

