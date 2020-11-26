<?php


namespace wsy;

use Exception;

class Queue
{

    /**
     * @var null
     */
    protected static $connector = null;


    /**
     * Queue constructor.
     * @param Conf|null $conf
     */
    public function __construct(Conf $conf = null)
    {
        if (is_null(self::$connector)) {
            if (!is_null($conf->class)) {
                if(!($conf->class instanceof QueueInterface)){
                    throw new Exception('conf class must be instance of QueueInterface.');
                }else{
                    self::$connector = new $conf->class($conf->config);
                }
            } else {
                self::$connector = new \wsy\amqp\Queue();
            }
        }
    }

    /**
     * 任务生产
     * @param $job
     * @return int|string
     * @throws Exception
     */
    public function push($job)
    {
        $event = new JobEvent([
            'job' => $job
        ]);
        if (!($event->job instanceof JobInterface)) {
            throw new Exception('Job must be instance of JobInterface.');
        }
        $message = serialize($event->job);
        $event->id = self::$connector->pushMessage($message, $event->ttr);
        return $event->id;
    }

    /**
     *
     * 消费者事件监听
     *
     */
    public function listen()
    {
        self::$connector->listen();
    }

}