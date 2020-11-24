<?php

return [
    /*
    |--------------------------------------------------------------------------
    | App utils
    |--------------------------------------------------------------------------
    |
    | Set of variables that'll be used in the application.
    |
    */

    'variables' => [
        'devise'               => [
            'XAF' => ['Franc CFA', 'XAF'],
            'USD' => ['Dollar USD', 'USD'],
            'EUR' => ['Euro', 'EUR']
        ],
        'nature_engagement'    => [
            'PEG' => ['Pré-engagement', 'PEG'],
            'REA' => ['Réalisation directe', 'REA']
        ],
        'type_engagement'      => [
            'BDC' => ['Bon de Commande', 'BDC'], 
            'DDM' => ['Demande de mission', 'DDM'],
            'DDF' => ['Demande de fond', 'DDF'],
            'LDC' => ['Lettre de commande', 'LDC'],
            'MAR' => ['Marché', 'MAR'],
            'DDP' => ['Demande de prêt', 'DDP']
        ],
        'etat_engagement'    => [
            'INIT' => ['Initié', 'INIT'],
            'PEG' => ['Engagé', 'PEG'],
            'IMP' => ['Imputé', 'IMP'],
            'REA' => ['Apuré', 'REA'],
            'CLOT' => ['Clôturé', 'CLOT']
        ],
        'statut_engagement' => [
            // 'VALIDF_NOEXC' => ['Validé au niveau final sans exécution (imputation ou apurement)', 'NA'],
            'SAISI' => ['Saisi', 'SAISI'],
            'VALIDP' => ['Validé au premier niveau', 'VALIDP'],
            'VALIDS' => ['Validé au second niveau', 'VALIDS'],
            'VALIDF' => ['Validé au niveau final', 'VALIDF']
        ],
        'operateur' => [
            'saisisseur' => ['Saisiseur', 'saisisseur'],
            'valideur_first' => ['Valideur au premier niveau', 'valideur_first'],
            'valideur_second' => ['Valideur au second niveau', 'valideur_second'],
            'valideur_final' => ['Valideur au niveau final', 'valideur_final']
        ],
        'constante' => [
            'TVA' => ['Taxe sur la valeur ajoutée au Cameroun', '19.25']
        ],
        'actions' => [
            'VALIDP' => ['Validation premier niveau', 'VALIDP'],
            'VALIDS' => ['Validation second niveau', 'VALIDS'],
            'VALIDF' => ['Validation niveau final', 'VALIDF'],
            'CANCEL_VALIDP' => ['Annulation validation premier niveau', 'CANCEL_VALIDP'],
            'CANCEL_VALIDS' => ['Annulation validation second niveau', 'CANCEL_VALIDS'],
            'CANCEL_VALIDF' => ['Annulation validation niveau final', 'CANCEL_VALIDF'],
            'UPDATE' => ['Mise à jour', 'UPDATE'],
            'CLOSE' => ['Clôture', 'CLOSE'],
            'RESTORE' => ['Restauration', 'RESTORE'],
            'SEND_BACK' => ['Renvoi', 'SEND_BACK'],
            'RESEND' => ['Re-soumission', 'RESEND'],
            'ADD_COMMENT' => ['Ajout commentaire', 'ADD_COMMENT'],

            'PREENGAGER' => ['Pré engagement', 'PREENGAGER'],
            'IMPUTER' => ['Imputation', 'IMPUTATION'],
            'APURER' => ['Apurement', 'APUREMENT'],

            'IMP_VALIDP' => ['Validation premier niveau (Imputation)', 'IMP_VALIDP'],
            'IMP_VALIDS' => ['Validation second niveau (Imputation)', 'IMP_VALIDS'],
            'IMP_VALIDF' => ['Validation niveau final (Imputation)', 'IMP_VALIDF'],
            'IMP_CANCEL_VALIDP' => ['Annulation validation premier niveau (Imputation)', 'IMP_CANCEL_VALIDP'],
            'IMP_CANCEL_VALIDS' => ['Annulation validation second niveau (Imputation)', 'IMP_CANCEL_VALIDS'],
            'IMP_CANCEL_VALIDF' => ['Annulation validation niveau final (Imputation)', 'IMP_CANCEL_VALIDF'],
            'IMP_UPDATE' => ['Mise à jour (Imputation)', 'IMP_UPDATE'],
            'IMP_CLOSE' => ['Clôture (Imputation)', 'IMP_CLOSE'],
            'IMP_RESTORE' => ['Restauration (Imputation)', 'IMP_RESTORE'],
            'IMP_SEND_BACK' => ['Renvoi (Imputation)', 'IMP_SEND_BACK'],
            'IMP_RESEND' => ['Re-soumission (Imputation)', 'IMP_RESEND'],
            'IMP_ADD_COMMENT' => ['Ajout commentaire (Imputation)', 'IMP_ADD_COMMENT'],
            
            'REA_VALIDP' => ['Validation premier niveau (Réalisation)', 'REA_VALIDP'],
            'REA_VALIDS' => ['Validation second niveau (Réalisation)', 'REA_VALIDS'],
            'REA_VALIDF' => ['Validation niveau final (Réalisation)', 'REA_VALIDF'],
            'REA_CANCEL_VALIDP' => ['Annulation validation premier niveau (Réalisation)', 'REA_CANCEL_VALIDP'],
            'REA_CANCEL_VALIDS' => ['Annulation validation second niveau (Réalisation)', 'REA_CANCEL_VALIDS'],
            'REA_CANCEL_VALIDF' => ['Annulation validation niveau final (Réalisation)', 'REA_CANCEL_VALIDF'],
            'REA_UPDATE' => ['Mise à jour (Réalisation)', 'REA_UPDATE'],
            'REA_CLOSE' => ['Clôture (Réalisation)', 'REA_CLOSE'],
            'REA_RESTORE' => ['Restauration (Réalisation)', 'REA_RESTORE'],
            'REA_SEND_BACK' => ['Renvoi (Réalisation)', 'REA_SEND_BACK'],
            'REA_RESEND' => ['Re-soumission (Réalisation)', 'REA_RESEND'],
            'REA_ADD_COMMENT' => ['Ajout commentaire (Réalisation)', 'REA_ADD_COMMENT'],
            
        ],
        'source' => [
            'API' => ['API', 'API'],
            'ADMIN_UI' => ['AdminUI', 'ADMIN_UI'],
            'SEEDER' => ['Seeder', 'SEEDER']
        ]
    ]
];