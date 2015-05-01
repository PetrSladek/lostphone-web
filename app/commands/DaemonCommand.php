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
use Gcm\Xmpp\Daemon;
use Hoa\Core\Event\Bucket;
use Hoa\Websocket\Client;
use Hoa\Websocket\Server;
use Kdyby\Doctrine\EntityManager;
use Nette\InvalidStateException;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;

class DaemonCommand extends \Symfony\Component\Console\Command\Command {

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var MessageService
     */
    private $messageService;

    /**
     * @var Daemon
     */
    private $gcm;


    /**
     * @var Client
     */
    private $websocket;



    public function __construct(Daemon $gcm, EntityManager $em, MessageService $messageService, \Hoa\Socket\Client $client)
    {
        parent::__construct();

        $this->gcm = $gcm;
        $this->em = $em;
        $this->messageService = $messageService;

        $this->websocket = new Client( $client );
        $this->websocket->setHost( "www.lostphone.cz" );
    }


    protected function configure()
    {
        $this->setName('app:daemon')
            ->setDescription('Spusti deamona pro prijimani zprav z GCM');
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {


//        $connections = [];
//
//
//        $this->server->on('open', function ( Bucket $bucket ) use ($output, $connections) {
//
//            echo 'new connection', "\n";
//            $connections[] = $bucket;
//
//            return;
//        });
//        $this->server->on('close', function ( Bucket $bucket ) use ($output, $connections) {
//
//            echo 'connection closed', "\n";
//            // find and delete
////            $connections[] = $bucket;
//
//            return;
//        });


        $this->gcm->onReady[] = function(Daemon $gcm) use($output) {
            $output->writeln("Ready / Auth success");
        };
        $this->gcm->onAuthFailure[] = function(Daemon $gcm, $reason) use($output) {
            $output->writeln("Auth failure (reason $reason)");
        };
        $this->gcm->onStop[] = function(Daemon $gcm) use($output) {
            $output->writeln("Daemon has stopped");
        };
        $this->gcm->onDisconnect[] = function(Daemon $gcm) use($output) {
            $output->writeln("Daemon has been disconected");
        };
        $this->gcm->onMessage[] = function(Daemon $gcm, RecievedMessage $message) use($output) {

            $now = new DateTime();
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

                    if ($output->isVerbose())
                        $output->writeln("[{$now->format('j.n.Y H:i:s')}] Command {$id} ACKed at {$acked->format('j.n.Y H:i:s')}");
                } catch (InvalidStateException $e) {
                    if ($output->isVerbose())
                        $output->writeln($e->getMessage());
                }

                return;
            }

            // Message
            $data = json_decode($data->message);
            try {
                $msg = $this->messageService->proccessRecievedData($device, $data);
                $this->em->flush();

                if ($output->isVerbose())
                    $output->writeln("[{$now->format('j.n.Y H:i:s')}] Reciever message {$msg->getClassName()} from {$device->getGcmId()}");

                $this->websocket->connect();
                $payload = json_encode([
                    'type' => 'newMessage',
                    'data' => serialize($msg),
                ]);
                $this->websocket->send($payload);
                $this->websocket->close();

            } catch(\Exception $e) {
                if ($output->isVerbose())
                    $output->writeln("[{$now->format('j.n.Y H:i:s')}] Error: {$e->getMessage()}");
            }


        };


        $this->gcm->run();
//        $this->server->run();

    }


}