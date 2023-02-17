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
 * Cluster Class
 **/
class Cluster extends CommonDBTM
{
    use Glpi\Features\Clonable;

   // From CommonDBTM
    public $dohistory                   = true;
    public static $rightname                   = 'cluster';

    public function getCloneRelations(): array
    {
        return [
            NetworkPort::class
        ];
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Cluster', 'Clusters', $nb);
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong)
         ->addImpactTab($ong, $options)
         ->addStandardTab('Item_Cluster', $ong, $options)
         ->addStandardTab('NetworkPort', $ong, $options)
         ->addStandardTab('Contract_Item', $ong, $options)
         ->addStandardTab('Document_Item', $ong, $options)
         ->addStandardTab('Ticket', $ong, $options)
         ->addStandardTab('Item_Problem', $ong, $options)
         ->addStandardTab('Change_Item', $ong, $options)
         ->addStandardTab('Appliance_Item', $ong, $options)
         ->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
            'id'                 => '31',
            'table'              => 'glpi_states',
            'field'              => 'completename',
            'name'               => __('Status'),
            'datatype'           => 'dropdown',
            'condition'          => ['is_visible_cluster' => 1]
        ];

        $tab[] = [
            'id'                 => '5',
            'table'              => $this->getTable(),
            'field'              => 'uuid',
            'name'               => __('UUID'),
            'datatype'           => 'string'
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

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        return $tab;
    }

    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
                Item_Cluster::class,
            ]
        );
    }

    public function checkAgainIfMandatoryFieldsAreCorrect(array $input):bool{
        $mandatory_missing = [];
        $incorrect_format = [];

        $fields_necessary = [
            'entities_id' => 'number',
            '_glpi_csrf_token' => 'string',
            //'is_recursive' => '',
            'name' => 'string',
            'states_id' => 'number',
            'clustertypes_id' => 'number',
            'users_id_tech' => 'number',
            'groups_id_tech' => 'number',
            'uuid' => 'string',
            'version' => 'string',
            'comment' => 'string',
            'autoupdatesystems_id' => 'number'
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

    public function checkAppliedBusinessRules(array &$input):bool{
        
        $selector_ids_incorrect = [];

        if($input['entities_id'] != 0 && Entity::getById($input['entities_id']) == false){
            array_push($selector_ids_incorrect,'entities_id');
        }
        else if($input['states_id'] != 0 && State::getById($input['states_id']) == false){
            array_push($selector_ids_incorrect,'states_id');
        }
        else if($input['clustertypes_id'] != 0 && ClusterType::getById($input['clustertypes_id']) == false){
            array_push($selector_ids_incorrect,'clustertypes_id');
        }
        else if($input['users_id_tech'] != 0 && User::getById($input['users_id_tech']) == false){
            array_push($selector_ids_incorrect,'users_id_tech');
        }
        else if($input['groups_id_tech'] != 0 && Group::getById($input['groups_id_tech']) == false){
            array_push($selector_ids_incorrect,'groups_id_tech');
        }
        else if($input['autoupdatesystems_id'] != 0 && AutoUpdateSystem::getById($input['autoupdatesystems_id']) == false){
            array_push($selector_ids_incorrect,'autoupdatesystems_id');
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
        return "ti ti-hierarchy-2";
    }
}
