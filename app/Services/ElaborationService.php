<?php

namespace App\Services;

use App\Models\Apurement;
use App\Models\Chapitre;
use App\Models\DraftTitre;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\Maquette;
use App\Models\Ligne;
use App\Models\LigneArchivee;
use App\Models\Rubrique;
use App\Models\Titre;
use Carbon\Traits\Timestamp;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class ElaborationService{

    // public function uploadFile(){
        
    // }


    public function loadMaquette($id){
        //get budget file from fs location
        // load content into variable
        // run loading script
        //update progress variable while doing that 

        //make sure the maquette file exists at the excpected location. otherwise return false
        //$exists = Storage::exists('public/files/'.$filename);
        $dbmaquette = Maquette::find($id);
        $name = $dbmaquette->name;
        $exists = Storage::exists('public/files/'.$name);

        if(!$exists){
            echo "file does not exist";
            //return false;
        }
        else{
            echo "file found!!";
            //delete existing drafts at this point
            // $titres = Titre::all();
            // if(count($titres) > 0){
            //     //update loader
            //     $dbmaquette->step= "Suppression de la maquette existante";
            //     $dbmaquette->status = "initiated";
            //     $dbmaquette->save();
            //     $titre_delete_rate = 1 / count($titres);
            //     foreach($titres as $titre){
            //         $chapitre_delete_rate = $titre_delete_rate/count($titres->chapitres);
            //         foreach($titre->chapitres as $chapitre){
            //             $chapitre->delete();
            //             $dbmaquette->loadprogress += ($chapitre_delete_rate * 100);
            //             $dbmaquette->status = "ongoing";
            //             $dbmaquette->save();
            //         }
            //         $titre->delete();
            //     }
            // }

            $this->process_maquette_file($name);
            return 'finished';
        }
    }

    public function load_external_maquette($name, $year){
        $exists = Storage::exists('public/files/'.$name);
        if(!$exists){
            echo "file does not exist";
            //return false;
        }
        else{
            echo "file found!!";
            $exercice = $this->getExercice($year);
            $this->process_external_maquette($name, $exercice);
            return 'finished';
        }
    }

    public function deleteMaquetteArchive($year){

    }

    public function getExercice($year){
        $exercice = ExerciceBudgetaire::where("annee_vote",$year-1)->get();
        if(empty($exercice)){
            //if there is no excercice set, create a new exercice with year being the year 1900, then save it
            echo "no exercice found. creating a new one";
            Log::info('no exercice found. creating a new one');
            $budget = new ExerciceBudgetaire;
            $vote_day = mktime(11, 14, 54, 8, 30, $year);
            $start_day = mktime(11, 14, 54, 0, 1, $year);
            $end_day = mktime(11, 14, 54, 8, 12, 2014);
            $budget->annee_vote = $year-1;
            $budget->date_vote = date("Y-m-d",$vote_day);
            $budget->date_debut = date('Y-m-d', $start_day);
            $budget->date_cloture = date('Y-m-d', $end_day);
            Log::info('saving : '.$budget->annee_vote.', '.$budget->date_vote.', '.$budget->date_debut.', '.$budget->date_cloture);
            $budget->save();
            return $budget;
        }
        else{
            return $exercice;       
        }
    }

    //create new exercice
    public function save($request){
        $budget = $this->getRunningExercice();

        //somehow, make sure running exercice has some lines attached to it. when that's the case, create new Exercice with year value being that of current exercice + 1.
        $ligne = Ligne::where('id_exercice_budgetaire',$budget->id);
        if(isset($ligne)){
            $budget = new ExerciceBudgetaire();
        }

        //set budget params
        if(isset($request->annee_vote)){
            $budget->annee_vote = $request->annee_vote;
        }
        if(isset($request->date_debut)){
            $budget->date_debut = $request->date_debut;
        }
        if(isset($request->date_cloture)){
            $budget->date_cloture = $request->date_cloture;
        }
        if(isset($request->description)){
            $budget->date_cloture = $request->date_cloture;
        }
        $budget->save();
        return $budget;
    }

    public function getRunningExercice(){
        $exercice = ExerciceBudgetaire::first(); //replace this with firstOrCreate()
        if(empty($exercice)){
            //if there is no excercice set, create a new exercice with year being the year 1900, then save it
            echo "no exercice found. creating a new one";
            Log::info('no exercice found. creating a new one');
            $budget = new ExerciceBudgetaire;
            $budget-> annee_vote = 1950;//date('Y');
            $budget-> date_vote = date("Y-m-d");
            $budget-> date_debut = date('Y-m-d', strtotime('first day of january next year'));
            $budget-> date_cloture = date('Y-m-d', strtotime('last day of december next year'));
            Log::info('saving : '.$budget->annee_vote.', '.$budget->date_vote.', '.$budget->date_debut.', '.$budget->date_cloture);
            $budget->save();
            return $budget;
        }
        else{
            return $exercice;       
        }
    }


    //update existing exercice
    public function update($request, $id){
        return 'not implemented yet';
    }

    public function getLoadProgress($name){

        $file = Maquette::where('name',$name)->first();
        if(isset($file)){
            return $file;
        }
        else{
            return 'no file matching the given maquettes name';
        }
    }

    private function process_maquette_file($dbmaquette){
        Log::info('processing maquette file');

        //$service = new DraftBudgetService;
        $budget = $this->getRunningExercice();
        
        //$file = Maquette::where('name',$name)->first();
        //$file_ref = Storage::get('public\files\\'.$name);
        
        //echo $file_ref;
        $path = Storage::path('public\files\\'.$dbmaquette->name);
        $titres = include $path;
        echo ('path found : '.$path);
        echo count($titres);
        
        // if(!isset($titres)||!isset($file)){
        //     return "couldn't find file";
        // }
        //$titres = include 'maquette.php';
        $dbmaquette->step="Processing Maquette";
        $dbmaquette->loadprogress = 0;
        $dbmaquette->save();
        $titre_progress_rate = 1/count($titres);
        
        for($k = 0; $k < count($titres); $k++){
            //loop testing
            $titre = $titres[$k];
            echo "the heck";
            echo "\n";
            echo $titre['label'];
            echo "\n";
            //persist titre
            $titreEntry = new Titre();
            $titreEntry->numero = $titre['numero'];
            $titreEntry->label = $titre['label'];
            $titreEntry->description = $titre['description'];
            $titreEntry->domaine = $titre['domaine'];
            $titreEntry->section = $titre['section'];
            $titreEntry->save();
            echo "saved titre : ".$titreEntry->label;
            Log::info('saved titre : '.$titreEntry->label);
            echo "\n";
            $chapitres = $titre['chapitres'];
            $chapitre_progress_rate = $titre_progress_rate/count($chapitres);
            for ($i = 0; $i < count($chapitres); $i++){
                $chapitre = $chapitres[$i];

                $chapitreEntry = new Chapitre;
                $chapitreEntry->numero = $chapitre['numero'];
                $chapitreEntry->label = $chapitre['label'];
                $chapitreEntry->description = $chapitre['description'];
                $chapitreEntry->domaine = $chapitre['domaine'];
                $chapitreEntry->section = $chapitre['section'];
                if(isset($chapitre['sous_section'])){
                    $chapitreEntry->sous_section = $chapitre['sous_section'];
                }
                $titreEntry -> chapitres() -> save($chapitreEntry);
                echo "saved chapitre : ".$chapitreEntry->label;
                
                echo "\n";
                $rubriques = $chapitre['rubriques'];
                for($j = 0; $j < count($rubriques); $j++){
                    $rubrique = $rubriques[$j];
                    $rubriqueEntry = new Rubrique;
                    $rubriqueEntry->numero = $rubrique['numero'];
                    $rubriqueEntry->label = $rubrique['label'];
                    $rubriqueEntry->description = $rubrique['description'];
                    $rubriqueEntry->domaine = $rubrique['domaine'];
                    $rubriqueEntry->section = $rubrique['section'];
                    if(isset($rubrique['sous_section'])){
                        $rubriqueEntry->sous_section = $rubrique['sous_section'];
                    }
                    $chapitreEntry -> rubriques() -> save($rubriqueEntry);
                    echo "saved rubrique : ".$rubriqueEntry->label;
                    Log::info('saved rubrique : '.$rubriqueEntry->label);    
                    echo "\n";
                    $lignes = $rubrique['lignes'];
                    for($m = 0; $m < count($lignes); $m++){
                        $ligne = $lignes[$m];
                        $ligneEntry = new Ligne;
                        //truncating label length to 100 characters if found longer
                        if(strlen($ligne['label']) >= 100){
                            $ligne['label'] = substr($ligne['label'],0,100);
                        }
                        $ligneEntry->label = $ligne['label'];
                        $ligneEntry->description = $ligne['description'];
                        $ligneEntry->montant = str_replace(" ","", $ligne['montant']);//;$ligne['montant'];
                        $ligneEntry->domaine = $ligne['domaine'];
                        $ligneEntry->section = $ligne['section'];
                        if(isset($ligne['sous_section'])){
                            $ligneEntry->sous_section = $ligne['sous_section'];
                        }
                        $ligneEntry->exercice_budgetaire_id = $budget->id;

                        $rubriqueEntry -> lignes() -> save($ligneEntry);
                        echo "saved ligne : ".$ligneEntry->label;
                        //Log::channel('syslog')->info('saved ligne : '.$ligneEntry->label);
                        //Log::info('saved ligne : '.$ligneEntry->label);

                        Log::stack(['single', 'syslog'])->info('saved ligne'.$ligneEntry->label);
                        echo "\n";
                    }
                }   
                //update progress on chapitres
                $file->loadprogress += ($chapitre_progress_rate * 100);
                $file->save();
                echo "file processing now at ".$file->loadprogress."%";
                Log::info("file processing now at ".$file->loadprogress."%");
            }
            //update progress for this titre
        }
    }


    // basically here, we don't add new titre, chapitres or rubriques.
    // we just match those in the maquette with those in the database. 
    // for now, if there is no match, then we just continue.
    private function process_external_maquette($maquette_name){
        Log::info('processing maquette file');

        //$service = new DraftBudgetService;
        $budget = $this->getRunningExercice();
        
        //$file = Maquette::where('name',$name)->first();
        //$file_ref = Storage::get('public\files\\'.$name);
        
        //echo $file_ref;
        $file = Storage::path('public\files\\'.$maquette_name);
        $titres = include $file;
        echo ('path found : '.$file);
        echo "\n";
        echo count($titres);
        echo "\n";
        // if(!isset($titres)||!isset($file)){
        //     return "couldn't find file";
        // }
        //$titres = include 'maquette.php';
        
        
        for($k = 0; $k < count($titres); $k++){
            //loop testing
            $titre = $titres[$k];

            //matching titre
            $titredb = Titre::where("label", $titre["label"]);
            if(empty($titredb)){
                Log::info('no titre match for : '.$titre["label"]);
                echo 'no titre match for : '.$titre["label"];
            }
            else{
                Log::info('into titre : '.$titre["label"]);
                echo 'into titre : '.$titre["label"];
                echo "\n";
                $chapitres = $titre['chapitres'];
                for ($i = 0; $i < count($chapitres); $i++){
                    $chapitre = $chapitres[$i];
                    //matching chapitre
                    $chapitredb = Chapitre::where("label", $chapitre["label"])->first();
                    if(empty($chapitredb)){
                        Log::info('no chapitre match for : '.$chapitre["label"]);
                        echo 'no chapitre match for : '.$chapitre["label"];
                    }
                    else{
                        Log::info($chapitre["label"]." matched by ".$chapitredb->label);
                        Log::info('into chapitre : '.$chapitre["label"]);
                        echo "\n";
                        $rubriques = $chapitre['rubriques'];
                        for($j = 0; $j < count($rubriques); $j++){
                            $rubrique = $rubriques[$j];
                            // matching rubrique
                            $rubriquedb = Rubrique::where("label", $rubrique["label"])->where('chapitre_id', $chapitredb->id)->first();
                            if(empty($rubriquedb)){
                                Log::info('no rubrique match for : '.$rubrique["label"]);
                                echo 'no rubrique match for : '.$rubrique["label"];
                            }
                            else{
                                Log::info('into rubrique : '.$rubrique["label"]);
                                echo 'into rubrique : '.$rubrique["label"];
                                echo "\n";
                                $lignes = $rubrique['lignes'];
                                for($m = 0; $m < count($lignes); $m++){
                                    $ligne = $lignes[$m];
                                    // matching ligne. do I though?
                                    $ligneEntry = new Ligne;
                                    //truncating label length to 100 characters if found longer
                                    if(strlen($ligne['label']) >= 100){
                                        $ligne['label'] = substr($ligne['label'],0,100);
                                    }
                                    $ligneEntry->label = $ligne['label'];
                                    $ligneEntry->description = $ligne['description'];
                                    $ligneEntry->montant = str_replace(" ","", $ligne['montant']);
                                    $ligneEntry->domaine = $ligne['domaine'];
                                    $ligneEntry->section = $ligne['section'];
                                    $ligneEntry->statut = "archive";
                                    if(isset($ligne['sous_section'])){
                                        $ligneEntry->sous_section = $ligne['sous_section'];
                                    }
                                    $ligneEntry->exercice_budgetaire_id = $budget->id;
                                    $ligneEntry->rubrique_id = $rubriquedb->id;
                                    //$rubriquedb->lignes()->save($ligneEntry);
                                    $ligneEntry->save();
                                    echo "saved ligne : ".$ligneEntry->label;
                                    //Log::channel('syslog')->info('saved ligne : '.$ligneEntry->label);
                                    //Log::info('saved ligne : '.$ligneEntry->label);
            
                                    Log::stack(['single', 'syslog'])->info('saved ligne'.$ligneEntry->label);
                                    echo "\n";
                                }
                            }
                        }   
                    }
                }
            }
        }
    }
}