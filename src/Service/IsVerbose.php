<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Service;

use Ampersand\VerboseLogRequest\Service\IsVerbose\Storage;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * This class cannot have any dependencies that are not fully defined in app/etc/*di.xml
 */
class IsVerbose
{
    const HEADER_NAME = 'X-Verbose-Log';
    const ENV_VAR_NAME = 'X_VERBOSE_LOG';

    /**
     * @var GetKey
     */
    private GetKey $getKey;

    /**
     * @param GetKey $getKey
     */
    public function __construct(GetKey $getKey)
    {
        $this->getKey = $getKey;
    }

    /**
     * Set the isVerbose flag for the remainder of the process
     *
     * @param HttpRequest $request
     * @param bool $force
     * @return void
     */
    public function init(HttpRequest $request, bool $force = false)
    {
        if (!$force && Storage::flagIsSet()) {
            return;
        }
        $isVerbose = false;

        $keyFromHeader = $request->getHeader(self::HEADER_NAME);
        if (is_string($keyFromHeader) && strlen($keyFromHeader)) {
            $isVerbose = $this->matchesKeyFromDeploymentConfig($keyFromHeader);
        }

        $keyFromEnv = $request->getServer(self::ENV_VAR_NAME, '');
        if (is_string($keyFromEnv) && strlen($keyFromEnv)) {
            $isVerbose = $this->matchesKeyFromDeploymentConfig($keyFromEnv);
        }

        Storage::setFlag($isVerbose, $force);
    }

    /**
     * Get from the static function to ensure we are always using the global flag for this request
     *
     * We dont end up with object manager differences between creation and getting of this class
     *
     * @return bool|string
     */
    public function isVerbose()
    {
        return Storage::getFlag();
    }

    /**
     * Does the provided string match the key from the deployment config, with the current date prepended to it
     *
     * @param string $string
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function matchesKeyFromDeploymentConfig($string)
    {
        $key = $this->getKey->execute();
        if (!$key) {
            return false;
        }
        return (trim($string) === $key);
    }
}
