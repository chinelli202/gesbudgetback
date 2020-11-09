<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use App\Models\User;

class EngagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->truncateTables();

        $devises = Config::get('gesbudget.variables.devise');
        $naturesEngagement = Config::get('gesbudget.variables.nature_engagement');
        $etatsEngagement = Config::get('gesbudget.variables.etat_engagement');
        $typesEngagement = Config::get('gesbudget.variables.type_engagement');
        $statutsEngagement = Config::get('gesbudget.variables.statut_engagement');

        foreach ($naturesEngagement as $natureEng => $naturedesc) {
            foreach ($typesEngagement as $typeEng => $typedesc) {
                foreach ($etatsEngagement as $etatEng => $etatdesc) {
                    foreach ($statutsEngagement as $statutEng => $statutdesc) {

                        if(
                            (( ($natureEng == 'PEG' && (
                                    ($etatEng=='INIT' && (in_array($statutEng, array('SAISI', 'VALIDP', 'VALIDS'))))
                                    || ($etatEng=='PEG' && (in_array($statutEng, array('SAISI', 'VALIDP', 'VALIDS','VALIDF'))))
                                    || ($etatEng=='IMP' && $statutEng == 'VALIDF')
                                    || ($etatEng=='REA' && $statutEng == 'VALIDF'))
                                )
                            || ($natureEng == 'REA' && (
                                    ($etatEng=='INIT' && (in_array($statutEng, array('SAISI', 'VALIDP', 'VALIDS'))))
                                    || ($etatEng=='PEG' && $statutEng == 'VALIDF') 
                                    || ($etatEng=='IMP' && $statutEng == 'VALIDF')
                                    || ($etatEng=='REA' && $statutEng == 'VALIDF')
                                    )
                                )
                            )
                            ) && $statutEng != 'VALIDF_NOEXC'  
                        ){
                            
                            $devise = array_keys($devises)[rand(0,2)];
                            $montant = rand(100000, 10000000);

                            if($etatEng == 'INIT'){
                                // Create a new engagement
                                $engagement = $this->createEngagement($typeEng,$montant, $devise, $natureEng, $etatEng, $statutEng);
                            }elseif($etatEng == 'PEG'){
                                if($statutEng == 'VALIDF'){
                                    if($natureEng == 'REA'){
                                        // Create a new engagement
                                        $engagement = $this->createEngagement($typeEng,$montant, $devise, $natureEng, $etatEng, $statutEng);
                                        
                                        // Create a new Imputation
                                        $imputation = $this->createImputation($engagement, 'VALIDF' );
                                        $engagement->cumul_imputations += $imputation->montant_ttc;
                                        $engagement->nb_imputations += 1;
                                        $engagement->etat = 'IMP';
                                        $engagement->save();
            
                                        $this->command->info('Created Imputation '. $imputation->id . ' for engagement ' . $engagement->code);
                                    }else{
                                        foreach($statutsEngagement as $statutimp => $statutimpdesc){
                                            // Create a new engagement
                                            $engagement = $this->createEngagement($typeEng,$montant, $devise, $natureEng, $etatEng, $statutEng);
                                            
                                            // Create a new Imputation
                                            $imputation = $this->createImputation($engagement, $statutimp == 'VALIDF_NOEXC' ? null: $statutimp);
                                            if(!is_null($imputation)){
                                                $this->command->info('Created Imputation '. $imputation->id . ' for engagement ' . $engagement->code);
                                            }
                                        }
                                    }
                                }else{
                                    // Create a new engagement
                                    $engagement = $this->createEngagement($typeEng,$montant, $devise, $natureEng, $etatEng, $statutEng);
                                }
                            }elseif($etatEng == 'IMP'){
                                foreach($statutsEngagement as $statutapur => $statutapurdesc){
                                    // Create a new engagement
                                    $engagement = $this->createEngagement($typeEng,$montant, $devise, $natureEng, $etatEng, $statutEng);
                                    
                                    // Create a new Imputation with FINAL status
                                    $imputation = $this->createImputation($engagement, 'VALIDF');
                                    $engagement->cumul_imputations += $imputation->montant_ttc;
                                    $engagement->nb_imputations += 1;
                                    $engagement->save();
                                    $this->command->info('Created Imputation '. $imputation->id . ' for engagement ' . $engagement->code);

                                    // Create a new apurement
                                    if($statutapur != 'VALIDF'){
                                        $apurement = $this->createApurement($engagement, $statutapur == 'VALIDF_NOEXC' ? null: $statutapur);
                                        if(!is_null($apurement)){
                                            $this->command->info('Created Apurement '. $apurement->id . ' for engagement ' . $engagement->code);
                                        }
                                    }
                                }
                            }elseif($etatEng == 'REA'){
                                // Create a new engagement
                                $engagement = $this->createEngagement($typeEng,$montant, $devise, $natureEng, $etatEng, $statutEng);
                                    
                                // Create a new Imputation with FINAL status
                                $imputation = $this->createImputation($engagement, 'VALIDF');
                                $engagement->cumul_imputations += $imputation->montant_ttc;
                                $engagement->nb_imputations += 1;
                                $engagement->save();
                                $this->command->info('Created Imputation '. $imputation->id . ' for engagement ' . $engagement->code);

                                // Create a new apurement with FINAL STATUS
                                $apurement = $this->createApurement($engagement, 'VALIDF');
                                $engagement->cumul_apurements += $apurement->montant_ttc;
                                $engagement->nb_apurements += 1;
                                $engagement->save();
                                $this->command->info('Created Apurement '. $apurement->id . ' for engagement ' . $engagement->code);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Truncates all tables related to engagement
     *
     * @return    void
     */
    public function truncateTables()
    {
        $this->command->info('Truncate des tables Engagements, Imputations et Apurements');
        Schema::disableForeignKeyConstraints();
        DB::table('engagements')->truncate();
        DB::table('imputations')->truncate();
        DB::table('apurements')->truncate();

        \App\Models\Engagement::truncate();
        \App\Models\Imputation::truncate();
        \App\Models\Apurement::truncate();

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Imputer un engagement
     *
     * @return    \App\Models\Engagement
     */
    public function createEngagement($typeEng, $montant, $devise, $natureEng, $etatEng, $statutEng ){
        $engagement = \App\Models\Engagement::firstOrCreate([
            'code' => $typeEng .substr(now()->format('ymd-His-u'),0,17), 
            'libelle' => 'Engagement de type ' . $typeEng . ' du '. now(),
            'montant_ht' => $montant,
            'montant_ttc' => $montant*1.1925,
            'devise' => $devise,

            'nature' => $natureEng,
            'type' => $typeEng,
            'etat' => $etatEng,
            'statut' => $statutEng,
            'nb_imputations' => 0,
            'cumul_imputations' => 0,
            'nb_apurements' => 0,
            'cumul_apurements' => 0,
            'saisisseur' => User::find(3)->matricule,
            'valideur_first' => in_array($statutEng, array('VALIDP', 'VALIDS','VALIDF' )) ? User::find(2)->matricule : null,
            'valideur_second' => in_array($statutEng, array('VALIDS','VALIDF' )) ? User::find(1)->matricule : null,
            'valideur_final' => in_array($statutEng, array('VALIDF')) ? User::find(1)->matricule : null,
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);
        $this->command->info('Created Engagement '. $engagement->code
            . '-' .$engagement->nature
            . '-' .$engagement->type
            . '-' .$engagement->etat
            . '-' .$engagement->statut
        );
        return $engagement;
    }

    /**
     * Imputer un engagement
     *
     * @return    \App\Models\Imputation
     */
    public function createImputation($engagement, $statut){
        if(is_null($statut)){
            return null;
        }
        return \App\Models\Imputation::firstOrCreate([
            'engagement_id' => $engagement->code,
            'reference' => bin2hex(random_bytes(4)),
            'montant_ht' => $engagement->montant_ht,
            'montant_ttc' => $engagement->montant_ttc,
            'devise' => $engagement->devise,
            'observations' => 'Imputation sur ' . $engagement->libelle,
            'statut' => $statut,
            'saisisseur' => User::find(3)->matricule,
            'valideur_first' => User::find(2)->matricule,
            'valideur_second' => User::find(1)->matricule,
            'valideur_final' => User::find(1)->matricule,
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);
    }

    /**
     * Apurer un engagement
     *
     * @return    \App\Models\Apurement
     */
    public function createApurement($engagement, $statut){
        if(is_null($statut)){
            return null;
        }
        return \App\Models\Apurement::firstOrCreate([
            'engagement_id' => $engagement->code,
            'libelle' => 'Apurement ' . $engagement->libelle,
            'reference_paiement' => bin2hex(random_bytes(4)),
            'montant_ht' => $engagement->montant_ht,
            'montant_ttc' => $engagement->montant_ttc,
            'devise' => $engagement->devise,
            'statut' => $statut,
            'observations' => 'Observation Apurement sur ' . $engagement->libelle,
            'saisisseur' => User::find(3)->matricule,
            'valideur_first' => User::find(2)->matricule,
            'valideur_second' => User::find(1)->matricule,
            'valideur_final' => User::find(1)->matricule,
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);
    }
}
