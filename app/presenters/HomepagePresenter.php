<?php
/**
 * Presenter hlavní obrazovky po přihlášení (Mapa, příkazy atd..)
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */

namespace App\Presenters;

use App\Model\Commands\Command;
use App\Model\Commands\EncryptStorageCommand;
use App\Model\Commands\GetLogCommand;
use App\Model\Commands\LocateCommand;
use App\Model\Commands\LockCommand;
use App\Model\Commands\PingCommand;
use App\Model\Commands\RingCommand;
use App\Model\Commands\WipeDataCommand;
use App\Model\Device;
use App\Model\Messages\LocationMessage;
use App\Model\Messages\Message;
use Gcm\Http\Sender;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\InvalidStateException;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;
use Nette\Utils\Random;
use Nette\Utils\Strings;


class HomepagePresenter extends BasePresenter
{

    /**
     * Doctrine Entity manažer
     * @var EntityManager
     * @inject Pripojí se sám DI contejneru
     */
    public $em;

    /**
     * GCM Sender pro posilani zprav pres HTTP
     * @var Sender
     * @inject Pripojí se sám DI contejneru
     */
    public $gcm;


    /**
     * ID zařízení
     * @var int
     * @persistent Prenasi se sám v URL adrese
     */
    public $deviceId = null;

    /**
     * Entita vybraného zaříázení
     * @var Device
     */
    protected $device;

    /**
     * Repozitář se zařízeními
     * @var EntityRepository
     */
    protected $devices;


    /**
     * Metoda, která se spouští na začátku životního cyklu HTTP requestu
     */
    protected function startup()
    {
        parent::startup();

        if(!$this->user->isLoggedIn()) {
            $this->flashMessage('Musíte se nejdříve přihlásit');
            $this->redirect('Sign:in');
        }

        // Vytáhnu repozitář se zařízeními
        $this->devices = $this->em->getRepository(Device::getClassName());


        // Pokud je zadané ID zařízení pokusím se ho nalézt V DB
        if($this->deviceId) {
            $this->device = $this->devices->find($this->deviceId);
            if($this->device->getOwner() !== $this->me) // Ověřím jestli je moje
                $this->error("Toto zarizeni neni vase", IResponse::S403_FORBIDDEN); // Pokud ne, vrátím chybu
        }

        if(!$this->device) // Pokud není zařízení definované vytáhnu první zařízení uživatele
            $this->device = $this->me->getFirstDevice();

        // Předám zařízení do šablony
        $this->template->device = $this->device;
    }


    /**
     * Továrnička na formulář pro změnu zařízení
     * @return Form
     */
    protected function createComponentFrmChangeDevice()
    {
        $form = new Form();

        $devices = [];
        foreach($this->me->getDevices() as $device)
            $devices[$device->getId()] = $device->getName();

        $form->addSelect('deviceId', 'Zařízení:', $devices)
             ->setDefaultValue($this->deviceId);

        $form->addSubmit("send", "Potvrdit");
        $form->onSuccess[] = $this->frmChangeDeviceSucceeded;

        return $form;
    }

    /**
     * Call back po odeslání předchozího formuláře
     * @param Form $form
     */
    public function frmChangeDeviceSucceeded(Form $form)
    {
        $this->deviceId = $form->values->deviceId == $this->me->getFirstDevice()->getId() ? null : $form->values->deviceId;
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }


    /**
     * Vykreslení výchozí stránky
     */
    public function renderDefault() {

        // Pokud mám zařízení načtu poslední zprávy a příkazy
        if($this->device) {
            $repo = $this->em->getRepository(Message::getClassName());
            $messages = $repo->findBy(['device' => $this->device], ['dateSent' => 'desc'], 10);
            $this->template->messages = $messages;

            $repo = $this->em->getRepository(Command::getClassName());
            $commands = $repo->findBy(['device' => $this->device], ['dateSent' => 'desc'], 10);
            $this->template->commands = $commands;
        }

        // Zjistim poslední lokaci zažízení
        $lastLocalization = false;
        if($this->device) {
            $repo = $this->em->getRepository(LocationMessage::getClassName());
            $pos = new ArrayHash();
            $lastLocalization = $repo->findOneBy(['device' => $this->device], ['dateSent' => 'desc'], 1);
            /** @var $lastLocalization LocationMessage */
            if ($lastLocalization) {
                $pos->lat = $lastLocalization->getLat();
                $pos->lng = $lastLocalization->getLng();
                $pos->zoom = 15;
                $pos->find = $lastLocalization->getDateSent()->format('j.n.Y H:i:s');
            }
        }
        // Pokud nemám lokaci, vytáhnu defaultní z configu
        if(!$lastLocalization) {
            $pos->lat = (float) $this->config->map->defaultPosition->lat;
            $pos->lng = (float) $this->config->map->defaultPosition->lng;
            $pos->zoom = (int) $this->config->map->defaultPosition->zoom;
            $pos->find = false;
        }
        // Předám lokaci do šablony
        $this->template->pos = $pos;



        // Předám lokaci a zařízení do ajaxového payloadu
        $this->payload->position = $pos;
        $this->payload->device = $this->device ? [
            'name' => $this->device->getName(),
            'locked' => $this->device->isLocked()
        ] : null;


    }


    /**
     * Továrnička na formulář uzamknutí zařízení
     * @return Form
     */
    protected function createComponentFrmLock()
    {
    	$form = new Form();

        $form->addText("displayText", 'Text na display');
        $form->addText("ownerPhoneNumber", 'Číslo pro zavolání zpět');
        $form->addText('pin', 'PIN  pro odekmnutí')
             ->setRequired('Musíte zadat PIN')
             ->setAttribute('type','numeric');

        $form->addSubmit("send", "Odeslat");
    	$form->onSuccess[] = $this->frmLockSucceeded;

        $this->prepareRenderer($form, $ajax=true);
    	return $form;
    }



    /**
     * Callback po odeslání předchozího formuláře
     * @param Form $form
     */
    public function frmLockSucceeded(Form $form)
    {
        $values = $form->values;
        $cmd = new LockCommand();

        $cmd->setDisplayText($values->displayText);
        $cmd->setOwnerPhoneNumber($values->ownerPhoneNumber);
        $cmd->setPassword($values->pin);

        $this->sendCommand($cmd);

        $this->payload->command = [
            'id' => $cmd->getId(),
            'text' => 'Uzamknout'
        ];
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');

    }


    /**
     * Akce obnovení dat (volaná ajaxem po čase, nebo po oznámení přes websockets)
     */
    public function handleRefresh() {

        // Repozitář s příkazy
        $repo = $this->em->getRepository(Command::getClassName());

        //Poslední ACK, které ještě užřivatel neviděl
        $ackeds = $repo->findBy(['device' => $this->device, 'dateAck !='=>null, 'dateViewAck'=>null], ['dateSent' => 'desc']);

        // tyto ACK přidám do AJAX payloadu a označním je v DB jako již zobrazené
        $now = new DateTime();
        foreach($ackeds as $ack) {
            $this->payload->ackeds[] = $this->commandToPayload($ack);
            $ack->dateViewAck = $now;
        }
        $this->em->flush();

        // Překreslím snippet se zprávami (tím se přidá do ajax payloadu)
        $this->redrawControl('messages');

        // Pokud není ajax přesměruju na sebe
        !$this->isAjax() ?  $this->redirect('this') : null;
    }

    /**
     * Akce Ping! (Pošle příkaz Ping do zařízení)
     */
    public function handlePing()
    {
        $cmd = new PingCommand();

        // Posle příkaz do zařízení a do webového prohlížeče
        $this->processHandleCommand($cmd);
    }

    /**
     * Akce Ring! (Pošle příkaz Ring do zařízení)
     */
    public function handleRing()
    {
        $cmd = new RingCommand();
        $cmd->setCloseAfter(60*1000); // Vzpnout po 60s

        // Posle příkaz do zařízení a do webového prohlížeče
        $this->processHandleCommand($cmd);
    }

    /**
     * Akce Lock! (Pošle příkaz Lock do zařízení)
     */
    public function handleLock()
    {
        $cmd = new LockCommand();

        $cmd->setDisplayText(null); // na displej nic
        $cmd->setOwnerPhoneNumber("+420000000000"); // telefoní číslo zatím žádný
        $cmd->setPassword( Random::generate(16) ); // heslo vyhenegujeme zatím náhodné

        // Posle příkaz do zařízení a do webového prohlížeče
        $this->processHandleCommand($cmd);
    }

    /**
     * Akce Locate! (Pošle příkaz Locate do zařízení)
     */
    public function handleLocate()
    {
        $cmd = new LocateCommand();

        // Posle příkaz do zařízení a do webového prohlížeče
        $this->processHandleCommand($cmd);
    }

    /**
     * Akce GetLog! (Pošle příkaz GetLog do zařízení)
     */
    public function handleGetLog()
    {
        $cmd = new GetLogCommand();

        // Posle příkaz do zařízení a do webového prohlížeče
        $this->processHandleCommand($cmd);
    }

    /**
     * Akce EncryptStorage! (Pošle příkaz EncryptStorage do zařízení)
     */
    public function handleEncryptStorage()
    {
        $cmd = new EncryptStorageCommand();

        // Posle příkaz do zařízení a do webového prohlížeče
        $this->processHandleCommand($cmd);
    }

    /**
     * Akce handleWipeData! (Pošle příkaz handleWipeData do zařízení)
     */
    public function handleWipeData()
    {
        $cmd = new WipeDataCommand();

        // Posle příkaz do zařízení a do webového prohlížeče
        $this->processHandleCommand($cmd);
    }

    /**
     *  Posle příkaz do zařízení a do webového prohlížeče a uložé ho do DB
     * @param Command $cmd
     */
    protected function processHandleCommand(Command $cmd) {

        // Poslat příkaz přes GCM
        $this->sendCommand($cmd);
        // Pošle ajaxem název příkazu apod.
        $this->payload->command = $this->commandToPayload($cmd);

        // Pokud je ajax přidá do payloadu všechny snippety, jinak přesměruje na sebe
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    /**
     * Pošle příkaz přes GCM do zařízení a uloží ho do DB
     * @param Command $cmd
     * @return \Gcm\Http\Response
     * @throws \Gcm\Http\IlegalApiKeyException
     * @throws \Gcm\Http\RuntimeException
     */
    protected function sendCommand(Command $cmd) {

        if(!$this->device)
            throw new InvalidStateException("Not choosen device");

        $cmd->setDateSent(new DateTime());
        $cmd->setDevice($this->device);

        $this->em->persist($cmd);
        $this->em->flush();

        $message = new \Gcm\Message($this->device->getGcmId(), $cmd->toGCMdata(), "collapse-".$cmd->getType());
        return $this->gcm->send($message);
    }

    /**
     * Vrátí název a potřebná data z příkazu pro AJAXovou odpověd
     * @param Command $command
     * @return array
     */
    protected function commandToPayload(Command $command) {
        $ret = ['id' => $command->getId()];
        switch($command->getType()) {
            case Command::TYPE_ENCRYPTSTORAGE:
                $ret['text'] = 'Zasifrovat disk';
            break;
            case Command::TYPE_WIPEDATA:
                $ret['text'] = 'Tovarni nastaveni';
            break;
            case Command::TYPE_RING:
                $ret['text'] = 'Prozvonit';
            break;
            case Command::TYPE_GETLOG:
                $ret['text'] = 'Vypis volani/SMS';
            break;
            case Command::TYPE_LOCATE:
                $ret['text'] = 'Lokalizovat';
            break;
            case Command::TYPE_LOCK:
                $ret['text'] = 'Uzamknout';
            break;
            case Command::TYPE_PING:
                $ret['text'] = 'Ping';
            break;
        }
        return $ret;
    }



//    public function actionEvents() {
//        header('Content-Type: text/event-stream');
//        header('Cache-Control: no-cache'); // recommended to prevent caching of event data.
//
//        /**
//         * Constructs the SSE data format and flushes that data to the client.
//         *
//         * @param string $id Timestamp/id of this connection.
//         * @param string $msg Line of text that should be transmitted.
//         */
//        function sendMsg($id, $msg) {
//            echo "id: $id" . PHP_EOL;
//            echo "data: $msg" . PHP_EOL;
//            echo PHP_EOL;
//            ob_flush();
//            flush();
//        }
//
//
//        $repo = $this->em->getRepository(Message::getClassName());
//        /** @var Message $lastMessage */
//        $lastMessage = $repo->findOneBy(['device' => $this->device], ['dateSent' => 'desc']);
//
//        $lastId = $lastMessage ? $lastMessage->id : 0;
//
//
//        $lastMessage = $repo->findOneBy(['device' => $this->device, 'id >' => $lastId]);
//        if ($lastMessage) {
//            $lastId = $lastMessage->id;
//            sendMsg($lastId, $lastMessage->getClassName());
//            sleep(1);
//        }
//
//
//        exit;
//    }


}
