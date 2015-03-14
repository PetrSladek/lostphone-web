<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 28.2.2015
 * Time: 11:50
 */

namespace App\Model\Messages;


use App\Model\Commands\Command;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/**
 * Class LocationMessage
 * @package App\Model
 * @Entity
 */
class LogMessage extends Message {

    /**
     * @Column(type="json_array", nullable=true)
     * @var array
     */
    protected $callLog;

    /**
     * @Column(type="json_array", nullable=true)
     * @var array
     */
    protected $smsLog;



    /**
     * @return int
     */
    public function getType()
    {
        return Message::TYPE_LOG;
    }



    /**
     * @return array
     */
    public function getCallLog()
    {
        return $this->callLog;
    }

    /**
     * @param array
     */
    public function setCallLog($callLog)
    {
        $this->callLog = $callLog;
    }

    /**
     * @return array
     */
    public function getSmsLog()
    {
        return $this->smsLog;
    }

    /**
     * @param array $smsLog
     */
    public function setSmsLog($smsLog)
    {
        $this->smsLog = $smsLog;
    }


    /**
     * @param string $input
     * @return array
     */
    public static function parseCallLog($input) {
        $headers = ['number','direction','date','duration'];
        return self::parseLog($headers, $input);
    }
    /**
     * @param string $input
     * @return array
     */
    public static function parseSMSLog($input) {
        $headers = ['number','box','date','body'];
        return self::parseLog($headers, $input);
    }

    protected static function parseLog($headers, $rows, $delimiterRows = "\n", $delimiterCells = "|") {
        $rows = trim((string) $rows);
        if(empty($rows))
            return [];

        $log = [];
        foreach(explode($delimiterRows, (string) $rows) as $row) {
            $cells = explode($delimiterCells, $row);
            if(count($cells) != count($headers))
                throw new \InvalidArgumentException("Row is wrong count column. Must have ".count($headers)." \"".implode($delimiterCells, $headers)."\" and get ".count($cells)." \"".$row."\"");
            $log[] = array_combine($headers, $cells);
        }
        return $log;
    }




}