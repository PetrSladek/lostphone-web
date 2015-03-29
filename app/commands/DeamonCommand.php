<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 22.3.2015
 * Time: 13:50
 */

namespace App\Commands;

use App\Model\Commands\LockCommand;
use App\Model\Device;
use App\Model\Commands\Command;

use App\Services\MessageService;
use Gcm\RecievedMessage;
use Gcm\Xmpp\Deamon;
use Kdyby\Doctrine\EntityManager;
use Nette\InvalidStateException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;

class DeamonCommand extends \Symfony\Component\Console\Command\Command {

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var MessageService
     */
    private $messageService;

    /**
     * @var Deamon
     */
    private $gcm;



    public function __construct(Deamon $gcm, EntityManager $em, MessageService $messageService)
    {
        parent::__construct();

        $this->gcm = $gcm;
        $this->em = $em;
        $this->messageService = $messageService;
    }


    protected function configure()
    {
        $this->setName('app:deamon')
            ->setDescription('Spusti deamona pro prijimani zprav z GCM');
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->gcm->onReady[] = function(Deamon $gcm) use($output) {
            $output->writeln("Ready / Auth success");
        };
        $this->gcm->onAuthFailure[] = function(Deamon $gcm, $reason) use($output) {
            $output->writeln("Auth failure (reason $reason)");
        };
        $this->gcm->onStop[] = function(Deamon $gcm) use($output) {
            $output->writeln("Deamon has stopped");
        };
        $this->gcm->onDisconnect[] = function(Deamon $gcm) use($output) {
            $output->writeln("Deamon has been disconected");
        };
        $this->gcm->onMessage[] = function(Deamon $gcm, RecievedMessage $message) use($output) {

            Debugger::log(print_r($message, true)); // ulozim prislou zpravu do info logu

            /** @var Device $device */
            $device = $this->em->getRepository(Device::getClassName())->findOneBy(['gcmId' => $message->getFrom()]);

            $data = $message->getData();

            // ACK
            if(@$data->ack && @$data->id) {
                $acked = new \DateTime($data->ack);
                $id =  $data->id;

                try {
                    $this->messageService->ackCommand($id, $acked, $device);
                    $this->em->flush();

                    $output->writeln("Command {$id} ACKed at ".$acked->format('j.n.Y H:i:s'));
                } catch (InvalidStateException $e) {
                    $output->writeln($e->getMessage());
                }

                return;
            }

            // Message
            $data = json_decode($data->message);
            try {
                $msg = $this->messageService->proccessRecievedData($device, $data);
                $this->em->flush();

                $output->writeln("Reciever message {$msg->getClassName()} from {$device->getGcmId()}");
            } catch(\Exception $e) {
                $output->writeln("Error: {$e->getMessage()}");
            }


        };

        $this->gcm->run();




    }


}