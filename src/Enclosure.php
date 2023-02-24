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

use Glpi\Application\View\TemplateRenderer;

/**
 * Enclosure Class
 **/
class Enclosure extends CommonDBTM
{
    use Glpi\Features\DCBreadcrumb;
    use Glpi\Features\Clonable;

   // From CommonDBTM
    public $dohistory                   = true;
    public static $rightname                   = 'datacenter';

    public function getCloneRelations(): array
    {
        return [
            Item_Enclosure::class,
            Item_Devices::class,
            NetworkPort::class
        ];
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Enclosure', 'Enclosures', $nb);
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong)
         ->addImpactTab($ong, $options)
         ->addStandardTab('Item_Enclosure', $ong, $options)
         ->addStandardTab('Item_Devices', $ong, $options)
         ->addStandardTab('NetworkPort', $ong, $options)
         ->addStandardTab('Infocom', $ong, $options)
         ->addStandardTab('Contract_Item', $ong, $options)
         ->addStandardTab('Document_Item', $ong, $options)
         ->addStandardTab('Ticket', $ong, $options)
         ->addStandardTab('Item_Problem', $ong, $options)
         ->addStandardTab('Change_Item', $ong, $options)
         ->addStandardTab('Log', $ong, $options);
        return $ong;
    }


    /**
     * Print the enclosure form
     *
     * @param $ID integer ID of the item
     * @param $options array
     *     - target filename : where to go when done.
     *     - withtemplate boolean : template or basic item
     *
     * @return boolean item found
     **/
    public function showForm($ID, array $options = [])
    {
        $this->initForm($ID, $options);
        TemplateRenderer::getInstance()->display('pages/assets/enclosure.html.twig', [
            'item'   => $this,
            'params' => $options,
        ]);
        return true;
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
            'id'                 => '2',
            'table'              => $this->getTable(),
            'field'              => 'id',
            'name'               => __('ID'),
            'massiveaction'      => false, // implicit field is id
            'datatype'           => 'number'
        ];

        $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab[] = [
            'id'                 => '40',
            'table'              => 'glpi_enclosuremodels',
            'field'              => 'name',
            'name'               => _n('Model', 'Models', 1),
            'datatype'           => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '31',
            'table'              => 'glpi_states',
            'field'              => 'completename',
            'name'               => __('Status'),
            'datatype'           => 'dropdown',
            'condition'          => ['is_visible_computer' => 1]
        ];

        $tab[] = [
            'id'                 => '5',
            'table'              => $this->getTable(),
            'field'              => 'serial',
            'name'               => __('Serial number'),
            'datatype'           => 'string',
        ];

        $tab[] = [
            'id'                 => '6',
            'table'              => $this->getTable(),
            'field'              => 'otherserial',
            'name'               => __('Inventory number'),
            'datatype'           => 'string',
        ];

        $tab[] = [
            'id'                 => '16',
            'table'              => $this->getTable(),
            'field'              => 'comment',
            'name'               => __('Comments'),
            'datatype'           => 'text'
        ];

        $tab[] = [
            'id'                 => '19',
            'table'              => $this->getTable(),
            'field'              => 'date_mod',
            'name'               => __('Last update'),
            'datatype'           => 'datetime',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '121',
            'table'              => $this->getTable(),
            'field'              => 'date_creation',
            'name'               => __('Creation date'),
            'datatype'           => 'datetime',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '23',
            'table'              => 'glpi_manufacturers',
            'field'              => 'name',
            'name'               => Manufacturer::getTypeName(1),
            'datatype'           => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '24',
            'table'              => 'glpi_users',
            'field'              => 'name',
            'linkfield'          => 'users_id_tech',
            'name'               => __('Technician in charge of the hardware'),
            'datatype'           => 'dropdown',
            'right'              => 'own_ticket'
        ];

        $tab[] = [
            'id'                 => '49',
            'table'              => 'glpi_groups',
            'field'              => 'completename',
            'linkfield'          => 'groups_id_tech',
            'name'               => __('Group in charge of the hardware'),
            'condition'          => ['is_assign' => 1],
            'datatype'           => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '61',
            'table'              => $this->getTable(),
            'field'              => 'template_name',
            'name'               => __('Template name'),
            'datatype'           => 'text',
            'massiveaction'      => false,
            'nosearch'           => true,
            'nodisplay'          => true,
        ];

        $tab[] = [
            'id'                 => '80',
            'table'              => 'glpi_entities',
            'field'              => 'completename',
            'name'               => Entity::getTypeName(1),
            'datatype'           => 'dropdown'
        ];

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        $tab = array_merge($tab, Datacenter::rawSearchOptionsToAdd(get_class($this)));

        $tab = array_merge($tab, Rack::rawSearchOptionsToAdd(get_class($this)));

        return $tab;
    }

    /**
     * Get already filled places
     *
     * @param string  $itemtype  The item type
     * @param integer $items_id  The item's ID
     *
     * @return array [x => ['depth' => 1, 'orientation' => 0, 'width' => 1, 'hpos' =>0]]
     *               orientation will not be available if depth is > 0.5; hpos will not be available
     *               if width is = 1
     */
    public function getFilled($itemtype = null, $items_id = null)
    {
        global $DB;

        $iterator = $DB->request([
            'FROM'   => Item_Enclosure::getTable(),
            'WHERE'  => [
                'enclosures_id' => $this->getID()
            ]
        ]);

        $filled = [];
        foreach ($iterator as $row) {
            if (
                empty($itemtype) || empty($items_id)
                || $itemtype != $row['itemtype'] || $items_id != $row['items_id']
            ) {
                $filled[$row['position']] = $row['position'];
            }
        }
        return $filled;
    }

    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
                Item_Enclosure::class,
            ]
        );
    }


    public function prepareInputForAdd($input)
    {
        if (isset($input["id"]) && ($input["id"] > 0)) {
            $input["_oldID"] = $input["id"];
        }
        unset($input['id']);
        unset($input['withtemplate']);
        return $input;
    }

    public function checkAgainIfMandatoryFieldsAreCorrect(array $input):bool{
        $mandatory_missing = [];
        $incorrect_format = [];

        $fields_necessary = [
            "entities_id" => "number",
            "_glpi_csrf_token" => "string",
            //"is_recursive" => 'bool',
            "name" => "string",
            "states_id" => "number",
            "locations_id" => "number",
            "users_id_tech" => "number",
            "manufacturers_id" => "number",
            "groups_id_tech" => "number",
            "enclosuremodels_id" => "number",
            "serial" => "string",
            "otherserial" => "string",
            "comment" => "string",
            "power_supplies" => "number"
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
            "entities_id" => "number",
            "_glpi_csrf_token" => "string",
            //"is_recursive" => 'bool',
            "name" => "string",
            "states_id" => "number",
            "locations_id" => "number",
            "users_id_tech" => "number",
            "manufacturers_id" => "number",
            "groups_id_tech" => "number",
            "enclosuremodels_id" => "number",
            "serial" => "string",
            "otherserial" => "string",
            "comment" => "string",
            "power_supplies" => "number",
            "id" => "number",
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
        $selector_fields_outrange = [];

        if(array_key_exists('entities_id', $input) && $input['entities_id'] != 0 && Entity::getById($input['entities_id']) == false){
            array_push($selector_ids_incorrect,'entities_id');
        }
        else if(array_key_exists('states_id', $input) && $input['states_id'] != 0 && State::getById($input['states_id']) == false){
            array_push($selector_ids_incorrect,'states_id');
        }
        else if(array_key_exists('locations_id', $input) && $input['locations_id'] != 0 && Location::getById($input['locations_id']) == false){
            array_push($selector_ids_incorrect,'locations_id');
        }
        else if(array_key_exists('users_id_tech', $input) && $input['users_id_tech'] != 0 && User::getById($input['users_id_tech']) == false){
            array_push($selector_ids_incorrect,'users_id_tech');
        }
        else if(array_key_exists('manufacturers_id', $input) && $input['manufacturers_id'] != 0 && Manufacturer::getById($input['manufacturers_id']) == false){
            array_push($selector_ids_incorrect,'manufacturers_id');
        }
        else if(array_key_exists('groups_id_tech', $input) && $input['groups_id_tech'] != 0 && Group::getById($input['groups_id_tech']) == false){
            array_push($selector_ids_incorrect,'groups_id_tech');
        }
        else if(array_key_exists('enclosuremodels_id', $input) && $input['enclosuremodels_id'] != 0 && EnclosureModel::getById($input['enclosuremodels_id']) == false){
            array_push($selector_ids_incorrect,'enclosuremodels_id');
        }
        else if(array_key_exists('id', $input) && $input['id'] != 0 && Enclosure::getById($input['id']) == false){
            array_push($selector_ids_incorrect,'enclosure_id');
        }

        if(array_key_exists('power_supplies', $input) && ($input['power_supplies'] < 0)){
            array_push($selector_fields_outrange,'power_supplies');
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
    
    public static function getIcon()
    {
        return "ti ti-columns";
    }
}
