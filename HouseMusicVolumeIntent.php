<?php
/**
 * Changes the volume on the house music system.
 */
class HouseMusicVolumeIntent implements Intent
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
     * Turn the volume up or down.
     * @param StdClass $slots Optional slots object
     * @return string
     */
    public function run($slots = null)
    {
        if (!$slots || !$slots->direction || !$slots->direction->value) {
            throw new \RuntimeException(
                'Error when trying to determine volume direction'
            );
        }

        $direction = $slots->direction->value;
        $volume = $this->getVolume();
        if ('up' === $direction) {
            setVolume($volume + 10);
            return 'Hup, hup, ha.';
        }
        setVolume($volume - 10);
        return 'Like a mouse.';
    }

    /**
     * Get the volume from a remote player.
     * @return integer Percent volume the remote server is playing at
     */
    protected function getVolume() {
        exec('ssh ' . $this->ip . ' amixer', $output);
        $volume = array_pop($output);
        preg_match('/\[(\d*)/', $volume, $matches);
        return array_pop($matches);
    }

    /**
     * Set the volume on the remote player.
     * @param integer $volume Percentage volume to play at.
     */
    protected function setVolume($volume) {
        $volume = escapeshellarg($volume) . '%';
        exec('ssh ' . $this->ip . ' amixer set PCM ' . $volume);
    }
}
