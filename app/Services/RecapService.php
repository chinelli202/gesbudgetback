<?php

namespace App\Services;

use App\Models\Chapitre;
use App\Models\Ligne;
use App\Models\Rubrique;
use Illuminate\Support\Facades\DB;
use stdClass;

class RecapService {

    public $criteres;

    public function __construct(){
        $this->criteres = ['mois', 'jour', 'rapport_mensuel'];
    }

    //method for retrieving recap values of a given ligne
    public function getRecapLigne($ligne_id, $critere, $params){
        $rligne = Ligne::find($ligne_id)->first;
        
        //TODO add properties
        $rligne = new stdClass();
        $rligne->prevision = $rligne->montant;


        //get realisations, engagements, execution depending on $critere : 
        //1. up to a certain day
        $realisations = DB::table('apurements')
            ->join('engagements', 'apurements.engagement_id', '=', 'engagements.code')
            ->join('lignes', 'engagements.ligne_id', '=', 'lignes.id')
           // ->whereMonth('apurements.created_at', $params['month'])
            ->sum('apurements.montant')
            ->get();
        
            //engagements : sum of imputation - sum of apurements
        $soe_imputations = DB::table('imputations')
            ->join('engagements', 'imputations.engagement_id', '=', 'engagements.code')
            ->join('lignes', 'engagements.ligne_id', '=', 'lignes.id')
            //->whereMonth('imputation.created_at', $params['month'])
            ->sum('entagements.montant')
            ->get();

        //2. on a given month

        $rligne->realisations = $realisations;
        $rligne->engagements = $soe_imputations - $realisations;
        $rligne->execution = $soe_imputations;
        $rligne->solde = $rligne->prevision - $rligne->execution;
        $rligne->taux_execution = floor(100 * ($rligne->solde/$rligne->prevision));
        // $rligne->taux_execution = 
        // $rligne->cumul_realisations = 
        // $rligne->realisations_mois = 
        return $rligne;
    }

    //method for retrieving recap values of a given rubrique
    public function getRecapRubrique($rubrique_id, $critere, $params){
        $rrubrique = Rubrique::find($rubrique_id);
        $rrubrique->rlignes = [];

        $sumrow = new stdClass();
        $sumrow->prevision = 0;
        $sumrow->realisations = 0;
        $sumrow->engagements = 0;
        $sumrow->execution = 0;
        $sumrow->solde = 0;

        foreach($rrubrique->lignes() as $ligne){
            $recapligne = $this->getRecapLigne($ligne->id, $critere, $params);
            $sumrow->prevision += $recapligne->prevision;
            $sumrow->realisations += $recapligne->realisations;
            $sumrow->engagements += $recapligne->engagements;
            $sumrow->execution += $recapligne->execution;
            $sumrow->solde += $recapligne->solde;            
            array_push($rrubrique->rlignes, $recapligne);
        }
        $sumrow->solde = $sumrow->prevision - $sumrow->execution;
        $sumrow->taux_execution = floor(100 * ($sumrow->solde/$sumrow->prevision));
        $rrubrique->sumrow = $sumrow;
        return $rrubrique;
    }

    //method for retrieving a recap object consisting of recap properties and collections of all recap rubriques with the given name
    public function getRecapNamedRubrique($name, $critere, $params){
        $rnamedrubrique = new stdClass();
        $rubriques = Rubrique::where('label', $name)->get();
        $rnamedrubrique->rrubriques = [];
        foreach($rubriques as $rubrique){
            array_push($rnamedrubrique->rrubriques, $this->getRecapRubrique($rubrique->id, $critere, $params));
        }
        //TODO add properties
        return $rnamedrubrique;
    }


    public function getRecapChapitre($chapitre_id, $critere, $params){
        $rchapitre = Chapitre::find($chapitre_id);
        $rchapitre->rrubriques = [];
        foreach($rchapitre->rubriques as $rubrique){
            array_push($rchapitre->rrubriques, $this->getRecapRubrique($rubrique->id, $critere, $params));
        }
        //TODO add properties
        return $rchapitre;
    }

    public function getRecapTitre($titre_id, $critere, $params){
        //get names, get recap named rubrique restricted to rubriques in this titre, group all recaps in a single collection
        $names = DB::table('rubriques')
                ->join('chapitres', 'rubriques.chapitre_id', '=', 'chapitres.id')
                ->join('titres', 'chapitres.titre_id', '=', 'titres.id')
                ->select('rubriques.label')
                ->distinct()->get();
        $namedrubriques = [];
        foreach($names as $name){
            $rnamedrubrique = new stdClass();
            $rubriques = DB::table('rubriques')
            ->join('chapitres', 'rubriques.chapitre_id', '=', 'chapitres.id')
            ->join('titres', 'chapitres.titre_id', '=', 'titres.id')
            ->where('rubriques.name', '$name')
            ->where('titres.id',$titre_id)
            ->get();
            $rnamedrubrique->rrubriques = [];
            foreach($rubriques as $rubrique){
                array_push($rnamedrubrique->rrubriques, $this->getRecapRubrique($rubrique->id, $critere, $params));
            }
            //TODO add properties
            array_push($namedrubriques, $rnamedrubrique);
        }

        $rtitre = new stdClass();
        $rtitre->namedrubriques = $namedrubriques;
        //TODO add properties
        return $rtitre;
    }

    public function getRecapSousSectionFonctionnement($critere, $params){
        //get names in sous sectio fonctionnement
        $recap = new stdClass();
        $names = DB::table('rubriques')->where('sous_section','fonctionnement')->select('label')->distinct()->get();

        $recap->rrubriques = [];
        foreach($names as $name){
            array_push($recap->rrubriques, $this->getRecapNamedRubrique ($name, $critere, $params));
        }

        //TODO add properties
        return $recap;
    }

    public function getRecapSousSectionInvestissement($critere, $params){
        $recap = new stdClass();
        //get chapitres where sous section is investissement
        $chapitres_id = DB::table('chapitres')->where('sous_section','investissement')->select('id')->get();
        $recap->rchapitres = [];
        foreach($chapitres_id as $chapitre_id){
            array_push($recap->rchapitres, $this->getRecapChapitre($chapitre_id, $critere, $params));
        }

        //TODO add properties
        return $recap;
    }

    public function getRecapSectionRecettes($critere, $params){
        $recap = new stdClass();
        $chapitres_id = DB::table('chapitres')->where('section','recettes')->select('id')->get();
        $recap->rchapitres = [];
        foreach($chapitres_id as $chapitre_id){
            array_push($recap->rchapitres, $this->getRecapChapitre($chapitre_id, $critere, $params));
        }

        //TODO add properties
        return $recap;
    }

    public function getRecapGeneralFonctionnement($critere, $params){
        //combines recap sous section fonctionnement, sous section investissement and section recettes
        $recap = new stdClass();
        $recap->sections = [];
        $sectiondepenses = [];
        $ssfonctionnement = $this->getRecapSousSectionFonctionnement($critere, $params);
        $ssinvestissement = $this->getRecapSousSectionInvestissement($critere, $params);
        array_push($recap->sections, $ssfonctionnement, $ssinvestissement);
        
        //TODO add properties
        return $recap;
    }

    public function getRecapBudgetFonctionnement(){
        //consists of : recapgeneral fonctionnement, recap sous section fonctionnement, recap titre adg, recap chapitres, 
        //recap sous section investissement, recap chapitres investissement, recap section recettes, recap chapitres recette
    }

    public function getRecapGeneralMandat(){

    }

    public function getRecapBudgetMandat(){

    }
}
