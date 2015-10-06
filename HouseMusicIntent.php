<?php
/**
 * Turn on the house music system.
 */
class HouseMusicIntent implements Intent
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
     * @param \StdClass $slots Slots object
     * @throws \RuntimeException
     * @return string
     */
    public function run($slots = null)
    {
        exec('ssh ' . $this->ip . ' \'killall mplayer ; cd music ; mplayer -slave -input file=/home/pi/mplayer-control -shuffle -playlist party.m3u\'', $output);

        return 'If this house is a rockin, don\'t bother knockin!';
    }
}
