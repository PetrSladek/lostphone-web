<?php

namespace App\Presenters;

use App\Model\Commands\Command;
use App\Model\Commands\LocateCommand;
use App\Model\Commands\LockCommand;
use App\Model\Commands\PingCommand;
use App\Model\Commands\RingCommand;
use App\Model\Device;
use App\Model\Messages\LocationMessage;
use App\Model\Messages\Message;
use CodeMonkeysRu\GCM;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette\Application\UI\Form;
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

    /** @var GCM\Sender @inject */
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

//    const DEVICE_REG_ID = "APA91bFWWWqeE5FLGjO2oegrTfegBA7Cl7FTdWferlDvdirXDrkDDWXcZNDV4e8SGVw5U-yZ4B9WYJTKwc5fjmZr3W5ENjXtDyT093AG8Qfjyu655jkgO3IJp9kNDfA3kOBK2IZVGy0srCDqwhlWy19-FBd4gA39KRweIlxmdHx6UAgjRFPCVYk";

    protected function startup()
    {
        parent::startup();

        if(!$this->user->isLoggedIn()) {
            $this->flashMessage('Musíte se nejdříve přihlásit');
            $this->redirect('Sign:in');
        }

        $this->devices = $this->em->getRepository(Device::getClassName());


        if($this->deviceId)
            $this->device = $this->devices->find($this->deviceId);

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



    public function handleRefresh() {
//        sleep(1); // second
        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }



    public function handleRing()
    {
        $cmd = new RingCommand();
        $cmd->setCloseAfter(20000);
        $this->sendCommand($cmd);

        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    public function handleLock()
    {
        $cmd = new LockCommand();

        $cmd->setDisplayText("Vrať mi ho!");
        $cmd->setOwnerPhoneNumber("+420 732 288 134");
        $cmd->setPassword("1234");

        $this->sendCommand($cmd);

        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    public function handleLocate()
    {
        $cmd = new LocateCommand();
        $this->sendCommand($cmd);

        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }

    public function handlePing()
    {
        $cmd = new PingCommand();
        $this->sendCommand($cmd);

        $this->isAjax() ? $this->redrawControl() : $this->redirect('this');
    }




    protected function sendCommand(Command $cmd) {

        if(!$this->device)
            throw new InvalidStateException("Not choosen device");

        $cmd->setDateSent(new DateTime());
        $cmd->setDevice($this->device);

        $this->em->persist($cmd);
        $this->em->flush();

        $message = new GCM\Message([$this->device->getGcmId()], $cmd->toGCMdata(), "collapse-".$cmd->getType());
        return $this->gcm->send($message);
    }



}
