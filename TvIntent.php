<?php
/**
 * Intent for controlling my Kodi machine.
 */
class TvIntent implements Intent
{
    protected $dbh;
    protected $username;
    protected $password;
    protected $hostname;
    protected $database;
    protected $player;
    protected $fullShows = [];
    protected $halfShows = [];
    protected $kidsShows;

    public function __construct($config)
    {
        if (!isset($config['kodi-username'])
            || !isset($config['kodi-password'])
            || !isset($config['kodi-database'])
            || !isset($config['kodi-hostname'])
            || !isset($config['kodi-player'])
        ) {
            throw new \RuntimeException('Kodi configuration not set');
        }

        $this->database = $config['kodi-database'];
        $this->hostname = $config['kodi-hostname'];
        $this->password = $config['kodi-password'];
        $this->player = $config['kodi-player'];
        $this->username = $config['kodi-username'];

        if (isset($config['full-shows'])) {
            $this->fullShows = $config['full-shows'];
        }
        if (isset($config['half-shows'])) {
            $this->halfShows = $config['half-shows'];
        }
        if (isset($config['kids-shows'])) {
            $this->kidsShows = $config['kids-shows'];
        }
    }

    /**
     * Run the intent.
     *
     * Connects to the database to randomly choose the next unwatched episode
     * from a list of shows.
     * @param \StdClass $slots Slots object from the intent
     * @return string
     * @throws \RuntimeException
     */
    public function run($slots = null)
    {
        try {
            $this->dbh = new \PDO(
                'mysql:dbname=' . $this->database . ';host=' . $this->hostname
                . ';charset=UTF8',
                $this->username,
                $this->password
            );
        } catch (\PDOException $e) {
            error_log('PDO connection failed: ' . $e->getMessage());
            throw new \RuntimeException('Database connection failed');
        }

        $size = $slots->size->value;
        switch ($size) {
            case 'full':
            case 'adult':
                $shows = $this->fullShows;
                break;
            case 'half':
            case 'short':
                $shows = $this->halfShows;
                break;
            case 'kid':
            case 'children':
                $shows = $this->kidsShows;
                break;
            default:
                return 'I didn\'t understand what kind of show to play';
        }

        if ([] === $shows) {
            return 'I don\'t have a list of ' . $size . ' shows to choose from';
        }

        return 'Starting the next unwatched ' . $size . ' show.';
        $path = $this->findNextShow($shows);

        $payload = [
            'jsonrpc' => '2.0',
            'method' => 'Player.Open',
            'params' => [
                'item' => [
                    'file' => $path,
                ],
            ],
        ];

        $payload = urlencode(json_encode($payload));
        $url = 'http://' . $this->player . '/jsonrpc?request=' . $payload;
        $ch = curl_init($url);
        curl_exec($ch);
        curl_close($ch);

        return 'Starting the next unwatched ' . $size . ' show.';
    }

    /**
     * Randomly pick the next show to watch.
     * @param array $shows Array of shows to choose from
     * @return string Path of the chosen show
     */
    protected function findNextShow($shows)
    {
        $query = 'SELECT episode.c18 AS path '
            . 'FROM files '
            . 'INNER JOIN episode USING (idFile) '
            . 'INNER JOIN tvshow USING (idShow) '
            . 'WHERE tvshow.c00 IN ("' . implode('", "', $shows) . '") '
            . 'AND playCount IS NULL '
            . 'ORDER BY RAND() '
            . 'LIMIT 1';
        $result = $this->dbh->query($query, \PDO::FETCH_ASSOC);
        return $result->fetch();
    }
}
