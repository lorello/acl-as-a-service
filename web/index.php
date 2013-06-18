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
use Aws\Common\Enum\Region;
use Aws\Silex\AwsServiceProvider;

# Create the app instance
$app = new Silex\Application();


$app->register(new AwsServiceProvider(), array(
    'aws.config' => '../aws-config.json'
));
// Note: You can also specify a path to a config file (e.g., 'aws.config' => '/path/to/aws/config/file.php')


# Turn on debugging
$app['debug'] = true;

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


$app->get(
    '/',
    function () {
        $help = "Sintax:\nGET /is-valid-ip/{IP}\n\n";
        return $help;
    }
);

$app->get(
    '/is-my-ip/{ip}',
    function ($ip) use ($allowed_ips) {
        $filter = new IPFilter($allowed_ips);
        if ($filter->check($ip)) {
            return 'TRUE';
        }
        return 'FALSE';
    }
)->assert('ip', '^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$');

$app->get(
    '/awsips',
    function () use ($app) {
        $client = $app['aws']->get('Ec2');
        $iterator = $client->getIterator('describeInstances');
        foreach($iterator as $object) {
            if (isset($object['PublicIpAddress'])) {
                $result_array[] = $object['PublicIpAddress'];
            }
        }
        return implode("\n", $result_array);
    }
);
# If your application is hosted behind a reverse proxy and you want Silex to trust the X-Forwarded-For* headers, you will need to run your application like this:
#Request::setTrustedProxies(array('127.0.0.1'));
$app->run();


