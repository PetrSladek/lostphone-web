<?php
/**
 * Presenter, který volá mobilní aplikace při zasílání zpráv a ACK přes HTTP
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */


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

    /**
     * Doctrine Entity manažer
     * @var EntityManager
     * @inject Připojí se sám z DI kontejneru
     */
    public $em;

    /**
     * Služba pro práci se zprávami
     * @var MessageService
     * @inject Připojí se sám z DI kontejneru
     */
    public $messageService;


    /**
     * Data získaná z HTTP requestu
     * @var ArrayHash
     */
    public $input;

    /**
     * GCM ID zareizeni od ktereho přišla zpráva
     * @var int
     */
    public $gcmId;

    /**
     * Nalezené zařízení podle GCM ID
     * @var Device|null
     */
    public $device;


    /**
     * Metoda, která se spouští na začátku životního cyklu HTTP requestu
     */
    protected function startup()
    {
        parent::startup();

        // z HTTP hlavicky zjistí GCM ID zařízení
        $gcmId = $this->getHttpRequest()->getHeader('device');

        Debugger::log($gcmId);

        // nacte Zarizeni z DB podle gcmId
        $this->device = $this->em->getRepository(Device::getClassName())->findOneBy(['gcmId'=>$gcmId]);

        // Spojí data z body, postu a souborů
        $data = array_merge(
            $this->getJsonBody(),
            $this->getHttpRequest()->getPost(),
            $this->getHttpRequest()->getFiles()
        );

        Debugger::log(print_r($data, true));

        $this->input = ArrayHash::from($data);
    }

    /**
     * Vrátí body HTTP požadavku, jako pole
     * @return array rozkodovaný JSON z body HTTP požadavku
     */
    protected function getJsonBody() {
        return (array) json_decode(file_get_contents("php://input"));
    }


    /**
     * /api/ vrati jen ERR
     */
    public function actionDefault() {
        $this->sendResponse(new TextResponse("ERR"));
    }

    /**
     * /api/messages zpracuje prichozi zprávy a vrati chybový kod 0 (vse OK)
     */
    public function actionMessages() {

        $this->messageService->proccessRecievedData($this->device, $this->input);
        $this->em->flush();

        $this->sendResponse(new TextResponse(0));
    }


    /**
     * /api/ack/<commandId> zpracuje prichozi ACK a vrati chybový kod 0 (vse OK)
     */
    public function actionAck($id) {

        $this->messageService->ackCommand($id, $this->input->date, $this->device);
        $this->em->flush();

        $this->sendResponse(new TextResponse(0));
    }





}
