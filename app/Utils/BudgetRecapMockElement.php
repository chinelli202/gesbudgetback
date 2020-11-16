<?php


    // protected $baniere;
    // protected $titre;
    // protected $chapitres;
    // protected $grandeslignes;
    // protected $grandesrubriques;

    $mockgrandesrubriques = ['DEPENSES','RECETTES'];
    $mockrubriquesdepense = ["DEPENSES FONCTIONNEMENT","DEPENSES INVESTISSEMENT"];
    $mockrubriquesdfonctionnement = [
                'A - Charges de personnel',
                'B - Missions ',
                'C- Diverses Représentations',
                'D - Charges diverses de fonctionnement',
                'E - Honoraires',
                'F - Dons - subventions',
                'G - Formation',
                'H - Imprévus'
    ];
    $mockrubriquesdinvestissement = ["A - Equipement-Immobilisation","B - Dépenses d'Hydrocarbures","C - Investissements financiers"];

    $mockrubriquesrecettes = ["A - Produits Financiers",
                "B - Remboursements prêts",
                "C - Recettes pétrolières",
                "D - Recettes de la Barge Rio Del Rey",
                "E - Autres recettes"];

    $mockchapitresadg = ["CABINET DU DIRECTEUR GENERAL","DIVISION INFORMATIQUE","DIVISION  COMMUNICATION"];
    $mockchapitresxadg = ["DIRECTION DES RESSOURCES HUMAINES","DIRECTION FINANCIERE","DIRECTION DES AFFAIRES JURIDIQUES"];
    $mockligne = [
        'label' => '',
        'prevision' => '',
        'realisations_mois' => '',
        'realisations_precedentes' => '',
        'realisations_cumulees' => '',
        'engagements_mois' => '',
        'execution_mois' => '',
        'solde' => '',
        'taux_execution' => ''
    ];

    $mocktypes = [
        'type1' => 'ban_gr',
        'type2' => 'ban_gl',
        'type3' => 'ban_titre_gl',
        'type4' => 'ti_chap_rub',
        'type5' => 'chap_rub',
        'type6' => 'chap_rub_sous_chap'
    ];

    $mocktableheader = [
        'prevision' => 'LIBELLES',
        'prevision_label' => 'Prévisions 2020 (1)',
        'realisations_mois_label' => 'Réalisations du mois de juin 2020  (2)',
        'realisations_precedentes_label' => 'Réalisations précédentes 2020 (3)',
        'realisations_cumulees_label' => 'Réalisations cumulées au 30/06/2020 (4)',
        'engagements_mois_label' => 'Engagements au 30/06/2020 (5)',
        'execution_mois_label' => 'Exécution au 30/06/2020 (6)',
        'solde_label' => 'Solde (7)',
        'taux_execution_label' => 'Taux d\'exécution (8)'
    ];

    function mockrecapgeneral(){
        //global $types, $grandesrubriques, $rubriquesdepense, $rubriquesdfonctionnement;
        $mockentries = include 'MockEntries.php';
        $mockgrandesrubriques = $mockentries['grandesrubriques'];
        $mocktypes = $mockentries['types'];
        $mockrubriquesdepense = $mockentries['rubriquesdepense'];
        $mockrubriquesdfonctionnement = $mockentries['rubriquesdfonctionnement'];


        $recapgeneral = new stdClass();
        $recapgeneral->type = $mocktypes['type1'];
        $recapgeneral->baniere = "RECAPITULATIF GENERAL";
        $recapgeneral->grandesrubriques = [];
        $recapgeneral->tableheader = $mockentries['tableheader'];
        foreach($mockgrandesrubriques as $granderubrique){
            echo $granderubrique;
            $granderub = new stdClass();
            $granderub->label = $granderubrique;
            $granderub->rubriques = [];
            foreach($mockrubriquesdepense as $rubriquedepense){
                $rubrique = new stdClass();
                $rubrique->label = $rubriquedepense;
                $rubrique->lignes = [];
                foreach($mockrubriquesdfonctionnement as $singlelabel){
                    $ligne = new stdClass();
                    $ligne->label = $singlelabel;
                    $ligne->prevision = "50 000 000";
                    $ligne->realisations_mois = "50 000 000";
                    $ligne->realisations_precedentes = "50 000 000";
                    $ligne->realisations_cumulees = "50 000 000";
                    $ligne->engagements_mois = "50 000 000";
                    $ligne->execution_mois = "50 000 000";
                    $ligne->solde = "50 000 000";
                    $ligne->taux_execution = "12";
                    array_push($rubrique->lignes, $ligne);
                }
                array_push($granderub->rubriques, $rubrique);
            }
            array_push($recapgeneral->grandesrubriques, $granderub);
        }
        return $recapgeneral;
    }

    // public function init(){
        
    // }

    // public function getrubriqueslabels(){
    //     [
    //         'A - Charges de personnel',
    //         'B - Missions ',
    //         'C- Diverses Représentations',
    //         'D - Charges diverses de fonctionnement',
    //         'E - Honoraires',
    //         'F - Dons - subventions',
    //         'G - Formation',
    //         'H - Imprévus'
    //     ]
    // }

