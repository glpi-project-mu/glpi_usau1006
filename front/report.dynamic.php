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

include('../inc/includes.php');

if (!isset($_GET['item_type']) || !is_string($_GET['item_type']) || !is_a($_GET['item_type'], CommonGLPI::class, true)) {
    return;
}

$itemtype = $_GET['item_type'];
Session::checkRight($itemtype::$rightname, READ);

function checkRightsToGenerateReport():bool{
    date_default_timezone_set("America/Lima");
    $current_time = date("Y-m-d H:i:s"); 

    if($_SESSION['n_reports_generated'] == 10){
        $_SESSION['n_reports_generated'] = 0;

        
        $_SESSION['until_waited_datetime'] = date("H:i:s", strtotime($current_time.' +180 seconds')); 
    }

    if($_SESSION['total_reports_generated'] == 20){
        $_SESSION['total_reports_generated'] = 0;
        Session::cleanOnLogout();
        Session::redirectIfNotLoggedIn();
        exit();
    }

    /*Toolbox::logInFile(
        'report_dynamic_times',
        sprintf(
            __('%1$s: %2$s'),
            basename(__FILE__,'.php'),
            sprintf(
                __('Tiempo actual %s, Tiempo hasta esperar: %s, Total reportes generados: %s') . "\n",
                date("Y-m-d H:i:s"),
                $_SESSION['until_waited_datetime'],
                $_SESSION['n_reports_generated']
            )
        )
    );*/

    if(strtotime(date("Y-m-d H:i:s")) < strtotime($_SESSION['until_waited_datetime'])){
        $dateObject = new DateTime($_SESSION['until_waited_datetime']);
        $msg_redirect = "Generaste/Descargaste muchos reportes, espera hasta las ".$dateObject->format('h:i:s A')." para volver a intentarlo";

        
        Session::addMessageAfterRedirect($msg_redirect,false,INFO,true);
        Session::addMessageAfterRedirect('10 generaciones de reportes más y se cerrará la sesión.',false,WARNING,false);
        Html::back();
        return false; 
    }else{
        $_SESSION['n_reports_generated'] =  $_SESSION['n_reports_generated'] + 1;
        $_SESSION['total_reports_generated'] =  $_SESSION['total_reports_generated'] + 1;
        return true;
    }
}

if (isset($_GET["display_type"])) {
    if ($_GET["display_type"] < 0) {
        $_GET["display_type"] = -$_GET["display_type"];
        $_GET["export_all"]   = 1;
    }

    switch ($itemtype) {
        case 'KnowbaseItem':
            if (checkRightsToGenerateReport()) {
                KnowbaseItem::showList($_GET, $_GET["is_faq"]);
            }
            break;

        case 'Stat':
            if (isset($_GET["item_type_param"])) {
                $params = Toolbox::decodeArrayFromInput($_GET["item_type_param"]);
                if(checkRightsToGenerateReport()){
                    switch ($params["type"]) {
                        case "comp_champ":
                            $val = Stat::getItems(
                                $_GET["itemtype"],
                                $params["date1"],
                                $params["date2"],
                                $params["dropdown"]
                            );
                            Stat::showTable(
                                $_GET["itemtype"],
                                $params["type"],
                                $params["date1"],
                                $params["date2"],
                                $params["start"],
                                $val,
                                $params["dropdown"]
                            );
                            break;
    
                        case "device":
                            $val = Stat::getItems(
                                $_GET["itemtype"],
                                $params["date1"],
                                $params["date2"],
                                $params["dropdown"]
                            );
                            Stat::showTable(
                                $_GET["itemtype"],
                                $params["type"],
                                $params["date1"],
                                $params["date2"],
                                $params["start"],
                                $val,
                                $params["dropdown"]
                            );
                            break;
    
                        default:
                              $val2 = (isset($params['value2']) ? $params['value2'] : 0);
                              $val  = Stat::getItems(
                                  $_GET["itemtype"],
                                  $params["date1"],
                                  $params["date2"],
                                  $params["type"],
                                  $val2
                              );
                             Stat::showTable(
                                 $_GET["itemtype"],
                                 $params["type"],
                                 $params["date1"],
                                 $params["date2"],
                                 $params["start"],
                                 $val,
                                 $val2
                             );
                    }
                }
            } else if (isset($_GET["type"]) && ($_GET["type"] == "hardwares")) {
                if (checkRightsToGenerateReport()) {
                    Stat::showItems("", $_GET["date1"], $_GET["date2"], $_GET['start']);
                }
            }
            break;

        default:
           // Plugin case
            if ($plug = isPluginItemType($itemtype)) {
                if (Plugin::doOneHook($plug['plugin'], 'dynamicReport', $_GET)) {
                    exit();
                }
            }
            $params = Search::manageParams($itemtype, $_GET);
            if (checkRightsToGenerateReport()) {
                Search::showList($itemtype, $params);
            }
    }
}
