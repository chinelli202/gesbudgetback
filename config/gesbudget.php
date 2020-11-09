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
            'XAF' => ['Franc CFA', 'NA'],
            'USD' => ['Dollar USD', 'NA'],
            'EUR' => ['Euro', 'NA']
        ],
        'nature_engagement'    => [
            'PEG' => ['Pré-engagement', 'NA'],
            'REA' => ['Réalisation directe', 'NA']
        ],
        'type_engagement'      => [
            'BDC' => ['Bon de Commande', 'NA'], 
            'DDM' => ['Demande de mission', 'NA'],
            'DDF' => ['Demande de fond', 'NA'],
            'LDC' => ['Lettre de commande', 'NA'],
            'MAR' => ['Marché', 'NA'],
            'DDP' => ['Demande de prêt', 'NA']
        ],
        'etat_engagement'    => [
            'INIT' => ['Initié', 'NA'],
            'CLOT' => ['Clôturé', 'NA'],
            'PEG' => ['Pré-engagé', 'NA'],
            'IMP' => ['Imputé', 'NA'],
            'REA' => ['Réalisé', 'NA']
        ],
        'statut_engagement' => [
            'VALIDF_NOEXC' => ['Validé au niveau final sans exécution (imputation ou apurement)', 'NA'],
            'SAISI' => ['Saisi', 'NA'],
            'VALIDP' => ['Validé au premier niveau', 'NA'],
            'VALIDS' => ['Validé au second niveau', 'NA'],
            'VALIDF' => ['Validé au niveau final', 'NA']
        ],
        'constante' => [
            'TVA' => ['Taxe sur la valeur ajoutée au Cameroun', '19.25']
        ],
        'actions' => [
            'VALIDP' => ['VALIDP', 'Validation premier niveau'],
            'VALIDS' => ['VALIDS', 'Validation second niveau'],
            'VALIDF' => ['VALIDF', 'Validation niveau final'],
            'UPDATE' => ['UPDATE', 'Mise à jour'],
            'CLOSE' => ['CLOSE', 'Clôture'],
            'RESTORE' => ['RESTORE', 'Restauration'],
            'SEND_BACK' => ['SEND_BACK', 'Renvoi'],
            'RESEND' => ['RESEND', 'Re-soumission'],
            'ADD_COMMENT' => ['ADD_COMMENT', 'Ajout commentaire'],
            'CANCEL_VALIDATION' => ['CANCEL_VALIDATION', 'Annulation validation'],
            'IMPUTER' => ['IMPUTER', 'Imputation'],
            'APURER' => ['APURER', 'Apurement']
        ]
    ]
];