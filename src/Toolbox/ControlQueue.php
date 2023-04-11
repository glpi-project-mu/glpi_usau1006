<?php

Class ControlQueue {
    protected $registry;
    protected static $instance;

    public function __construct(){
        $this->registry = new SplQueue();
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
        // late static binding
            self::$instance = new self;
            
        }
        return self::$instance;
    }

    public function addRegistryItem(string $item):void{
        $this->registry->enqueue($item);
    }

    public function getRegistryQueue(): SplQueue{
        return $this->registry;
    }

    public function popTopRegistryItem(): void{
        $this->registry->dequeue();
    }

    public function clearRegistry():void{
        unset($this->registry);
        $this->registry = new SplQueue();
    }

    /**
       * this function checks if there are 3 queued items whose timestamps
       * differ by less than 1 second between the 3
       * @return bool
       */  
    public function checkAnormalTimestampOnQueueItems() : bool{
        if($this->registry->count() === 3){
            $this->registry->rewind();
            $time1 = strtotime($this->registry->current());
            $this->registry->next();
            //$time2 = strtotime($this->registry->current());
            $this->registry->next();
            //$time3 = strtotime($this->registry->current());
            $this->registry->next();
            //$time4 = strtotime($this->registry->current());
            $this->registry->next();
            //$time5 = strtotime($this->registry->current());
            $this->registry->next();
            $time6 = strtotime($this->registry->current());

            /*Toolbox::logInFile(
                'diferencia_seg',
                sprintf(
                    __('%1$s: %2$s'),
                    basename(__FILE__,'.php'),
                    sprintf(
                        __('Tiempo 1: %s, Tiempo 2: %s, Tiempo 3: %s') . "\n",
                        date('m/d/Y H:i:s', $time1),
                        date('m/d/Y H:i:s', $time2),
                        date('m/d/Y H:i:s', $time3),
                    )
                )
            );*/

            if (
                (abs($time6 - $time1) <= 4)
                //|| ($time3 - $time2 === 0)
                ) 
            {
                return true;
            }

            return false;
        }
        return false;
    }

    public function checkAnormalTimestampOnQueueFiles() : bool{
        if($this->registry->count() === 10){
            $this->registry->rewind();
            $time1 = strtotime($this->registry->current());
            $this->registry->next();
            //$time2 = strtotime($this->registry->current());
            $this->registry->next();
            //$time3 = strtotime($this->registry->current());
            $this->registry->next();
            //$time4 = strtotime($this->registry->current());
            $this->registry->next();
            //$time5 = strtotime($this->registry->current());
            $this->registry->next();
            //$time6 = strtotime($this->registry->current());
            $this->registry->next();
            //$time7 = strtotime($this->registry->current());
            $this->registry->next();
            //$time8 = strtotime($this->registry->current());
            $this->registry->next();
            //$time9 = strtotime($this->registry->current());
            $this->registry->next();
            $time10 = strtotime($this->registry->current());

            /*Toolbox::logInFile(
                'diferencia_seg',
                sprintf(
                    __('%1$s: %2$s'),
                    basename(__FILE__,'.php'),
                    sprintf(
                        __('Tiempo 1: %s, Tiempo 2: %s, Tiempo 3: %s') . "\n",
                        date('m/d/Y H:i:s', $time1),
                        date('m/d/Y H:i:s', $time2),
                        date('m/d/Y H:i:s', $time3),
                    )
                )
            );*/

            if (
                (abs($time10 - $time1) <= 30) //maximo 10 archivos en 30 segundos
                //|| ($time3 - $time2 === 0)
                ) 
            {
                return true;
            }

            return false;
        }
        return false;
    }

}