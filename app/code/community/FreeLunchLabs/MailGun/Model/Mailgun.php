<?php

class FreeLunchLabs_MailGun_Model_Mailgun extends Mage_Core_Model_Abstract {

    public $apiUrl = "https://api.mailgun.net/v2/";

    public function mailgunRequest($type, $domain, $apiKey, $data, $method = Zend_Http_Client::GET, $uriOveride = false, $files = null) {
     
        $client = new Zend_Http_Client();
        $client->setAuth("api", $apiKey);
        $client->setMethod($method);
        
        if($uriOveride) {
            $client->setUri($uriOveride);
        } else {
            $client->setUri($this->apiUrl . $domain . "/" . $type);
        }
        
        if($method == Zend_Http_Client::POST) {
            foreach($data as $key => $value) {
                $client->setParameterPost($key, $value);
            }
        } else {
            foreach($data as $key => $value) {
                $client->setParameterGet($key, $value);
            }
        }

        if($files) {
            foreach($files as $file) {
                $client->setFileUpload($file['filename'], $file['param'], $file['data']);
            }
        }
        
        try {
            $response = $client->request();
            
            if($response->getStatus() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new Zend_Http_Exception("Error connecting to MailGun API. Returned error code: " . $response->getStatus() . " --- " . $response->getBody());
            }
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }
    
    public function send($message) {
        
        $domain = $message->getStore()->getConfig('mailgun/general/domain');
        $apiKey = $message->getStore()->getConfig('mailgun/general/key');
        $files = null;

        if(count($message->getAttachments())) {
            foreach($message->getAttachments() as $attachment) {
                $files[] = $attachment;
            }
        }

        $sendResponse = $this->mailgunRequest('messages', $domain, $apiKey, $message->getMessage(), Zend_Http_Client::POST, false, $files);
        
        if($message->getStore()->getConfig('mailgun/events/store')) {
            Mage::getModel('freelunchlabs_mailgun/email')->saveInitialSend($message, $sendResponse);
        }
        
        return $sendResponse;
    }

    public function processEmailEventsForAllStores() {
    
        $stores = Mage::getModel('core/store')->getCollection();
        
        foreach($stores as $store) {
            if($store->getConfig('mailgun/events/store')) {
                $this->processEmailEventsForSingleStore($store);
            }
        }        
    }
    
    public function processEmailEventsForSingleStore(Mage_Core_Model_Store $store) {
        
        if($store->getConfig('mailgun/events/store')) {
            
            $data = array(
                'end' => date("r", time() - 86400),
                'tags' => $store->getConfig('mailgun/general/tag')
            );
            
            $mailgunEvents = $this->mailgunRequest(
                    'events', 
                    $store->getConfig('mailgun/general/domain'), 
                    $store->getConfig('mailgun/general/key'), 
                    $data
            );
            
            $events = $mailgunEvents->items;
            
            while (sizeof($mailgunEvents->items) > 0) {
                $mailgunEvents = $this->mailgunRequest(
                    'events', 
                    $store->getConfig('mailgun/general/domain'), 
                    $store->getConfig('mailgun/general/key'), 
                    $data,
                    Zend_Http_Client::GET,
                    $mailgunEvents->paging->next    
                );
                
                $events = array_merge($events, $mailgunEvents->items);
            }
            
            $this->storeEvents($events);
        }
    }
    
    public function storeEvents($events) {
        foreach($events as $mailgunEvent) {
            $email = Mage::getModel('freelunchlabs_mailgun/email')->loadByMailgunIdAndRecipient($mailgunEvent->message->headers->{'message-id'}, $mailgunEvent->recipient);
            
            if($email->getId()) {
                $event = Mage::getModel('freelunchlabs_mailgun/event')->loadByTimestampAndEmailId($mailgunEvent->timestamp, $email->getId());
                
                if(!$event->getId()) {
                    $event->setEmailId($email->getId());
                    $event->setEventType($mailgunEvent->event);
                    $event->setTimestamp($mailgunEvent->timestamp);
                    $event->save();
                }
            }
        }
    }
    
}