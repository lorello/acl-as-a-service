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
use Symfony\Component\HttpKernel\HttpKernelInterface;


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

        // $app['ip.allowed'] = array();
        // $app['ip.aws'] = array();

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
        $help = "Syntax:\nGET /is-allowed/{IP}\n\n";
        return $help;
    }
);


/**
 * $IP/is-member-of/$LIST
 * check if an IP is members of a list
 */
$app->get(
    '/ip/{ip}/is-member-of/{list}',
    function ($ip) use ($app) {

        // check list existence
        if (!is_array($app['acl'][$name])) {
            return new Response("Invalid request: list '$name'' does not exists", 412);
        }

        // create a unique array
        $filter = new IPFilter($app['acl'][$name]);
        if ($filter->check($ip)) {
            return 'OK';
        }
        return new Response("$ip is not allowed", 401);
    }
)->assert('ip', '^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$')
 ->assert('name', '^[A-Za-z0-9]+$');

$app->get(
    '/ip/{ip}',
    function ($ip) use ($app) {
        $app->redirect("/ip/$ip/is-anywhere");
    }
);

$app->get(
    '/ip/{ip}/is-anywhere',
    function ($ip) use ($app) {

        // check list existence
        if (!is_array($app['acl'][$name])) {
            return new Response("Invalid request: list '$name'' does not exists", 412);
        }

        // create a unique array
        $filter = new IPFilter($app['acl'][$name]);
        if ($filter->check($ip)) {
            return 'OK';
        }
        return new Response("$ip is not allowed", 401);
    }
)->assert('ip', '^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$');


$app->get(
    '/update/aws',
    function () use ($app) {

        // get current client IP to authorize request
        $client_ip = $app['request']->getClientIp();
        $subRequest = Request::create("/is-allowed/$client_ip");
        $response = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
        if ($response->getStatusCode() > 200) {
            return new Response("Unauthorized request from $client_ip", 401);
        }

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
        return new Response('AWS IPs updated', 201);

    }
);


/**
 * List all IPs in a list
 */
$app->get(
    '/list/{name}',
    function ($name) use ($app) {

        // check list existence
        if (!is_array($app['acl'][$name])) {
            return new Response("Invalid request: list '$name'' does not exists", 412);
        }

        return implode("\n", $app['acl'][$name]);
    }
)->assert('name', '^[A-Za-z0-9]+$');

# TODO: should accept networks?
$app->put(
    '/{name}/{ip}',
    function ($name, $ip) use ($app) {

        // Authorize request
        $client_ip = $app['request']->getClientIp();
        $subRequest = Request::create("/is-allowed/$client_ip");
        $response = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
        if ($response->getStatusCode() > 200) {
            return new Response("Unauthorized request from $client_ip", 401);
        }

        // check list existence
        if (!is_array($app['acl'][$name])) {
            return new Response("Invalid request from '$client_ip': list '$name'' does not exists", 412);
        }
        if (!ip2long($ip)) {
            return new Response("Bad request from $client_ip: $ip is not a valid IP", 400);
        }

        $app['acl'][$name] = array_unique(array_merge($app['acl'][$name], array($ip)));
        $yaml = new Dumper();
        // the parameter '2' of yaml->dump makes output more readable
        file_put_contents(__DIR__.'/../config/data.yaml', $yaml->dump($app['acl'], 2));
        return 'OK';
    }
);

# If your application is hosted behind a reverse proxy and you want Silex to trust the X-Forwarded-For* headers, you will need to run your application like this:
#Request::setTrustedProxies(array('127.0.0.1'));
$app->run();


