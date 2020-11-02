<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use App\Models\User;

class VariablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->truncateTables();
        $variables = Config::get('app_seeder.variables');
        foreach($variables as $keyvar => $codes){
            foreach($codes as $keycode => $libelle){
                $variable = \App\Models\Variable::firstOrCreate([
                    'cle' => strtoupper($keyvar),
                    'code' => strtoupper($keycode),
                    'libelle' => $libelle[0],
                    'valeur' => $libelle[1],
                    'saisisseur' => User::find(3)->matricule,
                    'source' => Config::get('laratrust.constants.user_creation_source.SEEDER')
                ]);

                $this->command->info('Creating variable \''. $variable->cle . '\'-\''. $variable->code . '\'-\''. $variable->libelle[0] . '\'');
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
        $this->command->info('Truncate de la table Variables');
        Schema::disableForeignKeyConstraints();

        DB::table('variables')->truncate();
        \App\Models\Variable::truncate();

        Schema::enableForeignKeyConstraints();
    }
}
