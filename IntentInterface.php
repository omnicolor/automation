<?php

interface Intent
{
    /**
     * Run the intent
     * @param \StdClass $slots Slots object
     * @return string Response to return to Alexa
     */
    public function run($slots = null);
}
