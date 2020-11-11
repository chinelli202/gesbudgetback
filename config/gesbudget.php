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
            'CLOT' => ['Clôturé', 'CLOT'],
            'PEG' => ['Pré-engagé', 'PEG'],
            'IMP' => ['Imputé', 'IMP'],
            'REA' => ['Réalisé', 'REA']
        ],
        'statut_engagement' => [
            // 'VALIDF_NOEXC' => ['Validé au niveau final sans exécution (imputation ou apurement)', 'NA'],
            'SAISI' => ['Saisi', 'SAISI'],
            'VALIDP' => ['Validé au premier niveau', 'VALIDP'],
            'VALIDS' => ['Validé au second niveau', 'VALIDS'],
            'VALIDF' => ['Validé au niveau final', 'VALIDF']
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
            'APURER' => ['Apurement', 'APUREMENT']
        ]
    ]
];