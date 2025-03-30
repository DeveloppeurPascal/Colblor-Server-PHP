<?php
	/*
		Endpoint : /sendGrille.php
		Method : POST
		In :
			idpartie => texte, contient une chaine unique correspondant à la partie en cours
			idjoueur => texte, ID du jeu récupéré pour ce serveur
			grille => tableau JSON, grille actuelle du joueur
		Out : objet JSON
			status => entier, 0 - ok, 1 - partie gagnée par le joueur, 2 - partie gagnée par un autre joueur, 3 - partie terminée
			pseudo => pseudo du gagnant si la partie a été gagnée
		Codes http :
			200 => ok
			400 => paramètres manquants ou invalides
			404 => nouvelle partie lancée, le joueur a cliqué trop tard
			500 => problème indéterminé
	*/
	require_once(__DIR__.'/_fonctions.inc.php');

	header('Content-Type: application/json; charset=utf8');
	header('Access-Control-Allow-Origin: https://colblor.gamolf.fr');

	if (isset($_POST['idpartie'])) {
		try {
			$idpartie = $_POST['idpartie'];
		}
		catch (Exception $e) {
			http_response_code(500);// ne doit jamais se produire
			exit;
		}
		$PartieEnCours = getPartie();
		if ($idpartie != $PartieEnCours->IDPartie) {
			// Si pas OK => nouvelle partie lancée ou "idpartie" bidon
			http_response_code(404);
			exit;
		}
	}
	else {
		http_response_code(400); // manque idpartie dans les paramètres
		exit;
	}
// histo('idpartie='.$idpartie);
			
	if (isset($_POST['idjoueur'])) {
		try {
			$idjoueur = $_POST['idjoueur'];
		}
		catch (Exception $e) {
			http_response_code(500);// ne doit jamais se produire
			exit;
		}
		$listeJoueurs = loadPartieJoueurs($idpartie);
		if (! in_array($idjoueur, $listeJoueurs, true)) {
			// idjoueur inexistant dans la liste des joueurs de la partie en cours
			http_response_code(400);
			exit;
		}
		$Joueur = loadJoueur($idjoueur);
	}
	else {
		http_response_code(400); // manque idjoueur dans les paramètres
		exit;
	}
// histo('idjoueur='.$idjoueur);

	if (isset($_POST['grille'])) {
		try {
			$grille = json_decode($_POST['grille']);
		}
		catch (Exception $e) {
			http_response_code(500);// ne doit jamais se produire
			exit;
		}
		if (! is_array($grille)) {
			http_response_code(400);
			exit;
		}
	}
	else {
		http_response_code(400); // manque grille dans les paramètres
		exit;
	}
// histo('ngrillePOST='.$_POST['grille']);
// histo('grillePHP='.var_export($grille,true));
	$Joueur->Grille = $grille;
	saveJoueur($Joueur);

	$result = new stdClass();

	$result->status = 0;
	$result->pseudo = '';

	// Partie gagnée par un autre joueur ?
	if (strlen($PartieEnCours->GagnantIDJoueur)>0) {
		$result->pseudo = $PartieEnCours->GagnantPseudo;
		$result->status = 2;
	}
	else {
		// Partie terminée ? (délai de jeu écoulé)
		$TempsDepuisLeDebutDeLaPartie = time() - $PartieEnCours->DateHeureDebut;
		$delairestant = ($TempsDepuisLeDebutDeLaPartie>=CDureePartie)?0:CDureePartie-$TempsDepuisLeDebutDeLaPartie;
		if ($delairestant<1) {
			$result->status = 3;
		}
		else {
			// Le joueur a-t-il gagné la partie ?
			$CouleurActuelle = -1;
			$HasMultiColor = false;
			// histo(var_export(count($grille),true));
			if (CNbLig != count($grille)) {
				http_response_code(400); // mauvaise dimension de la grille
				exit;
			}
			for ($j=0; $j<CNbLig; $j++) {
				// histo(var_export(count($grille[$j]),true));
				if (CNbCol != count($grille[$j])) {
					http_response_code(400); // mauvaise dimension de la grille
					exit;
				}
				for ($i=0; $i<CNbCol; $i++) {
					if ($CouleurActuelle<0) {
						$CouleurActuelle = $grille[$j][$i];
					}
					else if ($CouleurActuelle != $grille[$j][$i]) {
						$HasMultiColor = true;
					}
					if ($HasMultiColor) {
						break;
					}
				}
				if ($HasMultiColor) {
					break;
				}
			}
			if (! $HasMultiColor) {
				// Qu'une seule couleur dans la grille, le joueur a gagné la partie
				$result->status = 1;
				$PartieEnCours->GagnantIDJoueur = $Joueur->IDJoueur;
				$PartieEnCours->GagnantPseudo = $Joueur->Pseudo;
				savePartie($PartieEnCours);
			}
		}
	}

	// histo(var_export($result,true));
	$json = json_encode($result);
	// histo($json);
	if (FALSE !== $json) {
		http_response_code(200);
		print($json);
	}
	else {
		http_response_code(500);
		exit;
	}
