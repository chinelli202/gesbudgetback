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
            'XAF' => 'Franc CFA',
            'USD' => 'Dollar USD',
            'EUR' => 'Euro'
        ],
        'nature_engagement'    => [
            'PEG' => 'Pré-engagement',
            'REA' => 'realisation'
        ],
        'type_engagement'      => [
            'BDC' => 'Bon de Commande', 
            'DDM' => 'Demande de mission',
            'DDF' => 'Demande de fond',
            'LDC' => 'Lettre de commande',
            'MAR' => 'Marché',
            'DDP' => 'Demande de prêt'
        ],
        'etat_engagement'    => [
            'INIT' => 'Initié',
            'CLOT' => 'Clôturé',
            'PEG' => 'Pré-engagé',
            'IMP' => 'Imputé',
            'REA' => 'Réalisé'
        ],
        'statut_engagement' => [
            'VALIDF_NOEXC' => 'Validé au niveau final sans exécution (imputation ou apurement)',
            'SAISI' => 'Saisi',
            'VALIDP' => 'Validé au premier niveau',
            'VALIDS' => 'Validé au second niveau',
            'VALIDF' => 'Validé au niveau final'
        ]
    ]
];