<?php

return [
    'grandesrubriques' => ['DEPENSES','RECETTES'],
    'rubriquesdepense' => ["DEPENSES FONCTIONNEMENT","DEPENSES INVESTISSEMENT"],
    'rubriquesdfonctionnement' => [
        'A - Charges de personnel',
        'B - Missions ',
        'C- Diverses Représentations',
        'D - Charges diverses de fonctionnement',
        'E - Honoraires',
        'F - Dons - subventions',
        'G - Formation',
        'H - Imprévus'
    ],
    'rubriquesdinvestissement'=> ["A - Equipement-Immobilisation","B - Dépenses d'Hydrocarbures","C - Investissements financiers"],
    'rubriquesrecettes' => ["A - Produits Financiers",
        "B - Remboursements prêts",
        "C - Recettes pétrolières",
        "D - Recettes de la Barge Rio Del Rey",
        "E - Autres recettes"
    ],
    'types' => [
        'type1' => 'ban_gr',
        'type2' => 'ban_gl',
        'type3' => 'ban_titre_gl',
        'type4' => 'ti_chap_rub',
        'type5' => 'chap_rub',
        'type6' => 'chap_rub_sous_chap'
    ],
    'tableheader' => [
        'prevision' => 'LIBELLES',
        'prevision_label' => 'Prévisions 2020 (1)',
        'realisations_mois_label' => 'Réalisations du mois de juin 2020  (2)',
        'realisations_precedentes_label' => 'Réalisations précédentes 2020 (3)',
        'realisations_cumulees_label' => 'Réalisations cumulées au 30/06/2020 (4)',
        'engagements_mois_label' => 'Engagements au 30/06/2020 (5)',
        'execution_mois_label' => 'Exécution au 30/06/2020 (6)',
        'solde_label' => 'Solde (7)',
        'taux_execution_label' => 'Taux d\'exécution (8)'
    ]
];