<?php

namespace App\Services;


use App\Models\Apurement;
use App\Models\User;
use App\Models\Variable;

class ApurementService {

  public function __construct(){

  }
  public const ApurementCreateValidator = [
      'engagement_id'     =>          'required|exists:engagements,code',
      'reference_paiement'=>          'required',
      'libelle'           =>          'required',
      'montant_ttc'       =>          'required',
      'devise'            =>          'required|exists:variables,code',
      'observations'      =>          'required'
  ];

  public static function enrichApurement($apurementId) {
      $apurement = Apurement::findOrFail($apurementId);
      $saisisseur = User::where('matricule', $apurement->saisisseur)->first();
      $valideurP = User::where('matricule', $apurement->valideur_first)->first();
      $valideurS = User::where('matricule', $apurement->valideur_second)->first();
      $valideurF = User::where('matricule', $apurement->valideur_final)->first();

      $devise = Variable::where('code', $apurement->devise)->first();
      $type = Variable::where('code', $apurement->type)->first();
      $etat = Variable::where('code', $apurement->etat)->first();
      $statut = Variable::where('code', $apurement->statut)->first();

      $apurement["saisisseur_name"] = $saisisseur->name;
      $apurement["valideurp_name"] = $valideurP->name ?? '';
      $apurement["valideurs_name"] = $valideurS->name ?? '';
      $apurement["valideurf_name"] = $valideurF->name ?? '';

      $apurement["devise_libelle"] = $devise->libelle ?? '';
      $apurement["etat_libelle"] = $etat->libelle ?? '';
      $apurement["statut_libelle"] = $statut->libelle ?? '';
      return $apurement;
  }
}