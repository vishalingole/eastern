<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentDateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('generate:payment-date-csv')
            ->setDescription('Salary Payment Date tool')
            ->addArgument('filename', InputArgument::REQUIRED, 'Name of the csv file')
            ->addArgument('year', InputArgument::OPTIONAL, 'Year like 2019/2018')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $fileName = $input->getArgument('filename');
        $output->writeln('Process Started...');
        $year = (NULL === $input->getArgument('year')) ? 2019 : $input->getArgument('year');

        $this->generateCsv($fileName, $year);

        $output->writeln('Command result.');
    }

    /**
     * @param String $fileName
     * @param integer $year
     * @return void
     */
    public function generateCsv(String $fileName, int $year)
    {
        $header = ["Month", "Bonus Payment Date", "Salary Payment Date"];
        $list = [];
        $filePointer = fopen($fileName, "w");
        fputcsv(
            $filePointer,
            $header
        );

        for ($month = 1; $month <= 12; $month++ ) {
           $list[] = $this->getBounusPaymentDate($month, $year);
        }

        foreach ($list as $line)
        {
            fputcsv(
                $filePointer,
                $line,
                ','
            );
        }

        fclose($filePointer);
    }

    /**
     * @param integer $month
     * @param integer $year
     * @return array
     */
    public function getBounusPaymentDate(int $month, int $year) {
        $dateToTest = $year."-".$month."-"."01";
        $monthEndDate = date('t', strtotime($dateToTest));
        $arr = [];
        for ($d = 1; $d <= $monthEndDate; $d++)
        {
            $time = mktime(12, 0, 0, $month, $d, $year);
            $arr[0] = date('F', $time);
            if (date('d', $time) == 15 ) {
                if (!in_array(date('l', $time), ['Saturday', 'Sunday'])) {
                    $arr[2] = date('Y-m-d', $time);
                } else {
                    $arr[2] = date('Y-m-d', strtotime('next Wednesday', $time));
                }
            } elseif (date('d', $time) == $monthEndDate) {
                if (!in_array(date('l', $time), ['Saturday', 'Sunday'])) {
                    $arr[1] = date('Y-m-d', $time);
                } else {
                    $arr[1] = date('Y-m-d', strtotime('previous Friday', $time));
                }
            }
        }

        return $arr;
    }

}
