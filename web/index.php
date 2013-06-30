<?php
/*
 * Created by lorello
 */
require_once __DIR__ . '/../vendor/autoload.php';

# TODO: there could be a better way ;-)
require_once __DIR__ . '/../vendor/ipfilter.class.php';

# to use Request object
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\MonologServiceProvider;
use Aws\Common\Enum\Region;
use Aws\Silex\AwsServiceProvider;
use Symfony\Component\Yaml\Dumper;

# Create the app instance
$app = new Silex\Application();

$env = getenv('APP_ENV') ?: 'prod';

$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__."/../config/$env.yaml"));
/*
$app->register(new MonologServiceProvider(), array(
        'monolog.logfile' => __DIR__.'/../log/silex.log',
));
 */
$app->register(new AwsServiceProvider(), array(
    'aws.config' => __DIR__.'/../aws-config.json'
));
// Note: You can also specify a path to a config file (e.g., 'aws.config' => '/path/to/aws/config/file.php')


# Turn on debugging
# $app['debug'] = true;

$allowed_ips[]='31.198.139.0/28';       # telecom netcruise @ Prato
$allowed_ips[]='46.226.179.80/28';      # viking @ Prato
$allowed_ips[]='77.238.6.0/23';         # ip @ Bologna
$allowed_ips[]='62.77.32.0/24';         # ip @ Bologna

$allowed_ips[]='194.177.114.*';         # ip @ I.Net / group 1
$allowed_ips[]='212.239.14.*';          # ip @ I.Net / group 2

$allowed_ips[]='156.54.107.95';         # ip default NAT @ SelfDC Telecom
$allowed_ips[]='156.54.107.96';         # ip @ SelfDC Telecom
$allowed_ips[]='156.54.107.97';         # ip @ SelfDC Telecom
$allowed_ips[]='156.54.107.98';         # ip @ SelfDC Telecom
$allowed_ips[]='156.54.107.104';        # ip @ SelfDC Telecom
$allowed_ips[]='156.54.107.105';        # ip @ SelfDC Telecom
$allowed_ips[]='156.54.107.106';        # ip @ SelfDC Telecom
$allowed_ips[]='156.54.107.107';        # ip @ SelfDC Telecom
$allowed_ips[]='192.168.0.0/16';        # private network
$allowed_ips[]='172.16.0.0/12';         # private network
$allowed_ips[]='10.0.0.0/8';            # private network


$app->before(
    function () use ($app) {

        $app['ip.allowed'] = array();
        $app['ip.aws'] = array();

        // load current networks
        $app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__."/../config/data.yaml"));
    }
);

$app->after(
    function () use ($app) {
    }
);

$app->get(
    '/',
    function () {
        $help = "Sintax:\nGET /is-allowed/{IP}\n\n";
        return $help;
    }
);

$app->get(
    '/is-allowed/{ip}',
    function ($ip) use ($app) {
        $filter = new IPFilter(array_merge($app['ip.allowed'], $app['ip.aws']));
        if ($filter->check($ip)) {
            return 'TRUE';
        }
        return 'FALSE';
    }
)->assert('ip', '^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$');

$app->get(
    '/update/aws',
    function () use ($app) {
        # TODO: catch errors
        $client = $app['aws']->get('Ec2');
        $iterator = $client->getIterator('describeInstances');
        foreach($iterator as $object) {
            if (isset($object['PublicIpAddress'])) {
                $result[] = $object['PublicIpAddress'];
            }
        }
        $data = array(
            'ip.allowed' => $app['ip.allowed'],
            'ip.aws' => $result
        );
        $yaml = new Dumper();
        file_put_contents(__DIR__.'/../config/data.yaml', $yaml->dump($data, 2));
        # TODO: redirect to list/aws if OK?
        return 'OK';
    }
);

$app->get(
    '/list/{name}',
    function ($name) use ($app) {
        return implode("\n", $app['ip.'.$name]);
    }
)->assert('name', '^(allowed|aws)$');

$app->put(
    '/allowed/{ip}',
    function ($ip) use ($app) {
        if (ip2long($ip)) {
            $data = array(
                'ip.allowed' => array_unique(array_merge($app['ip.allowed'], array($ip))),
                'ip.aws' => $app['ip.aws']
            );
            $yaml = new Dumper();
            file_put_contents(__DIR__.'/../config/data.yaml', $yaml->dump($data, 2));
            return 'OK';
        }
        return 'KO';
    }
);

# If your application is hosted behind a reverse proxy and you want Silex to trust the X-Forwarded-For* headers, you will need to run your application like this:
#Request::setTrustedProxies(array('127.0.0.1'));
$app->run();


