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
 * Monitor Class
 **/
class Monitor extends CommonDBTM
{
    use Glpi\Features\DCBreadcrumb;
    use Glpi\Features\Clonable;
    use Glpi\Features\Inventoriable;

   // From CommonDBTM
    public $dohistory                   = true;
    protected static $forward_entity_to = ['Infocom', 'ReservationItem', 'Item_OperatingSystem', 'NetworkPort',
        'Item_SoftwareVersion'
    ];

    public static $rightname                   = 'monitor';
    protected $usenotepad               = true;

    public function getCloneRelations(): array
    {
        return [
            Item_OperatingSystem::class,
            Item_Devices::class,
            Infocom::class,
            Contract_Item::class,
            Document_Item::class,
            Computer_Item::class,
            KnowbaseItem_Item::class
        ];
    }

    /**
     * Name of the type
     *
     * @param $nb  string   number of item in the type
     **/
    public static function getTypeName($nb = 0)
    {
        return _n('Monitor', 'Monitors', $nb);
    }


    /**
     * @see CommonDBTM::useDeletedToLockIfDynamic()
     *
     * @since 0.84
     **/
    public function useDeletedToLockIfDynamic()
    {
        return false;
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addImpactTab($ong, $options);
        $this->addStandardTab('Item_OperatingSystem', $ong, $options);
        $this->addStandardTab('Item_SoftwareVersion', $ong, $options);
        $this->addStandardTab('Item_Devices', $ong, $options);
        $this->addStandardTab('Computer_Item', $ong, $options);
        $this->addStandardTab('NetworkPort', $ong, $options);
        $this->addStandardTab('Infocom', $ong, $options);
        $this->addStandardTab('Contract_Item', $ong, $options);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('KnowbaseItem_Item', $ong, $options);
        $this->addStandardTab('Ticket', $ong, $options);
        $this->addStandardTab('Item_Problem', $ong, $options);
        $this->addStandardTab('Change_Item', $ong, $options);
        $this->addStandardTab('ManualLink', $ong, $options);
        $this->addStandardTab('Lock', $ong, $options);
        $this->addStandardTab('Notepad', $ong, $options);
        $this->addStandardTab('Reservation', $ong, $options);
        $this->addStandardTab('Domain_Item', $ong, $options);
        $this->addStandardTab('Appliance_Item', $ong, $options);
        $this->addStandardTab('RuleMatchedLog', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }

    public function prepareInputForAdd($input)
    {

        if (isset($input["id"]) && ($input["id"] > 0)) {
            $input["_oldID"] = $input["id"];
        }
        if (isset($input["size"]) && ($input["size"] == '')) {
            unset($input["size"]);
        }
        unset($input['id']);
        unset($input['withtemplate']);

        return $input;
    }


    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
                Computer_Item::class,
                Item_Project::class,
            ]
        );

        Item_Devices::cleanItemDeviceDBOnItemDelete(
            $this->getType(),
            $this->fields['id'],
            (!empty($this->input['keep_devices']))
        );
    }


    /**
     * Print the monitor form
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
        TemplateRenderer::getInstance()->display('pages/assets/monitor.html.twig', [
            'item'   => $this,
            'params' => $options,
        ]);
        return true;
    }


    /**
     * Return the linked items (in computers_items)
     *
     * @return array of linked items  like array('Computer' => array(1,2), 'Printer' => array(5,6))
     * @since 0.84.4
     **/
    public function getLinkedItems()
    {
        global $DB;

        $iterator = $DB->request([
            'SELECT' => 'computers_id',
            'FROM'   => 'glpi_computers_items',
            'WHERE'  => [
                'itemtype'  => $this->getType(),
                'items_id'  => $this->fields['id']
            ]
        ]);
        $tab = [];
        foreach ($iterator as $data) {
            $tab['Computer'][$data['computers_id']] = $data['computers_id'];
        }
        return $tab;
    }


    public function getSpecificMassiveActions($checkitem = null)
    {

        $actions = parent::getSpecificMassiveActions($checkitem);
        if (static::canUpdate()) {
            Computer_Item::getMassiveActionsForItemtype($actions, __CLASS__, 0, $checkitem);
            $actions += [
                'Item_SoftwareLicense' . MassiveAction::CLASS_ACTION_SEPARATOR . 'add'
               => "<i class='ma-icon fas fa-key'></i>" .
                  _x('button', 'Add a license')
            ];
            KnowbaseItem_Item::getMassiveActionsForItemtype($actions, __CLASS__, 0, $checkitem);
        }

        return $actions;
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
            'id'                 => '2',
            'table'              => $this->getTable(),
            'field'              => 'id',
            'name'               => __('ID'),
            'massiveaction'      => false,
            'datatype'           => 'number'
        ];

        $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab[] = [
            'id'                 => '4',
            'table'              => 'glpi_monitortypes',
            'field'              => 'name',
            'name'               => _n('Type', 'Types', 1),
            'datatype'           => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '40',
            'table'              => 'glpi_monitormodels',
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
            'condition'          => ['is_visible_monitor' => 1]
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
            'id'                 => '7',
            'table'              => $this->getTable(),
            'field'              => 'contact',
            'name'               => __('Alternate username'),
            'datatype'           => 'string',
        ];

        $tab[] = [
            'id'                 => '8',
            'table'              => $this->getTable(),
            'field'              => 'contact_num',
            'name'               => __('Alternate username number'),
            'datatype'           => 'string',
        ];

        $tab[] = [
            'id'                 => '10',
            'table'              => $this->getTable(),
            'field'              => 'uuid',
            'name'               => __('UUID'),
            'datatype'           => 'string',
        ];

        $tab[] = [
            'id'                 => '70',
            'table'              => 'glpi_users',
            'field'              => 'name',
            'name'               => User::getTypeName(1),
            'datatype'           => 'dropdown',
            'right'              => 'all'
        ];

        $tab[] = [
            'id'                 => '71',
            'table'              => 'glpi_groups',
            'field'              => 'completename',
            'name'               => Group::getTypeName(1),
            'condition'          => ['is_itemgroup' => 1],
            'datatype'           => 'dropdown'
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
            'id'                 => '16',
            'table'              => $this->getTable(),
            'field'              => 'comment',
            'name'               => __('Comments'),
            'datatype'           => 'text'
        ];

        $tab[] = [
            'id'                 => '11',
            'table'              => $this->getTable(),
            'field'              => 'size',
            'name'               => __('Size'),
            'datatype'           => 'decimal',
        ];

        $tab[] = [
            'id'                 => '41',
            'table'              => $this->getTable(),
            'field'              => 'have_micro',
            'name'               => __('Microphone'),
            'datatype'           => 'bool'
        ];

        $tab[] = [
            'id'                 => '42',
            'table'              => $this->getTable(),
            'field'              => 'have_speaker',
            'name'               => __('Speakers'),
            'datatype'           => 'bool'
        ];

        $tab[] = [
            'id'                 => '43',
            'table'              => $this->getTable(),
            'field'              => 'have_subd',
            'name'               => __('Sub-D'),
            'datatype'           => 'bool'
        ];

        $tab[] = [
            'id'                 => '44',
            'table'              => $this->getTable(),
            'field'              => 'have_bnc',
            'name'               => __('BNC'),
            'datatype'           => 'bool'
        ];

        $tab[] = [
            'id'                 => '45',
            'table'              => $this->getTable(),
            'field'              => 'have_dvi',
            'name'               => __('DVI'),
            'datatype'           => 'bool'
        ];

        $tab[] = [
            'id'                 => '46',
            'table'              => $this->getTable(),
            'field'              => 'have_pivot',
            'name'               => __('Pivot'),
            'datatype'           => 'bool'
        ];

        $tab[] = [
            'id'                 => '47',
            'table'              => $this->getTable(),
            'field'              => 'have_hdmi',
            'name'               => __('HDMI'),
            'datatype'           => 'bool'
        ];

        $tab[] = [
            'id'                 => '48',
            'table'              => $this->getTable(),
            'field'              => 'have_displayport',
            'name'               => __('DisplayPort'),
            'datatype'           => 'bool'
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
            'massiveaction'      => false,
            'datatype'           => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '82',
            'table'              => $this->getTable(),
            'field'              => 'is_global',
            'name'               => __('Global management'),
            'datatype'           => 'bool',
            'massiveaction'      => false
        ];

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        $tab = array_merge($tab, Datacenter::rawSearchOptionsToAdd(get_class($this)));

        $tab = array_merge($tab, Rack::rawSearchOptionsToAdd(get_class($this)));

        return $tab;
    }

   /**
    * @param $itemtype
    *
    * @return array
    */
    public static function rawSearchOptionsToAdd($itemtype = null)
    {
        $tab = [];

        $tab[] = [
            'id'                 => 'monitor',
            'name'               => self::getTypeName(Session::getPluralNumber())
        ];

        $tab[] = [
            'id'                 => '129',
            'table'              => 'glpi_computers_items',
            'field'              => 'id',
            'name'               => _x('quantity', 'Number of monitors'),
            'forcegroupby'       => true,
            'usehaving'          => true,
            'datatype'           => 'count',
            'massiveaction'      => false,
            'joinparams'         => [
                'jointype'           => 'child',
                'condition'          => ['NEWTABLE.itemtype' => 'Monitor']
            ]
        ];

        return $tab;
    }

    public function checkAgainIfMandatoryFieldsAreCorrect(array $input):bool{
        $mandatory_missing = [];
        $incorrect_format = [];

        $fields_necessary = [
        //'entities_id' => 'number',		
        '_glpi_csrf_token' => 'string',		
        //'is_recursive' => 'bool',		
        'name' => 'string',
        'states_id' => 'number',
        'locations_id' => 'number',
        'monitortypes_id' => 'number',
        'users_id_tech' => 'number',
        'manufacturers_id' => 'number',
        'groups_id_tech' => 'number',
        'monitormodels_id' => 'number',
        'contact_num' => 'string',
        'serial' => 'string',
        'contact' => 'string',
        'otherserial' => 'string',
        'is_global' => 'number',
        'size' => 'number',
        'users_id' => 'number',
        'groups_id' => 'number',
        'uuid' => 'string',
        'comment' => 'string',
        'autoupdatesystems_id' => 'number',
        'have_micro' => 'bool',
        'have_speaker' => 'bool',
        'have_subd' => 'bool',
        'have_bnc' => 'bool',
        'have_dvi' => 'bool',
        'have_hdmi' => 'bool',
        'have_displayport' => 'bool',
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
        //'is_recursive' => 'bool',		
        'name' => 'string',
        'states_id' => 'number',
        'locations_id' => 'number',
        'monitortypes_id' => 'number',
        'users_id_tech' => 'number',
        'manufacturers_id' => 'number',
        'groups_id_tech' => 'number',
        'monitormodels_id' => 'number',
        'contact_num' => 'string',
        'serial' => 'string',
        'contact' => 'string',
        'otherserial' => 'string',
        'is_global' => 'number',
        'size' => 'number',
        'users_id' => 'number',
        'groups_id' => 'number',
        'uuid' => 'string',
        'comment' => 'string',
        'autoupdatesystems_id' => 'number',
        'have_micro' => 'bool',
        'have_speaker' => 'bool',
        'have_subd' => 'bool',
        'have_bnc' => 'bool',
        'have_dvi' => 'bool',
        'have_hdmi' => 'bool',
        'have_displayport' => 'bool',
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
        else if(array_key_exists('locations_id', $input) && $input['locations_id'] != 0 && Location::getById($input['locations_id']) == false){
            array_push($selector_ids_incorrect,'locations_id');
        }
        else if(array_key_exists('monitortypes_id', $input) && $input['monitortypes_id'] != 0 && MonitorType::getById($input['monitortypes_id']) == false){
            array_push($selector_ids_incorrect,'monitortypes_id');
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
        else if(array_key_exists('monitormodels_id', $input) && $input['monitormodels_id'] != 0 && MonitorModel::getById($input['monitormodels_id']) == false){
            array_push($selector_ids_incorrect,'monitormodels_id');
        }
        else if(array_key_exists('users_id', $input) && $input['users_id'] != 0 && User::getById($input['users_id']) == false){
            array_push($selector_ids_incorrect,'users_id');
        }
        else if(array_key_exists('groups_id', $input) && $input['groups_id'] != 0 && Group::getById($input['groups_id']) == false){
            array_push($selector_ids_incorrect,'groups_id');
        }
        else if(array_key_exists('autoupdatesystems_id', $input) && $input['autoupdatesystems_id'] != 0 && AutoUpdateSystem::getById($input['autoupdatesystems_id']) == false){
            array_push($selector_ids_incorrect,'autoupdatesystems_id');
        }
        else if(array_key_exists('id', $input) && $input['id'] != 0 && Monitor::getById($input['id']) == false){
            array_push($selector_ids_incorrect,'monitor_id');
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
        return "ti ti-device-desktop";
    }
}
