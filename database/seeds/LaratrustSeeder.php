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

        $perm = \App\Models\Permission::create([
            'code' => 'saisir-pre-engagement',
            'name' => 'saisir-pre-engagement',
            'description' => implode(" ", explode("-", 'saisir-pre-engagement')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'validerp-pre-engagement',
            'name' => 'validerp-pre-engagement',
            'description' => implode(" ", explode("-", 'validerp-pre-engagement')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'validers-pre-engagement',
            'name' => 'validers-pre-engagement',
            'description' => implode(" ", explode("-", 'validers-pre-engagement')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'validerf-pre-engagement',
            'name' => 'validerf-pre-engagement',
            'description' => implode(" ", explode("-", 'validerf-pre-engagement')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'cloturer-pre-engagement',
            'name' => 'cloturer-pre-engagement',
            'description' => implode(" ", explode("-", 'cloturer-pre-engagement')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'validerp-cloture-preg',
            'name' => 'validerp-cloture-preg',
            'description' => implode(" ", explode("-", 'validerp-cloture-preg')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'validers-cloture-preg',
            'name' => 'validers-cloture-preg',
            'description' => implode(" ", explode("-", 'validers-cloture-preg')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'validerf-cloture-preg',
            'name' => 'validerf-cloture-preg',
            'description' => implode(" ", explode("-", 'validerf-cloture-preg')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'saisir-imputation',
            'name' => 'saisir-imputation',
            'description' => implode(" ", explode("-", 'saisir-imputation')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'validerp-imputation',
            'name' => 'validerp-imputation',
            'description' => implode(" ", explode("-", 'validerp-imputation')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'validers-imputation',
            'name' => 'validers-imputation',
            'description' => implode(" ", explode("-", 'validers-imputation')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'validerf-imputation',
            'name' => 'validerf-imputation',
            'description' => implode(" ", explode("-", 'validerf-imputation')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'saisir-apurement',
            'name' => 'saisir-apurement',
            'description' => implode(" ", explode("-", 'saisir-apurement')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'validerp-apurement',
            'name' => 'validerp-apurement',
            'description' => implode(" ", explode("-", 'validerp-apurement')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'validers-apurement',
            'name' => 'validers-apurement',
            'description' => implode(" ", explode("-", 'validers-apurement')),
            'saisisseur' => 'NA',
            'valideur' => 'NA',
            'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
        ]);

        $perm = \App\Models\Permission::create([
            'code' => 'validerf-apurement',
            'name' => 'validerf-apurement',
            'description' => implode(" ", explode("-", 'validerf-apurement')),
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
                $role->attachPermission('saisir-pre-engagement');
                $role->attachPermission('cloturer-pre-engagement');
                $role->attachPermission('saisir-imputation');
                $role->attachPermission('saisir-apurement');
            }

            if($role->name === 'administrator') {
                $role->attachPermission('validerp-pre-engagement');
                $role->attachPermission('validers-pre-engagement');
                $role->attachPermission('validerp-cloture-preg');
                $role->attachPermission('validers-cloture-preg');
                $role->attachPermission('validerp-imputation');
                $role->attachPermission('validers-imputation');
                $role->attachPermission('validerp-apurement');
                $role->attachPermission('validers-apurement');
            }

            if($role->name === 'superadministrator') {
                $role->attachPermission('validerf-pre-engagement');
                $role->attachPermission('validerf-cloture-preg');
                $role->attachPermission('validerf-imputation');
                $role->attachPermission('validerf-apurement');
            }

            if(Config::get('laratrust_seeder.create_users')) {
                $this->command->info("Creating '{$key}' user");
                // Create default user for each role
                $user = \App\Models\User::create([
                    'matricule' => '0000'. $increment,
                    'name' => ucwords(str_replace('_', ' ', $key)),
                    'email' => $key.'@app.com',
                    'password' => bcrypt('12345'),
                    'saisisseur' => 'NA',
                    'statut_utilisateur' => Config::get('laratrust.constants.user_status.INITIATED'),
                    'valideur' => 'NA',
                    'source'  => Config::get('laratrust.constants.user_creation_source.SEEDER'),
                    'division' => 'DBC',
                    'fonction' => 'cadre'
                ]);
                $user->attachRole($role);
            }
            $increment += 1;
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
