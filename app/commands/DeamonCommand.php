<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 22.3.2015
 * Time: 13:50
 */

namespace App\Commands;

use App\Model\Device;
use App\Model\Commands\Command;

use App\Services\MessageService;
use Gcm\RecievedMessage;
use Gcm\Xmpp\Deamon;
use Kdyby\Doctrine\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $this->gcm->onMessage[] = function(Deamon $gcm, RecievedMessage $message) use($output) {

//            $output->writeln( print_r($message, true));


            $device = $this->em->getRepository(Device::getClassName())->findOneBy(['gcmId' => $message->getFrom()]);

            $data = $message->getData();

            // ACK
            if(@$data->ack && @$data->id) {
                $acked = new \DateTime($data->ack);
                $id =  $data->id;

                /** @var Command $cmd */
                $cmd = $this->em->find( Command::getClassName(), $id);
                if($cmd->getDevice() == $device) {
                    $cmd->setDateAck( $acked );
                    $this->em->flush();
                    $output->writeln("Command {$id} ACKed at ".$acked->format('j.n.Y H:i:s'));
                } else {
                    $output->writeln("Command sended to {$cmd->getDevice()->getGcmId()}, but acked from {$device->getGcmId()}");
                }
                return;
            }

            // Message
            $data = json_decode($data->message);

            $this->messageService->proccessRecievedData($device, $data);
            $this->em->flush();
        };

        $this->gcm->run();




    }


}