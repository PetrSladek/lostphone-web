<?php
/**
 * Služba pro práci se zprávami
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */


namespace App\Services;

use App\Model\Commands\Command;
use App\Model\Commands\LockCommand;
use App\Model\Device;
use App\Model\Image;
use App\Model\Messages\GotchaMessage;
use App\Model\Messages\LocationMessage;
use App\Model\Messages\LogMessage;
use App\Model\Messages\Message;
use App\Model\Messages\PongMessage;
use App\Model\Messages\RegistrationMessage;
use App\Model\Messages\RingingTimeoutMessage;
use App\Model\Messages\SimStateChangedMessage;
use App\Model\Messages\UnlockMessage;
use App\Model\Messages\WrongPassMessage;
use App\Model\User;
use Kdyby\Doctrine\EntityManager;
use Nette\Http\FileUpload;
use Nette\InvalidStateException;
use Nette\Utils\DateTime;


class MessageService
{

    /**
     * Doctrine Entity manažer
     * @var EntityManager
     * @inject Připojí se sám z DI kontejneru
     */
    public $em;


    /**
     * Služba pro práci s obrázky
     * @var ImageService
     * @inject Připojí se sám z DI kontejneru
     */
    public $imageService;


    public function __construct(EntityManager $em, ImageService $imageService)
    {
        $this->em = $em;
        $this->imageService = $imageService;
    }


    /**
     * Zaznamená ověření odeslaného příkazu
     *
     * @param int $id
     * @param DateTime|string $date
     * @param Device $device
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function ackCommand($id, $date, $device) {
        // Najde příkaz podle ID
        $cmd = $this->em->find( Command::getClassName(), $id );
        /** @var Command $cmd */

        // Pokud oveření přišl o z jiného zařízení naž na které bylo odesláno vyhodím vyjímku
        if($cmd->getDevice() != $device)
            throw new InvalidStateException("Command sended to {$cmd->getDevice()->getGcmId()}, but acked from {$device->getGcmId()}");

        // Příkaz byl potvrzen
        $cmd->setDateAck( DateTime::from($date) );
        if($cmd instanceof LockCommand) // Pokud šlo o lock command, nastavim zařízení jako uzamknnuté
            $device->setLocked(true);
    }

    /**
     * Zpracuje příchozí data z HTTP či GCM
     * @param Device $device
     * @param \stdClass $data
     * @return GotchaMessage|LocationMessage|PongMessage|RegistrationMessage|RingingTimeoutMessage|UnlockMessage|WrongPassMessage
     */
    public function proccessRecievedData(&$device, $data)
    {
        // Podle typu vytvoří instanci zprávy
        switch ($data->type) {
            case Message::TYPE_PONG:
                $msg = new PongMessage();
                break;
            case Message::TYPE_REGISTRATION:
                // tady bude gcmId null
                $msg = new RegistrationMessage();
                $msg->setGcmId($data->gcmId);
                $msg->setIdentifier($data->identifier);
                $msg->setGoogleAccountEmail($data->googleAccountEmail);
                $msg->setBrand($data->brand);
                $msg->setModel($data->model);

                // Pokud zařízení už existuje použijeme ho, jinak vytvoříme nové
                $device = $this->em->getRepository(Device::getClassName())->findOneBy(['identifier' => $msg->getIdentifier()]);
                if (!$device) {
                    $device = new Device();
                    $device->setName(sprintf("%s %s", $msg->getBrand(), $msg->getModel()));
                    $device->setIdentifier($msg->getIdentifier());
                }
                $device->setGcmId($msg->getGcmId());
                $device->setRegistrationMessage($msg);


                // najdi ownera podle google account emailu
                $owner = $this->em->getRepository(User::getClassName())->findOneBy(['googleEmail' => $msg->getGoogleAccountEmail()]);
                if ($owner)
                    $device->setOwner($owner);


                $this->em->persist($device);

                break;

            case Message::TYPE_GOTCHA:
                $msg = new GotchaMessage();
                break;

            case Message::TYPE_RINGINGTIMEOUT:
                $msg = new RingingTimeoutMessage();
                break;

            case Message::TYPE_UNLOCK:
                $msg = new UnlockMessage();
                $device->setLocked(false);
                break;

            case Message::TYPE_WRONGPASS:
                $msg = new WrongPassMessage();
                /** @var FileUpload $photo */
                $photo = $data->frontPhoto;

                if ($photo) {

                    // otocime o 90 stupnu
//                    $photo->toImage()->rotate(90, \Nette\Utils\Image::rgb(255,255,255))->save($photo->getTemporaryFile());

                    $file = $this->imageService->uploadImage($photo);

                    $image = new Image();
                    $image->setFilename($file->getBasename());
                    $image->setExtension($file->getExtension());

                    $this->em->persist($image);

                    $msg->setFrontPhoto($image);
                }
                break;

            case Message::TYPE_LOCATION:
                $msg = new LocationMessage();
                $msg->setLat($data->lat);
                $msg->setLng($data->lng);
                break;

            case Message::TYPE_SIMSTATECHANGED:
                $msg = new SimStateChangedMessage();
                $msg->setImei($data->imei);
                $msg->setSubscriberId($data->subscriberId);
                $msg->setPhoneNumber($data->phoneNumber);
                $msg->setNetworkOperator($data->networkOperator);
                $msg->setNetworkOperatorName($data->networkOperatorName);
                $msg->setNetworkCountryIso($data->networkCountryIso);
                $msg->setSimOperator($data->simOperator);
                $msg->setSimOperatorName($data->simOperatorName);
                $msg->setSimCountryIso($data->simCountryIso);
                $msg->setSimSerialNumber($data->simSerialNumber);
                break;

            case Message::TYPE_LOG:
                $msg = new LogMessage();
//                $data->callLog = "+420732288134|OUTGOING|2014-12-12 12:22:34|1200\n";
//                $data->callLog .= "+420123456789|INCOMING|2014-12-13 13:33:45|1222\n";
//
//                $data->smsLog = "+420732288134|OUTBOX|2014-12-12 12:22:34|Nazdar vanilko\n";
//                $data->smsLog .= "+420123456789|INBOX|2014-12-13 13:33:45|No nazdar\n";

                $msg->setCallLog(LogMessage::parseCallLog($data->callLog));
                $msg->setSmsLog(LogMessage::parseSMSLog($data->smsLog));
                break;

            default:
                throw new \RuntimeException("Wrong message type");
                break;
        }

        $msg->setDateSent(new DateTime($data->date));

        $device->addMessage($msg);

        $this->em->persist($msg);

        return $msg;
    }

}