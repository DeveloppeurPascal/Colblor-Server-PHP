<?php
	require_once(__DIR__.'/_fonctions.inc.php');

	print('<h1>Test getID()</h1>'."\n");
	for ($i = 0; $i< 50; $i++){
		print('<p>'.getID().'</p>'."\n");
	}

	print('<h1>Test getIDPartieEnCours()</h1>'."\n");
	print('<p>'.getIDPartieEnCours().'</p>'."\n");

	print('<h1>Test createIDJoueur()</h1>'."\n");
	print('<p>'.createIDJoueur('TotoDeLaBrousse').'</p>'."\n");

	print('<h1>Test getPartie()</h1>'."\n");
	print('<p>'.var_export(getPartie(getIDPartieEnCours()),true).'</p>'."\n");

	// print('<h1>Test creerPartie()</h1>'."\n");
	// $partie = creerPartie();
	// print('<p>'.var_export($partie,true).'</p>'."\n");
	// $partie2 = loadPartie($partie->IDPartie);
	// print('<p>'.var_export($partie2,true).'</p>'."\n");
