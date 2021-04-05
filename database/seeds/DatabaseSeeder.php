<?php

use App\Models\Chapitre;
use App\Models\Entreprise;
use App\Models\Projet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        $service = new App\Services\DraftBudgetService();

        //seeding entreprises
        $siege = Entreprise::firstOrCreate([
            'code' => 'SNHSIEGE',
            'nom' => 'SNH SIEGE',
            'description' => 'SOCIETE NATIONALE DES HYDROCARBURES',
            'localisation' => 'YDE',
            'hasDomaines' => true
        ]);
        $cpsp = Entreprise::firstOrCreate([
            'code' => 'CPSP',
            'nom' => 'CPSP',
            'description' => 'COMITE DE PILOTATE ET DE SUIVI DU PIPELINE',
            'localisation' => 'YDE',
            'hasDomaines' => false
        ]);
        $snhdouala = Entreprise::firstOrCreate([
            'code' => 'SNHDOUALA',
            'nom' => 'REPRESENTATION SNH DOUALA',
            'description' => 'REPRESENTATION SNH DOUALA',
            'localisation' => 'DLA',
            'hasDomaines' => false
        ]);    
        $snhkribi = Entreprise::firstOrCreate([
            'code' => 'SNHKRIBI',
            'nom' => 'SNH KRIBI',
            'description' => 'REPRESENTATION SNH KRIBI',
            'localisation' => 'KRB',
            'hasDomaines' => false
        ]);

        $service->loadMaquette('maquette2021.php');
        $this->call(LaratrustSeeder::class);
        
        //$this->call(EngagementSeeder::class);
        $this->call(VariablesSeeder::class);
         DB::table('mocks_budgets_fonctionnement')->insert([
            'label' => 'Charges de personnel',
            'previsions' => 494333563,
            'realisations_mois' => 3005157409,
            'realisations_precedentes' => 3499490972,
            'realisations_cumulees' => 5453000000,
            'engagements' => 0,
            'execution' => 3499490972,
            'solde' => 6909193102,
            'taux_execution' => 34,
        ]);
        DB::table('mocks_budgets_fonctionnement')->insert([
            'label' => 'Missions',
            'previsions' => 800100000,
            'realisations_mois' => 1535000,
            'realisations_precedentes' => 70454688,
            'realisations_cumulees' => 71989688,
            'engagements' => 1587000,
            'execution' => 73576688,
            'solde' => 732469312,
            'taux_execution' => 9,
        ]);
        DB::table('mocks_budgets_fonctionnement')->insert([
            'label' => 'Diverses Représentations',
            'previsions' => 409462500,
            'realisations_mois' => 24313770,
            'realisations_precedentes' => 21921087,
            'realisations_cumulees' => 46234857,
            'engagements' => 1234370,
            'execution' => 47469227,
            'solde' => 361993273,
            'taux_execution' => 12,
        ]);
        DB::table('mocks_budgets_fonctionnement')->insert([
            'label' => 'Charges diverses de fonctionnement',
            'previsions' => 5453000000,
            'realisations_mois' => 380778814,
            'realisations_precedentes' => 1240213012,
            'realisations_cumulees' => 1620991826,
            'engagements' => 42725441,
            'execution' => 1663717267,
            'solde' => 3817976411,
            'taux_execution' => 31,
        ]);
        DB::table('mocks_budgets_fonctionnement')->insert([
            'label' => 'Honoraires',
            'previsions' => 918500000,
            'realisations_mois' => 2641950,
            'realisations_precedentes' => 67263134,
            'realisations_cumulees' => 69905084,
            'engagements' => 5962500,
            'execution' => 75867584,
            'solde' => 842632416,
            'taux_execution' => 8,
        ]);
        DB::table('mocks_budgets_fonctionnement')->insert([
            'label' => 'Dons - subventions',
            'previsions' => 455000000,
            'realisations_mois' => 1316500,
            'realisations_precedentes' => 88304620,
            'realisations_cumulees' => 89621120,
            'engagements' => 86500,
            'execution' => 89707620,
            'solde' => 365292380,
            'taux_execution' => 20,
        ]);
        DB::table('mocks_budgets_fonctionnement')->insert([
            'label' => 'Formation',
            'previsions' => 50000000,
            'realisations_mois' => 0,
            'realisations_precedentes' => 0,
            'realisations_cumulees' => 0,
            'engagements' => 0,
            'execution' => 0,
            'solde' => 50000000,
            'taux_execution' => 0,
        ]);
        DB::table('mocks_budgets_fonctionnement')->insert([
            'label' => 'Imprévus',
            'previsions' => 20000000,
            'realisations_mois' => 0,
            'realisations_precedentes' => 0,
            'realisations_cumulees' => 0,
            'engagements' => 0,
            'execution' => 0,
            'solde' => 20000000,
            'taux_execution' => 0,
        ]);
        

        //seeding projets
        //basically, load chapters, attach a project to each of the first 10 chapters loaded
        DB::table('chapitres')->orderBy('id')->chunk(5, function ($chapitres) {
            foreach ($chapitres as $chapitre) {
                $projet = new Projet();
                $projet->label = "Projet - ".$chapitre->label;
                $projet->description = "Projet - ".$chapitre->label;
                $projet->chapitre_id = $chapitre->id;
                $projet->save();
            }
        });
    }
}
