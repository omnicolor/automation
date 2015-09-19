<?php
/**
 * Skip whatever song the house is playing.
 */
class HouseMusicNextSongIntent implements Intent
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
     * Tells the music machine to move to the next song.
     * @return string
     */
    public function run()
    {
        exec('ssh ' . $this->ip . ' \'echo "pausing_keep_force pt_step 1" >> mplayer-control\'');
        return 'Yeah, that song wasn\'t very good.';
    }
}
