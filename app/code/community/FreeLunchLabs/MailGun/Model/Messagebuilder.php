<?php

class FreeLunchLabs_MailGun_Model_Messagebuilder extends Varien_Object {

    const API_USER = "api";
    const SDK_USER_AGENT = "freelunchlabs_magento_extension";
    const RECIPIENT_COUNT_LIMIT = 1000;
    const CAMPAIGN_ID_LIMIT = 3;
    const TAG_LIMIT = 3;
    const DEFAULT_TIME_ZONE = "UTC";

    //Common Exception Messages
    const EXCEPTION_INVALID_CREDENTIALS = "Your credentials are incorrect.";
    const EXCEPTION_GENERIC_HTTP_ERROR = "An HTTP Error has occurred! Check your network connection and try again.";
    const EXCEPTION_MISSING_REQUIRED_PARAMETERS = "The parameters passed to the API were invalid. Check your inputs!";
    const EXCEPTION_MISSING_ENDPOINT = "The endpoint you've tried to access does not exist. Check your URL.";
    const TOO_MANY_RECIPIENTS = "You've exceeded the maximum recipient count (1,000) on the to field with autosend disabled.";
    const INVALID_PARAMETER_NON_ARRAY = "The parameter you've passed in position 2 must be an array.";
    const INVALID_PARAMETER_ATTACHMENT = "Attachments must be passed with an \"@\" preceding the file path. Web resources not supported.";
    const INVALID_PARAMETER_INLINE = "Inline images must be passed with an \"@\" preceding the file path. Web resources not supported.";
    const TOO_MANY_PARAMETERS_CAMPAIGNS = "You've exceeded the maximum (3) campaigns for a single message.";
    const TOO_MANY_PARAMETERS_TAGS = "You've exceeded the maximum (3) tags for a single message.";
    const TOO_MANY_PARAMETERS_RECIPIENT = "You've exceeded the maximum recipient count (1,000) on the to field with autosend disabled.";

    protected $message = array();
    protected $variables = array();
    protected $files = array();
    protected $attachments = array();
    protected $counters = array('recipients' => array('to' => 0,
            'cc' => 0,
            'bcc' => 0),
            'attributes' => array('attachment' => 0,
            'campaign_id' => 0,
            'custom_option' => 0,
            'tag' => 0)
    );

    protected function safeGet($params, $key, $default) {
        if (array_key_exists($key, $params)) {
            return $params[$key];
        }
        return $default;
    }

    protected function getFullName($params) {
        if (array_key_exists("first", $params)) {
            $first = $this->safeGet($params, "first", "");
            $last = $this->safeGet($params, "last", "");
            return trim("$first $last");
        }
        return $this->safeGet($params, "full_name", "");
    }

    protected function parseAddress($address, $variables) {
        if (!is_array($variables)) {
            return $address;
        }
        $fullName = $this->getFullName($variables);
        if ($fullName != null) {
            return "'$fullName' <$address>";
        }
        return $address;
    }

    protected function addRecipient($headerName, $address, $variables) {
        $compiledAddress = $this->parseAddress($address, $variables);

        if (isset($this->message[$headerName])) {
            array_push($this->message[$headerName], $compiledAddress);
        } elseif ($headerName == "h:reply-to") {
            $this->message[$headerName] = $compiledAddress;
        } else {
            $this->message[$headerName] = array($compiledAddress);
        }
        if (array_key_exists($headerName, $this->counters['recipients'])) {
            $this->counters['recipients'][$headerName] += 1;
        }
    }

    public function addToRecipient($address, $variables = null) {
        if ($this->counters['recipients']['to'] > self::RECIPIENT_COUNT_LIMIT) {
            throw new FreeLunchLabs_MailGun_Model_Exceptions_TooManyParameters(TOO_MANY_PARAMETERS_RECIPIENT);
        }
        $this->addRecipient("to", $address, $variables);
        return end($this->message['to']);
    }

    public function addCcRecipient($address, $variables = null) {
        if ($this->counters['recipients']['cc'] > self::RECIPIENT_COUNT_LIMIT) {
            throw new FreeLunchLabs_MailGun_Model_Exceptions_TooManyParameters(TOO_MANY_PARAMETERS_RECIPIENT);
        }
        $this->addRecipient("cc", $address, $variables);
        return end($this->message['cc']);
    }

    public function addBccRecipient($address, $variables = null) {
        if ($this->counters['recipients']['bcc'] > self::RECIPIENT_COUNT_LIMIT) {
            throw new FreeLunchLabs_MailGun_Model_Exceptions_TooManyParameters(TOO_MANY_PARAMETERS_RECIPIENT);
        }
        $this->addRecipient("bcc", $address, $variables);
        return end($this->message['bcc']);
    }

    public function setFromAddress($address, $variables = null) {
        $this->addRecipient("from", $address, $variables);
        return $this->message['from'];
    }

    public function setReplyToAddress($address, $variables = null) {
        $this->addRecipient("h:reply-to", $address, $variables);
        return $this->message['h:reply-to'];
    }

    public function setSubject($subject = NULL) {
        if ($subject == NULL || $subject == "") {
            $subject = " ";
        }
        $this->message['subject'] = $subject;
        return $this->message['subject'];
    }
    
    public function getSubject() {
        return $this->message['subject'];
    }

    public function addCustomHeader($headerName, $headerData) {
        if (!preg_match("/^h:/i", $headerName)) {
            $headerName = "h:" . $headerName;
        }
        $this->message[$headerName] = array($headerData);
        return $this->message[$headerName];
    }

    public function setTextBody($textBody) {
        if ($textBody == NULL || $textBody == "") {
            $textBody = " ";
        }
        $this->message['text'] = $textBody;
        return $this->message['text'];
    }

    public function setHtmlBody($htmlBody) {
        if ($htmlBody == NULL || $htmlBody == "") {
            $htmlBody = " ";
        }
        $this->message['html'] = $htmlBody;
        return $this->message['html'];
    }
    
    public function getHtmlBody() {
        return $this->message['html'];
    }

    public function addAttachment($filename, $data) {
        if ($filename != null && $data != null) {
            $this->attachments[] = array(
                'filename' => $filename,
                'data' => $data,
                'param' => 'attachment'
            );
            return true;
        } else {
            throw new FreeLunchLabs_MailGun_Model_Exceptions_InvalidParameter(self::INVALID_PARAMETER_ATTACHMENT);
        }
    }

    public function addInlineImage($inlineImagePath, $inlineImageName = null) {
        if (preg_match("/^@/", $inlineImagePath)) {
            if (isset($this->files['inline'])) {
                $inlineAttachment = array('filePath' => $inlineImagePath,
                    'remoteName' => $inlineImageName);
                array_push($this->files['inline'], $inlineAttachment);
            } else {
                $this->files['inline'] = array(array('filePath' => $inlineImagePath,
                        'remoteName' => $inlineImageName));
            }
            return true;
        } else {
            throw new FreeLunchLabs_MailGun_Model_Exceptions_InvalidParameter(self::INVALID_PARAMETER_INLINE);
        }
    }

    public function setTestMode($testMode) {
        if (filter_var($testMode, self::FILTER_VALIDATE_BOOLEAN)) {
            $testMode = "yes";
        } else {
            $testMode = "no";
        }
        $this->message['o:testmode'] = $testMode;
        return $this->message['o:testmode'];
    }

    public function addCampaignId($campaignId) {
        if ($this->counters['attributes']['campaign_id'] < self::CAMPAIGN_ID_LIMIT) {
            if (isset($this->message['o:campaign'])) {
                array_push($this->message['o:campaign'], $campaignId);
            } else {
                $this->message['o:campaign'] = array($campaignId);
            }
            $this->counters['attributes']['campaign_id'] += 1;
            return $this->message['o:campaign'];
        } else {
            throw new FreeLunchLabs_MailGun_Model_Exceptions_TooManyParameters(self::TOO_MANY_PARAMETERS_CAMPAIGNS);
        }
    }

    public function addTag($tag) {
        if ($this->counters['attributes']['tag'] < self::TAG_LIMIT) {
            if (isset($this->message['o:tag'])) {
                array_push($this->message['o:tag'], $tag);
            } else {
                $this->message['o:tag'] = array($tag);
            }
            $this->counters['attributes']['tag'] += 1;
            return $this->message['o:tag'];
        } else {
            throw new FreeLunchLabs_MailGun_Model_Exceptions_TooManyParameters(self::TOO_MANY_PARAMETERS_TAGS);
        }
    }

    public function setDkim($enabled) {
        if (filter_var($enabled, FILTER_VALIDATE_BOOLEAN)) {
            $enabled = "yes";
        } else {
            $enabled = "no";
        }
        $this->message["o:dkim"] = $enabled;
        return $this->message["o:dkim"];
    }

    public function setOpenTracking($enabled) {
        if (filter_var($enabled, FILTER_VALIDATE_BOOLEAN)) {
            $enabled = "yes";
        } else {
            $enabled = "no";
        }
        $this->message['o:tracking-opens'] = $enabled;
        return $this->message['o:tracking-opens'];
    }

    public function setClickTracking($enabled) {
        if (filter_var($enabled, FILTER_VALIDATE_BOOLEAN)) {
            $enabled = "yes";
        } elseif ($enabled == "html") {
            $enabled = "html";
        } else {
            $enabled = "no";
        }
        $this->message['o:tracking-clicks'] = $enabled;
        return $this->message['o:tracking-clicks'];
    }

    public function setDeliveryTime($timeDate, $timeZone = NULL) {
        if (isset($timeZone)) {
            $timeZoneObj = new DateTimeZone("$timeZone");
        } else {
            $timeZoneObj = new DateTimeZone(self::DEFAULT_TIME_ZONE);
        }

        $dateTimeObj = new DateTime($timeDate, $timeZoneObj);
        $formattedTimeDate = $dateTimeObj->format(DateTime::RFC2822);
        $this->message['o:deliverytime'] = $formattedTimeDate;
        return $this->message['o:deliverytime'];
    }

    public function addCustomData($customName, $data) {
        if (is_array($data)) {
            $jsonArray = json_encode($data);
            $this->message['v:' . $customName] = $jsonArray;
            return $this->message['v:' . $customName];
        } else {
            throw new FreeLunchLabs_MailGun_Model_Exceptions_InvalidParameter(self::INVALID_PARAMETER_NON_ARRAY);
        }
    }

    public function addCustomParameter($parameterName, $data) {
        if (isset($this->message[$parameterName])) {
            array_push($this->message[$parameterName], $data);
            return $this->message[$parameterName];
        } else {
            $this->message[$parameterName] = array($data);
            return $this->message[$parameterName];
        }
    }

    public function getMessage() {
        return $this->message;
    }

    public function getFiles() {
        return $this->files;
    }

    public function getAttachments() {
        return $this->attachments;
    }

}