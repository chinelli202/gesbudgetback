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
        $this->criteres = ['mois', 'jour', 'rapport_mensuel', 'intervalle'];
    }

    //method for retrieving recap values of a given ligne
    public function getRecapLigne($ligne_id, $critere, $params){
        $rligne = Ligne::find($ligne_id);
        
        $recap = new stdClass();
        $recap->libelle = $rligne->label;
        $recap->prevision = 0;
        $recap->realisations = 0;
        $recap->realisationsMois = 0;
        $recap->realisationsMoisPrecedents = 0;
        $recap->engagements = 0;
        $recap->execution = 0;
        $recap->solde = 0;
        $recap->tauxExecution = 0;
        if($critere=='mois'){
            $recap->mois = date("F", mktime(0, 0, 0, $params->mois, 10));
        }
        
        if($rligne->montant>0){

            if($critere == 'jour'){
                $recap->prevision = $rligne->montant;
                $jour = $params->jour;
                //compute month id
                $time = strtotime($jour);

                $monthindex = date("n", $time);
                //get realisations, engagements, execution depending on $critere : 
                //1. up to a certain day
                $realisations = DB::table('apurements')
                    ->join('engagements', 'apurements.engagement_id', '=', 'engagements.code')
                    ->join('lignes', 'engagements.ligne_id', '=', 'lignes.id')
                    ->select('apurements.montant_ttc','apurements.created_at')
                    ->whereDate('apurements.created_at', '<',$jour)
                    ->where('lignes.id',$ligne_id)
                   // ->whereMonth('apurements.created_at', $params['month'])
                    ->sum('apurements.montant_ttc'); 
        
                $realisationsMois = DB::table('apurements')
                    ->join('engagements', 'apurements.engagement_id', '=', 'engagements.code')
                    ->join('lignes', 'engagements.ligne_id', '=', 'lignes.id')
                    ->select('apurements.montant_ttc','apurements.created_at')
                    ->where('lignes.id',$ligne_id)
                    ->whereMonth('apurements.created_at',$monthindex)
                   // ->whereMonth('apurements.created_at', $params['month'])
                    ->sum('apurements.montant_ttc');
        
                $realisationsMoisPrecedents = DB::table('apurements')
                    ->join('engagements', 'apurements.engagement_id', '=', 'engagements.code')
                    ->join('lignes', 'engagements.ligne_id', '=', 'lignes.id')
                    ->select('apurements.montant_ttc','apurements.created_at')
                    ->where('lignes.id',$ligne_id)
                    ->whereMonth('apurements.created_at','<',$monthindex)
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
                    ->whereDate('engagements.created_at','<',$jour)
                    ->sum('engagements.montant_ttc');
                //2. on a given month
                $recap->realisationsMois = $realisationsMois;
                $recap->realisationsMoisPrecedents = $realisationsMoisPrecedents;
                $recap->realisations = $recap->realisationsMoisPrecedents + $realisationsMois;
                $recap->engagements = $soe_imputations - $realisations;
                $recap->execution = $soe_imputations;
                $recap->solde = $recap->prevision - $recap->execution;
                $recap->tauxExecution = floor(100 * ($recap->execution/$recap->prevision));
            }
            else if($critere == 'mois'){

                $recap->prevision = $rligne->montant;
                //get realisations, engagements, execution depending on $critere : 
                //1. up to a certain day
                $realisations = DB::table('apurements')
                    ->join('engagements', 'apurements.engagement_id', '=', 'engagements.code')
                    ->join('lignes', 'engagements.ligne_id', '=', 'lignes.id')
                    ->select('apurements.montant_ttc','apurements.created_at')
                    ->where('lignes.id',$ligne_id)
                    ->whereMonth('apurements.created_at',$params->mois)
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
                    ->whereMonth('engagements.created_at',$params->mois)
                    ->sum('engagements.montant_ttc');
                //2. on a given month
                $recap->realisations = $realisations;
                $recap->engagements = $soe_imputations;//$soe_imputations - $realisations;
                $recap->execution = $recap->engagements + $recap->realisations;
                //$recap->solde = $recap->prevision - $recap->execution;
                $recap->tauxExecution = floor(100 * ($recap->execution/$recap->prevision));
            }
            else if($critere == 'rapport_mensuel'){
                $monthName = date("F", mktime(0, 0, 0, $params->mois, 10));
                $time = strtotime("last day of ".$monthName);
                $day = date("Y-m-d", $time);
                $parameters = new stdClass();
                $parameters->jour = $day;
                return $this->getRecapLigne($ligne_id, 'jour',  $parameters);
            }
        }

        return $recap;
    }

    //method for retrieving recap values of a given rubrique
    public function getRecapRubrique($rubrique_id, $critere, $params){                  
        $rrubrique = Rubrique::find($rubrique_id);
        $rrubrique->chapitrelabel = $rrubrique->chapitre->label;
        $collection = [];
        $sumrow = new stdClass();
        $recap = new stdClass();
        $recap->prevision = 0;
        $recap->libelle = $rrubrique->label;
        $recap->chapitre = $rrubrique->chapitre->label;
        $recap->realisations = 0;
        $recap->realisationsMois = 0;
        $recap->realisationsMoisPrecedents = 0;
        $recap->engagements = 0;
        $recap->execution = 0;
        $recap->solde = 0;
        if($critere=='mois'){
            $recap->mois = date("F", mktime(0, 0, 0, $params->mois, 10));
        }

        foreach($rrubrique->lignes as $ligne){
            $recapligne = $this->getRecapLigne($ligne->id, $critere, $params);
            $recap->prevision += $recapligne->prevision;
            $recap->realisations += $recapligne->realisations;
            $recap->realisationsMois += $recapligne->realisationsMois;
            $recap->realisationsMoisPrecedents += $recapligne->realisationsMoisPrecedents;
            $recap->engagements += $recapligne->engagements;
            $recap->execution += $recapligne->execution;
            $recap->solde += $recapligne->solde;            
            array_push($collection, $recapligne);
        }
        $recap->solde = $recap->prevision - $recap->execution;
        
        //if($critere!='mois')
        $recap->tauxExecution = $recap->prevision != 0 ? floor(100 * ($recap->execution/$recap->prevision)) : 0;
        
        // $rrubrique->collection = $collection;
        // $rrubrique->sumrow = $sumrow;

        
        $recap->collection = $collection;        
        //$recap->sumrow = $sumrow;

        //echo "added a new recap for rubrique ".$sumrow->libelle." of  ".$sumrow->chapitre." with parameters : ";
        Log::info( "added a new recap for rubrique ".$recap->libelle." of  ".$recap->chapitre." with parameters : ");
        Log::info( "prevision : ".$recap->prevision);
        Log::info( "realisations : ".$recap->realisations);
        Log::info( "realisationsMois : ".$recap->realisationsMois);
        Log::info( "realisationsMoisPrecedents : ".$recap->realisationsMoisPrecedents);
        Log::info( "engagements : ".$recap->engagements);
        Log::info( "execution : ".$recap->execution);
        Log::info( "solde : ".$recap->solde);
        Log::info( "tauxExecution : ".$recap->tauxExecution);
        Log::info( "prevision : ".$recap->prevision);

        $header = new stdClass();
        $header->name = $rrubrique->label;
        $header->labelLabel = "Lignes";
        $header->previsionsLabel = "Prévisions";
        $header->realisationsLabel = "Réalisations";
        $header->realisationsMoisPrecedentsLabel = "Réalisations mois précédents";
        $header->realisationsMoisLabel = "Réalisations cumulées";
        $header->executionLabel = "Exécution";
        $header->soldeLabel = "Solde";
        $header->tauxExecutionLabel = "Taux d'exécution";
        $recap->header = $header;
        return $recap;
    }

    //method for retrieving a recap object consisting of recap properties and collections of all recap rubriques with the given name
    public function getRecapRubriqueGroup($name, $critere, $params){
        $rubriques = Rubrique::where('label', $name)->get();
        $collection = [];
        $sumrow = new stdClass();
        $recap = new stdClass();
        
        $recap->libelle = $name;
        $recap->prevision = 0;
        $recap->realisations = 0;
        $recap->realisationsMois = 0;
        $recap->realisationsMoisPrecedents = 0;
        $recap->engagements = 0;
        $recap->execution = 0;
        $recap->solde = 0;
        if($critere=='mois'){
            $recap->mois = date("F", mktime(0, 0, 0, $params->mois, 10));
        }
        
        foreach($rubriques as $rubrique){
            $recaprubrique = $this->getRecapRubrique($rubrique->id, $critere, $params);
            $recap->prevision += $recaprubrique->prevision;
            $recap->realisations += $recaprubrique->realisations;
            $recap->realisationsMois += $recaprubrique->realisationsMois;
            $recap->realisationsMoisPrecedents += $recaprubrique->realisationsMoisPrecedents;
            $recap->engagements += $recaprubrique->engagements;
            $recap->execution += $recaprubrique->execution;
            $recap->solde += $recaprubrique->solde;            
            array_push($collection, $recaprubrique);
        }
        //TODO add properties
        $recap->tauxExecution = floor(100 * ($recap->execution/$recap->prevision));
        
       
        $recap->collection = $collection;        
        //$recap->sumrow = $sumrow;
        
        Log::info( "added a new recap for rubrique group".$name);
        Log::info( "prevision : ".$recap->prevision);
        Log::info( "realisations : ".$recap->realisations);
        Log::info( "realisationsMois : ".$recap->realisationsMois);
        Log::info( "realisationsMoisPrecedents : ".$recap->realisationsMoisPrecedents);
        Log::info( "engagements : ".$recap->engagements);
        Log::info( "execution : ".$recap->execution);
        Log::info( "solde : ".$recap->solde);
        Log::info( "tauxExecution : ".$recap->tauxExecution);
        Log::info( "prevision : ".$recap->prevision);

        $header = new stdClass();
        $header->name = $name;
        $header->labelLabel = "Divisions / Directions";
        $header->previsionsLabel = "Prévisions";
        $header->realisationsLabel = "Réalisations";
        $header->realisationsMoisPrecedentsLabel = "Réalisations mois précédents";
        $header->realisationsMoisLabel = "Réalisations cumulées";
        $header->executionLabel = "Exécution";
        $header->soldeLabel = "Solde";
        $header->tauxExecutionLabel = "Taux d'exécution";
        $recap->header = $header;
        return $recap;
    }

    public function getRecapChapitre($chapitre_id, $critere, $params){
        $rchapitre = Chapitre::find($chapitre_id);
        //$rchapitre->rrubriques = [];
        
        $collection = [];
        $sumrow = new stdClass();
        $header = new stdClass();
        $recap = new stdClass();
        $recap->prevision = 0;
        $recap->libelle = $rchapitre->label;
        $recap->realisations = 0;
        $recap->realisationsMois = 0;
        $recap->realisationsMoisPrecedents = 0;
        $recap->engagements = 0;
        $recap->execution = 0;
        $recap->solde = 0;
        if($critere=='mois'){
            $recap->mois = date("F", mktime(0, 0, 0, $params->mois, 10));
        }

        foreach($rchapitre->rubriques as $rubrique){
            $rrubrique = $this->getRecapRubrique($rubrique->id, $critere, $params);
            $recap->prevision += $rrubrique->prevision;
            $recap->realisations += $rrubrique->realisations;
            $recap->realisationsMois += $rrubrique->realisationsMois;
            $recap->realisationsMoisPrecedents += $rrubrique->realisationsMoisPrecedents;
            $recap->engagements += $rrubrique->engagements;
            $recap->execution += $rrubrique->execution;
            $recap->solde += $rrubrique->solde;            
            array_push($collection, $rrubrique);
        }
        //TODO add properties
        $recap->tauxExecution = floor(100 * ($recap->execution/$recap->prevision));
        
        
        $recap->collection = $collection;        
        //$recap->sumrow = $sumrow;

        
        $header->name = $rchapitre->label;
        $header->labelLabel = "Rubriques";
        $header->previsionsLabel = "Prévisions";
        $header->realisationsLabel = "Réalisations";
        $header->realisationsMoisPrecedentsLabel = "Réalisations mois précédents";
        $header->realisationsMoisLabel = "Réalisations cumulées";
        $header->executionLabel = "Exécution";
        $header->soldeLabel = "Solde";
        $header->tauxExecutionLabel = "Taux d'exécution";
        $recap->header = $header;
        
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
        $recap = new stdClass();
        $recap->prevision = 0;
        $recap->libelle = "Fonctionnement";
        $recap->realisations = 0;
        $recap->realisationsMois = 0;
        $recap->realisationsMoisPrecedents = 0;
        $recap->engagements = 0;
        $recap->execution = 0;
        $recap->solde = 0;
        
        foreach($names as $name){
            Log::info( "processing rubrique in fonctionnement named : ".$name->label);
            $recaprubriquegroup = $this->getRecapRubriqueGroup($name->label, $critere, $params);
            $recap->prevision += $recaprubriquegroup->prevision;
            $recap->realisations += $recaprubriquegroup->realisations;
            $recap->realisationsMois += $recaprubriquegroup->realisationsMois;
            $sumrow->realisationsMoisPrecedents += $recaprubriquegroup->realisationsMoisPrecedents;
            $sumrow->engagements += $recaprubriquegroup->engagements;
            $sumrow->execution += $recaprubriquegroup->execution;
            $sumrow->solde += $recaprubriquegroup->solde;            
            array_push($collection, $recaprubriquegroup);
        }

        $sumrow->tauxExecution = floor(100 * ($sumrow->execution/$sumrow->prevision));
        
        //$recap->sumrow = $sumrow;
        $recap->collection = $collection;

        Log::info( "added a new recap for sous section fonctionnement");
        Log::info( "prevision : ".$recap->prevision);
        Log::info( "realisations : ".$recap->realisations);
        Log::info( "realisationsMois : ".$recap->realisationsMois);
        Log::info( "realisationsMoisPrecedents : ".$recap->realisationsMoisPrecedents);
        Log::info( "engagements : ".$recap->engagements);
        Log::info( "execution : ".$recap->execution);
        Log::info( "solde : ".$recap->solde);
        Log::info( "tauxExecution : ".$recap->tauxExecution);
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

    public function getTree($domaine_p, $section_p){
        //load all chapitres of fonctionnement.
        //for each chapitre, load rubriques
        //for each rubrique, load lignes.
        $section = new stdClass();
        $chapitresdepenses = Chapitre::where('domaine',$domaine_p)
                    ->where('section',$section_p)->get();
        $chapitres = [];
        foreach($chapitresdepenses as $chap){
            $newchap = new stdClass();
            $newchap->label = $chap->label;
            $newchap->id = $chap->id;
            $rubriquesnewchap = $chap->rubriques;//Rubrique::where('chapitre_id',$chap->id);
            $rubriques = [];
            //loop through rubs and build rubriques
            foreach($rubriquesnewchap as $rub){
                $newrub = new stdClass();
                $newrub->label = $rub->label;
                $newrub->id = $rub->id;
                $lignesnewrub = $rub->lignes;//Ligne::where('rubrique_id',$rub->id);
                $lignes = [];
                foreach($lignesnewrub as $li){
                    $newli = new stdClass();
                    $newli->label = $li->label;
                    $newli->id =  $li->id;
                    array_push($lignes, $newli);
                }
                $newrub->lignes = $lignes;
                array_push($rubriques, $newrub);
            }
            //add rubriques array to newchap
            $newchap->rubriques = $rubriques;
            array_push($chapitres, $newchap);
        }
        $section->section = $section_p;
        $section->chapitres = $chapitres;
        return $section;
    }

    public function getRecapGeneralMandat(){

    }

    public function getRecapBudgetMandat(){

    }

    private function computeCollections($collection, $field, $method, $critere, $params){

    }
}