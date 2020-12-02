<?php

namespace App\Services;


use App\Models\Ligne;
use App\Models\Engagement;
use App\Models\User;
use App\Models\Variable;

class LigneService {

  public function __construct(){

  }

  public static function getSolde($ligneId) {
      /** get the ligne amount */
      $ligneMontant = Ligne::findOrFail($ligneId)->montant;

      /** get the amount of imputation from engagement */
      $engagements = Engagement::where('ligne_id', $ligneId)
        ->get();
      
      $imputationsMontant = 0;
      foreach ($engagements as $engagement) {
        $imputationsMontant += $engagement->cumul_imputations;
      }

      /** subtract the two */
      return [
        'montant_ligne' => $ligneMontant,
        'montant_imputations' => $imputationsMontant,
        'solde_restant' => $ligneMontant - $imputationsMontant
      ];
  }
}