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

        foreach ($config as $key => $modules) {

            // Create a new role
            $role = \App\Models\Role::firstOrCreate([
                'name' => $key,
                'display_name' => ucwords(str_replace('_', ' ', $key)),
                'description' => ucwords(str_replace('_', ' ', $key)),
                'saisisseur' => Config::get('laratrust.constants.user_creation_source.SEEDER'),
                'valideur' => 'NA',
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
                        'saisisseur' => Config::get('laratrust.constants.user_creation_source.SEEDER'),
                        'valideur' => 'NA',
                    ])->id;

                    $this->command->info('Creating Permission to '.$permissionValue.' for '. $module);
                }
            }

            // Attach all permissions to the role
            $role->permissions()->sync($permissions);

            if(Config::get('laratrust_seeder.create_users')) {
                $this->command->info("Creating '{$key}' user");
                // Create default user for each role
                $user = \App\Models\User::create([
                    'matricule' => '0000'. $increment,
                    'name' => ucwords(str_replace('_', ' ', $key)),
                    'email' => $key.'@app.com',
                    'password' => bcrypt('12345'),
                    'saisisseur' => Config::get('laratrust.constants.user_creation_source.SEEDER'),
                    'statut_utilisateur' => Config::get('laratrust.constants.user_status.INITIATED'),
                    'valideur' => 'NA',
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
