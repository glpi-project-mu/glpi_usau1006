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
use Glpi\Socket;
use Glpi\SocketModel;

/// Class Cable
class Cable extends CommonDBTM
{
   // From CommonDBTM
    public $dohistory         = true;
    public static $rightname         = 'cable_management';

    public static function getTypeName($nb = 0)
    {
        return _n('Cable', 'Cables', $nb);
    }

    public static function getFieldLabel()
    {
        return self::getTypeName(1);
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong)
         ->addStandardTab('Infocom', $ong, $options)
         ->addStandardTab('Ticket', $ong, $options)
         ->addStandardTab('Item_Problem', $ong, $options)
         ->addStandardTab('Change_Item', $ong, $options)
         ->addStandardTab('Log', $ong, $options);

        return $ong;
    }

    public function post_getEmpty()
    {
        $this->fields['color'] = '#dddddd';
        $this->fields['itemtype_endpoint_a'] = 'Computer';
        $this->fields['itemtype_endpoint_b'] = 'Computer';
    }

    public static function getAdditionalMenuLinks()
    {
        $links = [];
        if (static::canView()) {
            $insts = "<i class=\"fas fa-ethernet pointer\" title=\"" . Socket::getTypeName(Session::getPluralNumber()) .
            "\"></i><span class=\"sr-only\">" . Socket::getTypeName(Session::getPluralNumber()) . "</span>";
            $links[$insts] = Socket::getSearchURL(false);
        }
        if (count($links)) {
            return $links;
        }
        return false;
    }

    public static function getAdditionalMenuOptions()
    {
        if (static::canView()) {
            return [
                'socket' => [
                    'title' => Socket::getTypeName(Session::getPluralNumber()),
                    'page'  => Socket::getSearchURL(false),
                    'links' => [
                        'add'    => '/front/socket.form.php',
                        'search' => '/front/socket.php',
                    ]
                ]
            ];
        }
        return false;
    }

    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id'                 => 'common',
            'name'               => __('Characteristics')
        ];

        $tab[] = [
            'id'                 => '1',
            'table'              => $this->getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'itemlink',
            'massiveaction'      => false,
            'autocomplete'       => true,
        ];

        $tab[] = [
            'id'                 => '2',
            'table'              => $this->getTable(),
            'field'              => 'id',
            'name'               => __('ID'),
            'massiveaction'      => false,
            'datatype'           => 'number'
        ];

        $tab[] = [
            'id'                 => '4',
            'table'              => 'glpi_cabletypes',
            'field'              => 'name',
            'name'               => _n('Cable type', 'Cable types', 1),
            'datatype'           => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '5',
            'table'              => 'glpi_cablestrands',
            'field'              => 'name',
            'name'               => _n('Cable strand', 'Cable strands', 1),
            'datatype'           => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '6',
            'table'              => $this->getTable(),
            'field'              => 'otherserial',
            'name'               => __('Inventory number'),
            'datatype'           => 'string',
            'autocomplete'       => true,
        ];

        $tab[] = [
            'id'                 => '7',
            'table'              => $this->getTable(),
            'field'              => 'itemtype_endpoint_a',
            'name'               => sprintf(__('%s (%s)'), _n('Associated item type', 'Associated item types', 1), __('Endpoint A')),
            'datatype'           => 'itemtypename',
            'itemtype_list'      => 'socket_types',
            'forcegroupby'       => true,
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '8',
            'table'              => $this->getTable(),
            'field'              => 'items_id_endpoint_b',
            'name'               => sprintf(__('%s (%s)'), _n('Associated item', 'Associated items', 1), __('Endpoint B')),
            'massiveaction'      => false,
            'datatype'           => 'specific',
            'searchtype'         => 'equals',
            'additionalfields'   => ['itemtype_endpoint_b']
        ];

        $tab[] = [
            'id'                 => '9',
            'table'              => $this->getTable(),
            'field'              => 'itemtype_endpoint_b',
            'name'               => sprintf(__('%s (%s)'), _n('Associated item type', 'Associated item types', 1), __('Endpoint B')),
            'datatype'           => 'itemtypename',
            'itemtype_list'      => 'socket_types',
            'forcegroupby'       => true,
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '10',
            'table'              => $this->getTable(),
            'field'              => 'items_id_endpoint_a',
            'name'               => sprintf(__('%s (%s)'), _n('Associated item', 'Associated items', 1), __('Endpoint A')),
            'massiveaction'      => false,
            'datatype'           => 'specific',
            'searchtype'         => 'equals',
            'additionalfields'   => ['itemtype_endpoint_a']
        ];

        $tab[] = [
            'id'                 => '11',
            'table'              => SocketModel::getTable(),
            'field'              => 'name',
            'linkfield'          => 'socketmodels_id_endpoint_a',
            'name'               => sprintf(__('%s (%s)'), SocketModel::getTypeName(1), __('Endpoint A')),
            'datatype'           => 'dropdown',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '12',
            'table'              => SocketModel::getTable(),
            'field'              => 'name',
            'linkfield'          => 'socketmodels_id_endpoint_b',
            'name'               => sprintf(__('%s (%s)'), SocketModel::getTypeName(1), __('Endpoint B')),
            'datatype'           => 'dropdown',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '13',
            'table'              => Socket::getTable(),
            'field'              => 'name',
            'linkfield'          => 'sockets_id_endpoint_b',
            'name'               => sprintf(__('%s (%s)'), Socket::getTypeName(1), __('Endpoint B')),
            'datatype'           => 'dropdown',
            'massiveaction'       => false,
        ];

        $tab[] = [
            'id'                 => '14',
            'table'              => Socket::getTable(),
            'field'              => 'name',
            'linkfield'          => 'sockets_id_endpoint_a',
            'name'               => sprintf(__('%s (%s)'), Socket::getTypeName(1), __('Endpoint A')),
            'datatype'           => 'dropdown',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '15',
            'table'              => $this->getTable(),
            'field'              => 'color',
            'name'               => __('Color'),
            'datatype'           => 'color'
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
            'id'                 => '24',
            'table'              => 'glpi_users',
            'field'              => 'name',
            'linkfield'          => 'users_id_tech',
            'name'               => __('Technician in charge of the hardware'),
            'datatype'           => 'dropdown'
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
            'id'                 => '31',
            'table'              => 'glpi_states',
            'field'              => 'completename',
            'name'               => __('Status'),
            'datatype'           => 'dropdown',
            'condition'          => ['is_visible_cable' => 1]
        ];

        $tab[] = [
            'id'                 => '87',
            'table'              => $this->getTable(),
            'field'              => '_virtual_datacenter_position', // virtual field
            'additionalfields'   => [
                'items_id_endpoint_a',
                'itemtype_endpoint_a'
            ],
            'name'               => sprintf(__('%s (%s)'), __('Data center position'), __('Endpoint A')),
            'datatype'           => 'specific',
            'nosearch'           => true,
            'nosort'             => true,
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '88',
            'table'              => $this->getTable(),
            'field'              => '_virtual_datacenter_position', // virtual field
            'additionalfields'   => [
                'items_id_endpoint_b',
                'itemtype_endpoint_b'
            ],
            'name'               => sprintf(__('%s (%s)'), __('Data center position'), __('Endpoint B')),
            'datatype'           => 'specific',
            'nosearch'           => true,
            'nosort'             => true,
            'massiveaction'      => false
        ];

        return $tab;
    }


    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;
        switch ($field) {
            case 'items_id_endpoint_a':
                if (isset($values['itemtype_endpoint_a']) && !empty($values['itemtype_endpoint_a'])) {
                    $options['name']  = $name;
                    $options['value'] = $values[$field];
                    return Dropdown::show($values['itemtype_endpoint_a'], $options);
                }
                break;
            case 'items_id_endpoint_b':
                if (isset($values['itemtype_endpoint_b']) && !empty($values['itemtype_endpoint_b'])) {
                    $options['name']  = $name;
                    $options['value'] = $values[$field];
                    return Dropdown::show($values['itemtype_endpoint_b'], $options);
                }
                break;
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }

        switch ($field) {
            case 'items_id_endpoint_a':
            case 'items_id_endpoint_b':
                $itemtype = $values[str_replace('items_id', 'itemtype', $field)] ?? null;
                if ($itemtype !== null && class_exists($itemtype)) {
                    if ($values[$field] > 0) {
                        $item = new $itemtype();
                        $item->getFromDB($values[$field]);
                        return "<a href='" . $item->getLinkURL() . "'>" . $item->fields['name'] . "</a>";
                    }
                } else {
                    return ' ';
                }
                break;
            case '_virtual_datacenter_position':
                $itemtype = isset($values['itemtype_endpoint_b']) ? $values['itemtype_endpoint_b'] : $values['itemtype_endpoint_a'];
                $items_id = isset($values['items_id_endpoint_b']) ? $values['items_id_endpoint_b'] : $values['items_id_endpoint_a'];

                if (method_exists($itemtype, 'getDcBreadcrumbSpecificValueToDisplay')) {
                    return $itemtype::getDcBreadcrumbSpecificValueToDisplay($items_id);
                }
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    /**
     * Print the main form
     *
     * @param integer $ID      Integer ID of the item
     * @param array  $options  Array of possible options:
     *     - target for the Form
     *     - withtemplate : template or basic item
     *
     * @return void|boolean (display) Returns false if there is a rights error.
     **/
    public function showForm($ID, array $options = [])
    {
        $this->initForm($ID, $options);
        TemplateRenderer::getInstance()->display('pages/assets/cable.html.twig', [
            'item'   => $this,
            'params' => $options,
        ]);
        return true;
    }

    public function checkAgainIfMandatoryFieldsAreCorrect(array $input):bool{
        $mandatory_missing = [];
        $incorrect_format = [];

        $fields_necessary = [
            'entities_id' => 'number',
            '_glpi_csrf_token' => 'string',
            'name' => 'string',
            'states_id' => 'number',
            'cabletypes_id' => 'number',
            'users_id_tech' => 'number',
            'otherserial' => 'string',
            'comment' => 'string',
            'cablestrands_id' => 'number',
            'color' => 'hexcolor',
            'itemtype_endpoint_a' => 'string',
            'items_id_endpoint_a' => 'number',
            'socketmodels_id_endpoint_a' => 'number',
            'sockets_id_endpoint_a' => 'number',
            'itemtype_endpoint_b' => 'string',
            'items_id_endpoint_b' => 'number',
            'socketmodels_id_endpoint_b' => 'number',
            'sockets_id_endpoint_b' => 'number'
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
                else if($value == 'hexcolor' && !preg_match('/^#[a-f0-9]{6}$/i', $input[$key]) ){
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
            'name' => 'string',
            'states_id' => 'number',
            'cabletypes_id' => 'number',
            'users_id_tech' => 'number',
            'otherserial' => 'string',
            'comment' => 'string',
            'cablestrands_id' => 'number',
            'color' => 'hexcolor',
            'itemtype_endpoint_a' => 'string',
            'items_id_endpoint_a' => 'number',
            'socketmodels_id_endpoint_a' => 'number',
            'sockets_id_endpoint_a' => 'number',
            'itemtype_endpoint_b' => 'string',
            'items_id_endpoint_b' => 'number',
            'socketmodels_id_endpoint_b' => 'number',
            'sockets_id_endpoint_b' => 'number',
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
                else if($value == 'hexcolor' && !preg_match('/^#[a-f0-9]{6}$/i', $input[$key]) ){
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

        if(array_key_exists('entities_id', $input) && $input['entities_id'] != 0 && Entity::getById($input['entities_id']) == false){
            array_push($selector_ids_incorrect,'entities_id');
        }
        else if(array_key_exists('states_id', $input) && $input['states_id'] != 0 && State::getById($input['states_id']) == false){
            array_push($selector_ids_incorrect,'states_id');
        }
        else if(array_key_exists('cabletypes_id', $input) && $input['cabletypes_id'] != 0 && CableType::getById($input['cabletypes_id']) == false){
            array_push($selector_ids_incorrect,'cabletypes_id');
        }
        else if(array_key_exists('users_id_tech', $input) && $input['users_id_tech'] != 0 && User::getById($input['users_id_tech']) == false){
            array_push($selector_ids_incorrect,'users_id_tech');
        }
        else if(array_key_exists('cablestrands_id', $input) && $input['cablestrands_id'] != 0 && CableStrand::getById($input['cablestrands_id']) == false){
            array_push($selector_ids_incorrect,'cablestrands_id');
        }
        else if(array_key_exists('id', $input) && $input['id'] != 0 && Cable::getById($input['id']) == false){
            array_push($selector_ids_incorrect,'cable_id');
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


    public static function getIcon()
    {
        return "ti ti-line";
    }
}
