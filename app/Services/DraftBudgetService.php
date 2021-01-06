<?php

namespace App\Services;

use App\Models\Apurement;
use App\Models\Chapitre;
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


class DraftBudgetService{

    // public function uploadFile(){
        
    // }

    public function loadMaquette($name){
        //get budget file from fs location
        // load content into variable
        // run loading script
        //update progress variable while doing that 

        //make sure the maquette file exists at the excpected location. otherwise return false

        //$file = Maquette::where('name',$name)->orderBy('id', 'desc')->first();
        //$filename = $file->name;
        //$filename = $file->name;
        //$exists = Storage::exists('public/files/'.$filename);
        $exists = Storage::exists('public/files/'.$name);

        if(!$exists){
            echo "file does not exist";
            //return false;
        }
        else{
            //include 'loader.php';
            echo "file found!!";
            // $status = include $name;
            //1 - move lines to old table


            $lignes = Ligne::all();

            //get running exercice
            $exercice = $this->getRunningExercice();
            //well, of course initiate the deletion of lines only if there are actually any lines here
            //if there are none (meaning, the exercice just got created, simply move on to maquette loading script)
            if(sizeof($lignes) > 0){
                //Log 'deleting lignes'
                Log::info('deleting lines ');

                foreach($lignes as $ligne){
                    $ligne_archive = new LigneArchivee();
                    //truncating label length to 100 characters if found longer
                    if(strlen($ligne->label) > 100){
                        $ligne->label = substr($ligne->label,0,100);
                    }
                    $ligne_archive->label = $ligne->label;
                    $ligne_archive->description = $ligne->description;
                    $ligne_archive->montant = $ligne->montant;
                    $ligne_archive->domaine = $ligne->domaine;
                    $ligne_archive->section = $ligne->section;
                    // php code, turn date_vote into annee
                    $ligne_archive->annee = $exercice->annee_vote + 1;
                    $ligne_archive->rubrique = $ligne->rubrique->label;
                    $ligne_archive->description_rubrique = $ligne->rubrique->description;
                    $ligne_archive->chapitre = $ligne->rubrique->chapitre->label;
                    $ligne_archive->description_chapitre = $ligne->rubrique->chapitre->description;
                    $ligne_archive->titre = $ligne->rubrique->chapitre->titre->label;
                    $ligne_archive->description_titre = $ligne->rubrique->chapitre->titre->description;
                    
                    $exercice->lignesArchivees()->save($ligne_archive);
                    Log::info('archived '.$ligne->label.' of '.$ligne->rubrique->chapitre->label);
    
                    $ligne->delete();
                    Log::info('deleted row '.$ligne->label.' of '.$ligne->rubrique->chapitre->label.' from lignes db table');
                }
                // $rubriques = Rubrique::all();
                // foreach($rubriques as $rubrique){
                //     $rubrique->delete();
                // }
                // //$deletedRubriques = Rubrique::all()->delete();
                // Log::info('deleted all rubriques');

                // $chapitres = chapitre::all();
                // foreach($rubriques as $rubrique){
                //     $rubrique->delete();
                // }

                // $deletedchapitres = chapitre::all()->delete();
                // Log::info('deleted all chapitres');
                
                $titres = Titre::all();
                foreach($titres as $titre){
                    $titre->delete();
                }
                
                //$deletedTitre = Titre::all()->delete();
                //Log deleting titres
                Log::info('deleted all titres');
            }
           
            //include 'database/maquette-loader.php';
            //$this->process_maquette_file($filename);
            $this->process_maquette_file($name);
            return 'finished';
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
        
        //if it has no lines attached to it, then it was freshly created. leave it as such.

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
            return $file->loadprogress;
        }
        else{
            return 'no file matching the given maquettes name';
        }
        //return 'not implemented yet';
    }

    private function process_maquette_file($name){
        Log::info('processing maquette file');

        //$service = new DraftBudgetService;
        $budget = $this->getRunningExercice();
        
        //$file = Maquette::where('name',$name)->first();
        //$file_ref = Storage::get('public\files\\'.$name);
        
        //echo $file_ref;
        $path = Storage::path('public/files/'.$name);
        $titres = include $path;
        echo ('path found : '.$path);
        echo count($titres);
        
        // if(!isset($titres)||!isset($file)){
        //     return "couldn't find file";
        // }
        //$titres = include 'maquette.php';
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
            //$titreEntry->numero = $titre['numero'];
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
                //$chapitreEntry->numero = $chapitre['numero'];
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
                    //$rubriqueEntry->numero = $rubrique['numero'];
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
                        Log::channel('syslog')->info('saved ligne : '.$ligneEntry->label);
                        Log::info('saved ligne : '.$ligneEntry->label);

                        // //create 3 new engagements for each month and each ligne
                        // $coefs = [1/48,1/24,1/72,1/48];
                        // if($ligneEntry->montant!=0)
                        // {
                        //     for($p = 0; $p < 12; $p++){
                        //         $datemaker=mktime(11, 14, 30, $p, 12, 2020);
                        //         $date = date("Y-m-d h:i:sa", $datemaker);
                        //         for($q = 0; $q < 3; $q++){ //i here being the month. engagements must be added at specific month i, day 11, year 2020 time 14 22.
                        //                                     //they should be retrived the same way.

                        //             //let's build these dates.
                        //             echo "Created date is " .$date;
                        //             echo "\n";
                        //             $engagement = new Engagement();
                        //             $engagement->code = "code-".substr(now()->format('ymd-His-u'),0,16);
                        //             $engagement->libelle = "mock engagement "." - ".$chapitreEntry->label." - ".$ligneEntry->label."-".$q;
                        //             $engagement->nature = 'pre engagement';
                        //             $engagement->type = "BDC";
                        //             $engagement->etat = "imputé";
                        //             $engagement->statut = "validé";
                        //             $engagement->devise = "XAF";
                        //             $montant = ($ligneEntry->montant);
                        //             $coeficient = $coefs[(rand(1,4)-1)];
                        //             echo "montant : ".$montant.", coeficient : ".$coeficient.", total : ".($montant * $coeficient);
                        //             echo "\n";
                        //             $engagement->montant_ttc = floor($montant * $coeficient);
                        //             $engagement->created_at = $date;
                        //             $engagement->source = "idkkaodkf554d44";
                        //             $engagement->saisisseur = "00003";
                        //             $engagement->valideur_first = "00002";
                        //             $engagement->valideur_second = "00001";
                        //             $engagement->valideur_final = "00001";

                        //             $ligneEntry->engagements()->save($engagement);
                        //             echo "saved new engagement with "."code = ".$engagement->code.", libelle = ".$engagement->libelle.", nature = ".$engagement->nature
                        //                             .", montant_ttc = ".$engagement->montant_ttc.", valideur_first = ".$engagement->valideur_first
                        //                             .", id = ".$engagement->id;
                        //             echo "\n";
                        //         }
                        //         //create realisations for two of the previous engagements
                        //         if($p > 0){
                        //             //collect engagements
                        //             for($r = 0; $r < 2; $r++){
                        //                 //getting engagements from previous month
                        //                 //$last_month_date = 
                        //                 $relations = $ligneEntry->engagements()->whereMonth('created_at',$p)->get();  
                        //                 echo "found ".count($relations)." engagements during month ".$p;
                        //                 echo "\n";
                        //                 //$relations = $ligneEntry->engagements()->get();
                        //                 $eng = $relations[$r];
                        //                 $realisation = new Apurement();
                        //                 $realisation->libelle = "realisation";
                        //                 $realisation->reference_paiement = "6qs546g5q4sdg";
                        //                 $realisation->montant_ttc = $eng->montant_ttc;
                        //                 $realisation->devise = "XAF";
                        //                 $realisation->observations = "observation";
                        //                 $realisation->statut = "validé";
                        //                 $realisation->source = "58e55qs5d55d";
                        //                 $realisation->created_at = $date;
                        //                 $realisation->saisisseur = "00002";
                        //                 $realisation->valideur_first = "00002";
                        //                 $realisation->valideur_second = "00002";
                        //                 $realisation->valideur_final = "00002";

                        //                 $eng->apurements()->save($realisation);
                        //             }
                        //         }
                        //      }
                        // }

                        // Log::stack(['single', 'syslog'])->info('saved ligne'.$ligneEntry->label);
                        // echo "\n";
                    }
                }   
                //update progress on chapitres
                //$file->loadprogress += ($chapitre_progress_rate * 100);
                //$file->save();
                //echo "file processing now at ".$file->loadprogress."%";
                //Log::info("file processing now at ".$file->loadprogress."%");
            }
            //update progress for this titre
        }
    }
}