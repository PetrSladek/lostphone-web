<?php
/**
 * Zpráva ze zařízení s výpisem volání a SMS.
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */


namespace App\Model\Messages;


use App\Model\Commands\Command;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/**
 * @Entity
 */
class LogMessage extends Message {

    /**
     * Výpis volání
     * @Column(type="json_array", nullable=true)
     * @var array
     */
    protected $callLog;

    /**
     * Výpis posledních SMS
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
     * Deserialuzuje výpis volání
     * @param string $input
     * @return array
     */
    public static function parseCallLog($input) {
        $headers = ['number','direction','date','duration'];
        return self::parseLog($headers, $input);
    }
    /**
     * Deserialuzuje výpis SMS
     * @param string $input
     * @return array
     */
    public static function parseSMSLog($input) {
        $headers = ['number','box','date','body'];
        return self::parseLog($headers, $input);
    }

    /**
     * Deserialuzuje výpis volání/sms
     * @param array $headers Pole hlaviček
     * @param string $rows Data
     * @param string $delimiterRows Oddelovac řádků
     * @param string $delimiterCells Oddelovač sloupců
     * @return array
     */
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