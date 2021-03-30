Pour installer le plugin "parserPDF et que tout fonctionne correctement sous WordPress
1.Copier coller le répertoire /parserPDF dans /wp-content/plugins/ du site WordPress
2.Installer le plugin de formulaire "WordPress File Upload" dans Wordpress Extension
3.Copier coller le ShortCode issue du plugin du formulaire à l'endroit où l'on souhaite visualiser le formulaire.
4.Possibilité d'ajouter le plugin "Woo Discount Rules" pour ajouter des règles de réductions sur le prix dans le panier de Woocommerce

Le plugin "parserPDF",
Utilise le filtre "wfu_after_upload" du plugin "WordPress File Upload" pour lancer la simulation en PHP après que le fichier PDF a était uploadé coté FRONT
Il utilise la librairie PDF PARSER php pour extraire les medata du pdf
Il récupère les données utilisateurs concernant l'impression du PDF
Il calcul le prix du document imprimé.
Il calcul aussi le poids total du document (5g par feuille) et du format (A3 ou A4)
Des limitations sur la possibilité d'imprimer sont faites en fonction du nombre de feuilles et du type de reliure
Il créé un produit wooCommerce dans WordPress pour être compatible avec l'ajout au panier de Wordpress
Affiche le boutton "ajouter au panier" et les données de la simulation quand tout est prêt (JAVASCRIPT)

