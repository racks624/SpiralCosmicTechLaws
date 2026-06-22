<?php
namespace App\Modules\PayloadGenerator;

abstract class Builder
{
    protected string $lhost;
    protected int $lport;
    protected string $outputPath;

    public function __construct($lhost, $lport)
    {
        $this->lhost = $lhost;
        $this->lport = $lport;
        $this->outputPath = ROOT_PATH . '/storage/payloads/';
        if (!is_dir($this->outputPath)) mkdir($this->outputPath, 0755, true);
    }

    abstract public function generate(): string;
    abstract public function getFileExtension(): string;
    abstract public function getMimeType(): string;

    public function getOutputPath($filename)
    {
        return $this->outputPath . $filename;
    }

    public function save($content, $filename): string
    {
        $fullPath = $this->getOutputPath($filename);
        file_put_contents($fullPath, $content);
        return $fullPath;
    }
}
