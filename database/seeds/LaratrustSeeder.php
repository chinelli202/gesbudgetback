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
        
        
        foreach ($config as $key => $modules) {

            // Create a new role
            $role = \App\Models\Role::firstOrCreate([
                'name' => $key,
                'display_name' => ucwords(str_replace('_', ' ', $key)),
                'description' => ucwords(str_replace('_', ' ', $key)),
                'saisisseur' => 'NA',
                'valideur' => 'NA',
                'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
            ]);
            $permissions = [];

            $this->command->info('Creating Role '. strtoupper($key));

            // Reading role permission modules
            foreach ($modules as $module => $value) {

                foreach (explode(',', $value) as $p => $perm) {

                    $permissionValue = $mapPermission->get($perm);

                    $permissions[] = \App\Models\Permission::firstOrCreate([
                        'code' => $module . '-' . $permissionValue,
                        'name' => ucfirst($permissionValue) . ' ' . ucfirst($module),
                        'description' => ucfirst($permissionValue) . ' ' . ucfirst($module),
                        'saisisseur' => 'NA',
                        'valideur' => 'NA',
                        'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
                    ])->id;

                    $this->command->info('Creating Permission to '.$permissionValue.' for '. $module);
                }
            }

            // Attach all permissions to the role
            $role->permissions()->sync($permissions);
            
            if($role->name === 'user') {
                $role->attachPermission('ENG_INIT_SAISI');
                $role->attachPermission('ENG_INIT_CLOT');
                $role->attachPermission('ENG_PEG_SAISI');
                $role->attachPermission('ENG_PEG_CLOT');
                $role->attachPermission('ENG_IMP_SAISI');
                $role->attachPermission('ENG_IMP_CLOT');
                $role->attachPermission('ENG_IMP_REG');
            }

            if($role->name === 'administrator') {
                $role->attachPermission('ENG_INIT_VALIDP');
                $role->attachPermission('ENG_INIT_VALIDS');
                $role->attachPermission('ENG_PEG_VALIDP');
                $role->attachPermission('ENG_PEG_VALIDS');
                $role->attachPermission('ENG_IMP_VALIDP');
                $role->attachPermission('ENG_IMP_VALIDS');
            }

            if($role->name === 'superadministrator') {
                $role->attachPermission('ENG_INIT_VALIDF');
                $role->attachPermission('ENG_PEG_VALIDF');
                $role->attachPermission('ENG_IMP_VALIDF');
            }

            if(Config::get('laratrust_seeder.create_users')) {
                $this->command->info("Creating '{$key}' user");
                // Create default user for each role
                $user = \App\Models\User::create([
                    'matricule' => '0000'. $increment,
                    'name' => ucwords(str_replace('_', ' ', $key)).'1',
                    'email' => $key.'1@app.com',
                    'password' => bcrypt('12345'),
                    'saisisseur' => 'NA',
                    'statut_utilisateur' => Config::get('laratrust.constants.user_status.INITIATED'),
                    'valideur' => 'NA',
                    'source'  => Config::get('laratrust.constants.user_creation_source.SEEDER'),
                    'division' => 'DBC',
                    'fonction' => 'cadre'
                ]);
                $user->attachRole($role);
                $increment += 1;
            }

            if(Config::get('laratrust_seeder.create_users')) {
                $this->command->info("Creating '{$key}' user");
                // Create default user for each role
                $user = \App\Models\User::create([
                    'matricule' => '0000'. $increment,
                    'name' => ucwords(str_replace('_', ' ', $key)).'2',
                    'email' => $key.'2@app.com',
                    'password' => bcrypt('12345'),
                    'saisisseur' => 'NA',
                    'statut_utilisateur' => Config::get('laratrust.constants.user_status.INITIATED'),
                    'valideur' => 'NA',
                    'source'  => Config::get('laratrust.constants.user_creation_source.SEEDER'),
                    'division' => 'DBC',
                    'fonction' => 'cadre'
                ]);
                $user->attachRole($role);
                $increment += 1;
            }
        }
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
