<?php

// $recapparams = new stdClass();
// $recapparams->jour = "2020-06-12";
// $recapservice = new App\Services\RecapService();
// $id = 8;
// $critere = "jour";
// $recapdata = $recapservice->getRecapChapitre($id, $critere, $recapparams);
// $parserservice = new App\Utils\ExcellParser();
// $parserparams = new stdClass();
// //set filename, request type, set baniere
// $parserparams->baniere = $recapdata->libelle;
// $parserparams->filename = "rapport_".$critere."_".$recapparams->jour.".xlsx";


//$parserservice->toExcell($recapdata, $parserparams);

$service = new App\Services\RecapService();
$params = new stdClass();
$critere = 'jour';
$params->jour = "2020-06-12";
//$params->section = "Dépenses";
$params->domaine = "Fonctionnement";
$params->sectiontype = 'sous_section';
$params->sectionname = 'investissement';

//params for domaine
$params->domaine = "mandat";
$params->filename = "rapport_".$params->domaine."_".$params->jour.".xlsx";
$recap = $service->getRecapDomaine($critere, $params);
$params->baniere = $recap->libelle;


//$recapdata = $service->getRecapSection($critere, $params);

//now just print it to excell
 $parserservice = new App\Utils\ExcellParser();
 //$params->baniere = $recapdata->libelle;
 $params->type = 'domaine';
 //$params->filename = "rapport_".$params->baniere.".xlsx";
 $parserservice->toExcell($recap, $params);