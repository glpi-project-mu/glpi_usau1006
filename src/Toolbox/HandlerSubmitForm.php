<?php


include('ControlQueue.php');

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
            Session::cleanOnLogout();
            Session::redirectIfNotLoggedIn();
        }else{
            $newID = $entity->add($_POST);
        }

        $currentDatetime = new DateTime(null,new DateTimeZone('America/Lima'));
              
        if($registry->count() === 3){
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
            Session::cleanOnLogout();
            Session::redirectIfNotLoggedIn();
        }else{
            $success = $entity->update($_POST);
        }

        $currentDatetime = new DateTime(null,new DateTimeZone('America/Lima'));
              
        if($registry->count() === 3){
            $ctrlQueueUpdate->popTopRegistryItem();
        }
        $ctrlQueueUpdate->addRegistryItem($currentDatetime->format('Y-m-d H:i:s'));

        $_SESSION[$nameUpdtQueue] = serialize($ctrlQueueUpdate);

        return $success;
    }
}