<?php

namespace App\Presenters;

use App\Model\Commands\Command;
use App\Model\Messages\LocationMessage;
use App\Model\Messages\Message;
use App\Model\Messages\PongMessage;
use App\Model\Messages\RegistrationMessage;
use App\Model\Messages\RingingTimeoutMessage;
use App\Model\Messages\UnlockMessage;
use App\Model\Messages\WrongPassMessage;
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

    protected function getPut() {
//        $put = null;
//        try {
//            $put = Json::encode(file_get_contents("php://input"));
//        } catch(JsonException $e) {}
//
//        return empty($put) ? array() : (array) $put;

        return (array) json_decode(file_get_contents("php://input"));
    }

    protected function startup()
    {
        parent::startup();

        $data = array_merge(
            $this->getPut(),
            $this->getHttpRequest()->getPost(),
            $this->getHttpRequest()->getFiles()
        );

        Debugger::log(print_r($data, true));

        $this->input = ArrayHash::from($data);
    }

    public function actionDefault() {

//        dump(new DateTime("Wed Mar 04 12:26:57 CET 2015"));

        $this->sendResponse(new TextResponse("ERR"));
    }

    public function actionMessages() {

        switch($this->input->type) {
            case Message::TYPE_PONG:
                $msg = new PongMessage();
            break;
            case Message::TYPE_REGISTRATION:
                $msg = new RegistrationMessage();
                $msg->setGcmId($this->input->gcmId);
                $msg->setImei($this->input->imei);
                $msg->setGoogleAccountEmail($this->input->googleAccountEmail);
                break;
            case Message::TYPE_GOTCHA:
                $msg = new RingingTimeoutMessage();
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
            default:
                $this->sendResponse(new TextResponse(1));
                $this->terminate();
            break;
        }

        $msg->setDateSent(new DateTime($this->input->date));

        $this->em->persist($msg);
        $this->em->flush();


        $this->sendResponse(new TextResponse(0));
    }


    public function actionAck($id) {
        /** @var Command $cmd */
        $cmd = $this->em->find( Command::getClassName(), $id );
        $cmd->setDateAck( new DateTime($this->input->date) );

        $this->em->flush();

        $this->sendResponse(new TextResponse(0));
    }





}
