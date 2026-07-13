<?php
/**
 * Catalogue des types de documents/services demandables en ligne
 * et métadonnées des champs spécifiques à chaque type.
 */

$DOCUMENT_TYPES = [
    'etat-civil' => [
        'label' => "Demande d'acte(s) d'état civil",
        'delai' => 'Variable selon acte',
        'icon'  => 'file-text',
        'fields' => [],
    ],
    'construire' => [
        'label' => 'Autorisation de construire',
        'delai' => '30 jours',
        'icon'  => 'building-2',
        'fields' => ['adresse_terrain', 'reference_cadastrale', 'superficie', 'nature_travaux'],
    ],
    'cmu' => [
        'label' => 'Couverture Maladie Universelle (CMU)',
        'delai' => '5 – 10 jours',
        'icon'  => 'shield-plus',
        'fields' => ['nombre_personnes_foyer'],
    ],
    'bourse' => [
        'label' => 'Demande de bourse',
        'delai' => '15 jours',
        'icon'  => 'graduation-cap',
        'fields' => ['etablissement', 'niveau_etude'],
    ],
];

$FIELD_META = [
    'pere'                   => ['label' => 'Nom du père', 'type' => 'text'],
    'mere'                   => ['label' => 'Nom de la mère', 'type' => 'text'],
    'conjoint'               => ['label' => "Nom du/de la conjoint(e)", 'type' => 'text'],
    'date_mariage'           => ['label' => 'Date du mariage', 'type' => 'date'],
    'defunt_nom'             => ['label' => 'Nom complet du défunt', 'type' => 'text'],
    'date_deces'             => ['label' => 'Date du décès', 'type' => 'date'],
    'lien_parente'           => ['label' => 'Lien de parenté avec le défunt', 'type' => 'text'],
    'numero_acte_precedent'  => ['label' => "Numéro de l'acte précédent (si connu)", 'type' => 'text'],
    'adresse_terrain'        => ['label' => 'Adresse du terrain', 'type' => 'text'],
    'reference_cadastrale'   => ['label' => 'Référence cadastrale', 'type' => 'text'],
    'superficie'             => ['label' => 'Superficie (m²)', 'type' => 'number'],
    'nature_travaux'         => ['label' => 'Nature des travaux', 'type' => 'textarea'],
    'nombre_personnes_foyer' => ['label' => 'Nombre de personnes dans le foyer', 'type' => 'number'],
    'etablissement'          => ['label' => 'Établissement scolaire/universitaire', 'type' => 'text'],
    'niveau_etude'           => ['label' => "Niveau d'étude", 'type' => 'text'],

    // Formulaire consolidé « Demande d'acte(s) d'état civil »
    'types_actes'            => ['label' => "Types d'actes demandés", 'type' => 'text'],
    'pere_prenom'            => ['label' => 'Prénom du père', 'type' => 'text'],
    'pere_nom'               => ['label' => 'Nom du père', 'type' => 'text'],
    'mere_prenom'            => ['label' => 'Prénom de la mère', 'type' => 'text'],
    'mere_nom'               => ['label' => 'Nom de la mère', 'type' => 'text'],
    'annee_registre'         => ['label' => 'Année du registre', 'type' => 'text'],
    'numero_registre'        => ['label' => 'Numéro dans le registre', 'type' => 'text'],
    'qualite_demandeur'      => ['label' => 'Qualité du demandeur', 'type' => 'text'],
    'mode_delivrance'        => ['label' => 'Mode de délivrance souhaité', 'type' => 'text'],
    'mode_paiement'          => ['label' => 'Mode de paiement', 'type' => 'text'],
    'reference_paiement'     => ['label' => 'Référence de la transaction', 'type' => 'text'],
];

/** Options pour le formulaire consolidé « Demande d'acte(s) d'état civil ». */
$ETAT_CIVIL_ACTES = [
    'naissance-extrait'          => "Extrait d'acte de naissance",
    'naissance-copie'            => "Copie littérale d'acte de naissance",
    'mariage-extrait'            => "Extrait d'acte de mariage",
    'certificat-residence'       => 'Certificat de résidence',
    'certificat-vie-individuelle'=> 'Certificat de vie individuelle',
    'certificat-vie-collective'  => 'Certificat de vie collective',
    'deces'                      => 'Certificat de décès',
];

$ETAT_CIVIL_QUALITES = [
    "Moi-même (personne concernée)",
    "Père",
    "Mère",
    "Tuteur légal",
    "Conjoint(e)",
    "Autre (avec procuration)",
];

$ETAT_CIVIL_MODES_DELIVRANCE = [
    "Retrait au guichet de la mairie",
    "Envoi par e-mail (PDF)",
    "Envoi à domicile (courrier)",
];

$ETAT_CIVIL_MODES_PAIEMENT = [
    'wave'         => 'Wave',
    'orange_money' => 'Orange Money (OM)',
];

$ETAT_CIVIL_PAIEMENT_NUMEROS = [
    'wave'         => '78 352 62 23',
    'orange_money' => '78 352 62 23',
];
