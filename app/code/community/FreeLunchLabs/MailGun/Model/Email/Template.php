<?php

class FreeLunchLabs_MailGun_Model_Email_Template extends Mage_Core_Model_Email_Template {

    var $bcc = null;
    var $replyto = null;
    var $returnPath = null; //This is not used because MailGun overides it for their internal purposes.

    public function send($email, $name = null, array $variables = array()) {
        //Get appropriate store
        if(isset($variables['store'])) {
            $store = $variables['store'];
        } elseif($this->getDesignConfig()->getStore()) {
            $store = Mage::getModel('core/store')->load($this->getDesignConfig()->getStore());
        } else {
            $store = Mage::app()->getStore();
        }
        
        if ($store->getConfig('mailgun/general/active')) {
            if (!$this->isValidForSend()) {
                Mage::logException(new Exception('This letter cannot be sent.')); 
                return false;
            }
            
            $message = Mage::getModel('freelunchlabs_mailgun/messagebuilder');

            //Recipient(s)
            $emails = array_values((array) $email);
            $names = is_array($name) ? $name : (array) $name;
            $names = array_values($names);
            foreach ($emails as $key => $email) {
                if (!isset($names[$key])) {
                    $names[$key] = substr($email, 0, strpos($email, '@'));
                }
            }

            $variables['email'] = reset($emails);
            $variables['name'] = reset($names);

            //Add To Recipients
            $isPrimary = true;
            foreach ($emails as $key => $email) {
                if($isPrimary) {
                    //Add primary recipient
                    $message->setPrimaryRecipient($email);
                }
                $isPrimary = false;
                
                $message->addToRecipient($names[$key] . " <" . $email . ">");
            }
            
            //Subject
            $subject = $this->getProcessedTemplateSubject($variables);
            $message->setSubject($subject);
            
            //From Name
            $message->setFromAddress($this->getSenderName() . " <" . $this->getSenderEmail() . ">");

            //Bcc
            if (is_array($this->bcc)) {
                foreach ($this->bcc as $bcc_email) {
                    $message->addBccRecipient($bcc_email);
                }
            } elseif ($this->bcc) {
                $message->addBccRecipient($this->bcc);
            }

            //Reply To
            if (!is_null($this->replyto)) {
                $message->setReplyToAddress($this->replyto);
            }

            //Message Body
            $this->setUseAbsoluteLinks(true);
            $processedTemplateBody = $this->getProcessedTemplate($variables, true);

            if ($this->isPlain()) {
                $message->setTextBody($processedTemplateBody);
            } else {
                $message->setHtmlBody($processedTemplateBody);
            }

            //Attachments
            if($this->getMail()->hasAttachments) {
                foreach($this->getMail()->getParts() as $part) {
                    if($part->disposition == "attachment") {
                        $message->addAttachment($part->filename, $part->getRawContent());
                    }
                }
            }

            //Add Unique Args
            $message->addCustomData("message_data", array('id' => 123456));
            
            //Tag message with type
            $message->addTag($this->getTemplateId());
            $message->addTag($store->getConfig('mailgun/general/tag'));
            
            if($store->getConfig('mailgun/events/opens')) {
                $message->setOpenTracking(true);
            }
            
            if($store->getConfig('mailgun/events/clicks')) {
                $message->setClickTracking(true);
            }
            
            //Set store
            $message->setStore($store);
            
            //Send it!
            try {
                Mage::getModel('freelunchlabs_mailgun/mailgun')->send($message);
                $this->_mail = null;
            } catch (Exception $e) {
                $this->_mail = null;
                Mage::logException($e);
                return false;
            }

            return true;
        } else {
            return parent::send($email, $name, $variables);
        }
    }

    public function addBcc($bcc) {
        $this->bcc = $bcc;
        return parent::addBcc($bcc);
    }

    public function setReturnPath($email) {
        $this->returnPath = $email;
        return parent::setReturnPath($email);
        
    }

    public function setReplyTo($email) {
        $this->replyto = $email;
        return parent::setReplyTo($email);
    }

}