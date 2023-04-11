<?php


include('ControlQueue.php');
use Glpi\Http\Response;
Class HandlerSubmitForm {

    /**
     * Add an element to Db after press submit button in form
     * @param $entity An specific entity
     * @param string $nameQueue Name of Queue on var $_Session
     * @return int Return 0 if it fails or a id number if it success
     */
    public static function add($entity, string $nameQueue):int{

        if(!isset($_SESSION[$nameQueue])){
            $ctrlQueue = new ControlQueue();
            $_SESSION[$nameQueue] = serialize($ctrlQueue);

            /*Toolbox::logInFile(
                'event_queue',
                sprintf(
                    __('%1$s: %2$s'),
                    basename(__FILE__,'.php'),
                    sprintf(
                        __('Se creo la instancia para la cola %s') . "\n",
                        $nameQueue
                    )
                )
            );*/
        }

        $ctrlQueueAdd = unserialize($_SESSION[$nameQueue]);
        $registry = $ctrlQueueAdd->getRegistryQueue();
    
        $newID = 0;
       
        if($ctrlQueueAdd->checkAnormalTimestampOnQueueItems()){
            //Session::cleanOnLogout();
            //Session::redirectIfNotLoggedIn();
            Response::sendError(404, 'Accion bloqueada - Muchas solicitudes');
        }else{
            $newID = $entity->add($_POST);
        }

        $currentDatetime = new DateTime(null,new DateTimeZone('America/Lima'));
              
        if($registry->count() === 6){
            $ctrlQueueAdd->popTopRegistryItem();
        }
        $ctrlQueueAdd->addRegistryItem($currentDatetime->format('Y-m-d H:i:s'));

        $_SESSION[$nameQueue] = serialize($ctrlQueueAdd);

        return $newID;
    }

    public static function update($entity, string $nameUpdtQueue): bool{
        if(!isset($_SESSION[$nameUpdtQueue])){
            $ctrlQueue = new ControlQueue();
            $_SESSION[$nameUpdtQueue] = serialize($ctrlQueue);
        }

        $ctrlQueueUpdate = unserialize($_SESSION[$nameUpdtQueue]);
        $registry = $ctrlQueueUpdate->getRegistryQueue();

        $success = 0;
        if($ctrlQueueUpdate->checkAnormalTimestampOnQueueItems()){
            //Session::cleanOnLogout();
            //Session::redirectIfNotLoggedIn();
            Response::sendError(404, 'Accion bloqueada - Muchas solicitudes');
        }else{
            $success = $entity->update($_POST);
        }

        $currentDatetime = new DateTime(null,new DateTimeZone('America/Lima'));
              
        if($registry->count() === 6){
            $ctrlQueueUpdate->popTopRegistryItem();
        }
        $ctrlQueueUpdate->addRegistryItem($currentDatetime->format('Y-m-d H:i:s'));

        $_SESSION[$nameUpdtQueue] = serialize($ctrlQueueUpdate);

        return $success;
    }

    public static function post(GLPIUploadHandler $upload_handler,string $name_uploadQueue):array {

        //Response::sendError(404, 'Accion bloqueada - Muchas solicitudes de subir archivos, Maximo 10 en 30 seconds');
        $response = [];

        date_default_timezone_set("America/Lima");
        $current_time = date("Y-m-d H:i:s");

        if(!isset($_SESSION[$name_uploadQueue])){
            $ctrlQueue = new ControlQueue();
            $_SESSION[$name_uploadQueue] = serialize($ctrlQueue);
        }

        $ctrlQueueUpload = unserialize($_SESSION[$name_uploadQueue]);
        $registry = $ctrlQueueUpload->getRegistryQueue();
        
        /*if(!array_key_exists('n_uploads_generated',$_SESSION)){
            $_SESSION['n_uploads_generated'] = 0;
        }*/

        if(!array_key_exists('upload_waittime',$_SESSION)){
            $_SESSION['upload_waittime'] = null;
        }

    
        /*if($_SESSION['n_uploads_generated'] == 10){
            $_SESSION['n_uploads_generated'] = 0;
    
            
            $_SESSION['upload_waittime'] = date("H:i:s", strtotime($current_time.' +30 seconds')); 
        }
        */

        if($ctrlQueueUpload->checkAnormalTimestampOnQueueFiles()){ //10 subidas de archivos dentro de 30 segundos
            //$_SESSION['n_uploads_generated'] = 0;
    
            $ctrlQueueUpload->clearRegistry();
            $_SESSION[$name_uploadQueue] = serialize($ctrlQueueUpload);

            $_SESSION['upload_waittime'] = date("H:i:s", strtotime($current_time.' +300 seconds')); 
        }

    
        if(strtotime(date("Y-m-d H:i:s")) < strtotime($_SESSION['upload_waittime'])){
            $dateObject = new DateTime($_SESSION['upload_waittime']);
            
            Response::sendError(404, 'Accion bloqueada - Muchas solicitudes de subir archivos. Maximo 10 en 30 segundos, espera hasta las '.$dateObject->format('h:i:s A')." para volver a intentarlo");
            
            return $response;

        }else{
            //$_SESSION['n_uploads_generated'] =  $_SESSION['n_uploads_generated'] + 1;
            $currentDatetime = new DateTime(null,new DateTimeZone('America/Lima'));

            if($registry->count() === 10){
                $ctrlQueueUpload->popTopRegistryItem();
            }
              
            $ctrlQueueUpload->addRegistryItem($currentDatetime->format('Y-m-d H:i:s'));
            $_SESSION[$name_uploadQueue] = serialize($ctrlQueueUpload);
            
            $response = $upload_handler->post(false);
            return $response; 
        }
              
     
        

        

        /*Toolbox::logInFile(
            'event_queue',
            sprintf(
                __('%1$s: %2$s'),
                basename(__FILE__,'.php'),
                sprintf(
                    __('Me estoy ejecutando, el response no me rebota %s') . "\n",
                    $namePostQueue
                )
            )
        );*/

        
    }
}