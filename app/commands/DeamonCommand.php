<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 22.3.2015
 * Time: 13:50
 */

namespace App\Commands;

use App\Model\Device;
use App\Services\MessageService;
use Gcm\RecievedMessage;
use Gcm\Xmpp\Deamon;
use Kdyby\Doctrine\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeamonCommand extends Command {

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

            $output->writeln( print_r($message, true));
            $device = $this->em->getRepository(Device::getClassName())->findOneBy(['gcmId' => $message->getFrom()]);

            $data = json_decode($message->getData()->message);

            $this->messageService->proccessRecievedData($device, $data);
            $this->em->flush();
        };

        $this->gcm->run();




    }


}