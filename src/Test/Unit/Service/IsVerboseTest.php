<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Test\Unit\Service;

use Ampersand\VerboseLogRequest\Service\GetKey;
use Ampersand\VerboseLogRequest\Service\IsVerbose;
use Ampersand\VerboseLogRequest\Service\IsVerbose\Storage;
use Magento\Framework\App\Request\Http as HttpRequest;
use PHPUnit\Framework\TestCase;

class IsVerboseTest extends TestCase
{
    /**
     * @var GetKey|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private GetKey $getKey;

    /**
     * @var IsVerbose
     */
    private IsVerbose $isVerbose;

    /**
     *
     */
    protected function setUp(): void
    {
        Storage::reset(true);
        $this->getKey = $this->createMock(GetKey::class);
        $this->isVerbose = new IsVerbose($this->getKey);
    }

    /**
     *
     */
    public function testVerboseNotSet()
    {
        $this->getKey->expects($this->never())
            ->method('execute');

        $this->isVerbose->init($this->createMock(HttpRequest::class));
    }

    /**
     * @param $keyInDeployConf
     * @param $keyInHeader
     * @param $isVerboseExpected
     *
     * @dataProvider verboseSetProvider
     */
    public function testVerboseSetInHeader($keyInDeployConf, $keyInHeader, $isVerboseExpected)
    {
        $request = $this->createMock(HttpRequest::class);
        $request->expects($this->once())
            ->method('getHeader')
            ->with(IsVerbose::HEADER_NAME)
            ->willReturn($keyInHeader);

        $this->getKey->expects($this->exactly($keyInHeader ? 1 : 0))
            ->method('execute')
            ->willReturn($keyInDeployConf);

        $this->isVerbose->init($request);
        $this->assertEquals($isVerboseExpected, Storage::getFlag());
        $this->assertEquals($isVerboseExpected, $this->isVerbose->isVerbose());
    }

    /**
     * @param $keyInDeployConf
     * @param $keyInEnv
     * @param $isVerboseExpected
     *
     * @dataProvider verboseSetProvider
     */
    public function testVerboseSetInEnv($keyInDeployConf, $keyInEnv, $isVerboseExpected)
    {
        $request = $this->createMock(HttpRequest::class);
        $request->expects($this->once())
            ->method('getEnv')
            ->with(IsVerbose::HEADER_NAME, '')
            ->willReturn($keyInEnv);

        $this->getKey->expects($this->exactly($keyInEnv ? 1 : 0))
            ->method('execute')
            ->willReturn($keyInDeployConf);

        $this->isVerbose->init($request);
        $this->assertEquals($isVerboseExpected, Storage::getFlag());
        $this->assertEquals($isVerboseExpected, $this->isVerbose->isVerbose());
    }

    public function verboseSetProvider()
    {
        return [
            'Deploy key matches provided key, isVerbose=true' => [
                'some_key_goes_here',
                'some_key_goes_here',
                true
            ],
            'Deploy key does not match provided key, isVerbose=false' => [
                'some_key_goes_here',
                'some_key_goes_here_boo',
                false
            ],
            'Key cannot be gotten, cannot not match env, isVerbose=false' => [
                false,
                'some_key_goes_here_boo',
                false
            ],
            'Key cannot be gotten, no key provided in env, isVerbose=false' => [
                false,
                false,
                false
            ],
        ];
    }
}
