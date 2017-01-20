<?php

namespace Sithous\AntiSpamBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sithous\AntiSpamBundle\Entity\SithousAntiSpamType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;


class DeleteTypeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sithous:antispam:delete')
            ->setDescription('Generate new SithousAntiSpamType.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $repository = $em->getRepository('SithousAntiSpamBundle:SithousAntiSpamType');
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        $types = array();
        foreach($repository->findAll() as $type)
        {
            $types[] = $type->getId();
        }

        if(!$id = $questionHelper->ask(
            $input,
            $output,
            new ChoiceQuestion('<question>Enter the SithousAntiSpamType ID to delete:</question> ',$types)
        ))
        {
            return $output->writeln('<error>ERROR: An ID must be specified.</error>');
        }

        if(!$entity = $repository->findOneById($id))
        {
            return $output->writeln("<error>ERROR: SithousAntiSpamType with ID \"$id\" could not be found.</error>");
        }

        $em->remove($entity);
        $em->flush();

        return $output->writeln("<info>Successfully removed SithousAntiSpamType \"$id\"</info>");
    }
}