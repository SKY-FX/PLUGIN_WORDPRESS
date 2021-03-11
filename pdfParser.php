<?php
/*
Plugin Name: PARSER PDF
Plugin URI: https://www.sylvain-chabaud.fr
Description: Chabaud Sylvain
Version: 1.0
Author URI: https://www.sylvain-chabaud.fr
*/





if (!function_exists('wfu_after_upload_handler')) 
{
	// retrieves the attachment ID from the file URL
	function pippin_get_image_id($image_url) {
		global $wpdb;
		$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url )); 
		return $attachment[0]; 
	}

	function wfu_after_upload_handler($changable_data, $additional_data) 
	{
		// Include 'Composer' autoloader.
		include 'vendor/autoload.php';

		// Build necessary objects.
		$parser = new \Smalot\PdfParser\Parser();	
	
		// Récupère le premier fichier "uploadé" et le seul
		$files = $additional_data["files"];
		$file = $additional_data["files"][0];
		  
		// Vérifie que le téléchargement du PDF a eu lieu avec succes
		if ( $file["upload_result"] == "success" || $file["upload_result"] == "warning" ) 
		{
			$TitrePDF   = "";
			$nbPagesPDF  = "";
			$FormatUser  = "";
			$ImpressionUser  = "";
			$ReliureUser  = "";
			$nbReliureUser  = "";
			$infosPrix = "";
			
			// ########
			// ######## METADATA du PDF
			// ########
			// $nbPagesPDF ='';
			// $im = new Imagick();
			$fileName = $file[ "filepath" ];
			// $im->readimage( $fileName ); 
			// $nbPagesPDF = $im->getNumberImages();
			$titrePDF = basename($fileName);
			// $infosPdf = 'Nom : ' . $titrePDF . '<br/>' . 'Nombre de page(s) : ' . $nbPagesPDF . '<br/>';
			
				
			// Récupère l'adresse du PDF et le parse avec la librairie PDFPARSER
			$file_path = $file["filepath"];
			$pdf = $parser->parseFile($file_path);
			
			
			// Récupère toutes les METADONNEES du fichier PDF avec la librairie PDF PARSER
			$details  = $pdf->getDetails();
		
			// Récupère les METADONNEES (string or array) qui nous intersse (titre, nbPage).
			$infosPdf = 'PDFname : ' . $titrePDF . '<br/>';
			foreach ($details as $property => $value) {
				if (is_array($value)) { $value = implode(', ', $value); }
				
				if ($value) 
				{ 
					$infosPdf .= $property. ' : ' . $value . '<br/>'; 
					// if ($property == "Title") {$titrePDF = $value;} 
					if ($property == "Pages") {$nbPagesPDF = $value;} 
				}
			}
			
			// ########
			// ######## DONNEES UTILISATEURS DU FORMULAIRE
			// ########
			//Récupère toutes les données utilisateurs du formulaire 
			$userDatas = $file["user_data"];
			
			// Split les données du formulaire dans un tableau.
			$infosDatas = "";
			$infosPdfTab = array();
			foreach ( $userDatas as $userData )
			{
				$infosDatas .= $userData["label"] . ' : ' . $userData["value"] . ' <br/>';
				array_push($infosPdfTab, $userData["value"]);
			}
			$FormatUser  = $infosPdfTab[0];
			$ImpressionUser  = $infosPdfTab[1];
			$ReliureUser  = $infosPdfTab[2];
			$nbReliureUser  = $infosPdfTab[3];
			
			
			// Pour les reliures "dos carré collé" : pas d'impression en dessous de 100 feuilles
			$pasImpression = "";
			if ( $ReliureUser=="Dos-carré-collé" && $nbPagesPDF<="100") $pasImpression = "Minimum 100 feuilles pour imprimer en Dos-carré-collé";
			
			if ($pasImpression=="")
			{

				// Convert to Text
				// $pages  = $pdf->getPages();
				// $page 	= trim($pages[0]);
				// $text = trim($pdf->getText());
				
				// ########
				// ######## CALCUL DU PRIX DE L'IMPRESSION DU PDF EN FONCTION DE (format, impression, reliure, nbPages, nbReliures)
				// ########
				$reliureTab = [
					'Plastique'       => ['1-20' => 2.00, '21-50' => 2.50, '51-100' => 2.90, '101-150' => 3.70, '151-200' => 4.20, '201Plus' => 5.50],
					'Métal'           => ['1-20' => 2.10, '21-50' => 2.70, '51-100' => 3.10, '101-150' => 3.90, '151-200' => 4.40, '201Plus' => 5.70],
					'Unibind'        => ['1-20' => 2.30, '21-50' => 2.90, '51-100' => 3.30, '101-150' => 4.10, '151-200' => 4.60, '201Plus' => 5.90],
					'Thermocollé'     => ['1-20' => 3.30, '21-50' => 3.90, '51-100' => 4.30, '101-150' => 5.10, '151-200' => 5.60, '201Plus' => 9.90],
					'Dos-carré-collé' => ['1-20' => 0.00, '21-50' => 0.00, '51-100' => 0.00, '101-150' => 7.00, '151-200' => 7.00, '201Plus' => 7.00]
				];
				$impressionCouleur = [
					'A4' => ['1-5' => 0.70, '6-10' => 0.60, '11-50' => 0.50, '51-150' => 0.40, '151-500' => 0.30, '501-1000' => 0.25, '1000Plus' => 0.20],
					'A3' => ['1-5' => 1.40, '6-10' => 1.20, '11-50' => 1.00, '51-150' => 0.80, '151-500' => 0.60, '501-1000' => 0.50, '1000Plus' => 0.40]
					
				];
				$impressionNB = [
					'A4' => ['1-5' => 0.20, '6-20' => 0.15, '21-250' => 0.10, '251-500' => 0.08, '501-1000' => 0.06, '1000Plus' => 0.05],
					'A3' => ['1-5' => 0.40, '6-20' => 0.30, '21-250' => 0.20, '251-500' => 0.16, '501-1000' => 0.12, '1000Plus' => 0.10]
				];
				
				// Label tableau couleur
				if ($nbPagesPDF<=5) $label_impCouleur = '1-5';
				else if ($nbPagesPDF<=10) $label_impCouleur = '6-10';
				else if ($nbPagesPDF<=50) $label_impCouleur = '11-50';
				else if ($nbPagesPDF<=150) $label_impCouleur = '51-150';
				else if ($nbPagesPDF<=500) $label_impCouleur = '151-500';
				else if ($nbPagesPDF<=1000) $label_impCouleur = '501-1000';
				else $label_impCouleur = '1000Plus';
				
				// Label tableau Noir et blanc
				if ($nbPagesPDF<=5) $label_NB = '1-5';
				else if ($nbPagesPDF<=20) $label_NB = '6-20';
				else if ($nbPagesPDF<=250) $label_NB = '21-250';
				else if ($nbPagesPDF<=500) $label_NB = '251-500';
				else if ($nbPagesPDF<=1000) $label_NB = '501-1000';
				else $label_NB = '1000Plus';
				
				// Label tableau Reliure
				if ($nbPagesPDF<=20) $label_reliure = '1-20';
				else if ($nbPagesPDF<=50) $label_reliure = '21-50';
				else if ($nbPagesPDF<=100) $label_reliure = '51-100';
				else if ($nbPagesPDF<=150) $label_reliure = '101-150';
				else if ($nbPagesPDF<=200) $label_reliure = '151-200';
				else $label_reliure = '201Plus';
				
				// Calcul du prix total en fonction du format (A3, A4), du nombre de feuille, du nombre de reliure et de la couleur ou pas.
				// total_prix = prix_reliure*nbReliureUser + prix_impression*nbPagesPDF
				// prix reliure : fonction de reliure, reliure et du format
				// prix impression : fonction de nbfeuilles, et format
				// un pourcentage est appliqué après le calcul du total pour prendre en compte la remise en fonction du nombre de reliure
				
				// Prix reliure
				$prix_reliure = floatval($reliureTab[$ReliureUser][$label_reliure]);
				if ($FormatUser=="A3") $prix_reliure += floatval(1);
				
				
				// Prix impression
				if ($ImpressionUser=="Couleur")
				{
					$prix_impression = floatval($impressionCouleur[$FormatUser][$label_impCouleur]);
				}
				else
				{
					$prix_impression = floatval($impressionNB[$FormatUser][$label_NB]);
				}
				
				
				// Fait la somme des deux prix (impression et reliure)
				// + Remise sur le nombre de reliure
				$prix = $prix_reliure*floatval($nbReliureUser) + $prix_impression*floatval($nbPagesPDF)*floatval($nbReliureUser);
				$prixUnitaire = $prix_reliure + $prix_impression*floatval($nbPagesPDF);
				$infosPrix = (float)$prix;
				$infosPrixUnitaire = (float)$prixUnitaire;
				//Remise
				if (floatval($nbReliureUser)>50)
				{
					$infosPrix = (float)$prix*0.8;
					$infosPrixUnitaire = (float)$prixUnitaire*0.8;
				}
				else if (floatval($nbReliureUser)>10)
				{
					$infosPrix = (float)$prix*0.9;
					$infosPrixUnitaire = (float)$prixUnitaire*0.9;
				}
				
				
				// ########
				// ######## CREATION DU PRODUIT WORDPRESS POUR WOOCOMMERCE
				// ########
				$custom_price = $infosPrixUnitaire;
				$product = new WC_Product;
				$product->set_name('IMPRESSION ' . $titrePDF);
				$product->set_description('NomPdf : ' . $titrePDF . ',<br/>Format : ' . $FormatUser . ',<br/>Impression : ' . $ImpressionUser . ',<br/>Reliure : ' . $ReliureUser . ',<br/>nbPages : ' . $nbPagesPDF . ',<br/>nbReliures : ' . $nbReliureUser . ',<br/>Prix par reliure : ' . $custom_price . '€');
				$product->set_regular_price($custom_price);
				$visibility = 'hidden';
				$product->set_catalog_visibility($visibility);
				

				// store the image ID in a var
				$image_url = 'https://chang-in.fr/wp-content/uploads/2021/03/imprimante-1.png';
				$image_id = pippin_get_image_id($image_url);
				$product->set_image_id($image_id);
				$product->save();
				
				// Récupère l'ID et le permaLink du produit tout juste créé
				// Ils servirons lorsque l'utilisateur cliquera sur le bouton ADD_TO_CART
				$pdfProductId = $product->get_id();
				$permalink = $product->get_permalink();
				
				
				// ****************************//
				// TEST CONVERT PDF INTO JPG
				//******************************//		
				// $pages ='';
				// $im = new Imagick();
				// $im->readimage( $file[ "filepath" ] ); 
				// $pages = $im->getNumberImages();
				
				// for($p = 1; $p <= $pages; $p++)
				// {
					// $im->setIteratorIndex($p-1);
					// $uniqueColor = $im->getImageColors();
					// $test .= 'Page ' . $p . ' : ' . $uniqueColor . '<br/>';
				// }
				// $im->clear(); 
				// $im->destroy();
				//###########
				//###########
			
			
				// ########
				// ######## RENVOIE LES DONNEES A AFFICHER : UTILISE LE JAVASCRIPT
				// ########
				
				$changable_data["js_script"] = "
					
					// Récupère toutes les div dans lesquels on va afficher nos resultats
					var currentBtnDiv = document.getElementById('boutton_AddToCart');
					var currentPrixDiv = document.getElementById('pdfPrix');
					var currentPdfDiv = document.getElementById('pdfParserInfos');
					var currentDatasDiv = document.getElementById('datasParserInfos');
					
					// Nettoie le contenu des div s'il y en a avant d'en mettre d'autres
					if (currentBtnDiv)
					{
						currentBtnDiv.innerHTML = '';
						currentPrixDiv.innerHTML = '';
						currentPdfDiv.innerHTML = '';
						currentDatasDiv.innerHTML = '';
					}
					
					//
					// CREER UN BOUTON POUR REDIRIGER VERS AJOUTER PANIER DE WOOCOMMERCE
					// ET LE PLACE DANS LA DIV ASSOCIE
					var btn = document.createElement('BUTTON');
					btn.innerHTML = 'Ajoute moi au panier';
					btn.setAttribute('type', 'submit');
					// btn.setAttribute('id', 'btnId');
					btn.setAttribute('style', 'padding:20px; backgroundColor:#ef7d00;');
					var form = document.createElement('form');
					form.setAttribute('method','post');
					// form.setAttribute('action','https://chang-in.fr/?add-to-cart=$pdfProductId');
					form.setAttribute('action','$permalink');
					form.appendChild(btn);
					currentBtnDiv.appendChild(form);
				
					// DIV POUR LE PRIX
					var newDivPrix = document.createElement('div');  
					// newDivPrix.innerHTML = 'Prix pour imprimer un PDF (prix par unité) : <b>$infosPrixUnitaire €</b><br/>Prix total (pour $nbReliureUser reliure(s)) : <b>$infosPrix €</b><br/>Nombre de couleur(s) par page : <br/>$test';
					newDivPrix.innerHTML = 'Prix pour imprimer un PDF (prix par unité) : <b>$infosPrixUnitaire €</b><br/>Prix total (pour $nbReliureUser reliure(s)) : <b>$infosPrix €</b>';
					currentPrixDiv.appendChild(newDivPrix);
				
					// DIV POUR LES INFOS DU PDF
					var newDivFile = document.createElement('div');
					newDivFile.innerHTML = 'PDF-METADATAS : <br/><br/>$infosPdf';
					currentPdfDiv.appendChild(newDivFile);
					
					// DIV POUR LES DONNEES UTILISATEURS
					var newDivDatas = document.createElement('div');
					newDivDatas.innerHTML = '<br/><br/>DATAS-USER : <br/><br/>$infosDatas<br/>$pasImpression';
					currentDatasDiv.appendChild(newDivDatas);
					
				";
			}
			else
			{
				$changable_data["js_script"] = "
					var currentBtnDiv = document.getElementById('boutton_AddToCart');
					var currentPrixDiv = document.getElementById('pdfPrix');
					var currentPdfDiv = document.getElementById('pdfParserInfos');
					var currentDatasDiv = document.getElementById('datasParserInfos');
					// var currentBtnIdDiv = document.getElementById('datasParserInfos');
					
					// Nettoie le contenu des div s'il y en a avant d'en mettre d'autres
					if (currentBtnDiv)
					{
						currentBtnDiv.innerHTML = '';
						currentPrixDiv.innerHTML = '';
						currentPdfDiv.innerHTML = '';
						currentDatasDiv.innerHTML = '';
					}
					
					// DIV POUR L'ERREUR
					var newDivError = document.createElement('div');  
					// form.setAttribute('style','color:red;');
					newDivError.innerHTML = '$pasImpression<br/>';
					currentPrixDiv.appendChild(newDivError);
				";
			}
			
		}
		
		return $changable_data;
	}
	add_filter('wfu_after_upload', 'wfu_after_upload_handler', 10, 2);
}