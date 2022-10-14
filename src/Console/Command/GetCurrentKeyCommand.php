<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Console\Command;

use Ampersand\VerboseLogRequest\Service\GetKey;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetCurrentKeyCommand extends Command
{
    /**
     * @var GetKey
     */
    private GetKey $getKey;

    /**
     * Constructor
     *
     * @param GetKey $getKey
     */
    public function __construct(GetKey $getKey)
    {
        $this->getKey = $getKey;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('ampersand:verbose-log-request:get-key');
        $this->setDescription('Get the current key for passing along in env variables or the header.');
        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $todaysKey = $this->getKey->execute();
        if (!$todaysKey) {
            $output->writeln('<error>Could not get todays key, is the deployment config readable?</error>');
            return 1;
        }
        $output->writeln('<info>Todays key is : ' . $todaysKey . '</info>');
        return 0;
    }
}
