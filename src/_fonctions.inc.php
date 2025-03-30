<?php
	// TODO : ajouter des LOCK / UNLOCK sur les fichiers ou les opérations de modification de données
	define('CDureePartie',60);
	define('CNbCol', 10);
	define('CNbLig', 10);

	function histo($msg) {
		$f=__DIR__.'/log.txt';
		file_put_contents($f,@file_get_contents($f)."\n".$msg);
	}

	function getNomFichierPartie($idpartie=false) {
		if (false !== $idpartie) {
			return __DIR__.'/data/'.$idpartie.'.partie';
		}
		else {
			return false;
		}
	}

	function getNomFichierPartieJoueurs($idpartie=false) {
		if (false !== $idpartie) {
			return __DIR__.'/data/'.$idpartie.'.joueurs';
		}
		else {
			return false;
		}
	}

	function getNomFichierJoueur($idjoueur=false) {
		if (false !== $idjoueur) {
			return __DIR__.'/data/'.$idjoueur.'.joueur';
		}
		else {
			return false;
		}
	}

	function getNomFichierIDPartieEnCours() {
		return __DIR__.'/data/encours.partie';
	}

	function getID() {
		$id = '';
		for($i = 0; $i<50; $i++) {
			$c = mt_rand(0, 10+26+26-1);
			if ($c<10) {
				$id .= chr(ord('0')+$c);
			}
			else if ($c<10+26) {
				$id .= chr(ord('a')+$c-10);
			}
			else if ($c<10+26+26) {
				$id .= chr(ord('A')+$c-10-26);
			}
			else {
				die('getID() : ne doit jamais arriver');
			}
		}
		return $id;
	}

	function savePartie($partie=false) {
		if (is_object($partie) && isset($partie->IDPartie)) {
			file_put_contents(getNomFichierPartie($partie->IDPartie), json_encode($partie));
			return true;
		}
		else {
			return false;
		}
	}

	function creerPartie() {
		$partie = new stdClass();
		$partie->IDPartie = getID();
		$partie->Grille = array();
		for ($j=0; $j<CNbLig; $j++) {
			$partie->Grille[$j] = array();
			for ($i=0; $i<CNbCol; $i++) {
				$partie->Grille[$j][$i] = mt_rand(1,7);
			}
		}
		$partie->Couleur = $partie->Grille[mt_rand(0,CNbLig-1)][mt_rand(0,CNbCol-1)];
		$partie->DateHeureDebut = time();
		$partie->GagnantIDJoueur = '';
		$partie->GagnantPseudo = '';
		savePartie($partie);
		file_put_contents(getNomFichierIDPartieEnCours(), $partie->IDPartie);
		return $partie;
	}
	
	function getIDPartieEnCours() {
		if (file_exists(getNomFichierIDPartieEnCours())) {
			$idpartie = file_get_contents(getNomFichierIDPartieEnCours());
			$partie = loadPartie($idpartie);
			if (is_object($partie)) {
				if ($partie->DateHeureDebut + 2*CDureePartie > time()) {
					return $partie->IDPartie;
				}
			}
		}
		return creerPartie()->IDPartie;
	}

	function loadPartie($idpartie=false) {
		if (false === $idpartie) {
			$idpartie = getIDPartieEnCours();
		}
		if ((false !== $idpartie) && (file_exists(getNomFichierPartie($idpartie)))) {
			return json_decode(file_get_contents(getNomFichierPartie($idpartie)));
		}
		return false;
	}

	function savePartieJoueurs($liste, $idpartie = false) {
		if (! is_array($liste)) {
			return false;
		}
		if (false === $idpartie) {
			$idpartie = getIDPartieEnCours();
		}
		file_put_contents(getNomFichierPartieJoueurs($idpartie), json_encode($liste));
	}

	function loadPartieJoueurs($idpartie = false) {
		if (false === $idpartie) {
			$idpartie = getIDPartieEnCours();
		}
		if (file_exists(getNomFichierPartieJoueurs($idpartie))) {
			return json_decode(file_get_contents(getNomFichierPartieJoueurs($idpartie)));
		}
		else {
			return array();
		}
	}
	
	function saveJoueur($idjoueur, $pseudo='', $grille=false) {
		if (is_object($idjoueur)) {
			$Joueur = $idjoueur;
		}
		else {
			$Joueur = new stdClass();
			$Joueur->IDJoueur = $idjoueur;
			$Joueur->Pseudo = $pseudo;
			$Joueur->Grille = $grille;
		}
		if (false !== ($NomFichier = getNomFichierJoueur($Joueur->IDJoueur))) {
			$Joueur->horodatage = time();
			file_put_contents($NomFichier, json_encode($Joueur));
		}
	}

	function loadJoueur($idjoueur) {
		if (file_exists(getNomFichierJoueur($idjoueur))) {
			return json_decode(file_get_contents(getNomFichierJoueur($idjoueur)));
		}
		return false;
	}

	function getJoueurPseudo($idjoueur) {
		if (false !== $Joueur = loadJoueur($idjoueur)) {
			return $Joueur->Pseudo;
		}
		else {
			return '';
		}
	}

	function createIDJoueur($pseudo) {
		$listeJoueurs = loadPartieJoueurs();
		do {
			$PasOk = false;
			$id = getID();
			for ($i=0; ($PasOk || ($i < count($listeJoueurs))); $i++) {
				$PasOk = ($listeJoueurs[$i] == $id);
			}
		} while ($PasOk);
		$listeJoueurs[] = $id;
		savePartieJoueurs($listeJoueurs);
		saveJoueur($id, $pseudo);
		return $id;
	}

	function getPartie($idpartie=false) {
		if (false !== $idpartie) {
			return loadPartie($idpartie);
		}
		return getPartie(getIDPartieEnCours());
	}
