<?php


namespace matla\phpstanEfw;


use PHPStan\Command\ErrorsConsoleStyle;
use PHPStan\Command\Output;
use PHPStan\Command\Symfony\SymfonyOutput;
use PHPStan\Command\Symfony\SymfonyStyle;
use ReflectionClass;

class FileOutputFactory
{
    private $lastStream;
    private $streamClassName;
    private $input;

    public function __construct(Output $origOutput)
    {
        /**
         * @var SymfonyStyle $origStyle
         */
        $origStyle = $origOutput->getStyle();
        $origSStyle = $origStyle->getSymfonyStyle();

        $reflectionClass = new ReflectionClass($origSStyle);
        $parentReflectionClass = $reflectionClass->getParentClass();
        $humbugboxNamespace = explode('\\', $parentReflectionClass->getNamespaceName())[0];
        $reflectionProperty = $parentReflectionClass->getProperty('input');
        $reflectionProperty->setAccessible(true);
        $this->input = $reflectionProperty->getValue($origSStyle);
        $this->streamClassName =  "\\$humbugboxNamespace\\Symfony\\Component\\Console\\Output\\StreamOutput";
    }

    public function createFileOutput(string $filename)
    {
        if($this->lastStream !== null)
        {
            fclose($this->lastStream);
        }

        $this->lastStream = fopen($filename, 'w');

        $stream = new $this->streamClassName($this->lastStream);
        return new SymfonyOutput($stream, new SymfonyStyle(new ErrorsConsoleStyle($this->input, $stream)));
    }

    public function __destruct()
    {
        fclose($this->lastStream);
    }
}