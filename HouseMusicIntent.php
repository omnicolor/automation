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
     * Verifies that the music filesystem is mounted, then starts the music.
     * @param \StdClass $slots Slots object
     * @throws \RuntimeException
     * @return string
     */
    public function run($slots = null)
    {
        $this->mountFilesystem();
        exec('ssh ' . $this->ip . ' \'killall mplayer ; cd music ; mplayer -slave -input file=/home/pi/mplayer-control -shuffle -playlist party.m3u\'', $output);

        return 'If this house is a rockin, don\'t bother knockin!';
    }

    /**
     * SSHes to the music machine and attempts to mount the filesystem if
     * needed.
     * @throws \RuntimeException
     */
    protected function mountFilesystem()
    {
        exec('ssh ' . $this->ip . ' ls music', $output);
        if ([] !== $output) {
            return;
        }
        error_log('Media player: SSHFS not mounted');

        // SSH file system is unmounted for whatever reason...
        exec('ssh ' . $this->ip . ' sshfs mordor-media:/home/music music');

        // Test to see if it's connected now.
        exec('ssh ' . $this->ip . ' ls music', $output);
        if ([] === $output) {
            // Still not connected, something's very wrong.
            error_log('Failed to mount SSHFS on master pi');
            throw new \RuntimeException(
                'Master pie could not mount music directory. Please ask Omni to fix something.'
            );
        }
    }
}
