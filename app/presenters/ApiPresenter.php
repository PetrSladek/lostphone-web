<?php

namespace App\Presenters;

use App\Model\Commands\Command;
use App\Model\Device;
use App\Model\Messages\GotchaMessage;
use App\Model\Messages\LocationMessage;
use App\Model\Messages\LogMessage;
use App\Model\Messages\Message;
use App\Model\Messages\PongMessage;
use App\Model\Messages\RegistrationMessage;
use App\Model\Messages\RingingTimeoutMessage;
use App\Model\Messages\SimStateChangedMessage;
use App\Model\Messages\UnlockMessage;
use App\Model\Messages\WrongPassMessage;
use App\Model\User;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\Responses\TextResponse;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Tracy\Debugger;


/**
 * Api presenter.
 */
class ApiPresenter extends BasePresenter
{

    /** @var EntityManager @inject */
    public $em;

    /**
     * @var ArrayHash
     */
    public $input;

    /**
     * @var int GCM ID zareizemi od ktereho prisel event
     */
    public $gcmId;

    /**
     * @var Device|null nalezene zarizeni podle GCM ID
     */
    public $device;

    protected function getJsonBody() {
        return (array) json_decode(file_get_contents("php://input"));
    }

    protected function startup()
    {
        parent::startup();

        $gcmId = $this->getHttpRequest()->getHeader('device');
        Debugger::log($gcmId);
        // nacte Zarizeni z DB podle gcmId
        $this->device = $this->em->getRepository(Device::getClassName())->findOneBy(['gcmId'=>$gcmId]);

        $data = array_merge(
            $this->getJsonBody(),
            $this->getHttpRequest()->getPost(),
            $this->getHttpRequest()->getFiles()
        );

        Debugger::log(print_r($data, true));
        $this->input = ArrayHash::from($data);
    }

    public function actionDefault() {

        $this->sendResponse(new TextResponse("ERR"));
    }

    public function actionMessages() {

        switch($this->input->type) {
            case Message::TYPE_PONG:
                $msg = new PongMessage();
            break;
            case Message::TYPE_REGISTRATION:
                // tady bude gcmId null
                $msg = new RegistrationMessage();
                $msg->setGcmId($this->input->gcmId);
                $msg->setIdentifier($this->input->identifier);
                $msg->setGoogleAccountEmail($this->input->googleAccountEmail);
                $msg->setBrand($this->input->brand);
                $msg->setModel($this->input->model);

//                $device = $this->em->getRepository(Device::getClassName())->findOneBy(['identifier'=>$msg->getIdentifier()]);
//                if(!$device) {
                $this->device = new Device();
                $this->device->setName(sprintf("%s %s", $msg->getBrand(), $msg->getModel()));
                $this->device->setIdentifier($msg->getIdentifier());
//                }
                $this->device->setGcmId($msg->getGcmId());
                $this->device->setRegistrationMessage($msg);

                // najdi ownera podle emailu
                $owner = $this->em->getRepository(User::getClassName())->findOneBy(['googleEmail'=>$msg->getGoogleAccountEmail()]);
                if($owner)
                    $this->device->setOwner($owner);

                $this->em->persist($this->device);

                break;

            case Message::TYPE_GOTCHA:
                $msg = new GotchaMessage();
                break;

            case Message::TYPE_RINGINGTIMEOUT:
                $msg = new RingingTimeoutMessage();
            break;

            case Message::TYPE_UNLOCK:
                $msg = new UnlockMessage();
            break;

            case Message::TYPE_WRONGPASS:
                $msg = new WrongPassMessage();
                // TODO save Photo
                $msg->setFrontPhoto(null);
            break;

            case Message::TYPE_LOCATION:
                $msg = new LocationMessage();
                $msg->setLat($this->input->lat);
                $msg->setLng($this->input->lng);
            break;

            case Message::TYPE_SIMSTATECHANGED:
                $msg = new SimStateChangedMessage();
                $msg->setImei($this->input->imei);
                $msg->setSubscriberId($this->input->subscriberId);
                $msg->setPhoneNumber($this->input->phoneNumber);
                $msg->setNetworkOperator($this->input->networkOperator);
                $msg->setNetworkOperatorName($this->input->networkOperatorName);
                $msg->setNetworkCountryIso($this->input->networkCountryIso);
                $msg->setSimOperator($this->input->simOperator);
                $msg->setSimOperatorName($this->input->simOperatorName);
                $msg->setSimCountryIso($this->input->simCountryIso);
                $msg->setSimSerialNumber($this->input->simSerialNumber);
            break;

            case Message::TYPE_LOG:
                $msg = new LogMessage();
//                $this->input->callLog = "+420732288134|OUTGOING|2014-12-12 12:22:34|1200\n";
//                $this->input->callLog .= "+420123456789|INCOMING|2014-12-13 13:33:45|1222\n";
//
//                $this->input->smsLog = "+420732288134|OUTBOX|2014-12-12 12:22:34|Nazdar vanilko\n";
//                $this->input->smsLog .= "+420123456789|INBOX|2014-12-13 13:33:45|No nazdar\n";

                $msg->setCallLog( LogMessage::parseCallLog($this->input->callLog) );
                $msg->setSmsLog( LogMessage::parseSMSLog($this->input->smsLog) );
            break;

            default:
                $this->sendResponse(new TextResponse(1));
                $this->terminate();
            break;
        }

        $msg->setDateSent(new DateTime($this->input->date));

        if($this->device) {
            $this->device->addMessage($msg);

            $this->em->persist($msg);
            $this->em->flush();

            $this->sendResponse(new TextResponse(0));
        } else {
            $this->sendResponse(new TextResponse(1));
        }


    }


    public function actionAck($id) {
        /** @var Command $cmd */
        $cmd = $this->em->find( Command::getClassName(), $id );
        $cmd->setDateAck( new DateTime($this->input->date) );

        $this->em->flush();

        $this->sendResponse(new TextResponse(0));
    }





}
