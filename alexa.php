<?php
/**
 * Get the volume from a remote player.
 * @param string $pi User and host to log in to
 * @return integer Percent volume the remote server is playing at
 */
function getVolume($pi) {
    exec('ssh ' . $pi . ' amixer', $output);
    $volume = array_pop($output);
    preg_match('/\[(\d*)/', $volume, $matches);
    return array_pop($matches);
}

/**
 * Set the volume on the remote player.
 * @param integer $volume Percentage volume to play at.
 * @param string $pi User and host to log in to
 */
function setVolume($volume, $pi) {
    $volume = escapeshellarg($volume) . '%';
    exec('ssh ' . $pi . ' amixer set PCM ' . $volume);
}

function findNextShow($shows, $dbh) {
    $query = 'SELECT episode.c18 AS path '
        . 'FROM files '
        . 'INNER JOIN episode USING (idFile) '
        . 'INNER JOIN tvshow USING (idShow) '
        . 'WHERE tvshow.c00 IN ("' . implode('", "', $shows) . '") '
        . 'AND playCount IS NULL '
        . 'ORDER BY episode.c05 '
        . 'LIMIT 1';
    $result = $dbh->query($query, \PDO::FETCH_ASSOC);
    return $result->fetch();
}

/**
 * Return the next unwatched short show.
 * @param PDO $dbh
 * @return string
 */
function getHalfShow($dbh) {
    $shows = [
        'The Guild',
        'Robot Chicken',
    ];

    return findNextShow($shows, $dbh)['path'];
}

/**
 * Return the next unwatched hour long show.
 * @param PDO $dbh
 * @return string
 */
function getFullShow($dbh) {
    $shows = [
        '12 Monkeys',
        'Castle',
        'Continuum',
        'Dark Matter',
        'Falling Skies',
        'Game of Thrones',
        'Gotham',
        'Killjoys',
        'Lucifer',
        'Marvel\'s Agent Carter',
        'Marvel\'s Agents of S.H.I.E.L.D.',
        'Marvel\'s Daredevil',
        'Mr. Robot',
        'Once Upon a Time',
        'Penny Dreadful',
        'Silicon Valley',
        'Supernatural',
    ];
    return findNextShow($shows, $dbh)['path'];
}

function getKidShow($dbh) {
    $shows = [
        'Paw Patrol',
        'Scooby Doo',
    ];
    return findNextShow($shows, $dbh)['path'];
}

$input = json_decode(file_get_contents('php://input'));

$request = $input->request;

header('Content-Type: application/json;charset=UTF-8');
$response = [
    'version' => '1.0',
    'response' => [
        'shouldEndSession' => true,
    ],
];

if ('LaunchRequest' === $request->type) {
    error_log('Mordor received LaunchRequest');
    $response['response']['outputSpeech'] = [
        'type' => 'PlainText',
        'text' => 'You didn\'t tell me what to ask Mordor.',
    ];
    echo json_encode($response);
    exit();
}

$intent = $request->intent->name;
$pi = 'pi@ip-address';

switch ($intent) {
    case 'HouseMusicIntent':
        // Let's make sure the filesystem is mounted.
        exec('ssh ' . $pi . ' ls music', $output);
        if ([] === $output) {
            error_log('Media player: SSHFS not mounted');

            // SSH file system is unmounted for whatever reason...
            exec('ssh ' . $pi . ' sshfs mordor-media:/home/music music');

            exec('ssh ' . $pi . ' ls music', $output);
            if ([] === $output) {
                error_log('Failed to mount SSHFS on master pi');
                $response['response']['outputSpeech'] = [
                    'type' => 'PlainText',
                    'text' => 'Master pie could not mount music directory. Please ask Omni to fix something.',
                ];
                echo json_encode($response);
                exit();
            }
        }

        exec('ssh ' . $pi . ' \'killall mplayer ; cd music ; mplayer -slave -input file=/home/pi/mplayer-control -shuffle -playlist party.m3u\'', $output);

        $response['response']['outputSpeech'] = [
            'type' => 'PlainText',
            'text' => 'If this house is a rockin, don\'t bother knockin!',
        ];
        echo json_encode($response);
        break;
    case 'QuietHouseIntent':
        exec('ssh ' . $pi . ' killall mplayer');
        $response['response']['outputSpeech'] = [
            'type' => 'PlainText',
            'text' => 'You used to be cool. You used to be about the music.',
        ];
        echo json_encode($response);
        break;
    case 'HouseMusicVolumeIntent':
        $direction = $request->intent->slots->direction->value;
        $volume = getVolume($pi);
        if ('up' === $direction) {
            setVolume($volume + 10, $pi);
            $response['response']['outputSpeech'] = [
                'type' => 'PlainText',
                'text' => 'Hup, hup, ha.',
            ];
        } else {
            setVolume($volume - 10, $pi);
            $response['response']['outputSpeech'] = [
                'type' => 'PlainText',
                'text' => 'Like a mouse.',
            ];
        }
        echo json_encode($response);
        break;
    case 'HouseMusicNextSongIntent':
        error_log('Skipping crappy song');
        exec('ssh ' . $pi . ' \'echo "pausing_keep_force pt_step 1" >> mplayer-control\'');
        $response['response']['outputSpeech'] = [
            'type' => 'PlainText',
            'text' => 'Yeah, that song wasn\'t very good.',
        ];
        echo json_encode($response);
        break;
    case 'TimeIntent':
        $response['response']['outputSpeech'] = [
            'type' => 'PlainText',
            'text' => 'No, it\'s not time to feed fatty',
        ];
        echo json_encode($response);
        exit();
    case 'TvIntent':
        try {
            $dbh = new \PDO(
                'mysql:dbname=database;host=localhost;charset=UTF8',
                'username',
                'password'
            );
        } catch (\PDOException $e) {
            error_log('PDO connection failed: ' . $e->getMessage());
            exit(1);
        }

        $size = $request->intent->slots->size->value;
        switch ($size) {
            case 'full':
                $path = getFullShow($dbh);
                error_log('Mordor playing full adult show: ' . $path);
                break;
            case 'half':
                $path = getHalfShow($dbh);
                error_log('Mordor playing half adult show: ' . $path);
                break;
            case 'kid':
                $path = getKidShow($dbh);
                error_log('Mordor playing kid show: ' . $path);
                break;
            default:
                $response['response']['outputSpeech'] = [
                    'type' => 'PlainText',
                    'text' => 'I didn\'t understand what kind of show to play',
                ];
                echo json_encode($response);
                exit();
        }

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
        $url = 'http://192.168.1.111:80/jsonrpc?request=' . $payload;
        error_log($url);
        $ch = curl_init($url);
        curl_exec($ch);
        curl_close($ch);

        $response['response']['outputSpeech'] = [
            'type' => 'PlainText',
            'text' => 'Starting the next unwatched show.',
        ];
        echo json_encode($response);
        break;
    default:
        error_log('Mordor received unknown intent: ' . $intent);
        $response['response']['outputSpeech'] = [
            'type' => 'PlainText',
            'text' => 'Sorry, Mordor doesn\'t know how to do what you\'re asking',
        ];
        echo json_encode($response);
        exit();
}
