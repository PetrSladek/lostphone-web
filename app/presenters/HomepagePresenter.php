<?php

namespace App\Presenters;

use App\Model\Commands\Command;
use App\Model\Commands\LocateCommand;
use App\Model\Commands\LockCommand;
use App\Model\Commands\PingCommand;
use App\Model\Commands\RingCommand;
use App\Model\Messages\LocationMessage;
use App\Model\Messages\Message;
use CodeMonkeysRu\GCM;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\Responses\TextResponse;
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

    const DEVICE_REG_ID = "APA91bFWWWqeE5FLGjO2oegrTfegBA7Cl7FTdWferlDvdirXDrkDDWXcZNDV4e8SGVw5U-yZ4B9WYJTKwc5fjmZr3W5ENjXtDyT093AG8Qfjyu655jkgO3IJp9kNDfA3kOBK2IZVGy0srCDqwhlWy19-FBd4gA39KRweIlxmdHx6UAgjRFPCVYk";

    protected function startup()
    {
        parent::startup();

        if(!$this->user->isLoggedIn()) {
            $this->flashMessage('Musíte se nejdříve přihlásit');
            $this->redirect('Sign:in');
        }
    }


    public function renderDefault() {

        $repo = $this->em->getRepository(Message::getClassName());

        $messages = $repo->findBy([], ['dateSent'=>'desc'], 10);
        $this->template->messages = $messages;

        $repo = $this->em->getRepository(LocationMessage::getClassName());

        $pos = new ArrayHash();

        /**
         * @var $lastLocalization LocationMessage
         */
        $lastLocalization = $repo->findOneBy([], ['dateSent'=>'desc'], 1);
        if($lastLocalization) {
            $pos->lat = $lastLocalization->getLat();
            $pos->lng = $lastLocalization->getLng();
            $pos->zoom = 15;
        } else {
            $pos->lat = (float) $this->config->map->defaultPosition->lat;
            $pos->lng = (float) $this->config->map->defaultPosition->lng;
            $pos->zoom = (int) $this->config->map->defaultPosition->zoom;
        }
        $this->template->pos = $pos;


        if($this->isAjax()) {
            $this->payload->position = $pos;
            $this->redrawControl('message');
        }
    }



    public function handleRing()
    {
        $cmd = new RingCommand();
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

        $cmd->setDateSent(new DateTime());
        $this->em->persist($cmd);
        $this->em->flush();

        $message = new GCM\Message([self::DEVICE_REG_ID], $cmd->toGCMdata(), "collapse-".$cmd->getType());
        return $this->gcm->send($message);
    }



}
