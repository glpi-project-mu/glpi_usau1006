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

class RecurrentChange extends CommonITILRecurrent
{
    /**
     * @var string CommonDropdown
     */
    public $second_level_menu = "recurrentchange";

    /**
     * @var string Right managements
     */
    public static $rightname = 'recurrentchange';

    public static function getTypeName($nb = 0)
    {
        return __('Recurrent changes');
    }

    public static function getConcreteClass()
    {
        return Change::class;
    }

    public static function getTemplateClass()
    {
        return ChangeTemplate::class;
    }

    public static function getPredefinedFieldsClass()
    {
        return ChangeTemplatePredefinedField::class;
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
            'is_active' => 'bool',
            'changetemplates_id' => 'number',
            'begin_date' => '',
            'end_date' => '',
            'periodicity' => 'number',
            'create_before' => 'number',
            'calendars_id' => 'number'
            ];


        foreach($fields_necessary as $key => $value){
            
            if(!isset($input[$key])){
                array_push($mandatory_missing, $key);
                break; 
            }else{
                //Si la key existe en $_POST

                if($value == 'number' && !is_numeric($input[$key]) ){
                    array_push($incorrect_format, $key);
                    break;
                }
                else if($value == 'string' && !is_string($input[$key]) ){
                    array_push($incorrect_format, $key);
                    break;
                }
                else if($value == 'bool' && !($input[$key] == '0' || $input[$key] == '1') ){
                    array_push($incorrect_format, $key);
                    break;
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

    public function checkAllFieldsInUpdate(array $input):bool{
        
        $incorrect_format = [];

        $fields_necessary = [
            'entities_id' => 'number',
            '_glpi_csrf_token' => 'string',
            //'is_recursive' => 'number',
            'name' => 'string',
            'comment' => 'string',
            'is_active' => 'bool',
            'changetemplates_id' => 'number',
            'begin_date' => '',
            'end_date' => '',
            'periodicity' => 'number',
            'create_before' => 'number',
            'calendars_id' => 'number',
            'id' => 'number'
            ];


        foreach($fields_necessary as $key => $value){
            
            if(array_key_exists($key,$input)){

                if($value == 'number' && !is_numeric($input[$key]) ){
                    array_push($incorrect_format, $key);
                    break;
                }
                else if($value == 'string' && !is_string($input[$key]) ){
                    array_push($incorrect_format, $key);
                    break;
                }
                else if($value == 'bool' && !($input[$key] == '0' || $input[$key] == '1') ){
                    array_push($incorrect_format, $key);
                    break;
                }
            }
        }

        //REGLA DE NOGOCIO:

        if (count($incorrect_format)) {
            //TRANS: %s are the fields concerned
            $message = sprintf(
                __('Los siguientes campos fueron enviados con formato incorrecto. Por favor corregir: %s'),
                implode(", ", $incorrect_format)
            );
            Session::addMessageAfterRedirect($message, false, WARNING);
            return false;
        }else{
            return $this->checkAppliedBusinessRules($input);
        }


    }

    public function checkAppliedBusinessRules(array &$input):bool{
        $selector_fields_outrange = [];
        
       
        if(array_key_exists('periodicity',$input) && $input['periodicity'] < 0 || $input['periodicity'] > 315576000 ){
            array_push($selector_fields_outrange,'periodicity');
        }

        else if(array_key_exists('create_before',$input) && $input['create_before'] < 0 || $input['create_before'] > 1209600 ){
            array_push($selector_fields_outrange,'create_before');
        }

        if(array_key_exists('begin_date',$input) && array_key_exists('end_date',$input) ){
            
            $timeunixDate = strtotime($input['begin_date']);
            $timeunixTTR = strtotime($input['end_date']);
    
            if( $timeunixDate !== false && $timeunixTTR !== false){
                if($timeunixDate < strtotime('1990-01-01')){
                    array_push($selector_fields_outrange,"'Start Date' no debe ser inferior a '1990-01-01'");
                }else if($timeunixTTR < strtotime('1990-01-01')){
                    array_push($selector_fields_outrange,"'End Date' no debe ser inferior a '1990-01-01'");
                }
                
                if($timeunixDate > $timeunixTTR){
                    array_push($selector_fields_outrange,"'Star Date' no debe ser mayor a a 'End Date'");
                }
            }
        }

        $selector_ids_incorrect=[];

        if(array_key_exists('entities_id',$input) && $input['entities_id'] != 0 && Entity::getById($input['entities_id']) == false){
            array_push($selector_ids_incorrect,'entities_id');
        }
        else if(array_key_exists('calendars_id',$input) && $input['calendars_id'] != 0 && Calendar::getById($input['calendars_id']) == false){
            array_push($selector_ids_incorrect,'calendars_id');
        }
        else if(array_key_exists('changetemplates_id',$input) && $input['changetemplates_id'] != 0 && ChangeTemplate::getById($input['changetemplates_id']) == false){
            array_push($selector_ids_incorrect,'changetemplates_id');
        }
        else if(array_key_exists('id',$input) && $input['id'] != 0 && RecurrentChange::getById($input['id']) == false){
            array_push($selector_ids_incorrect,'recurrentchange_id');
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
