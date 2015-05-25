<?php
/**
 * Příkaz z CLI pro spuštění WebSocket serveru
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
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
     * WebSocket server
     * @var Server
     */
    private $server;


    /**
     * @param \Hoa\Socket\Server $server Socket server
     */
    public function __construct(\Hoa\Socket\Server $server)
    {
        parent::__construct();
        $this->server = new Server( $server ); // tcp://127.0.0.1:8889
    }


    /**
     * Konfigurace prikazu pro CLI
     */
    protected function configure()
    {
        $this->setName('app:server')
            ->setDescription('Spusti WebSocket server pro predavani zprav do prohlizece');
    }


    /**
     * Spusteni prikazu pro CLI
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Párovani Websocket klienta pdole DeviceID
        $connections = [];


        // Informace o pripojeni klienta
        $this->server->on('open', function ( Bucket $bucket ) use ($output, &$connections) {

            $now = new DateTime();
            if ($output->isVerbose())
                $output->writeln("[{$now->format('j.n.Y H:i:s')}] New connection");

            return;
        });

        // Informace o odpojení klienta
        $this->server->on('close', function ( Bucket $bucket ) use ($output, &$connections) {

            $now = new DateTime();
            if ($output->isVerbose())
                $output->writeln("[{$now->format('j.n.Y H:i:s')}] Connection closed");

            return;
        });

        // Pri prichozí zprávě
        $this->server->on('message', function ( Bucket $bucket ) use ($output, &$connections) {

            try {
                $now = new DateTime();
                $data = $bucket->getData();

                if ($output->isVerbose())
                    $output->writeln("[{$now->format('j.n.Y H:i:s')}] Message: " . $data['message']);

                // Rozkoduju JSON data
                $payload = Json::decode($data['message']);

                // Nove naslouchani na deviceId (novy klient)
                if($payload->type == 'newDeviceListening') {
                    $deviceId = $payload->data;
                    $connections[$deviceId] = $node = $bucket->getSource()->getConnection()->getCurrentNode();

                    $this->server->send("Zaregistroval sis zasilani", $node); // Poslu mu zpet informaci zpravu
                }
                // Nova zprava z GCM
                elseif ($payload->type == 'newMessage') {
                    $msg = @unserialize($payload->data); // Ziskam objekt Message
                    if ($msg && $msg instanceof Message) {
                        /** @var \App\Model\Messages\Message $msg */
                        $deviceId = $msg->getDevice()->getId();
                        // Podle DeviceID ze zpravy najdu Websocket klienta
                        $node = $connections[$deviceId];
                        // a dam mu vedet ze ma v DB novou zpravu
                        $this->server->send("newMessage", $node);
                    }
                }

            } catch( \Exception $e ) {
                 if ($output->isVerbose())
                    $output->writeln("[{$now->format('j.n.Y H:i:s')}] Error: {$e->getMessage()}");
            }
            return;
        });


        // Spustim WebSocket server. Bezi dokud neni ukoncen
        $this->server->run();
    }


}