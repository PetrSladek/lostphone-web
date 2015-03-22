<?php

/** @var \SystemContainer|Nette\DI\Container $container */
$container = require __DIR__ . '/../app/bootstrap.php';


$gcm = $container->getByType('\Gcm\Xmpp\Deamon');
$em = $container->getByType('\Kdyby\Doctrine\EntityManager');
$messageService = $container->getByType('\App\Services\MessageService');

//$gcm->onConnect[] = function(\Gcm\Deamon $gcm) {
//    print "Connect";
//};
$gcm->onReady[] = function(\Gcm\Xmpp\Deamon $gcm) {
    print "Ready / Auth success";
};
$gcm->onAuthFailure[] = function(\Gcm\Xmpp\Deamon $gcm, $reason) {
    print "Auth failure (reason $reason)";
};
$gcm->onStop[] = function(\Gcm\Xmpp\Deamon $gcm) {
    print "Deamon has stopped";
};
$gcm->onMessage[] = function(\Gcm\Xmpp\Deamon $gcm, \Gcm\RecievedMessage $message) use ($em, $messageService) {

    print_r($message);

    $device = $em->getRepository('\App\Model\Device')->findOneBy(['gcmId' => $message->getFrom()]);

    $data = json_decode($message->getData()->message);

    $messageService->proccessRecievedData($device, $data);
    $em->flush();
};

$gcm->run();


//
//$conn = new XMPPHP_XMPP("gcm.googleapis.com", 5235, "941272288463", "AIzaSyDI5lm2_VX_jXb5qG7_O-13F3yKGFVsfQk", "xmpphp", null, $printlog = true, $loglevel = XMPPHP_Log::LEVEL_DEBUG);
//$conn->useSSL();
//$conn->autoSubscribe();
//
//try {
//    $conn->connect();
//    while (!$conn->isDisconnected()) {
//        $payloads = $conn->processUntil(array('message', 'presence', 'end_stream', 'session_start', 'vcard'));
//        foreach ($payloads as $event) {
//            $pl = $event[1];
//            switch ($event[0]) {
//                case 'message':
//                    print "---------------------------------------------------------------------------------\n";
//                    print "Message from: {$pl['from']}\n";
//                    if ($pl['subject'])
//                        print "Subject: {$pl['subject']}\n";
//                    print $pl['body'] . "\n";
//                    print "---------------------------------------------------------------------------------\n";
//                    $conn->message($pl['from'], $body = "Thanks for sending me \"{$pl['body']}\".", $type = $pl['type']);
//                    $cmd = explode(' ', $pl['body']);
//                    if ($cmd[0] == 'quit')
//                        $conn->disconnect();
//                    if ($cmd[0] == 'break')
//                        $conn->send("</end>");
//                    if ($cmd[0] == 'vcard') {
//                        if (!($cmd[1]))
//                            $cmd[1] = $conn->user . '@' . $conn->server;
//// take a note which user requested which vcard
//                        $vcard_request[$pl['from']] = $cmd[1];
//// request the vcard
//                        $conn->getVCard($cmd[1]);
//                    }
//                    break;
//                case 'presence':
//                    print "Presence: {$pl['from']} [{$pl['show']}] {$pl['status']}\n";
//                    break;
//                case 'session_start':
//                    print "Session Start\n";
//                    $conn->getRoster();
//                    $conn->presence($status = "Cheese!");
//                    break;
//                case 'vcard':
//// check to see who requested this vcard
//                    $deliver = array_keys($vcard_request, $pl['from']);
//// work through the array to generate a message
//                    print_r($pl);
//                    $msg = '';
//                    foreach ($pl as $key => $item) {
//                        $msg .= "$key: ";
//                        if (is_array($item)) {
//                            $msg .= "\n";
//                            foreach ($item as $subkey => $subitem) {
//                                $msg .= " $subkey: $subitem\n";
//                            }
//                        } else {
//                            $msg .= "$item\n";
//                        }
//                    }
//// deliver the vcard msg to everyone that requested that vcard
//                    foreach ($deliver as $sendjid) {
//// remove the note on requests as we send out the message
//                        unset($vcard_request[$sendjid]);
//                        $conn->message($sendjid, $msg, 'chat');
//                    }
//                    break;
//            }
//        }
//    }
//} catch (XMPPHP_Exception $e) {
//    die($e->getMessage());
//}