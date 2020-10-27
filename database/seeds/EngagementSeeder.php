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

        $devises = Config::get('app_seeder.variables.devise');
        $naturesEngagement = Config::get('app_seeder.variables.nature_engagement');
        $etatsEngagement = Config::get('app_seeder.variables.etat_engagement');
        $typesEngagement = Config::get('app_seeder.variables.type_engagement');
        $statutsEngagement = Config::get('app_seeder.variables.statut_engagement');

        foreach ($naturesEngagement as $nature => $naturedesc) {
            foreach ($typesEngagement as $type => $typedesc) {
                foreach ($etatsEngagement as $etat => $etatdesc) {
                    foreach ($statutsEngagement as $statut => $statutdesc) {
                        $montant = rand(100000, 10000000);

                        if($nature == 'PEG' || ($nature == 'REA' 
                                && (
                                    ($etat='PEG' && (in_array($statut, array('SAISI', 'VALIDP', 'VALIDS')))) 
                                    || ($etat='IMP' && $statut = 'VALIDF')
                                    || ($etat='REA')
                        ))){
                            // Create a new engagement
                            $engagement = \App\Models\Engagement::firstOrCreate([
                                'code' => $type .substr(now()->format('ymd-His-u'),0,16), 
                                'libelle' => 'Engagement de type ' . $typedesc . ' du '. now(),
                                'montant_ht' => $montant,
                                'montant_ttc' => $montant*1.1925,
                                'devise' => array_values($devises)[rand(0,2)],

                                'nature' => $naturedesc,
                                'type' => $typedesc,
                                'etat' => $etatdesc,
                                'statut' => $statutdesc,
                
                                'cumul_imputations' => 0,
                                'cumul_apurements' => 0,
                                'saisisseur' => User::find(3)->matricule,
                                'valideur_first' => User::find(2)->matricule,
                                'valideur_second' => User::find(1)->matricule,
                                'valideur_final' => User::find(1)->matricule,
                                'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
                            ]);

                            $this->command->info('Creating Engagement '. $engagement->code);

                            if($etat == 'IMP' || $etat == 'REA') {
                                // Create a new Imputation
                                $imputation = $this->createImputation($engagement);
    
                                $this->command->info('Creating Imputation '. $imputation->id . ' for engagement ' . $engagement->code);
                            }
    
                            if($etat == 'REA'){
                                // Create a new apurement
                                $apurement = $this->createApurement($engagement);
    
                                $this->command->info('Creating Apurement '. $apurement->id . ' for engagement ' . $engagement->code);
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
     * @return    \App\Models\Imputation
     */
    public function createImputation($engagement){
        return \App\Models\Imputation::firstOrCreate([
            'engagement_id' => $engagement->code,
            'reference' => bin2hex(random_bytes(4)),
            'montant_ht' => $engagement->montant_ht,
            'montant_ttc' => $engagement->montant_ttc,
            'devise' => $engagement->devise,
            'observations' => 'Imputation sur ' . $engagement->libelle,
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
    public function createApurement($engagement){
        return \App\Models\Apurement::firstOrCreate([
            'engagement_id' => $engagement->code,
            'libelle' => 'Apurement ' . $engagement->libelle,
            'reference_paiement' => bin2hex(random_bytes(4)),
            'montant_ht' => $engagement->montant_ht,
            'montant_ttc' => $engagement->montant_ttc,
            'devise' => $engagement->devise,
            'observations' => 'Observation Apurement sur ' . $engagement->libelle,
            'saisisseur' => User::find(3)->matricule,
            'valideur_first' => User::find(2)->matricule,
            'valideur_second' => User::find(1)->matricule,
            'valideur_final' => User::find(1)->matricule,
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);
    }
}
