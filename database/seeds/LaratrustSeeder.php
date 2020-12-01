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
            'code' => 'INIT.SAISI',
            'name' => 'Saisir un préengagement',
            'description' => 'Saisir un préengagement',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'INIT.VALIDP',
            'to_perform_on' => 'INIT.SAISI',
            'name' => 'Valider un préengagement au 1er niveau',
            'description' => 'Valider un préengagement au 1er niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'INIT.VALIDS',
            'to_perform_on' => 'INIT.VALIDP',
            'name' => 'Valider un préengagement au 2nd niveau',
            'description' => 'Valider un préengagement au 2nd niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'INIT.VALIDF',
            'to_perform_on' => 'INIT.VALIDS',
            'name' => 'Valider un préengagement au niveau final',
            'description' => 'Valider un préengagement au niveau final',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'INIT.CLOT',
            'name' => 'Clôturer un préengagement',
            'description' => 'Clôturer un préengagement. Un préengagement ne peut être clôturé que lorsqu\'il n\'a pas encore été validé au niveau final.',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);
        
        /** Permissions pour les engagements engagés */
        $perm = \App\Models\Permission::create([
            'code' => 'PEG.SAISI',
            'name' => 'Saisir une imputation',
            'description' => 'Saisir une imputation',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'PEG.VALIDP',
            'to_perform_on' => 'PEG.SAISI',
            'name' => 'Valider une imputation au 1er niveau',
            'description' => 'Valider une imputation au 1er niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'PEG.VALIDS',
            'to_perform_on' => 'PEG.VALIDP',
            'name' => 'Valider une imputation au 2nd niveau',
            'description' => 'Valider une imputation au 2nd niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'PEG.VALIDF',
            'to_perform_on' => 'PEG.VALIDS',
            'name' => 'Valider une imputation au niveau final',
            'description' => 'Valider une imputation au niveau final',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'PEG.CLOT',
            'name' => 'Clôturer une imputation',
            'description' => 'Clôturer une imputation. Une imputation ne peut être clôturé que lorsqu\'elle n\'a pas encore été validé au niveau final.',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        /** Permissions pour les engagements imputés */
        $perm = \App\Models\Permission::create([
            'code' => 'IMP.SAISI',
            'name' => 'Saisir un apurement',
            'description' => 'Saisir un apurement',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'IMP.VALIDP',
            'to_perform_on' => 'IMP.SAISI',
            'name' => 'Valider un apurement au 1er niveau',
            'description' => 'Valider un apurement au 1er niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'IMP.VALIDS',
            'to_perform_on' => 'IMP.VALIDP',
            'name' => 'Valider un apurement au 2nd niveau',
            'description' => 'Valider un apurement au 2nd niveau',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'IMP.VALIDF',
            'to_perform_on' => 'IMP.VALIDS',
            'name' => 'Valider un apurement au niveau final',
            'description' => 'Valider un apurement au niveau final',
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'IMP.CLOT',
            'name' => 'Clôturer un apurement',
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
                $role->attachPermission('INIT.SAISI');
                $role->attachPermission('INIT.CLOT');
                $role->attachPermission('PEG.SAISI');
                $role->attachPermission('PEG.CLOT');
                $role->attachPermission('IMP.SAISI');
                $role->attachPermission('IMP.CLOT');
                $role->attachPermission('IMP.REG');
            }

            if($role->name === 'administrator') {
                $role->attachPermission('INIT.VALIDP');
                $role->attachPermission('INIT.VALIDS');
                $role->attachPermission('PEG.VALIDS');
                $role->attachPermission('PEG.VALIDS');
                $role->attachPermission('IMP.VALIDS');
                $role->attachPermission('IMP.VALIDS');
            }

            if($role->name === 'superadministrator') {
                // $role->attachPermission('INIT.VALIDF');
                $role->attachPermission('PEG.VALIDF');
                $role->attachPermission('IMP.VALIDF');
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
