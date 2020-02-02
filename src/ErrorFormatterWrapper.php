<?php


namespace matla\phpstanEfw;

use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\Command\Output;
use PHPStan\Command\OutputStyle;

class ErrorFormatterWrapper implements ErrorFormatter
{
    /**
     * @var array<array>
     */
    protected $errorFormatters = [];
    private $outputStyle;

    public function __construct(?OutputStyle $outputStyle, array $errorFormatters)
    {
        $this->errorFormatters = $errorFormatters;
        $this->outputStyle = $outputStyle;
    }

    public function formatErrors(AnalysisResult $analysisResult, Output $origOutput): int
    {

        $fileOutputFactory = new FileOutputFactory($origOutput);

        /**
         * @var ErrorFormatter $errorFormatter
         * @var Output $output
         */
        foreach ($this->errorFormatters as ['formatter' => $errorFormatter, 'output' => $output]) {
            $output = $output ?? $origOutput;
            $file = null;

            if (!$output instanceof Output) {
                $output = $fileOutputFactory->createFileOutput($output);
            }

            $errorFormatter->formatErrors($analysisResult, $output);

            if ($file !== null) {
                fclose($file);
            }
        }

        return $analysisResult->hasErrors();
    }

}