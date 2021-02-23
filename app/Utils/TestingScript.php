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

// $service = new App\Services\RecapService();
// $params = new stdClass();
// $critere = 'jour';
// $params->jour = "2020-06-12";
// //$params->section = "Dépenses";
// $params->domaine = "Fonctionnement";
// $params->sectiontype = 'sous_section';
// $params->sectionname = 'investissement';

// //params for domaine
// $params->domaine = "mandat";
// $params->filename = "rapport_".$params->domaine."_".$params->jour.".xlsx";
// $recap = $service->getRecapDomaine($critere, $params);
// $params->baniere = $recap->libelle;


// //$recapdata = $service->getRecapSection($critere, $params);

// //now just print it to excell
//  $parserservice = new App\Utils\ExcellParser();
//  //$params->baniere = $recapdata->libelle;
//  $params->type = 'domaine';
//  //$params->filename = "rapport_".$params->baniere.".xlsx";
//  $parserservice->toExcell($recap, $params);

 function match_sentences($sentence1, $sentence2, $threshold = 0.5){
    // forming tokens
    $tokens1 = explode(" ", $sentence1);
    $tokens2 = explode(" ", $sentence2);
    // looking for matches
    $union = $tokens2;
    $intersection = [];
    
    foreach ($tokens1 as $token1 ){
        // try matching each token of second sentence with this token. if no match is found at the end of the loop, then add it to the union array.
        // if a match is found, add it to the intersection
        $matched = false;
        foreach($tokens2 as $token2){
            if($token1 === $token2){
                $matched = true;
                break;
            }
        }
        if($matched == true){
            array_push($intersection, $token1);
        }
        else{
            array_push($union, $token1);
        } 
    }
    // calculate Jaccard similarity formula
    $ratio = count($intersection)/count($union);
    if($ratio >= $threshold)
        echo "both sentences match";
    else
        echo "sentences don't match";
    return $ratio >= $threshold ? true : false;
}

function setEntrepriseForModel(){
    $titres = App\Models\Titre::all();
    foreach($titres as $titre){
        $titre->code_entreprise = "SNHSIEGE";
        $titre->save();
    }
    echo "done setting titres\n";
    $chapitres = App\Models\Chapitre::all();
    foreach($chapitres as $chapitre){
        $chapitre->code_entreprise = "SNHSIEGE";
        $chapitre->save();
    }
    echo "done setting chapitres\n";
    $rubriques = App\Models\Rubrique::all();
    foreach($rubriques as $rubrique){
        $rubrique->code_entreprise = "SNHSIEGE";
        $rubrique->save();
    }
    echo "done setting rubriques\n";
    $lignes = App\Models\Ligne::all();
    foreach($lignes as $ligne){
        $ligne->code_entreprise = "SNHSIEGE";
        $ligne->save();
    }
    echo "done setting lignes\n";

    echo "done setting entreprise for model";
}

// $sentence1 = "Etude en vue de la mise en place d'une Médiathèque";
// $sentence2 = "en place d'une Médiathèque";
// $sentence3 = "szel leeff lefqz leff";

// echo "result for matching ".$sentence1." with ".$sentence2."\n";
// echo match_sentences($sentence1, $sentence2, 0.8);
setEntrepriseForModel();