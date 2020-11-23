<?php


$service = new App\Services\RecapService();
$params = new stdClass();
$critere = 'jour';
$params->jour = "2020-06-12";
$params->section = "Dépenses";
$params->domaine = "Fonctionnement";
$service->getRecapSection($critere, $params);