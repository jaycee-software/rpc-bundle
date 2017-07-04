<?php

namespace JayceeSoftware\RpcBundle\Foundation\RequestSource;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use JayceeSoftware\RpcBundle\Foundation\JsonRpcRequest;
use JayceeSoftware\RpcBundle\Foundation\JsonRpcResponse;

class AMQPConsumer implements ConsumerInterface
{
    /**
     * @var string Name of Service
     */
    protected $serviceName;
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var null|LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var bool
     */
    protected $debug = false;

    protected static $exceptionsToDie = [
        'PhpAmqpLib\Exception\AMQPRuntimeException',
        'Doctrine\DBAL\DBALException'
    ];

    /**
     * @param string $serviceName
     * @param ContainerInterface $container
     * @param LoggerInterface|null $logger
     */
    public function __construct($serviceName, ContainerInterface $container, LoggerInterface $logger = null)
    {
        $this->serviceName = $serviceName;
        $this->container = $container;
        // @todo BC
        $this->logger = $logger ?: $container->get('monolog.logger.worker', ContainerInterface::NULL_ON_INVALID_REFERENCE);
    }

    public function execute(AMQPMessage $msg)
    {
        if (null !== $this->logger) {
            // @todo dirty hack but here it's impossible to pass service name to logger
            $this->logger->info('['.$this->serviceName.'] New message', [$msg->body]);
        }
        $req = unserialize($msg->body);

        if (empty($req['method'])) {
            throw new \RuntimeException('Section "method" must be defined.');
        }

        $kernel = $this->getNewKernel();
        $request = JsonRpcRequest::create(
            '/'.$this->serviceName.'/'.(str_replace('.', '/', $req['method'])),
            'POST',
            (empty($req['params']) ? array() : $req['params']),
            array(),
            array(),
            array(
                'USER_TOKEN' => !empty($req['token']) ? $req['token'] : null,
                'SERVICE_NAME' => $this->serviceName
            ),
            null,
            JsonRpcRequest::TYPE_AMQP
        );
        try {
            $response = $kernel->handle($request, HttpKernelInterface::MASTER_REQUEST, false);

            if ($response->isOk()) {
                if (null !== $this->logger) {
                    $this->logger->info('Message processed.');
                }
            } else {
                if (null !== $this->logger) {
                    $this->logger->error('Message was processed with error.', $response->getContent());
                }
            }
            $kernel->terminate($request, $response);

            return $response->__toString();
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->error($e->getMessage(), $e->getTrace());
            }
            $response = JsonRpcResponse::createFromException($e);
            $kernel->terminate($request, $response);

            if (in_array(get_class($e), self::$exceptionsToDie)) {
                throw $e;
            }

            return $response->__toString();
        }
        return false;
    }

    /**
     * @return HttpKernelInterface
     * @todo refactorme
     */
    protected function getNewKernel()
    {
        $kernel = $this->container->get('kernel');
        $kernel->getContainer()->get('doctrine')->resetManager();
        $kernel->getContainer()->get('security.context')->setToken();
        $kernel->getContainer()->get('fxs_rpc_service.service')->setUserToken(null);
        return $kernel;
        $this->environment = $kernel->getEnvironment();
        $this->debug = $kernel->isDebug();

        if ($kernel instanceof Kernel) {
            return clone $kernel;
        } elseif ($kernel instanceof HttpCache) { // @todo seems like we should check if kernel is instance of HttpCache
            throw new \Exception('Not implemented yet');
            $t = $kernel->getKernel();
            //$k = new HttpCache(clone $t);
            return $k;
        }
    }
}
