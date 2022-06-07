<?php declare(strict_types=1);

namespace Tetraquark;

use \Tetraquark\Block;
use \Tetraquark\Foundation\{BlockAbstract};

/**
 *  Class contains Imports in form of the string
 */
class Import
{
    protected array $scripts   = [];
    protected array $retrivals = [];
    protected string $lastAlias = '';

    public function __construct()
    {
        // nothing
    }

    public function setScript(string $path, string $script): self
    {
        Log::log('Add script: ' . $path);
        $this->scripts[$path] = [
            "script" => $script,
            "alias"  => BlockAbstract::generateAliasStatic($this->lastAlias),
            "retrivals" => []
        ];
        $this->lastAlias = $this->scripts[$path]['alias'];
        return $this;
    }

    public function scriptExists(string $path): bool
    {
        return isset($this->scripts[$path]);
    }


    public function addRetrival(string $path, string $importsFrom, string $retrival): self
    {
        Log::log('Add retrival: ' . $retrival);
        if (!isset($this->retrivals[$path])) {
            $this->retrivals[$path] = [];
        }
        $this->retrivals[$path][] = [
            "retrival" => $retrival,
            "import" => $importsFrom
        ];
        return $this;
    }

    public function getRetrivals(): array
    {
        return $this->retrivals;
    }

    public function getScripts(): array
    {
        return $this->scripts;
    }

    public function getScript(string $path): array
    {
        return $this->scripts[$path] ?? throw new Exception('Import not found with path ' . htmlentities($path), 404);
    }

    public function recreate(): string
    {
        if (\sizeof($this->scripts) == 0) {
            return '';
        }

        $imports = 'let Ī={};';
        $skipScripts = [];
        $addToTheEndImports = '';
        foreach ($this->getRetrivals() as $path => $retrivals) {
            if ($this->scriptExists($path)) {
                $script = $this->getScript($path);
                $imports .= 'Ī.' . $script['alias'] . '=' . 'Ī=>{';
                $imports .= $this->recreateRetrivals($retrivals);
                $imports .= $script['script'] . "}";
                $skipScripts[$path] = true;
            } else {
                $addToTheEndImports .= $this->recreateRetrivals($retrivals);
            }
        }

        foreach ($this->getScripts() as $path => $script) {
            if (isset($skipScripts[$path])) {
                continue;
            }
            $imports .= 'Ī.' . $script['alias'] . '=' . 'Ī=>{' . $script['script'] . "}";
        }

        $imports .= $addToTheEndImports . 'a=undefined;';

        return $imports;
    }

    private function recreateRetrivals(array $retrivals): string
    {
        $retrs = '';
        foreach ($retrivals as $retrival) {
            $alias = $this->getScript($retrival['import'])['alias'];
            $retrs .= $retrival['retrival'] . 'Ī.' . $alias . '(Ī);';
        }
        return $retrs;
    }
}
