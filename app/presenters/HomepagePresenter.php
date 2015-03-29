<?php

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


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

    /** @var EntityManager @inject */
    public $em;

    /** @var Sender @inject */
    public $gcm;


    /** @var int @persistent */
    public $deviceId = null;

    /**
     * @var Device Selected device
     */
    protected $device;

    /**
     * @var EntityRepository
     */
    protected $devices;

    protected function startup()
    {
        parent::startup();

        if(!$this->user->isLoggedIn()) {
            $this->flashMessage('Musíte se nejdříve přihlásit');
            $this->redirect('Sign:in');
        }

        $this->devices = $this->em->getRepository(Device::getClassName());


        if($this->deviceId) {
            $this->device = $this->devices->find($this->deviceId);
            if($this->device->getOwner() !== $this->me)
                $this->error("Toto zarizeni neni vase", IResponse::S403_FORBIDDEN);
        }

        if(!$this->device)
            $this->device = $this->me->getFirstDevice();

        $this->template->device = $this->device;
    }


    /**
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
     * @param Form $form
     */
    public function frmChangeDeviceSucceeded(Form $form)
    {
        $this->deviceId = $form->values->deviceId == $this->me->getFirstDevice()->getId() ? null : $form->values->deviceId;
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }




    public function renderDefault() {

        if($this->device) {
            $repo = $this->em->getRepository(Message::getClassName());
            $messages = $repo->findBy(['device' => $this->device], ['dateSent' => 'desc'], 10);
            $this->template->messages = $messages;

            $repo = $this->em->getRepository(Command::getClassName());
            $commands = $repo->findBy(['device' => $this->device], ['dateSent' => 'desc'], 10);
            $this->template->commands = $commands;
        }

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

        if(!$lastLocalization) {
            $pos->lat = (float) $this->config->map->defaultPosition->lat;
            $pos->lng = (float) $this->config->map->defaultPosition->lng;
            $pos->zoom = (int) $this->config->map->defaultPosition->zoom;
            $pos->find = false;
        }
        $this->template->pos = $pos;


        if($this->isAjax()) {
            $this->payload->position = $pos;
            $this->payload->device = $this->device ? $this->device->getName() : null;
            $this->redrawControl('message');
        }
    }


    /**
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


    public function handleRefresh() {
//        sleep(1); // second

        $repo = $this->em->getRepository(Command::getClassName());
        $ackeds = $repo->findBy(['device' => $this->device, 'dateAck !='=>null, 'dateViewAck'=>null], ['dateSent' => 'desc']);
        $now = new DateTime();
        foreach($ackeds as $ack) {
            $this->payload->ackeds[] = $this->commandToPayload($ack);
            $ack->dateViewAck = $now;
        }
        $this->em->flush();

        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }


    public function handlePing()
    {
        $cmd = new PingCommand();
        $this->sendCommand($cmd);

        $this->payload->command = $this->commandToPayload($cmd);
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    public function handleRing()
    {
        $cmd = new RingCommand();
        $cmd->setCloseAfter(20000);
        $this->sendCommand($cmd);

        $this->payload->command = $this->commandToPayload($cmd);
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

//    public function handleLock()
//    {
//        $cmd = new LockCommand();
//
//        $cmd->setDisplayText("Vrať mi ho!");
//        $cmd->setOwnerPhoneNumber("+420 732 288 134");
//        $cmd->setPassword("1234");
//
//        $this->sendCommand($cmd);
//
//        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
//    }

    public function handleLocate()
    {
        $cmd = new LocateCommand();
        $this->sendCommand($cmd);

        $this->payload->command = $this->commandToPayload($cmd);
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    public function handleGetLog()
    {
        $cmd = new GetLogCommand();
        $this->sendCommand($cmd);

        $this->payload->command = $this->commandToPayload($cmd);
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    public function handleEncryptStorage()
    {
        $cmd = new EncryptStorageCommand();
        $this->sendCommand($cmd);

        $this->payload->command = $this->commandToPayload($cmd);
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    public function handleWipeData()
    {
        $cmd = new WipeDataCommand();
        $this->sendCommand($cmd);

        $this->payload->command = $this->commandToPayload($cmd);
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }





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



}
