<?php

namespace Sithous\AntiSpamBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sithous\AntiSpamBundle\Entity\SithousAntiSpamType;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;


class GeneratorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sithous:antispam:generate')
            ->setDescription('Generate new SithousAntiSpamType.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $repository = $em->getRepository('SithousAntiSpamBundle:SithousAntiSpamType');
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        if(!$id = $questionHelper->ask(
            $input,
            $output,
            new Question('<question>Please enter the ID for this type:</question> ')
        ))
        {
            return $output->writeln('<error>ERROR: ID cannot be blank.</error>');
        }

        if($repository->findOneById($id))
        {
            return $output->writeln('<error>ERROR: SithousAntiSpamType with this ID already exists.</error>');
        }

        $trackIp = $questionHelper->ask(
                $input,
                $output,
                new ConfirmationQuestion('<question>Track IP [Y/N]?</question> ')
            ) == 'y';

        $trackUser = $questionHelper->ask(
                $input,
                $output,
                new ConfirmationQuestion('<question>Track User [Y/N]?</question> ')
            ) == 'y';

        $maxTime = $questionHelper->ask(
            $input,
            $output,
            new Question('<question>Max Time to track action (seconds):</question> ')
        );

        if(!$maxTime || $maxTime < 1)
        {
            return $output->writeln('<error>ERROR: Max Time must be >= 1.</error>');
        }

        $maxCalls = $questionHelper->ask(
            $input,
            $output,
            new Question('<question>Max Calls that can happin in MaxTime:</question> ')
        );

        if(!$maxCalls || $maxCalls < 1)
        {
            return $output->writeln('<error>ERROR: Max Calls must be >= 1.</error>');
        }

        $output->writeln("");
        $output->writeln("ID: $id");
        $output->writeln("trackIp: ".($trackIp ? 'true' : 'false'));
        $output->writeln("trackUser: ".($trackUser ? 'true' : 'false'));
        $output->writeln("maxTime: $maxTime seconds");
        $output->writeln("maxCalls: $maxCalls");
        $output->writeln("");

        if(!$questionHelper->ask(
                $input,
                $output,
                new ConfirmationQuestion('<question>Is the above information correct [Y/N]?</question> ')
            ) == 'y')
        {
            return $output->writeln('<error>You have cancelled.</error>');
        }

        $entity = new SithousAntiSpamType();
        $entity
            ->setId($id)
            ->setTrackIp($trackIp)
            ->setTrackUser($trackUser)
            ->setMaxTime($maxTime)
            ->setMaxCalls($maxCalls);

        $em->persist($entity);
        $em->flush();

        return $output->writeln("<info>Successfully added SithousAntiSpamType \"$id\"</info>");
    }
}