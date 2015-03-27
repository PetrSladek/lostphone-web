<?php

namespace App\Presenters;

use App\Model\Commands\Command;
use App\Model\Device;
use App\Model\Image;
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
use App\Services\ImageService;
use App\Services\MessageService;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\Responses\TextResponse;
use Nette\Http\FileUpload;
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

    /** @var EntityManager
     * @inject
     */
    public $em;

    /**
     * @var MessageService
     * @inject
     */
    public $messageService;


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

        $this->messageService->proccessRecievedData($this->device, $this->input);
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
