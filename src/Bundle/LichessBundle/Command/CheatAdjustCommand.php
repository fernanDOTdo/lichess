<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Redistributes a cheater ELO to his victims
 */
class CheatAdjustCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'Username of the cheater'),
            ))
            ->setName('lichess:cheat:adjust')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $user = $this->getContainer()->get('fos_user.repository.user')->findOneByUsername($username);
        if(!$user) {
            throw new \InvalidArgumentException(sprintf('The user "%s" does not exist', $username));
        }
        $punisher = $this->getContainer()->get('lichess.cheat.punisher');
        $punisher->setLogger(function($message) use ($output)
        {
            $output->writeLn($message);
        });
        $punisher->punish($user);
        $this->getContainer()->get('lichess.object_manager')->flush();
    }
}
