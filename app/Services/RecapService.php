<?php

namespace App\Services;

use App\Models\Chapitre;
use App\Models\Ligne;
use App\Models\Rubrique;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use stdClass;

class RecapService {

    public $criteres;

    public function __construct(){
        $this->criteres = ['mois', 'jour', 'rapport_mensuel'];
    }

    //method for retrieving recap values of a given ligne
    public function getRecapLigne($ligne_id, $critere, $params){
        $rligne = Ligne::find($ligne_id);
        
        $recap = new stdClass();
        $recap->prevision = 0;
        $recap->realisations = 0;
        $recap->realisationsMois = 0;
        $recap->realisationsMoisPrecedents = 0;
        $recap->engagements = 0;
        $recap->execution = 0;
        $recap->solde = 0;
        $recap->tauxExecution = 0;
        
        if($rligne->montant>0){

            $recap->prevision = $rligne->montant;
    
            //get realisations, engagements, execution depending on $critere : 
            //1. up to a certain day
            $realisations = DB::table('apurements')
                ->join('engagements', 'apurements.engagement_id', '=', 'engagements.code')
                ->join('lignes', 'engagements.ligne_id', '=', 'lignes.id')
                ->select('apurements.montant_ttc','apurements.created_at')
                ->whereDate('apurements.created_at', '<','2020-06-30')
                ->where('lignes.id',$ligne_id)
               // ->whereMonth('apurements.created_at', $params['month'])
                ->sum('apurements.montant_ttc'); 
    
            $realisationsMois = DB::table('apurements')
                ->join('engagements', 'apurements.engagement_id', '=', 'engagements.code')
                ->join('lignes', 'engagements.ligne_id', '=', 'lignes.id')
                ->select('apurements.montant_ttc','apurements.created_at')
                ->where('lignes.id',$ligne_id)
                ->whereMonth('apurements.created_at',6)
               // ->whereMonth('apurements.created_at', $params['month'])
                ->sum('apurements.montant_ttc');
    
            $realisationsMoisPrecedents = DB::table('apurements')
                ->join('engagements', 'apurements.engagement_id', '=', 'engagements.code')
                ->join('lignes', 'engagements.ligne_id', '=', 'lignes.id')
                ->select('apurements.montant_ttc','apurements.created_at')
                ->where('lignes.id',$ligne_id)
                ->whereMonth('apurements.created_at','<',6)
               // ->whereMonth('apurements.created_at', $params['month'])
                ->sum('apurements.montant_ttc');
            
                //engagements : sum of imputation - sum of apurements
            //$soe_imputations = DB::table('imputations')
            $soe_imputations = DB::table('engagements')
                //->join('engagements', 'imputations.engagement_id', '=', 'engagements.code')
                ->join('lignes', 'engagements.ligne_id', '=', 'lignes.id')
                //->whereMonth('imputation.created_at', $params['month'])
                ->select('engagements.montant_ttc','engagements.created_at')
                ->where('lignes.id',$ligne_id)
                ->whereDate('engagements.created_at','<','2020-06-30')
                ->sum('engagements.montant_ttc');
    
            //2. on a given month
            $recap->realisationsMois = $realisationsMois;
            $recap->realisationsMoisPrecedents = $realisationsMoisPrecedents;
            $recap->realisations = $recap->realisationsMoisPrecedents + $realisationsMois;
            
            
            $recap->engagements = $soe_imputations - $realisations;
            $recap->execution = $soe_imputations;
            $recap->solde = $recap->prevision - $recap->execution;
            $recap->tauxExecution = floor(100 * ($recap->solde/$recap->prevision));
        }
        
        return $recap;
    }

    //method for retrieving recap values of a given rubrique
    public function getRecapRubrique($rubrique_id, $critere, $params){
        $rrubrique = Rubrique::find($rubrique_id);
        $rrubrique->chapitrelabel = $rrubrique->chapitre->label;
        $collection = [];
        $sumrow = new stdClass();
        $sumrow->prevision = 0;
        $sumrow->libelle = $rrubrique->label;
        $sumrow->chapitre = $rrubrique->chapitre->label;
        $sumrow->realisations = 0;
        $sumrow->realisationsMois = 0;
        $sumrow->realisationsMoisPrecedents = 0;
        $sumrow->engagements = 0;
        $sumrow->execution = 0;
        $sumrow->solde = 0;

        foreach($rrubrique->lignes as $ligne){
            $recapligne = $this->getRecapLigne($ligne->id, $critere, $params);
            $sumrow->prevision += $recapligne->prevision;
            $sumrow->realisations += $recapligne->realisations;
            $sumrow->realisationsMois += $recapligne->realisationsMois;
            $sumrow->realisationsMoisPrecedents += $recapligne->realisationsMoisPrecedents;
            $sumrow->engagements += $recapligne->engagements;
            $sumrow->execution += $recapligne->execution;
            $sumrow->solde += $recapligne->solde;            
            array_push($collection, $recapligne);
        }
        $sumrow->solde = $sumrow->prevision - $sumrow->execution;
        
        $sumrow->tauxExecution = $sumrow->prevision != 0 ? floor(100 * ($sumrow->solde/$sumrow->prevision)) : 0;
        
        // $rrubrique->collection = $collection;
        // $rrubrique->sumrow = $sumrow;

        $recap = new stdClass();
        $recap->collection = $collection;        
        $recap->sumrow = $sumrow;

        //echo "added a new recap for rubrique ".$sumrow->libelle." of  ".$sumrow->chapitre." with parameters : ";
        Log::info( "added a new recap for rubrique ".$sumrow->libelle." of  ".$sumrow->chapitre." with parameters : ");
        Log::info( "prevision : ".$recap->sumrow->prevision);
        Log::info( "realisations : ".$recap->sumrow->realisations);
        Log::info( "realisationsMois : ".$recap->sumrow->realisationsMois);
        Log::info( "realisationsMoisPrecedents : ".$recap->sumrow->realisationsMoisPrecedents);
        Log::info( "engagements : ".$recap->sumrow->engagements);
        Log::info( "execution : ".$recap->sumrow->execution);
        Log::info( "solde : ".$recap->sumrow->solde);
        Log::info( "tauxExecution : ".$recap->sumrow->tauxExecution);
        Log::info( "prevision : ".$recap->sumrow->prevision);
        return $recap;
    }

    //method for retrieving a recap object consisting of recap properties and collections of all recap rubriques with the given name
    public function getRecapRubriqueGroup($name, $critere, $params){
        $rubriques = Rubrique::where('label', $name)->get();
        $collection = [];
        $sumrow = new stdClass();
        $sumrow->libelle = $name;
        $sumrow->prevision = 0;
        $sumrow->realisations = 0;
        $sumrow->realisationsMois = 0;
        $sumrow->realisationsMoisPrecedents = 0;
        $sumrow->engagements = 0;
        $sumrow->execution = 0;
        $sumrow->solde = 0;
        
        foreach($rubriques as $rubrique){
            $recaprubrique = $this->getRecapRubrique($rubrique->id, $critere, $params);
            $sumrow->prevision += $recaprubrique->sumrow->prevision;
            $sumrow->realisations += $recaprubrique->sumrow->realisations;
            $sumrow->realisationsMois += $recaprubrique->sumrow->realisationsMois;
            $sumrow->realisationsMoisPrecedents += $recaprubrique->sumrow->realisationsMoisPrecedents;
            $sumrow->engagements += $recaprubrique->sumrow->engagements;
            $sumrow->execution += $recaprubrique->sumrow->execution;
            $sumrow->solde += $recaprubrique->sumrow->solde;            
            array_push($collection, $recaprubrique);
        }
        //TODO add properties
        $sumrow->tauxExecution = floor(100 * ($sumrow->solde/$sumrow->prevision));
        
        $recap = new stdClass();
        $recap->collection = $collection;        
        $recap->sumrow = $sumrow;
        
        Log::info( "added a new recap for rubrique group".$name);
        Log::info( "prevision : ".$recap->sumrow->prevision);
        Log::info( "realisations : ".$recap->sumrow->realisations);
        Log::info( "realisationsMois : ".$recap->sumrow->realisationsMois);
        Log::info( "realisationsMoisPrecedents : ".$recap->sumrow->realisationsMoisPrecedents);
        Log::info( "engagements : ".$recap->sumrow->engagements);
        Log::info( "execution : ".$recap->sumrow->execution);
        Log::info( "solde : ".$recap->sumrow->solde);
        Log::info( "tauxExecution : ".$recap->sumrow->tauxExecution);
        Log::info( "prevision : ".$recap->sumrow->prevision);
        return $recap;
    }

    public function getRecapChapitre($chapitre_id, $critere, $params){
        $rchapitre = Chapitre::find($chapitre_id);
        //$rchapitre->rrubriques = [];
        
        $collection = [];
        $sumrow = new stdClass();
        $sumrow->prevision = 0;
        $sumrow->libelle = $rchapitre->label;
        $sumrow->realisations = 0;
        $sumrow->realisationsMois = 0;
        $sumrow->realisationsMoisPrecedents = 0;
        $sumrow->engagements = 0;
        $sumrow->execution = 0;
        $sumrow->solde = 0;

        foreach($rchapitre->rubriques as $rubrique){
            $rrubrique = $this->getRecapRubrique($rubrique->id, $critere, $params);
            $sumrow->prevision += $rrubrique->sumrow->prevision;
            $sumrow->realisations += $rrubrique->sumrow->realisations;
            $sumrow->realisationsMois += $rrubrique->sumrow->realisationsMois;
            $sumrow->realisationsMoisPrecedents += $rrubrique->sumrow->realisationsMoisPrecedents;
            $sumrow->engagements += $rrubrique->sumrow->engagements;
            $sumrow->execution += $rrubrique->sumrow->execution;
            $sumrow->solde += $rrubrique->sumrow->solde;            
            array_push($collection, $rrubrique);
        }
        //TODO add properties
        $sumrow->tauxExecution = floor(100 * ($sumrow->solde/$sumrow->prevision));
        
        $recap = new stdClass();
        $recap->collection = $collection;        
        $recap->sumrow = $sumrow;
        return $recap;
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
        $recapsoussection = new stdClass();
        $names = DB::table('rubriques')->select('rubriques.label')->where('sous_section','Fonctionnement')->distinct()->get();

        $collection = [];
        $sumrow = new stdClass();
        $sumrow->prevision = 0;
        $sumrow->libelle = "Fonctionnement";
        $sumrow->realisations = 0;
        $sumrow->realisationsMois = 0;
        $sumrow->realisationsMoisPrecedents = 0;
        $sumrow->engagements = 0;
        $sumrow->execution = 0;
        $sumrow->solde = 0;
        
        foreach($names as $name){
            Log::info( "processing rubrique in fonctionnement named : ".$name->label);
            $recaprubriquegroup = $this->getRecapRubriqueGroup($name->label, $critere, $params);
            $sumrow->prevision += $recaprubriquegroup->sumrow->prevision;
            $sumrow->realisations += $recaprubriquegroup->sumrow->realisations;
            $sumrow->realisationsMois += $recaprubriquegroup->sumrow->realisationsMois;
            $sumrow->realisationsMoisPrecedents += $recaprubriquegroup->sumrow->realisationsMoisPrecedents;
            $sumrow->engagements += $recaprubriquegroup->sumrow->engagements;
            $sumrow->execution += $recaprubriquegroup->sumrow->execution;
            $sumrow->solde += $recaprubriquegroup->sumrow->solde;            
            array_push($collection, $recaprubriquegroup);
        }

        $sumrow->tauxExecution = floor(100 * ($sumrow->solde/$sumrow->prevision));
        $recap = new stdClass();
        $recap->sumrow = $sumrow;
        $recap->collection = $collection;

        Log::info( "added a new recap for sous section fonctionnement");
        Log::info( "prevision : ".$recap->sumrow->prevision);
        Log::info( "realisations : ".$recap->sumrow->realisations);
        Log::info( "realisationsMois : ".$recap->sumrow->realisationsMois);
        Log::info( "realisationsMoisPrecedents : ".$recap->sumrow->realisationsMoisPrecedents);
        Log::info( "engagements : ".$recap->sumrow->engagements);
        Log::info( "execution : ".$recap->sumrow->execution);
        Log::info( "solde : ".$recap->sumrow->solde);
        Log::info( "tauxExecution : ".$recap->sumrow->tauxExecution);
        Log::info( "nombre de sections trouvees : ".count($recap->collection));
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

    private function computeCollections($collection, $field, $method, $critere, $params){

    }
}