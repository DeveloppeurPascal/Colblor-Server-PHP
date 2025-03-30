<?php
	/*
		Endpoint : /getEcrans.php
		Method : GET
		In :
			horodatage => entier, à 0 la première fois, à la valeure retournée par le serveur les fois suivantes, permet de filter les grilles reçues par leur date de dernière mise à jour
		Out : objet JSON
			horodatage => timestamp en cours lors de la réponse
			idpartie => chaine, ID de la partie en cours
			GagnantIDJoueur => chaine, ID du gagnant de la partie
			GagnantPseudo => chaine, pseudo du gagnant de la partie
			ecrans => tableau JSON d'objets (idjoueur (string), pseudo du joueur (string), partie gagnée (1 (oui) ou 0 (non)), grille (tableau))
		Codes http :
			200 => ok
			500 => problème indéterminé
	*/

	require_once(__DIR__.'/_fonctions.inc.php');

	header('Content-Type: application/json; charset=utf8');
	header('Access-Control-Allow-Origin: *');

	if (isset($_GET['horodatage'])) {
		try {
			$horodatage = 1*$_GET['horodatage'];
		}
		catch (Exception $e) {
			http_response_code(500);// ne doit jamais se produire
			exit;
		}
	}
	else {
		$horodatage = 0;
	}
// histo(var_export($horodatage,true));

	$result = new stdClass();
	
	$partie = getPartie();
		
	$result->horodatage = time();
	$result->idpartie = $partie->IDPartie;
	$result->GagnantIDJoueur = $partie->GagnantIDJoueur;
	$result->GagnantPseudo = $partie->GagnantPseudo;
	
	$result->ecrans = array();
		$listeJoueurs = loadPartieJoueurs($result->idpartie);
	for ($i = 0; $i < count($listeJoueurs); $i++) {
		$joueur = loadJoueur($listeJoueurs[$i]);
		if ($joueur->horodatage >= $horodatage) {
			$result->ecrans[] = $joueur;
		}
	}
// histo(var_export($result, true));

	$json = json_encode($result);
	if (FALSE !== $json) {
		http_response_code(200);
		print($json);
	}
	else {
		http_response_code(500);
		exit;
	}
