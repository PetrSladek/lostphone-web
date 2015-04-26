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

use App\Model\Messages\Message;
use App\Services\MessageService;
use Gcm\RecievedMessage;
use Gcm\Xmpp\Deamon;
use Hoa\Core\Event\Bucket;
use Hoa\Websocket\Server;
use Kdyby\Doctrine\EntityManager;
use Nette\InvalidStateException;
use Nette\Neon\Exception;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;

class WebSocketServerCommand extends \Symfony\Component\Console\Command\Command {

    /**
     * @var Server
     */
    private $server;



    public function __construct()
    {
        parent::__construct();


        $this->server = new Server( new \Hoa\Socket\Server('tcp://127.0.0.1:8889') );
    }


    protected function configure()
    {
        $this->setName('app:server')
            ->setDescription('Spusti WebSocket server pro predavani zprav do prohlizece');
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $connections = [];

        $this->server->on('open', function ( Bucket $bucket ) use ($output, &$connections) {

            echo 'new connection', "\n";
//            $connections[] = $bucket;

            return;
        });
        $this->server->on('close', function ( Bucket $bucket ) use ($output, &$connections) {

            echo 'connection closed', "\n";
            // find and delete
//            $connections[] = $bucket;

            return;
        });

        $this->server->on('message', function ( Bucket $bucket ) use ($output, &$connections) {

            try {
                $now = new DateTime();
                $data = $bucket->getData();


                if ($output->isVerbose())
                    $output->writeln("[{$now->format('j.n.Y H:i:s')}] Message: " . $data['message']);

                $payload = Json::decode($data['message']);

                if($payload->type == 'newDeviceListening') {
                    $deviceId = $payload->data;
                    $connections[$deviceId] = $node = $bucket->getSource()->getConnection()->getCurrentNode();
                    $this->server->send("Zaregistroval sis zasilani", $node);
                }
                elseif ($payload->type == 'newMessage') {
                    $msg = @unserialize($payload->data);
                    if ($msg && $msg instanceof Message) {
                        /** @var \App\Model\Messages\Message $msg */
                        $deviceId = $msg->getDevice()->getId();

                        $node = $connections[$deviceId];
                        $this->server->send("newMessage", $node);
                    }
                }
                // todo NewAcked


//            $bucket->getSource()->send($data['message']);
            } catch( \Exception $e ) {
                 if ($output->isVerbose())
                    $output->writeln("[{$now->format('j.n.Y H:i:s')}] Error: {$e->getMessage()}");
            }
            return;
        });


        $this->server->run();

    }


}