<?php declare(strict_types=1);

namespace Tetraquark\Analyzer\JavaScript;

use Tetraquark\Analyzer\JavaScript\Util\LandmarkStorage;

/**
 * @codeCoverageIgnore
 */
abstract class Instruction
{
    public static function get(): array
    {
        return [
            // /* SINGLE LINE COMMENT */
            // "\/\//find:\n::'comment'\\" => [
            //     "class" => "SingleCommentBlock"
            // ],
            // /* MULTI LINE COMMENT */
            // "\/*/find:'*/'::'comment'\\" => [
            //     "class" => "MultiCommentBlock"
            // ],
            ...LandmarkStorage::getIfAndShortIf(),
            ...LandmarkStorage::getClassDefinition(),
            ...LandmarkStorage::getVariableDefinitions(),
            ...LandmarkStorage::getVariable(),
            ...LandmarkStorage::getStaticVariable(),
            ...LandmarkStorage::getSpreadVariable(),
            ...LandmarkStorage::getArrayAndDeconstructionAssignment(),
            ...LandmarkStorage::getSpreadArray(),
            ...LandmarkStorage::getApostrophe(),
            ...LandmarkStorage::getTemplateLiteral(),
            ...LandmarkStorage::getQuote(),
            ...LandmarkStorage::getComma(),
            ...LandmarkStorage::getArrowFunctionWithAsync(),
            ...LandmarkStorage::getKeyword(),
            ...LandmarkStorage::getClassGenerator(),
            ...LandmarkStorage::getMethodAndCaller(),
            ...LandmarkStorage::getConsecutiveCaller(),
            ...LandmarkStorage::getGetter(),
            ...LandmarkStorage::getSetter(),
            ...LandmarkStorage::getAsync(),
            ...LandmarkStorage::getStaticGetter(),
            ...LandmarkStorage::getStaticSetter(),
            ...LandmarkStorage::getStaticAsync(),
            ...LandmarkStorage::getTry(),
            ...LandmarkStorage::getCatch(),
            ...LandmarkStorage::getFinally(),
            ...LandmarkStorage::getFirstInChain(),
            ...LandmarkStorage::getNextInChain(),
            ...LandmarkStorage::getThis(),
            ...LandmarkStorage::getEqual(),
            ...LandmarkStorage::getUnequal(),
            ...LandmarkStorage::getDoWhile(),
            ...LandmarkStorage::getWhileAndShortWhile(),
            ...LandmarkStorage::getElseAndElseIf(),
            ...LandmarkStorage::getFalse(),
            ...LandmarkStorage::getTrue(),
            ...LandmarkStorage::getForAndShortFor(),
            ...LandmarkStorage::getForOfAndInCondition(),
            ...LandmarkStorage::getFunctionAndGenerator(),
            ...LandmarkStorage::getNewInstance(),
            ...LandmarkStorage::getObjectAndSpreadObject(),
            ...LandmarkStorage::getReturn(),
            ...LandmarkStorage::getSwitchAndCases(),
            ...LandmarkStorage::getSymbol(),
            ...LandmarkStorage::getYeld(),
            ...LandmarkStorage::getScope(),
            ...LandmarkStorage::getImport(),
            ...LandmarkStorage::getExport(),
            ...LandmarkStorage::getNumber(),
        ];
    }
}
