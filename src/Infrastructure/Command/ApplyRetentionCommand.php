<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Domain\Privacy\Service\RetentionPolicy;

use function sprintf;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:privacy:retention', description: 'Purge unbookmarked articles after 90 days and audit/tombstone records after 30 days.')]
final class ApplyRetentionCommand extends Command
{
    public function __construct(private readonly RetentionPolicy $policy)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('apply', null, InputOption::VALUE_NONE, 'Apply deletion; default is a dry run.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $this->policy->run((bool) $input->getOption('apply'));
        $output->writeln(sprintf('%d articles eligible. Mode: %s.', $count, $input->getOption('apply') ? 'applied' : 'dry-run'));

        return Command::SUCCESS;
    }
}
