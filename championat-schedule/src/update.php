#!/usr/local/bin/php -q
<?php

$start = microtime(true);

$_SERVER["DOCUMENT_ROOT"] = ''; // DOCUMENT_ROOT
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
define("NO_AGENT_CHECK", true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
set_time_limit(0);


CModule::IncludeModule('iblock');

$res = CIBlockElement::GetList(array(),array("IBLOCK_ID" =>$IBLOCK_ID),false,false, array("ID"));
while ($clear = $res->GetNext()) {
   CIBlockElement::Delete($clear['ID']);
}


$type = array ($football_sec=>"Футбол", $hockey_sec =>"Хоккей", $basketball_sec =>"Баскетбол" );

$el = new CIBlockElement;

$json = file_get_contents('output.json');
$json = json_decode($json, true);


foreach ($json as $event) {


    $now = date('d.m.Y H:i');
    $date_start = trim($event['date']);
    $date = new DateTime($date_start);
    $date->modify('+3 hours');
    $date_end = $date->format('d.m.Y H:i');


    $PROP = array();

    $PROP['type'] = array_search($event['sport'], $type);
    
    $PROP['tournament'] = $event['tournament'];

    if (strtotime($now) > strtotime($date_start) && strtotime($now) < strtotime($date_end))
        $PROP['live'] = 30; // Свойство типа список, метка прямой трансляции

    $name = $event['team1'].' - '.$event['team2'];
    $code = "";

    $arLoadProductArray = Array(
        "DATE_ACTIVE_FROM" => $date_start,
        "DATE_ACTIVE_TO" => $date_end,
        "IBLOCK_SECTION_ID" => false,
        "IBLOCK_ID" => $IBLOCK_ID,
        "NAME" => $name,
        "CODE" => $code,
        "PROPERTY_VALUES" => $PROP,
        "ACTIVE" => "Y"            
    );

    if($PRODUCT_ID = $el->Add($arLoadProductArray))
        echo "New ID: ".$PRODUCT_ID;
    else
        echo "Error: ".$el->LAST_ERROR;


}
