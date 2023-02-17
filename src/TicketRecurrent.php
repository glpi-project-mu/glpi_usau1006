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

/**
 * Ticket Recurrent class
 *
 * @since 0.83
 **/
class TicketRecurrent extends CommonITILRecurrent
{
    /**
     * @var string CommonDropdown
     */
    public $second_level_menu = "ticketrecurrent";

    /**
     * @var string Right managements
     */
    public static $rightname = 'ticketrecurrent';

    public static function getTypeName($nb = 0)
    {
        return __('Recurrent tickets');
    }

    public static function getConcreteClass()
    {
        return Ticket::class;
    }

    public static function getTemplateClass()
    {
        return TicketTemplate::class;
    }

    public static function getPredefinedFieldsClass()
    {
        return TicketTemplatePredefinedField::class;
    }

    public function handlePredefinedFields(
        array $predefined,
        array $input
    ): array {
        $input = parent::handlePredefinedFields($predefined, $input);

       // Compute internal_time_to_resolve if predefined based on create date
        if (isset($predefined['internal_time_to_resolve'])) {
            $input['internal_time_to_resolve'] = Html::computeGenericDateTimeSearch(
                $predefined['internal_time_to_resolve'],
                false,
                $this->getCreateTime()
            );
        }

        return $input;
    }

    public function checkAgainIfMandatoryFieldsAreCorrect(array $input):bool{
        $mandatory_missing = [];
        $incorrect_format = [];

        $fields_necessary = [
            'entities_id' => 'number',
            '_glpi_csrf_token' => 'string',
            //'is_recursive' => 'number',
            'name' => 'string',
            'comment' => 'string',
            'is_active' => 'number',
            'tickettemplates_id' => 'number',
            'begin_date' => '',
            'end_date' => '',
            'periodicity' => 'number',
            'create_before' => 'number',
            'calendars_id' => 'number',
            ];


        foreach($fields_necessary as $key => $value){
            
            if(!isset($input[$key])){
                array_push($mandatory_missing, $key); 
            }else{
                //Si la key existe en $_POST

                if($value == 'number' && !is_numeric($input[$key]) ){
                    array_push($incorrect_format, $key);
                }
                else if($value == 'string' && !is_string($input[$key]) ){
                    array_push($incorrect_format, $key);
                }
            }
        }

        //REGLA DE NOGOCIO:


        if (count($mandatory_missing)) {
            //TRANS: %s are the fields concerned
            $message = sprintf(
                __('No se enviaron los siguientes campos en la petición. Por favor corregir: %s'),
                implode(", ", $mandatory_missing)
            );
            Session::addMessageAfterRedirect($message, false, ERROR);
        }

        if (count($incorrect_format)) {
            //TRANS: %s are the fields concerned
            $message = sprintf(
                __('Los siguientes campos fueron enviados con formato incorrecto. Por favor corregir: %s'),
                implode(", ", $incorrect_format)
            );
            Session::addMessageAfterRedirect($message, false, WARNING);
        }


        if(count($mandatory_missing) || count($incorrect_format)){
            return false;
        }else{
            return $this->checkAppliedBusinessRules($input);
        }
    }

    public function checkAppliedBusinessRules(array &$input):bool{
        $selector_fields_outrange = [];
        
        if($input['is_active'] < 0 || $input['is_active'] > 1){
            array_push($selector_fields_outrange,'is_active');
        }

        else if($input['periodicity'] < 0 || $input['periodicity'] > 315576000 ){
            array_push($selector_fields_outrange,'periodicity');
        }

        $timeunixDate = strtotime($input['begin_date']);
        $timeunixTTR = strtotime($input['end_date']);


        if( $timeunixDate !== false && $timeunixTTR !== false){

            if($timeunixDate > $timeunixTTR){
                array_push($selector_fields_outrange,'StarDate mayor a EndDate');
            }
        }

        $selector_ids_incorrect=[];

        if($input['calendars_id'] != 0 && Calendar::getById($input['calendars_id']) == false){
            array_push($selector_ids_incorrect,'calendars_id');
        }
        else if($input['tickettemplates_id'] != 0 && TicketTemplate::getById($input['tickettemplates_id']) == false){
            array_push($selector_ids_incorrect,'tickettemplates_id');
        }

        if(count($selector_fields_outrange)){
            $message = sprintf(
                __('Se detectó al menos un campo fuera de su rango establecido. Por favor corregir: %s'),
                implode(", ", $selector_fields_outrange)
            );
            Session::addMessageAfterRedirect($message, false, WARNING);
        }

        if(count($selector_ids_incorrect)){
            $message = sprintf(
                __('Se detectó al menos un campo con Id incorrecto. Por favor corregir: %s'),
                implode(", ", $selector_ids_incorrect)
            );
            Session::addMessageAfterRedirect($message, false, ERROR);
        }

        if(count($selector_fields_outrange) || count($selector_ids_incorrect)){
            return false;
        }
        else{
            return true;
        }

    }
}
