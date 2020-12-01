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
            'code' => 'INIT_SAISI',
            'name' => 'INIT_SAISI',
            'display_name' => 'Saisir un préengagement',
            'description' => 'Saisir un préengagement',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'INIT_VALIDP',
            'name' => 'INIT_VALIDP',
            'to_perform_on' => 'INIT_SAISI',
            'display_name' => 'Valider un préengagement au 1er niveau',
            'description' => 'Valider un préengagement au 1er niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'INIT_VALIDS',
            'name' => 'INIT_VALIDS',
            'to_perform_on' => 'INIT_VALIDP',
            'display_name' => 'Valider un préengagement au 2nd niveau',
            'description' => 'Valider un préengagement au 2nd niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'INIT_VALIDF',
            'name' => 'INIT_VALIDF',
            'to_perform_on' => 'INIT_VALIDS',
            'display_name' => 'Valider un préengagement au niveau final',
            'description' => 'Valider un préengagement au niveau final',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'INIT_CLOT',
            'name' => 'INIT_CLOT',
            'display_name' => 'Clôturer un préengagement',
            'description' => 'Clôturer un préengagement. Un préengagement ne peut être clôturé que lorsqu\'il n\'a pas encore été validé au niveau final.',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);
        
        /** Permissions pour les engagements engagés */
        $perm = \App\Models\Permission::create([
            'code' => 'PEG_SAISI',
            'name' => 'PEG_SAISI',
            'display_name' => 'Saisir une imputation',
            'description' => 'Saisir une imputation',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'PEG_VALIDP',
            'name' => 'PEG_VALIDP',
            'to_perform_on' => 'PEG_SAISI',
            'display_name' => 'Valider une imputation au 1er niveau',
            'description' => 'Valider une imputation au 1er niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'PEG_VALIDS',
            'name' => 'PEG_VALIDS',
            'to_perform_on' => 'PEG_VALIDP',
            'display_name' => 'Valider une imputation au 2nd niveau',
            'description' => 'Valider une imputation au 2nd niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'PEG_VALIDF',
            'name' => 'PEG_VALIDF',
            'to_perform_on' => 'PEG_VALIDS',
            'display_name' => 'Valider une imputation au niveau final',
            'description' => 'Valider une imputation au niveau final',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'PEG_CLOT',
            'name' => 'PEG_CLOT',
            'display_name' => 'Clôturer une imputation',
            'description' => 'Clôturer une imputation. Une imputation ne peut être clôturé que lorsqu\'elle n\'a pas encore été validé au niveau final.',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        /** Permissions pour les engagements imputés */
        $perm = \App\Models\Permission::create([
            'code' => 'IMP_SAISI',
            'name' => 'IMP_SAISI',
            'display_name' => 'Saisir un apurement',
            'description' => 'Saisir un apurement',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'IMP_VALIDP',
            'name' => 'IMP_VALIDP',
            'to_perform_on' => 'IMP_SAISI',
            'display_name' => 'Valider un apurement au 1er niveau',
            'description' => 'Valider un apurement au 1er niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'IMP_VALIDS',
            'name' => 'IMP_VALIDS',
            'to_perform_on' => 'IMP_VALIDP',
            'display_name' => 'Valider un apurement au 2nd niveau',
            'description' => 'Valider un apurement au 2nd niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'IMP_VALIDF',
            'name' => 'IMP_VALIDF',
            'to_perform_on' => 'IMP_VALIDS',
            'display_name' => 'Valider un apurement au niveau final',
            'description' => 'Valider un apurement au niveau final',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'IMP_CLOT',
            'name' => 'IMP_CLOT',
            'display_name' => 'Clôturer un apurement',
            'description' => 'Clôturer un apurement. Un apurment ne peut être clôturé que lorsqu\'elle n\'a pas encore été validé au niveau final.',
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
                $role->attachPermission('INIT_SAISI');
                $role->attachPermission('INIT_CLOT');
                $role->attachPermission('PEG_SAISI');
                $role->attachPermission('PEG_CLOT');
                $role->attachPermission('IMP_SAISI');
                $role->attachPermission('IMP_CLOT');
                $role->attachPermission('IMP_REG');
            }

            if($role->name === 'administrator') {
                $role->attachPermission('INIT_VALIDP');
                $role->attachPermission('INIT_VALIDS');
                $role->attachPermission('PEG_VALIDP');
                $role->attachPermission('PEG_VALIDS');
                $role->attachPermission('IMP_VALIDP');
                $role->attachPermission('IMP_VALIDS');
            }

            if($role->name === 'superadministrator') {
                $role->attachPermission('INIT_VALIDF');
                $role->attachPermission('PEG_VALIDF');
                $role->attachPermission('IMP_VALIDF');
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
