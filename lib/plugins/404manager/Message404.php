<?php
/**
 * Created by IntelliJ IDEA.
 * User: gerard
 * Date: 18/06/2018
 * Time: 20:56
 */

class Message404
{


    private $content = "";
    private $type = self::TYPE_CLASSIC;

    const TYPE_CLASSIC = 'Classic';
    const TYPE_WARNING = 'Warning';

    /**
     * Message404 constructor.
     */
    public function __construct()
    {

    }

    public function addContent($message)
    {
        $this->content .= $message;
    }

    public function setType($type)
    {
        $this->type=$type;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getType()
    {
        return $this->type;
    }

}