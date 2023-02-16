<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2023 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

use Glpi\Csv\CsvResponse;
use Glpi\Csv\StatCsvExport;
use Glpi\Http\Response;
use Glpi\Stat\StatData;

include('../inc/includes.php');

// Check rights
Session::checkRight("statistic", READ);

// Read params
$statdata_itemtype = $_UGET['statdata_itemtype'] ?? null;

// Validate stats itemtype
if (!is_a($statdata_itemtype, StatData::class, true)) {
    Response::sendError(400, "Invalid stats itemtype", Response::CONTENT_TYPE_TEXT_PLAIN);
}

// Get data and output csv
$graph_data = new $statdata_itemtype($_GET);
date_default_timezone_set("America/Lima");
$current_time = date("Y-m-d H:i:s"); 

if($_SESSION['n_graphreports_generated'] == 10){
    $_SESSION['n_graphreports_generated'] = 0;    
    $_SESSION['until_waited_datetime_graph'] = date("H:i:s", strtotime($current_time.' +180 seconds')); 
}

if($_SESSION['total_graphreports_generated'] == 20){
    $_SESSION['total_graphreports_generated'] = 0;
    Session::cleanOnLogout();
    Session::redirectIfNotLoggedIn();
    exit();
}

if(strtotime(date("Y-m-d H:i:s")) < strtotime($_SESSION['until_waited_datetime_graph'])){
    $dateObject = new DateTime($_SESSION['until_waited_datetime_graph']);
    $msg_redirect = "Generaste/Descargaste muchos reportes, espera hasta las ".$dateObject->format('h:i:s A')." para volver a intentarlo.";

    Session::addMessageAfterRedirect($msg_redirect,false,INFO,true);
    Session::addMessageAfterRedirect('10 generaciones de reportes más y se cerrará la sesión.',false,WARNING,false);
    Html::back();
}else{
    $_SESSION['n_graphreports_generated'] =  $_SESSION['n_graphreports_generated'] + 1;
    $_SESSION['total_graphreports_generated'] =  $_SESSION['total_graphreports_generated'] + 1;

    CsvResponse::output(
        new StatCsvExport($graph_data->getSeries(), $graph_data->getOptions())
    );
}

