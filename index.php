<?php

    // Show errors after start engine
    ini_set('display_errors', true);
    ini_set('error_reporting', E_ALL);
    define('BASEPATH',   str_replace('\\', '/', dirname(__FILE__)) . '/');
   
    require BASEPATH . '_private/vendor/autoload.php'; // Engine and others libs
    require BASEPATH . '_private/settings.php'; // Engine config
    require BASEPATH . '_private/helpers.php'; // Others help functions

    // Start engine
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    $app = new \Slim\App(["settings" => $config]);

    // Show errors in beautiful form
    $app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);

    $container = $app->getContainer();

    // Append logger
    $container['logger'] = function($c)
    {
        $logger = new \Monolog\Logger('appLogger');
        $file   = new \Monolog\Handler\StreamHandler($c['settings']['logs']['path']);
        $logger->pushHandler($file);
        return $logger;
    };

    // Engine routing and controlles
    $app->group
    (
        '/v1',
        function () use ($app)
        {
            $app->get
            (
                '/rates/latest',
                function (Request $request, Response $response)
                {
                    $model = new CalcDataModel(true); // We get data only from cache
                    $readyData =
                    [
                        'success' => false,
                        'payload' => []
                    ];
                    
                    try
                    {
                        $readyData =
                        [
                            'success' => true,
                            'payload' =>
                            [
                                'timestamp' => MarketModel::GetLastUpdatedTime(),
                                'index'     => $model->GetTrueBTC(),
                                'exchanges' => $model->GetData()
                            ]
                        ];
                    }
                    catch (Exception $e)
                    {
                        $this->logger->addInfo(sprintf("Error = %s", $e->getMessage()));
                    }
                    
                    return $response->getBody()->write
                    (
                        json_encode($readyData)
                    );
                }
            );
            $app->get
            (
                '/update_time',
                function (Request $request, Response $response)
                {
                    $updInfo = new stdClass();
                    $updInfo->timestamp = MarketModel::GetLastUpdatedTime();
                    return $response->getBody()->write
                    (
                        json_encode($updInfo)
                    );
                }
            );
            $app->get
            (
                '/update_all',
                function (Request $request, Response $response)
                {
                    $model   = new CalcDataModel(false); // We get not from cache if cache is expired
                    $data    = $model->GetData();
                    $updInfo = new stdClass();
                    $updInfo->timestamp = MarketModel::GetLastUpdatedTime();
                    return $response->getBody()->write
                    (
                        json_encode($updInfo)
                    );
                }
            );
            $app->get
            (
                '/market/{name}',
                function (Request $request, Response $response)
                {
                    $name    = $request->getAttribute('name');
                    $model   = new CalcDataModel(false); // We get not from cache if cache is expired
                    $data    = $model->GetMarketDataByMarketName($name);
                    return $response->getBody()->write
                    (
                        json_encode($data)
                    );
                }
            );
        }
    );
    
    $app->run();