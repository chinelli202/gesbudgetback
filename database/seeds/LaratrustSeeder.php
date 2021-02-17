<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class LaratrustSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return  void
     */
    public function run()
    {
        $this->truncateLaratrustTables();

        $config = config('laratrust_seeder.roles_structure');
        $mapPermission = collect(config('laratrust_seeder.permissions_map'));
        $increment = 1;

        $teamSnhYde = \App\Models\Team::firstOrCreate([
            'name' => 'snh_yde',
            'display_name' => 'SNH YDE',
            'description' => 'Siège SNH',
            'entreprise_code' => 'SNHSIEGE'
        ]);

        /** Permissions à l'étape d'initialisation des préengagements */
        $perm = \App\Models\Permission::create([
            'code' => 'ENG_INIT_SAISI',
            'name' => 'ENG_INIT_SAISI',
            'display_name' => 'Saisir un préengagement',
            'description' => 'Saisir un préengagement',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'ENG_INIT_VALIDP',
            'name' => 'ENG_INIT_VALIDP',
            'to_perform_on' => 'INIT_SAISI',
            'display_name' => 'Valider un préengagement au 1er niveau',
            'description' => 'Valider un préengagement au 1er niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'ENG_INIT_VALIDS',
            'name' => 'ENG_INIT_VALIDS',
            'to_perform_on' => 'INIT_VALIDP',
            'display_name' => 'Valider un préengagement au 2nd niveau',
            'description' => 'Valider un préengagement au 2nd niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'ENG_INIT_VALIDF',
            'name' => 'ENG_INIT_VALIDF',
            'to_perform_on' => 'INIT_VALIDS',
            'display_name' => 'Valider un préengagement au niveau final',
            'description' => 'Valider un préengagement au niveau final',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'ENG_INIT_CLOT',
            'name' => 'ENG_INIT_CLOT',
            'display_name' => 'Clôturer un préengagement',
            'description' => 'Clôturer un préengagement. Un préengagement ne peut être clôturé que lorsqu\'il n\'a pas encore été validé au niveau final.',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);
        
        /** Permissions pour les engagements engagés */
        $perm = \App\Models\Permission::create([
            'code' => 'ENG_PEG_SAISI',
            'name' => 'ENG_PEG_SAISI',
            'display_name' => 'Saisir une imputation',
            'description' => 'Saisir une imputation',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'ENG_PEG_VALIDP',
            'name' => 'ENG_PEG_VALIDP',
            'to_perform_on' => 'PEG_SAISI',
            'display_name' => 'Valider une imputation au 1er niveau',
            'description' => 'Valider une imputation au 1er niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'ENG_PEG_VALIDS',
            'name' => 'ENG_PEG_VALIDS',
            'to_perform_on' => 'PEG_VALIDP',
            'display_name' => 'Valider une imputation au 2nd niveau',
            'description' => 'Valider une imputation au 2nd niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'ENG_PEG_VALIDF',
            'name' => 'ENG_PEG_VALIDF',
            'to_perform_on' => 'PEG_VALIDS',
            'display_name' => 'Valider une imputation au niveau final',
            'description' => 'Valider une imputation au niveau final',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'ENG_PEG_CLOT',
            'name' => 'ENG_PEG_CLOT',
            'display_name' => 'Clôturer une imputation',
            'description' => 'Clôturer une imputation. Une imputation ne peut être clôturé que lorsqu\'elle n\'a pas encore été validé au niveau final.',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        /** Permissions pour les engagements imputés */
        $perm = \App\Models\Permission::create([
            'code' => 'ENG_IMP_SAISI',
            'name' => 'ENG_IMP_SAISI',
            'display_name' => 'Saisir un apurement',
            'description' => 'Saisir un apurement',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'ENG_IMP_VALIDP',
            'name' => 'ENG_IMP_VALIDP',
            'to_perform_on' => 'IMP_SAISI',
            'display_name' => 'Valider un apurement au 1er niveau',
            'description' => 'Valider un apurement au 1er niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'ENG_IMP_VALIDS',
            'name' => 'ENG_IMP_VALIDS',
            'to_perform_on' => 'IMP_VALIDP',
            'display_name' => 'Valider un apurement au 2nd niveau',
            'description' => 'Valider un apurement au 2nd niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'ENG_IMP_VALIDF',
            'name' => 'ENG_IMP_VALIDF',
            'to_perform_on' => 'IMP_VALIDS',
            'display_name' => 'Valider un apurement au niveau final',
            'description' => 'Valider un apurement au niveau final',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'ENG_IMP_CLOT',
            'name' => 'ENG_IMP_CLOT',
            'display_name' => 'Clôturer un apurement',
            'description' => 'Clôturer un apurement. Un apurment ne peut être clôturé que lorsqu\'elle n\'a pas encore été validé au niveau final.',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);
        
        $perm = \App\Models\Permission::create([
            'code' => 'ENG_IMP_REG',
            'name' => 'ENG_IMP_REG',
            'display_name' => 'Passer une écriture de régularisation',
            'description' => 'Passer une écriture de régularisation',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        /** Rôles de base de l'application */
        $roledbc = \App\Models\Role::firstOrCreate([
            'name' => 'DBC',
            'display_name' => 'Directeur du budget',
            'description' => 'Directeur du budget',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $roledbc->attachPermission('ENG_INIT_SAISI');
        $roledbc->attachPermission('ENG_INIT_CLOT');
        $roledbc->attachPermission('ENG_PEG_SAISI');
        $roledbc->attachPermission('ENG_PEG_CLOT');
        $roledbc->attachPermission('ENG_IMP_SAISI');
        $roledbc->attachPermission('ENG_IMP_CLOT');
        $roledbc->attachPermission('ENG_IMP_REG');

        $roledbc->attachPermission('ENG_INIT_VALIDP');
        $roledbc->attachPermission('ENG_INIT_VALIDS');
        $roledbc->attachPermission('ENG_PEG_VALIDP');
        $roledbc->attachPermission('ENG_PEG_VALIDS');
        $roledbc->attachPermission('ENG_IMP_VALIDP');
        $roledbc->attachPermission('ENG_IMP_VALIDS');

        $roledbc->attachPermission('ENG_INIT_VALIDF');
        $roledbc->attachPermission('ENG_PEG_VALIDF');
        $roledbc->attachPermission('ENG_IMP_VALIDF');
        
        
        $roledbca = \App\Models\Role::firstOrCreate([
            'name' => 'DBCA',
            'display_name' => 'Directeur adjoint du budget',
            'description' => 'Directeur adjoint du budget',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $roledbca->attachPermission('ENG_INIT_SAISI');
        $roledbca->attachPermission('ENG_INIT_CLOT');
        $roledbca->attachPermission('ENG_PEG_SAISI');
        $roledbca->attachPermission('ENG_PEG_CLOT');
        $roledbca->attachPermission('ENG_IMP_SAISI');
        $roledbca->attachPermission('ENG_IMP_CLOT');
        $roledbca->attachPermission('ENG_IMP_REG');

        $roledbca->attachPermission('ENG_INIT_VALIDP');
        $roledbca->attachPermission('ENG_INIT_VALIDS');
        $roledbca->attachPermission('ENG_PEG_VALIDP');
        $roledbca->attachPermission('ENG_PEG_VALIDS');
        $roledbca->attachPermission('ENG_IMP_VALIDP');
        $roledbca->attachPermission('ENG_IMP_VALIDS');

        $roledbca->attachPermission('ENG_INIT_VALIDF');
        $roledbca->attachPermission('ENG_PEG_VALIDF');
        $roledbca->attachPermission('ENG_IMP_VALIDF');

        $rolecc = \App\Models\Role::firstOrCreate([
            'name' => 'chef-cellule',
            'display_name' => 'Chef de cellule',
            'description' => 'Chef de cellule',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $rolecc->attachPermission('ENG_INIT_SAISI');
        $rolecc->attachPermission('ENG_INIT_CLOT');
        $rolecc->attachPermission('ENG_PEG_SAISI');
        $rolecc->attachPermission('ENG_PEG_CLOT');
        $rolecc->attachPermission('ENG_IMP_SAISI');
        $rolecc->attachPermission('ENG_IMP_CLOT');
        $rolecc->attachPermission('ENG_IMP_REG');

        $rolecc->attachPermission('ENG_INIT_VALIDP');
        $rolecc->attachPermission('ENG_INIT_VALIDS');
        $rolecc->attachPermission('ENG_PEG_VALIDP');
        $rolecc->attachPermission('ENG_PEG_VALIDS');
        $rolecc->attachPermission('ENG_IMP_VALIDP');
        $rolecc->attachPermission('ENG_IMP_VALIDS');

        $rolesai = \App\Models\Role::firstOrCreate([
            'name' => 'saisisseur',
            'display_name' => 'Saisisseur',
            'description' => 'Saisisseur',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);
        $rolesai->attachPermission('ENG_INIT_SAISI');
        $rolesai->attachPermission('ENG_INIT_CLOT');
        $rolesai->attachPermission('ENG_PEG_SAISI');
        $rolesai->attachPermission('ENG_PEG_CLOT');
        $rolesai->attachPermission('ENG_IMP_SAISI');
        $rolesai->attachPermission('ENG_IMP_CLOT');
        $rolesai->attachPermission('ENG_IMP_REG');

        $roleuser = \App\Models\Role::firstOrCreate([
            'name' => 'user',
            'display_name' => 'Utilisateur',
            'description' => 'Utilisateur',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $user = \App\Models\User::create([
            'matricule' => '00379',
            'sexe' => 'M',
            'name' => 'NGANGO EBANDJO Eugène',
            'first_name' => 'Eugène',
            'last_name' => 'NGANGO EBANDJO',
            'email' => 'eugene.ngango@snh.cm',
            'password' => bcrypt('00379'),
            'saisisseur' => 'NA',
            'statut_utilisateur' => Config::get('laratrust.constants.user_status.INITIATED'),
            'valideur' => 'NA',
            'source'  => Config::get('laratrust.constants.user_creation_source.SEEDER'),
            'division' => 'DBC',
            'fonction' => 'directeur',
            'representation' => 'YDE'
        ]);
        $user->attachRole($roledbc, $teamSnhYde);

        $user = \App\Models\User::create([
            'matricule' => '00000',
            'sexe' => 'F',
            'name' => 'MENGUE ME MBARGA Salomé',
            'first_name' => 'Salomé',
            'last_name' => 'MENGUE ME MBARGA',
            'email' => 'salome.mengue@snh.cm',
            'password' => bcrypt('00000'),
            'saisisseur' => 'NA',
            'statut_utilisateur' => Config::get('laratrust.constants.user_status.INITIATED'),
            'valideur' => 'NA',
            'source'  => Config::get('laratrust.constants.user_creation_source.SEEDER'),
            'division' => 'DBC',
            'fonction' => 'sous_directeur',
            'representation' => 'YDE'
        ]);
        $user->attachRole($roledbca, $teamSnhYde);

        $user = \App\Models\User::create([
            'matricule' => '00475',
            'sexe' => 'F',
            'name' => 'NGO MBOG Odette',
            'first_name' => 'Odette',
            'last_name' => 'NGO MBOG',
            'email' => 'odette.ngombog@snh.cm',
            'password' => bcrypt('00475'),
            'saisisseur' => 'NA',
            'statut_utilisateur' => Config::get('laratrust.constants.user_status.INITIATED'),
            'valideur' => 'NA',
            'source'  => Config::get('laratrust.constants.user_creation_source.SEEDER'),
            'division' => 'DBC',
            'fonction' => 'chef_section_cellule',
            'representation' => 'YDE'
        ]);
        $user->attachRole($rolecc, $teamSnhYde);

        $user = \App\Models\User::create([
            'matricule' => '00362',
            'sexe' => 'M',
            'name' => 'NAAH AMBASSA Ignace',
            'first_name' => 'Ignace',
            'last_name' => 'NAAH AMBASSA',
            'email' => 'ignace.naah@snh.cm',
            'password' => bcrypt('00362'),
            'saisisseur' => 'NA',
            'statut_utilisateur' => Config::get('laratrust.constants.user_status.INITIATED'),
            'valideur' => 'NA',
            'source'  => Config::get('laratrust.constants.user_creation_source.SEEDER'),
            'division' => 'DBC',
            'fonction' => 'chef_section_cellule',
            'representation' => 'YDE'
        ]);
        $user->attachRole($rolecc, $teamSnhYde);

        $user = \App\Models\User::create([
            'matricule' => '00171',
            'sexe' => 'M',
            'name' => 'BELINGA Louis Roger',
            'first_name' => 'Louis Roger',
            'last_name' => 'BELINGA',
            'email' => 'roger.belinga@snh.cm',
            'password' => bcrypt('00171'),
            'saisisseur' => 'NA',
            'statut_utilisateur' => Config::get('laratrust.constants.user_status.INITIATED'),
            'valideur' => 'NA',
            'source'  => Config::get('laratrust.constants.user_creation_source.SEEDER'),
            'division' => 'DBC',
            'fonction' => 'agent_maitrise',
            'representation' => 'YDE'
        ]);
        $user->attachRole($rolesai, $teamSnhYde);

        $user = \App\Models\User::create([
            'matricule' => '00614',
            'sexe' => 'M',
            'name' => 'NJOCK ELOKOBI',
            'first_name' => 'NJOCK ELOKOBI',
            'last_name' => 'NJOCK ELOKOBI',
            'email' => 'njock.elokobi@snh.cm',
            'password' => bcrypt('00614'),
            'saisisseur' => 'NA',
            'statut_utilisateur' => Config::get('laratrust.constants.user_status.INITIATED'),
            'valideur' => 'NA',
            'source'  => Config::get('laratrust.constants.user_creation_source.SEEDER'),
            'division' => 'DBC',
            'fonction' => 'cadre',
            'representation' => 'YDE'
        ]);
        $user->attachRole($rolesai, $teamSnhYde);

        $user = \App\Models\User::create([
            'matricule' => '00705',
            'sexe' => 'M',
            'name' => 'NANGA NOUKITI Noël Axel',
            'first_name' => 'Noël Axel',
            'last_name' => 'NANGA NOUKITI',
            'email' => 'noel.nanga@app.com',
            'password' => bcrypt('00705'),
            'saisisseur' => 'NA',
            'statut_utilisateur' => Config::get('laratrust.constants.user_status.INITIATED'),
            'valideur' => 'NA',
            'source'  => Config::get('laratrust.constants.user_creation_source.SEEDER'),
            'division' => 'DBC',
            'fonction' => 'temporaire',
            'representation' => 'YDE'
        ]);
        $user->attachRole($rolesai, $teamSnhYde);
    }

    /**
     * Truncates all the laratrust tables and the users table
     *
     * @return    void
     */
    public function truncateLaratrustTables()
    {
        $this->command->info('Truncating User, Role and Permission tables');
        Schema::disableForeignKeyConstraints();
        DB::table('permission_role')->truncate();
        DB::table('permission_user')->truncate();
        DB::table('role_user')->truncate();
        if(Config::get('laratrust_seeder.truncate_tables')) {
            \App\Models\Role::truncate();
            \App\Models\Permission::truncate();
        }
        if(Config::get('laratrust_seeder.truncate_tables') && Config::get('laratrust_seeder.create_users')) {
            \App\Models\User::truncate();
        }
        Schema::enableForeignKeyConstraints();
    }
}
