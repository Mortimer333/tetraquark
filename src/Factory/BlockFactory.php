<?php

namespace Tetraquark\Factory;
use \Tetraquark\Trait\{BlockValidateTrait};

class BlockFactory
{

    public static function create()
    {
        $prefix = 'Tetraquark\Block\\';
        $class  = $prefix . $className;

        if (!\class_exists($class)) {
            throw new Exception("Passed class doesn't exist: " . htmlspecialchars($className), 404);
        }

        // If this is variable creatiion without any type declaration then its attribute assignment and we shouldn't add anything before it
        if ($hint == '=') {
            $hint = '';
        }

        if ($class == Block\ChainLinkBlock::class) {
            $lastBlock = $blocks[\sizeof($blocks) - 1] ?? null;

            $first = false;
            // Check if we are not between some equasion with at least two ChainBlocks
            if ($lastBlock) {
                $startBlock = $lastBlock->getInstructionStart() + \mb_strlen($lastBlock->getInstruction());
                for ($i=$startBlock; $i < \mb_strlen(self::$content); $i++) {
                    $letter = self::$content[$i];
                    if ($letter == ' ') {
                        continue;
                    }
                    if (Validate::isSpecial($letter) || $letter == "\n") {
                        $first = true;
                    }
                    break;
                }
            }

            if (
                $first
                || !($lastBlock instanceof Block\ChainLinkBlock)
                || (
                    $lastBlock instanceof Block\ChainLinkBlock
                    && (
                        $lastBlock->getSubtype() == Block\ChainLinkBlock::END_METHOD
                        || $lastBlock->getSubtype() == Block\ChainLinkBlock::END_VARIABLE
                    )
                )
            ) {
                $block = new $class($start, Block\ChainLinkBlock::FIRST);

                $possibleUndefined = \mb_substr($possibleUndefined, 0, -(\mb_strlen($block->getInstruction()) + 1));
                if (Validate::isValidUndefined($possibleUndefined)) {
                    $blocks[] = new Block\UndefinedBlock($start - \mb_strlen($possibleUndefined), $possibleUndefined);
                }

                $blocks[] = $block;
                $possibleUndefined = '';
            }
            return new $class($start + 1, $hint);
        }
        return new $class($start, $hint);
    }
}
