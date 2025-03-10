<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => "Отображение сделок",
    "DESCRIPTION" => "Компонент отображения сделок",
	"PATH" => array(
        "NAME"=>"Список сделок",
		"ID" => "deal.list",
		"CHILD" => array(
			"ID" => "deal.list",
			"NAME" => "Список сделок"
		)
	),
);