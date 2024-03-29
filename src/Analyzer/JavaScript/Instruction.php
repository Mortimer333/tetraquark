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
            ...LandmarkStorage::getFalse(),
            ...LandmarkStorage::getTrue(),
            ...LandmarkStorage::getIfAndShortIf(),
            ...LandmarkStorage::getClassDefinition(),
            ...LandmarkStorage::getApostrophe(),
            ...LandmarkStorage::getTemplateLiteral(),
            ...LandmarkStorage::getQuote(),
            ...LandmarkStorage::getComma(),
            ...LandmarkStorage::getKeyword(),
            ...LandmarkStorage::getThis(),
            ...LandmarkStorage::getEqual(),
            ...LandmarkStorage::getUnequal(),
            ...LandmarkStorage::getSpreadVariable(),
            ...LandmarkStorage::getArrayAndDeconstructionAssignment(),
            ...LandmarkStorage::getSpreadArray(),
            ...LandmarkStorage::getNewInstance(),
            ...LandmarkStorage::getSymbol(),
            ...LandmarkStorage::getYield(),
            ...LandmarkStorage::getVariableDefinitions(),
            ...LandmarkStorage::getVariable(),
            ...LandmarkStorage::getScope(),
            ...LandmarkStorage::getNumber(),
            ...LandmarkStorage::getStaticVariable(),
            ...LandmarkStorage::getArrowFunctionWithAsync(),
            ...LandmarkStorage::getConstantMethodAndClassMethodAndCaller(),
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
            ...LandmarkStorage::getArrayChain(),
            ...LandmarkStorage::getDoWhile(),
            ...LandmarkStorage::getWhileAndShortWhile(),
            ...LandmarkStorage::getElseAndElseIf(),
            ...LandmarkStorage::getForAndShortFor(),
            ...LandmarkStorage::getForOfAndInCondition(),
            ...LandmarkStorage::getFunctionAndGenerator(),
            ...LandmarkStorage::getObjectAndSpreadObject(),
            ...LandmarkStorage::getReturn(),
            ...LandmarkStorage::getSwitchAndCases(), // <-
            ...LandmarkStorage::getImport(),
            ...LandmarkStorage::getExport(),
        ];
    }
}
