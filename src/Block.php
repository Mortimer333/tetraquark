<?php declare(strict_types=1);

namespace Tetraquark;

abstract class Block
{
    public const SCOPE_SCRIPT = 'script';
    public const SCOPE_GLOBAL = 'global';
    public const SCOPE_LOCAL  = 'local';
    private string $content;
    private string $subtype;
    private string $scope;
    private array  $data;

    public function __construct(
        string $content,
        string $subtype,
        string $scope = self::SCOPE_GLOBAL,
        array $data  = []
    ) {
        $this->content = $content;
        $this->subtype = $subtype;
        $this->scope   = $scope;
        $this->data    = $data;
        $this->objectify();
    }
}
