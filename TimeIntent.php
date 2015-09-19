<?php
/**
 * Tell what time it is.
 *
 * Hint: It's not time to feed the cat, no matter how annoying he's being.
 */
class TimeIntent implements Intent
{
    /**
     * Set up the intent.
     * @param array $config Unused
     */
    public function __construct($config)
    {
    }

    /**
     * Run the intent.
     * @param \StdClass $slots Unused
     * @return string
     */
    public function run($slots = null)
    {
        return 'No, it\'s not time to feed fatty.';
    }
}
