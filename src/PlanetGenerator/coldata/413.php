<?php

use Stu\PlanetGenerator\PlanetGenerator;

$data[PlanetGenerator::COLGEN_DETAILS] = "Klasse H - Basisklasse Wüste";

$bonusdata = array(PlanetGenerator::BONUS_AENERGY, PlanetGenerator::BONUS_AENERGY, PlanetGenerator::BONUS_HABITAT);

$data[PlanetGenerator::CONFIG_COLGEN_SIZEW] = 7;
$data[PlanetGenerator::CONFIG_COLGEN_SIZEH] = 5;

$hasground = 0;

$data[PlanetGenerator::COLGEN_BASEFIELD] = 401;
$odata[PlanetGenerator::COLGEN_BASEFIELD] = 900;
$udata[PlanetGenerator::COLGEN_BASEFIELD] = 802;

$phases = 0;
$ophases = 0;
$uphases = 0;


// config

$felsen = rand(18, 24);
$berge = rand(8, 10);
$dunes = rand(6, 8);

$erde = rand(6, 10);


// Surface Phases

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Fels";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $felsen;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "401");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "713");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 10;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "normal";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Berge";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $berge;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array("0" => "713");
$phase[$phases][PlanetGenerator::COLGEN_TO] = array("0" => "703");
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 5;
$phases++;

$phase[$phases][PlanetGenerator::COLGEN_MODE] = "nocluster";
$phase[$phases][PlanetGenerator::COLGEN_DESCRIPTION] = "Dünen";
$phase[$phases][PlanetGenerator::COLGEN_NUM] = $dunes;
$phase[$phases][PlanetGenerator::COLGEN_FROM] = array(401, 713);
$phase[$phases][PlanetGenerator::COLGEN_TO] = array(403, 404);
$phase[$phases][PlanetGenerator::COLGEN_ADJACENT] = array(401, 403, 404);
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$phase[$phases][PlanetGenerator::COLGEN_FRAGMENTATION] = 50;
$phases++;


$uphase[$uphases][PlanetGenerator::COLGEN_MODE] = "normal";
$uphase[$uphases][PlanetGenerator::COLGEN_DESCRIPTION] = "Erde";
$uphase[$uphases][PlanetGenerator::COLGEN_NUM] = $erde;
$uphase[$uphases][PlanetGenerator::COLGEN_FROM] = array(802);
$uphase[$uphases][PlanetGenerator::COLGEN_TO] = array(801);
$uphase[$uphases][PlanetGenerator::COLGEN_ADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_NOADJACENTLIMIT] = 0;
$uphase[$uphases][PlanetGenerator::COLGEN_FRAGMENTATION] = 15;
$uphases++;

return [
    $odata,
    $data,
    $udata,
    [],
    $phase,
    $uphase,
    $hasground
];
