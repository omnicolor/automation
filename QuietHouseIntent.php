<?php
/**
 * Turn off the house music system.
 */
class QuietHouseIntent implements Intent
{
    /**
     * @var string IP of the music machine
     */
    protected $ip;

    /**
     * Set up the intent.
     * @param array $config Configuration array
     * @throws \RuntimeException
     */
    public function __construct($config)
    {
        if (!isset($config['music-ip'])) {
            throw new \RuntimeException(
                'Configuration for Raspberry Pi not set'
            );
        }
        $this->ip = $config['music-ip'];
    }

    /**
     * Run the intent.
     *
     * Kills the media player on the music machine.
     * @param \StdClass $slots Slots object
     * @return string
     */
    public function run($slots = null)
    {
        exec('ssh ' . $this->ip . ' killall mplayer');
        return 'You used to be cool. You used to be about the music.';
    }
}
