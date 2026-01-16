<?php

namespace App\Notifications;

class BaseNotification
{
    protected $via = [];

    protected $attributes = [];

    protected $type;

    protected $manager = null;

    public $force_email = false;

    public function __construct()
    {
        $this->type = get_class($this);
        $this->manager = new NotificationService;
    }

    public function getType()
    {
        return $this->type;
    }
    public function toDatabase()
    {
        return [];
    }

    public function getTitle()
    {
        return '';
    }

    public function getBody()
    {
        return '';
    }

    public function getParams()
    {
        $params = $this->getAttribute('data')['params'] ;

        if(is_string($params)){
            $params = json_decode($params , true) ;
        }

        return $params ;
    }

    /**
     * Set a value tu notification.
     *
     * @param $key string  Name of attribute
     * @param $value string|Model  Value of attribute
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function getAttribute($key, $default = null)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return $default;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function keys()
    {
        return array_keys($this->attributes);
    }

    public function notify($users)
    {
        foreach ($this->via as $channel) {
            $this->manager->send($this, $users, $channel);
        }
    }
}
