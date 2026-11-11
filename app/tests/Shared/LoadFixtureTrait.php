<?php

declare(strict_types=1);

namespace App\Tests\Shared;

trait LoadFixtureTrait
{
    protected function loadFixture(string $path): string
    {
        $projectDir = self::getContainer()->getParameter('kernel.project_dir');

        assert(is_string($projectDir));

        $filename = sprintf(
            '%s/tests/Fixtures/%s',
            $projectDir,
            ltrim($path, '/'),
        );

        $content = file_get_contents($filename);

        if (false === $content) {
            throw new \RuntimeException(sprintf('Fixture "%s" cannot be read.', $filename));
        }

        return $content;
    }
}
