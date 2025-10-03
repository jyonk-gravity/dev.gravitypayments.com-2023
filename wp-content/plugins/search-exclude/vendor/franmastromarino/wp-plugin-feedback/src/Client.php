<?php

namespace QuadLayers\PluginFeedback;

class Client
{
    /** @var Collector */
    private $collector;
    /** @var Validator */
    private $validator;
    /** @var Request */
    private $request;

    /**
     * Constructor to initialize the client with plugin slug and version.
     *
     * @param string $pluginBasename The plugin slug.
     */
    public function __construct(
        string $pluginBasename
    ) {
        $this->collector = new Collector($pluginBasename);
        $this->validator = new Validator();
        $this->request = new Request();
    }

     /**
      * Sends feedback to the server.
      *
      * @param  bool   $isAnonymous     Determines if the feedback is anonymous.
      * @param  string $feedbackReason  The reason for the feedback.
      * @param  string $feedbackDetails Additional details for the feedback.
      * @return bool True if the feedback was sent successfully, False otherwise.
      */
    public function sendFeedback(string $feedbackReason = '', string $feedbackDetails = '', $isAnonymous = false, $hasFeedback = false) : bool
    {
        // Collect data
        $data = $this->collector->collectData($feedbackReason, $feedbackDetails, $isAnonymous, $hasFeedback);

        // Validate data
        if (!$this->validator->validate($data, $isAnonymous)) {
            return false;
        }

        // Send data
        return $this->request->send($data);
    }
}
