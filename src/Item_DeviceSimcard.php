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
 * @since 9.2
 */


/**
 * Relation between item and devices
 **/
class Item_DeviceSimcard extends Item_Devices
{
    public static $itemtype_2 = 'DeviceSimcard';
    public static $items_id_2 = 'devicesimcards_id';

    protected static $notable = false;

    public static $undisclosedFields      = ['pin', 'pin2', 'puk', 'puk2'];

    public static function getTypeName($nb = 0)
    {
        return _n('Simcard', 'Simcards', $nb);
    }

    public static function getSpecificities($specif = '')
    {
        return [
            'serial'         => parent::getSpecificities('serial'),
            'otherserial'    => parent::getSpecificities('otherserial'),
            'locations_id'   => parent::getSpecificities('locations_id'),
            'states_id'      => parent::getSpecificities('states_id'),
            'pin'            => ['long name'  => __('PIN code'),
                'short name' => __('PIN code'),
                'size'       => 20,
                'id'         => 15,
                'datatype'   => 'text',
                'right'      => 'devicesimcard_pinpuk',
                'nosearch'   => true,
                'nodisplay'  => true,
                'protected'  => true
            ],
            'pin2'            => ['long name'  => __('PIN2 code'),
                'short name' => __('PIN2 code'),
                'size'       => 20,
                'id'         => 16,
                'datatype'   => 'string',
                'right'      => 'devicesimcard_pinpuk',
                'nosearch'   => true,
                'nodisplay'  => true,
                'protected'  => true
            ],
            'puk'             => ['long name'  => __('PUK code'),
                'short name' => __('PUK code'),
                'size'       => 20,
                'id'         => 17,
                'datatype'   => 'string',
                'right'      => 'devicesimcard_pinpuk',
                'nosearch'   => true,
                'nodisplay'  => true,
                'protected'  => true
            ],
            'puk2'            => ['long name'  => __('PUK2 code'),
                'short name' => __('PUK2 code'),
                'size'       => 20,
                'id'         => 18,
                'datatype'   => 'string',
                'right'      => 'devicesimcard_pinpuk',
                'nosearch'   => true,
                'nodisplay'  => true,
                'protected'  => true
            ],
            'lines_id'        => ['long name'  => Line::getTypeName(1),
                'short name' => Line::getTypeName(1),
                'size'       => 20,
                'id'         => 19,
                'datatype'   => 'dropdown'
            ],
            'msin'           => ['long name'  => __('Mobile Subscriber Identification Number'),
                'short name' => __('MSIN'),
                'size'       => 20,
                'id'         => 20,
                'datatype'   => 'string',
                'tooltip'    => __('MSIN is the last 8 or 10 digits of IMSI')
            ],
            'users_id'        => ['long name'  => User::getTypeName(1),
                'short name' => User::getTypeName(1),
                'size'       => 20,
                'id'         => 21,
                'datatype'   => 'dropdown',
                'dropdown_options' => ['right' => 'all']
            ],
            'groups_id'        => ['long name'  => Group::getTypeName(1),
                'short name' => Group::getTypeName(1),
                'size'       => 20,
                'id'         => 22,
                'datatype'   => 'dropdown'
            ],
        ];
    }

    public static function getNameField()
    {
        return 'serial';
    }

    public function checkAgainIfMandatoryFieldsAreCorrect(array $input):bool{
        $mandatory_missing = [];
        $incorrect_format = [];

        $fields_necessary = [
            'entities_id' => 'number',
            '_glpi_csrf_token' => 'string',
            'itemtype' => '',
            'devicesimcards_id' => 'number',
            'pin' => 'string',
            'pin2' => 'string',
            'puk' => 'string',
            'puk2' => 'string',
            'lines_id' => 'number',
            'msin' => 'string',
            'serial' => 'string',
            'otherserial' => 'string',
            'locations_id' => 'number',
            'states_id' => 'number',
            'users_id' => 'string',
            'groups_id' => 'number',
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
                
               
            }
        }

        //REGLA DE NOGOCIO:


        if (count($mandatory_missing)) {
            //TRANS: %s are the fields concerned
            $message = sprintf(
                __('No se envio el siguiente campo en la petición HTTP. Por favor corregir: %s'),
                implode(", ", $mandatory_missing)
            );
            Session::addMessageAfterRedirect($message, false, ERROR);
        }

        if (count($incorrect_format)) {
            //TRANS: %s are the fields concerned
            $message = sprintf(
                __('El siguiente campo fue enviado con tipo de dato incorrecto al esperado. Por favor corregir: %s'),
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
            'itemtype' => '',
            'devicesimcards_id' => 'number',
            'pin' => 'string',
            'pin2' => 'string',
            'puk' => 'string',
            'puk2' => 'string',
            'lines_id' => 'number',
            'msin' => 'string',
            'serial' => 'string',
            'otherserial' => 'string',
            'locations_id' => 'number',
            'states_id' => 'number',
            'users_id' => 'string',
            'groups_id' => 'number',
            'id' => 'number'
        ];


        foreach($fields_necessary as $key => $value){
            
            if(array_key_exists($key,$input)){
                //Si la key existe en $_POST
                if($value == 'number' && !is_numeric($input[$key]) ){
                    array_push($incorrect_format, $key);
                    break;
                }
                else if($value == 'string' && !is_string($input[$key]) ){
                    array_push($incorrect_format, $key);
                    break;
                }      
            }
        }

        //REGLA DE NOGOCIO:

        if (count($incorrect_format)) {
            //TRANS: %s are the fields concerned
            $message = sprintf(
                __('El siguiente campo fue enviado con tipo de dato incorrecto al esperado. Por favor corregir: %s'),
                implode(", ", $incorrect_format)
            );
            Session::addMessageAfterRedirect($message, false, WARNING);
            return false;
        }else{
            return $this->checkAppliedBusinessRules($input);
        }

    }

    public function checkAppliedBusinessRules(array &$input):bool{
        
        $selector_ids_incorrect = [];

        if(array_key_exists('id', $input) && $input['entities_id'] != 0 && Entity::getById($input['entities_id']) == false){
            array_push($selector_ids_incorrect,'entities_id');
        }
        else if(array_key_exists('states_id', $input) && $input['states_id'] != 0 && State::getById($input['states_id']) == false){
            array_push($selector_ids_incorrect,'states_id');
        }
        else if(array_key_exists('devicesimcards_id', $input) && $input['devicesimcards_id'] != 0 && DeviceSimcard::getById($input['devicesimcards_id']) == false){
            array_push($selector_ids_incorrect,'devicesimcards_id');
        }
        else if(array_key_exists('lines_id', $input) && $input['lines_id'] != 0 && Line::getById($input['lines_id']) == false){
            array_push($selector_ids_incorrect,'lines_id');
        }
        else if(array_key_exists('locations_id', $input) && $input['locations_id'] != 0 && Location::getById($input['locations_id']) == false){
            array_push($selector_ids_incorrect,'locations_id');
        }
        else if(array_key_exists('users_id', $input) && $input['users_id'] != 0 && User::getById($input['users_id']) == false){
            array_push($selector_ids_incorrect,'users_id');
        }
        else if(array_key_exists('groups_id', $input) && $input['groups_id'] != 0 && Group::getById($input['groups_id']) == false){
            array_push($selector_ids_incorrect,'groups_id');
        }   
        else if(array_key_exists('id', $input) && $input['id'] != 0 && Item_DeviceSimcard::getById($input['id']) == false){
            array_push($selector_ids_incorrect,'simcard_id');
        }
       
     
        if(count($selector_ids_incorrect)){
            $message = sprintf(
                __('Se detectó al menos un campo con Id incorrecto. Por favor corregir: %s'),
                implode(", ", $selector_ids_incorrect)
            );
            Session::addMessageAfterRedirect($message, false, ERROR);
        }

        if(count($selector_ids_incorrect)){
            return false;
        }
        else{
            return true;
        }

    }

    public function getImportCriteria(): array
    {
        return [
            'serial' => 'equal',
            'msin' => 'equal',
        ];
    }
}
