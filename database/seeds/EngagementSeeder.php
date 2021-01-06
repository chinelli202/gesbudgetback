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
        $typesPaiement = Config::get('gesbudget.variables.type_paiement');
        $statutsEngagement = Config::get('gesbudget.variables.statut_engagement');

        for ($i=0; $i < 3; $i++) {
            /** Create engagements with state INIT and for each statuts  */
            foreach ($typesEngagement as $typeEng => $typedesc) {
                $natureEng = 'PEG';
                $etatEng ='INIT';
                $devise = array_keys($devises)[rand(0,2)];
                $montant = rand(100000, 10000000);
                
                foreach ($statutsEngagement as $statutEng => $statutdesc) {
                    if($statutEng === 'VALIDF') {
                        $etatEng = 'PEG';
                    }
                    $engagement = $this->createEngagement($typeEng,$montant, $devise, $natureEng, $etatEng, $statutEng);
                }
            }

            /** Create engagement with state PEG but with no imputation  */
            $natureEng = 'PEG';
            $etatEng = 'PEG';
            $statutEng = 'VALIDF';
            $devise = array_keys($devises)[rand(0,2)];
            $montant = rand(100000, 10000000);
            $engagement = $this->createEngagement($typeEng,$montant, $devise, $natureEng, $etatEng, $statutEng);

            /** Create engagement with state PEG but with imputation for each statut  */
            foreach ($typesEngagement as $typeEng => $typedesc) {
                $natureEng = 'PEG';
                $etatEng = 'PEG';
                $statutEng = 'VALIDF';

                foreach ($statutsEngagement as $statutimp => $statutimpdesc) {
                    $devise = array_keys($devises)[rand(0,2)];
                    $montant = rand(100000, 10000000);

                    if($statutimp === 'VALIDF') {
                        $etatEng = 'IMP';
                    }
                    $engagement = $this->createEngagement($typeEng,$montant, $devise, $natureEng, $etatEng, $statutEng);
                    $imputation = $this->createImputation($engagement, $statutimp);
                    if(!is_null($imputation)){
                        $this->command->info('Created Imputation '. $imputation->id . ' for engagement ' . $engagement->code);
                    }
                }
            }


            /** Create engagement with state IMP but with imputation for each statut  */
            foreach ($typesEngagement as $typeEng => $typedesc) {
                $natureEng = 'PEG';
                $etatEng = 'IMP';
                $statutEng = 'VALIDF';
                $statutimp = 'VALIDF';

                foreach ($statutsEngagement as $statutapur => $statutapurdesc) {
                    $devise = array_keys($devises)[rand(0,2)];
                    $montant = rand(100000, 10000000);
                    $typePaiement = $typesPaiement[mt_rand(0,2)][1];

                    if($statutapur === 'VALIDF') {
                        $etatEng = 'APUR';
                    }
                    $engagement = $this->createEngagement($typeEng,$montant, $devise, $natureEng, $etatEng, $statutEng);
                    $imputation = $this->createImputation($engagement, $statutimp);
                    $apurement = $this->createApurement($engagement, $statutapur, $typePaiement);

                    if(!is_null($imputation)){
                        $this->command->info('Created Imputation '. $imputation->id . ' for engagement ' . $engagement->code);
                    }
                    if(!is_null($imputation)){
                        $this->command->info('Created Apurement '. $apurement->id . ' for engagement ' . $engagement->code);
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
        $ligne = \App\Models\Ligne::all()->first();
        $ligne_id = $ligne->id + rand(0, 300);
        $rubrique = \App\Models\Rubrique::where('id', $ligne->rubrique_id)->first();

        $engagement = \App\Models\Engagement::firstOrCreate([
            'code' => $typeEng .substr(now()->format('ymd-His-u'),0,17), 
            'code_comptabilite' => $typeEng .strval(DB::getPdo()->lastInsertId()+1).'-'.substr(now()->format('ymd-His-u'),0,17), 
            'libelle' => 'Engagement de type ' . $typeEng . ' du '. now(),
            'montant_ht' => 0,
            'montant_ttc' => $montant,
            'devise' => $devise,

            'nature' => $natureEng,
            'type' => $typeEng,
            'etat' => $etatEng,
            'statut' => $statutEng,
            'latest_statut' => $statutEng,
            'latest_edited_at' => now(),
            'nb_imputations' => 0,
            'cumul_imputations' => 0,
            'nb_apurements' => 0,
            'cumul_apurements' => 0,
            'saisisseur' => User::find(5)->matricule,
            'valideur_first' => in_array($statutEng, array('VALIDP', 'VALIDS','VALIDF' )) ? User::find(3)->matricule : null,
            'valideur_second' => in_array($statutEng, array('VALIDS','VALIDF' )) ? User::find(4)->matricule : null,
            'valideur_final' => in_array($statutEng, array('VALIDF')) ? User::find(1)->matricule : null,
            'source' => Config::get('gesbudget.variables.source.SEEDER')[0],
            'ligne_id' => $ligne_id,
            'rubrique_id' => $rubrique->id,
            'chapitre_id' => $rubrique->chapitre_id
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
        $vfirst = null;
        $vsecond = null;
        $vfinal = null;

        if($statut === 'VALIDP') {
            $vfirst = User::find(3)->matricule;
        } else if($statut === 'VALIDS') {
            $vfirst = User::find(3)->matricule;
            $vsecond = User::find(4)->matricule;
        } else if($statut === 'VALIDF') {
            $vfirst = User::find(3)->matricule;
            $vsecond = User::find(4)->matricule;
            $vfinal = User::find(1)->matricule;
        }

        $engagement->latest_statut = $statut;
        $engagement->latest_edited_at = now();
        $engagement->save();

        return \App\Models\Imputation::firstOrCreate([
            'engagement_id' => $engagement->code,
            'reference' => bin2hex(random_bytes(4)),
            'montant_ht' => $engagement->montant_ht,
            'montant_ttc' => $engagement->montant_ttc,
            'devise' => $engagement->devise,
            'observations' => 'Imputation sur ' . $engagement->libelle,
            'statut' => $statut,
            'etat' => Config::get('gesbudget.variables.etat_engagement.INIT')[1],
            'saisisseur' => User::find(5)->matricule,
            'valideur_first' => $vfirst,
            'valideur_second' => $vsecond,
            'valideur_final' => $vfinal,
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);
    }

    /**
     * Apurer un engagement
     *
     * @return    \App\Models\Apurement
     */
    public function createApurement($engagement, $statut, $typePaiement){
        if(is_null($statut)){
            return null;
        }

        $vfirst = null;
        $vsecond = null;
        $vfinal = null;

        if($statut === 'VALIDP') {
            $vfirst = User::find(3)->matricule;
        } else if($statut === 'VALIDS') {
            $vfirst = User::find(3)->matricule;
            $vsecond = User::find(4)->matricule;
        } else if($statut === 'VALIDF') {
            $vfirst = User::find(3)->matricule;
            $vsecond = User::find(4)->matricule;
            $vfinal = User::find(1)->matricule;
        }

        $engagement->latest_statut = $statut;
        $engagement->latest_edited_at = now();
        $engagement->save();

        return \App\Models\Apurement::firstOrCreate([
            'engagement_id' => $engagement->code,
            'libelle' => 'Apurement ' . $engagement->libelle,
            'reference_paiement' => bin2hex(random_bytes(4)),
            "type_paiement" => $typePaiement,
            'montant_ht' => $engagement->montant_ht,
            'montant_ttc' => $engagement->montant_ttc,
            'devise' => $engagement->devise,
            'statut' => $statut,
            'etat' => Config::get('gesbudget.variables.etat_engagement.INIT')[1],
            'observations' => 'Observation Apurement sur ' . $engagement->libelle,
            'saisisseur' => User::find(5)->matricule,
            'valideur_first' => $vfirst,
            'valideur_second' => $vsecond,
            'valideur_final' => $vfinal,
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);
    }
}
