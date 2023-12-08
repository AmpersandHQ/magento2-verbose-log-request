<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\CacheDecorator;

use Ampersand\VerboseLogRequest\Service\IsVerbose;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Cache\Frontend\Decorator\Bare;
use Magento\Framework\Cache\FrontendInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * Cache frontend decorator
 */
class VerboseLogger extends Bare
{
    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var IsVerbose
     */
    private IsVerbose $isVerbose;

    /**
     * @param FrontendInterface $frontend
     * @param HttpRequest $request
     * @param IsVerbose $isVerbose
     * @param Logger $logger
     */
    public function __construct(
        FrontendInterface $frontend,
        HttpRequest $request,
        IsVerbose $isVerbose,
        Logger $logger
    ) {
        parent::__construct($frontend);
        $this->logger = $logger;
        $isVerbose->init($request);
        $this->isVerbose = $isVerbose;
    }

    /**
     * Load from the cache and log to debug.log if this is a verbose request
     *
     * Load from the cache and log to debug.log if this is a verbose request
     *
     * @inheritDoc
     */
    public function load($identifier)
    {
        $result = parent::load($identifier);
        // Duplicate the inner `isVerbose` check so that this module can be installed without segfaulting
        if ($this->isVerbose->isVerbose()) {
            $this->logger->debug('cache_load: ', ['identifier' => $identifier, 'result' => $result]);
        }
        return $result;
    }

    /**
     * Save to the cache and log to debug.log if this is a verbose request
     *
     * Save to the cache and log to debug.log if this is a verbose request
     *
     * @inheritDoc
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        $result = parent::save($data, $identifier, $tags, $lifeTime);
        // Duplicate the inner `isVerbose` check so that this module can be installed without segfaulting
        if ($this->isVerbose->isVerbose()) {
            $this->logger->debug(
                'cache_save: ',
                [
                    'identifier' => $identifier,
                    'tags' => $tags,
                    'lifetime' => $lifeTime,
                    'result' => $result,
                    'data' => $data
                ]
            );
        }
        return $result;
    }
}
