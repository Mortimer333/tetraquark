<?php
namespace Tetraquark\Trait;

use Orator\Log;
use Tetraquark\Model\Block\{BlockModel, ScriptBlockModel};

/**
 * @codeCoverageIgnore
 */
trait ReaderDisplayTrait
{
    public function displayLandmarks(array $script): void
    {
        $landmarks = $this->getLandmarks($script);
        $this->showLandmark($landmarks);
    }

    public function showLandmark(array $landmarks): void
    {
        Log  ::log($landmarks['landmark']);
        if (!empty($landmarks['children'])) {
            Log  ::increaseIndent();
            foreach ($landmarks['children'] as $child) {
                $this->showLandmark($child);
            }
            Log  ::decreaseIndent();
        }
    }

    public function getLandmarks(array $script): array
    {
        $landmarks = [
            "landmark" => '',
            "data" => [],
            "children" => []
        ];

        foreach ($script['data'] ?? [] as $key => $value) {
            if (!is_array($value)) {
                $landmarks["data"][] = $key . ': `' . $value . '`';
            }
        }

        foreach ($script as $key => $block) {
            if ($key == 'parent') {
                continue;
            }
            if ($key === 'landmark') {
                $landmark = $block['_custom']['class'] ?? 'No class';
                $landmark .= ' {';
                unset($block['_custom']['class']);
                $i = 0;
                foreach ($block['_custom'] ?? [] as $key => $value) {
                    if ($i === 0) {
                        $landmark .= $key . ': ' . $value;
                    } else {
                        $landmark .= ', ' . $key . ': ' . $value;
                    }
                    $i++;
                }
                $landmark .= '}';
                $landmarks["landmark"] = $landmark ?? 'No class';
                $block = $block['_custom'] ?? [];
            }
            if ($block instanceof BlockModel) {
                $block = $block->toArray();
            }
            if (is_array($block)) {
                $res = $this->getLandmarks($block);
                if (empty($res['landmark'])) {
                    $landmarks["children"] = array_merge($landmarks["children"], $res['children']);
                } else {
                    $landmarks["children"][] = $res;
                }
            }
        }
        if (!empty($landmarks['data'])) {
            $landmarks['landmark'] .= ' [' . implode(', ', $landmarks['data']) . ']';
        }
        return $landmarks;
    }

    public function displayScriptBlocks(array $script, bool $item = true): void
    {
        Log  ::increaseIndent();
        foreach ($script as $key => $block) {
            if (is_array($block) || $block instanceof BlockModel) {
                Log  ::log('[');
                Log  ::increaseIndent();
            }
            if ($block instanceof BlockModel) {
                $this->displayBlock($block);
            } else if (is_array($block)) {
                Log  ::log('"' . $key . '" => [');
                $this->displayScriptBlocks($block, true);
                Log  ::log('],');
            } else {
                if (is_numeric($key)) {
                    Log  ::log(json_encode($block, JSON_PRETTY_PRINT) . ',', replaceNewLine: false);
                } else {
                    Log  ::log('"' . $key . '" => ' . json_encode($block, JSON_PRETTY_PRINT) . ',', replaceNewLine: false);
                }
            }
            if (is_array($block) || $block instanceof BlockModel) {
                Log  ::decreaseIndent();
                Log  ::log('],');
            }
        }
        Log  ::decreaseIndent();
    }

    public function displayBlock(BlockModel $block): void
    {
        foreach ($block->toArray() as $key => $value) {
            if ($key === 'children') {
                Log  ::log('"' . $key . '" => [');
                $this->displayScriptBlocks($value);
                Log  ::log('],');
            } elseif ($key === 'parent' && !is_null($value)) {
                if ($value instanceof ScriptBlockModel) {
                    Log  ::log('"' . $key . '" => script');
                } else {
                    Log  ::log('"' . $key . '" => parent[' . $value?->getIndex() . ']');
                }
            } else {
                if ($key == 'landmark') {
                    Log  ::log('"' . $key . '" => [');
                    $this->displayScriptBlocks($value['_custom'] ?? []);
                    Log  ::log('],');
                } elseif (is_array($value)) {
                    Log  ::log('"' . $key . '" => [');
                    $this->displayScriptBlocks($value);
                    Log  ::log('],');
                } else {
                    Log  ::log('"' . $key . '" => ' . json_encode($value, JSON_PRETTY_PRINT) . ',', replaceNewLine: false);
                }
            }
        }
    }
}
