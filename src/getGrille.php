<?php
	/*
		Endpoint : /getGrille.php
		Method : GET
		In :
			pseudo => pseudo du joueur
		Out : objet JSON
			idpartie => texte, contient une chaine unique correspondant à la partie en cours
			idjoueur => texte, contient une chaine unique correspondant à l'identifiant du joueur sur la partie en cours
			couleur => couleur de départ
			grille => tableau à deux dimensions, correspond à la grille (stockée de la même façon que dans le jeu)
			delairestant => entier, nombre de secondes restantes pour la partie en cours
			delaiavantsuivante => entier, nombre de secondes restantes avant la partie suivante
		Codes http :
			200 => ok
			400 => paramètres manquants ou invalides
			500 => problème indéterminé
	*/

	require_once(__DIR__.'/_fonctions.inc.php');

	header('Content-Type: application/json; charset=utf8');
	header('Access-Control-Allow-Origin: https://colblor.gamolf.fr');

	if (isset($_GET['pseudo'])) {
		try {
			$pseudo = $_GET['pseudo'];
		}
		catch (Exception $e) {
			http_response_code(500);// ne doit jamais se produire
			exit;
		}
		// if ('' == $pseudo) {
			// http_response_code(400);
			// exit;
		// }
	}
	else {
		http_response_code(400); // manque pseudo dans les paramètres
		exit;
	}

	$result = new stdClass();

	$result->idpartie = getIDPartieEnCours();
	$result->couleur = getPartie($result->idpartie)->Couleur;
	$result->grille = getPartie($result->idpartie)->Grille;
	if (strlen(getPartie($result->idpartie)->GagnantIDJoueur)>0) {
		$result->delairestant = 0;
	}
	else {
		$TempsDepuisLeDebutDeLaPartie = time() - getPartie($result->idpartie)->DateHeureDebut;
		$result->delairestant = ($TempsDepuisLeDebutDeLaPartie>=CDureePartie)?0:CDureePartie-$TempsDepuisLeDebutDeLaPartie;
	}
	if ($result->delairestant>0) {
		$result->idjoueur = createIDJoueur($pseudo);
	}
	else {
		$result->idjoueur = '';
	}
	$HeureDemarragePartieSuivante = getPartie($result->idpartie)->DateHeureDebut + CDureePartie*2;
	$TempsAvantProchainePartie = $HeureDemarragePartieSuivante - time();
	$result->delaiavantsuivante = ($TempsAvantProchainePartie<1)?0:$TempsAvantProchainePartie;

	$json = json_encode($result);
	if (FALSE !== $json) {
		http_response_code(200);
		print($json);
	}
	else {
		http_response_code(500);
		exit;
	}
